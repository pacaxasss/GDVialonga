<?php
require_once __DIR__ . '/functions.php';

function fpfRequest(string $path): string {
    $url = rtrim(FPF_BASE_URL, '/') . $path;
    $lastError = '';

    for ($attempt = 0; $attempt < 3; $attempt++) {
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_USERAGENT => SITE_NAME . ' results sync',
        ]);

        $body = curl_exec($curl);
        $status = (int)curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $lastError = curl_error($curl);
        curl_close($curl);

        if ($body !== false && $status >= 200 && $status < 300) return $body;
        if ($body !== false) $lastError = 'HTTP ' . $status;
        if ($attempt < 2) usleep(250000);
    }

    throw new RuntimeException('Falha ao contactar a FPF: ' . $lastError);
}

function fpfParseHtml(string $html): array {
    $document = new DOMDocument();
    $previous = libxml_use_internal_errors(true);
    $loaded = $document->loadHTML($html);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    if (!$loaded) {
        throw new RuntimeException('Não foi possível interpretar a resposta da FPF.');
    }

    return [$document, new DOMXPath($document)];
}

function fpfText(DOMXPath $xpath, string $query, DOMNode $context): string {
    $node = $xpath->query($query, $context)?->item(0);
    return trim($node?->textContent ?? '');
}

function fpfMatchDate(string $sourceDate, string $season): string {
    if (!preg_match('/^(\d{1,2})\s+([[:alpha:]\x{00C0}-\x{024F}.]+)$/ui', trim($sourceDate), $parts)) {
        throw new RuntimeException('Data de jogo FPF inválida: ' . $sourceDate);
    }

    $monthName = mb_strtolower(rtrim($parts[2], '.'), 'UTF-8');
    $months = [
        'jan' => 1, 'fev' => 2, 'mar' => 3, 'abr' => 4,
        'mai' => 5, 'jun' => 6, 'jul' => 7, 'ago' => 8,
        'set' => 9, 'out' => 10, 'nov' => 11, 'dez' => 12,
    ];
    $month = $months[mb_substr($monthName, 0, 3, 'UTF-8')] ?? null;
    if ($month === null || !preg_match('/^(\d{4})\/(\d{2})$/', $season, $seasonParts)) {
        throw new RuntimeException('Época ou mês FPF inválido.');
    }

    $year = (int)$seasonParts[1] + ($month < 7 ? 1 : 0);
    return sprintf('%04d-%02d-%02d', $year, $month, (int)$parts[1]);
}

function fpfShortName(string $team): string {
    if (mb_stripos($team, 'vialonga', 0, 'UTF-8') !== false) return 'GDV';

    $words = preg_split('/\s+/u', trim($team), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $letters = '';
    foreach ($words as $word) {
        $letters .= mb_substr(preg_replace('/[^\p{L}\p{N}]/u', '', $word), 0, 1, 'UTF-8');
        if (mb_strlen($letters, 'UTF-8') >= 3) break;
    }

    return mb_strtoupper(mb_substr($letters ?: $team, 0, 3, 'UTF-8'), 'UTF-8');
}

function syncFpfResults(string $competitionId = 'distrital-honra', string $season = SEASON): array {
    if ($competitionId !== 'distrital-honra' || $season !== SEASON) {
        throw new InvalidArgumentException('A sincronização FPF está configurada apenas para a competição principal e época ativa.');
    }

    $competitionUrl = '/Competition/Details?competitionId=' . rawurlencode((string)FPF_COMPETITION_ID)
        . '&seasonId=' . rawurlencode((string)FPF_SEASON_ID);
    $competitionHtml = fpfRequest($competitionUrl);
    [$competitionDocument, $competitionXPath] = fpfParseHtml($competitionHtml);

    $selectedSeason = $competitionXPath->query('//select[@id="SeasonId"]/option[@selected]')->item(0);
    if (!$selectedSeason || $selectedSeason->getAttribute('value') !== (string)FPF_SEASON_ID) {
        throw new RuntimeException('A FPF não confirmou a época configurada; os dados locais foram mantidos.');
    }
    $rounds = [];
    $totalRounds = 0;
    foreach ($competitionXPath->query('//a[contains(@href, "GetClassificationAndMatchesByFixture")]') as $link) {
        $totalRounds++;
        $roundState = preg_split('/\s+/', trim($link->getAttribute('class'))) ?: [];
        if (!array_intersect($roundState, ['past', 'current'])) continue;
        if (preg_match('/fixtureId=(\d+)/', $link->getAttribute('href'), $parts)) {
            $round = (int)trim($link->textContent);
            if ($round > 0) $rounds[$round] = $parts[1];
        }
    }
    ksort($rounds);
    if ($totalRounds < 20 || empty($rounds)) {
        throw new RuntimeException('A FPF devolveu poucas jornadas ou não marcou rondas publicadas; os dados locais foram mantidos.');
    }

    $matches = [];
    $officialStandings = [];
    foreach ($rounds as $round => $fixtureId) {
        $html = fpfRequest('/Competition/GetClassificationAndMatchesByFixture?fixtureId=' . rawurlencode($fixtureId));
        [, $xpath] = fpfParseHtml($html);

        $roundStandings = [];
        foreach ($xpath->query('//div[contains(concat(" ", normalize-space(@class), " "), " classification ")]') as $row) {
            $cells = [];
            foreach ($row->childNodes as $cell) {
                if ($cell instanceof DOMElement) $cells[] = trim($cell->textContent);
            }
            if (count($cells) !== 9 || !ctype_digit($cells[0]) || !ctype_digit($cells[2])) continue;
            $teamName = $cells[1];
            if ($teamName === '') continue;
            $roundStandings[mb_strtolower($teamName, 'UTF-8')] = [
                'position' => (int)$cells[0],
                'team' => $teamName,
                'short' => fpfShortName($teamName),
                'played' => (int)$cells[2],
                'won' => (int)$cells[3],
                'drawn' => (int)$cells[4],
                'lost' => (int)$cells[5],
                'goals_for' => (int)$cells[6],
                'goals_against' => (int)$cells[7],
                'points' => (int)$cells[8],
                'is_gdv' => (int)(mb_stripos($teamName, 'vialonga', 0, 'UTF-8') !== false),
            ];
        }
        if (count($roundStandings) >= count($officialStandings)) {
            $officialStandings = $roundStandings;
        }

        foreach ($xpath->query('//a[contains(concat(" ", normalize-space(@class), " "), " game-link ")]') as $link) {
            if (!preg_match('/matchId=(\d+)/', $link->getAttribute('href'), $idParts)) continue;

            $homeTeam = fpfText($xpath, './/div[contains(concat(" ", normalize-space(@class), " "), " home-team ")]', $link);
            $awayTeam = fpfText($xpath, './/div[contains(concat(" ", normalize-space(@class), " "), " away-team ")]', $link);
            $scoreText = fpfText($xpath, './/div[contains(concat(" ", normalize-space(@class), " "), " score ")]/span[1]', $link);
            $sourceDate = fpfText($xpath, './/span[contains(concat(" ", normalize-space(@class), " "), " game-schedule ")]', $link);
            $venue = fpfText($xpath, './/div[contains(concat(" ", normalize-space(@class), " "), " game-list-stadium ")]//small', $link);

            if ($homeTeam === '' || $awayTeam === '' || $sourceDate === '') {
                throw new RuntimeException('A FPF devolveu um jogo incompleto; os dados locais foram mantidos.');
            }

            $homeScore = null;
            $awayScore = null;
            $status = 'scheduled';
            if (preg_match('/^\s*(\d+)\s*[-–]\s*(\d+)\s*$/u', $scoreText, $scoreParts)) {
                $homeScore = (int)$scoreParts[1];
                $awayScore = (int)$scoreParts[2];
                $status = 'finished';
            } elseif (preg_match('/adiad|cancel|anulad/i', $scoreText)) {
                $status = 'cancelled';
            }

            $homeIsGdv = (int)(mb_stripos($homeTeam, 'vialonga', 0, 'UTF-8') !== false);
            $awayIsGdv = (int)(mb_stripos($awayTeam, 'vialonga', 0, 'UTF-8') !== false);
            $matchTime = '';
            if ($homeIsGdv || $awayIsGdv) {
                $detailsHtml = fpfRequest('/Match/GetMatchInformation?matchId=' . rawurlencode($idParts[1]));
                [$detailsDocument] = fpfParseHtml($detailsHtml);
                $detailsText = preg_replace('/\s+/u', ' ', $detailsDocument->textContent) ?? '';
                if (preg_match('/Hora:\s*(\d{1,2}:\d{2})/u', $detailsText, $timeParts)) {
                    $matchTime = $timeParts[1];
                }
            }

            $matches[$idParts[1]] = [
                'round' => $round,
                'home_team' => $homeTeam,
                'home_short' => fpfShortName($homeTeam),
                'home_is_gdv' => $homeIsGdv,
                'away_team' => $awayTeam,
                'away_short' => fpfShortName($awayTeam),
                'away_is_gdv' => $awayIsGdv,
                'match_date' => fpfMatchDate($sourceDate, $season),
                'match_time' => $matchTime,
                'venue' => $venue,
                'status' => $status,
                'home_score' => $homeScore,
                'away_score' => $awayScore,
                'scorers' => '[]',
            ];
        }
    }

    $gdvMatches = array_filter($matches, static fn(array $match): bool => $match['home_is_gdv'] || $match['away_is_gdv']);
    if (count($matches) < 16 || count($gdvMatches) < 1 || count($officialStandings) < 16) {
        throw new RuntimeException(sprintf(
            'Lote FPF incompleto: %d jogos, %d jornadas, %d jogos do GDV e %d equipas na tabela; os dados locais foram mantidos.',
            count($matches),
            count($rounds),
            count($gdvMatches),
            count($officialStandings)
        ));
    }

    $db = getDB();
    $db->beginTransaction();
    try {
        $db->prepare('DELETE FROM matches WHERE competition_id=? AND season=?')->execute([$competitionId, $season]);
        $db->prepare('DELETE FROM standings WHERE competition_id=? AND season=?')->execute([$competitionId, $season]);
        $db->prepare('UPDATE competitions SET name=?,short=?,squad=?,season=? WHERE id=?')
            ->execute([FPF_COMPETITION_NAME, 'AF Lisboa I', 'Seniores', $season, $competitionId]);

        $insert = $db->prepare('INSERT INTO matches
            (competition_id,season,round,home_team,home_short,home_is_gdv,away_team,away_short,away_is_gdv,
             match_date,match_time,venue,status,home_score,away_score,scorers)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
        foreach ($matches as $match) {
            $insert->execute([
                $competitionId, $season, $match['round'], $match['home_team'], $match['home_short'],
                $match['home_is_gdv'], $match['away_team'], $match['away_short'], $match['away_is_gdv'],
                $match['match_date'], $match['match_time'], $match['venue'], $match['status'],
                $match['home_score'], $match['away_score'], $match['scorers'],
            ]);
        }

        $insertTeam = $db->prepare('INSERT INTO standings (competition_id,season,position,team,short,is_gdv) VALUES (?,?,?,?,?,?)');
        foreach ($officialStandings as $team) {
            $insertTeam->execute([$competitionId, $season, $team['position'], $team['team'], $team['short'], $team['is_gdv']]);
        }
        $db->commit();
    } catch (Throwable $error) {
        if ($db->inTransaction()) $db->rollBack();
        throw $error;
    }

    recalculateStandings($competitionId, $season);

    $updateOfficial = $db->prepare('UPDATE standings SET position=?,played=?,won=?,drawn=?,lost=?,goals_for=?,goals_against=?,points=? WHERE competition_id=? AND season=? AND team=?');
    foreach ($officialStandings as $team) {
        $updateOfficial->execute([
            $team['position'], $team['played'], $team['won'], $team['drawn'], $team['lost'],
            $team['goals_for'], $team['goals_against'], $team['points'], $competitionId, $season, $team['team'],
        ]);
    }

    return [
        'matches' => count($matches),
        'finished' => count(array_filter($matches, static fn(array $match): bool => $match['status'] === 'finished')),
        'scheduled' => count(array_filter($matches, static fn(array $match): bool => $match['status'] === 'scheduled')),
        'teams' => count(dbFetchAll('SELECT id FROM standings WHERE competition_id=? AND season=?', [$competitionId, $season])),
    ];
}