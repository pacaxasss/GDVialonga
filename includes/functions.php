<?php
/**
 * GDV — Funções públicas (usa SQLite via db.php)
 */
require_once __DIR__ . '/db.php';

// ── Datas ──────────────────────────────────────────
function formatDate(string $date, string $format = 'd/m/Y'): string {
    return $date ? date($format, strtotime($date)) : '';
}
function formatDateLong(string $date): string {
    if (!$date) return '';
    $months = [1=>'janeiro',2=>'fevereiro',3=>'março',4=>'abril',5=>'maio',6=>'junho',
               7=>'julho',8=>'agosto',9=>'setembro',10=>'outubro',11=>'novembro',12=>'dezembro'];
    $ts = strtotime($date);
    return date('j', $ts) . ' de ' . $months[(int)date('n', $ts)] . ' de ' . date('Y', $ts);
}

// ── Labels ─────────────────────────────────────────
function nationalityLabel(string $code): string {
    $map = [
        'PT' => ['flag' => '🇵🇹', 'name' => 'Portugal'],
        'GW' => ['flag' => '🇬🇼', 'name' => 'Guiné-Bissau'],
        'SN' => ['flag' => '🇸🇳', 'name' => 'Senegal'],
        'CV' => ['flag' => '🇨🇻', 'name' => 'Cabo Verde'],
        'BR' => ['flag' => '🇧🇷', 'name' => 'Brasil'],
        'AO' => ['flag' => '🇦🇴', 'name' => 'Angola'],
        'ST' => ['flag' => '🇸🇹', 'name' => 'São Tomé e Príncipe'],
        'MZ' => ['flag' => '🇲🇿', 'name' => 'Moçambique'],
    ];
    $code  = strtoupper(trim($code));
    $entry = $map[$code] ?? ['flag' => '🏳️', 'name' => $code];
    return trim($entry['flag'] . ' ' . $entry['name']);
}

function positionLabel(string $p): string {
    return match($p) {
        'goalkeeper'=>'Guarda-Redes','defender'=>'Defesa',
        'midfielder'=>'Médio','forward'=>'Avançado',
        'coach'=>'Equipa Técnica','staff'=>'Staff', default=>ucfirst($p)
    };
}
function categoryLabel(string $c): string {
    $label = match($c) {
        'clube'=>'Clube','seniores'=>'Seniores','formacao'=>'Formação',
        'regiao'=>'Vialonga & Região','comunicado'=>'Comunicado', default=>ucfirst($c)
    };
    return htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function categoryClass(string $c): string {
    return match($c) {
        'clube'=>'cat-clube','seniores'=>'cat-seniores','formacao'=>'cat-formacao',
        'regiao'=>'cat-regiao','comunicado'=>'cat-comunicado', default=>'cat-default'
    };
}
function formBadgeClass(string $r): string {
    return match($r) { 'W'=>'form-w','D'=>'form-d','L'=>'form-l', default=>'form-d' };
}
function formBadgeText(string $r): string {
    return match($r) { 'W'=>'V','D'=>'E','L'=>'D', default=>'-' };
}
function truncate(string $text, int $len = 120): string {
    $text = strip_tags($text);
    if (strlen($text) <= $len) return $text;
    return substr($text, 0, strrpos(substr($text, 0, $len), ' ')) . '…';
}

function sanitizeNewsHtml(string $html): string {
    if (trim($html) === '') return '';

    $document = new DOMDocument('1.0', 'UTF-8');
    $previous = libxml_use_internal_errors(true);
    $loaded = $document->loadHTML(
        '<?xml encoding="UTF-8"><div id="gdv-news-root">' . $html . '</div>',
        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
    );
    libxml_clear_errors();
    libxml_use_internal_errors($previous);
    if (!$loaded) return htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    $root = (new DOMXPath($document))->query('//*[@id="gdv-news-root"]')->item(0);
    if (!$root) return htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    $allowedTags = ['p', 'br', 'strong', 'b', 'em', 'i', 'u', 'ul', 'ol', 'li', 'h2', 'h3', 'blockquote', 'a'];
    $blockedTags = ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'svg', 'math', 'template'];
    $clean = function (DOMNode $parent) use (&$clean, $allowedTags, $blockedTags): void {
        for ($child = $parent->firstChild; $child;) {
            $next = $child->nextSibling;
            if ($child instanceof DOMComment || $child instanceof DOMProcessingInstruction) {
                $parent->removeChild($child);
            } elseif ($child instanceof DOMElement) {
                $tag = strtolower($child->tagName);
                if (in_array($tag, $blockedTags, true)) {
                    $parent->removeChild($child);
                } else {
                    $clean($child);
                    foreach (iterator_to_array($child->attributes) as $attribute) {
                        $name = strtolower($attribute->name);
                        if ($tag !== 'a' || $name !== 'href') $child->removeAttributeNode($attribute);
                    }
                    if ($tag === 'a') {
                        $href = trim($child->getAttribute('href'));
                        $scheme = strtolower((string)parse_url($href, PHP_URL_SCHEME));
                        if ($href === '' || preg_match('/[\x00-\x20\\\\]/', $href) || str_starts_with($href, '//')
                            || ($scheme !== '' && !in_array($scheme, ['http', 'https', 'mailto'], true))) {
                            $child->removeAttribute('href');
                        }
                        $child->setAttribute('rel', 'nofollow noopener noreferrer');
                    }
                    if (!in_array($tag, $allowedTags, true)) {
                        while ($child->firstChild) $parent->insertBefore($child->firstChild, $child);
                        $parent->removeChild($child);
                    }
                }
            } elseif ($child->nodeType !== XML_TEXT_NODE) {
                $parent->removeChild($child);
            }
            $child = $next;
        }
    };
    $clean($root);

    $result = '';
    foreach ($root->childNodes as $child) $result .= $document->saveHTML($child);
    return $result;
}

function safeNewsImagePath(string $path): string {
    $path = ltrim(trim($path), '/');
    if (str_contains($path, '..') || !preg_match('~^assets/images/[A-Za-z0-9._/-]+$~', $path)) {
        return 'assets/images/hero_stadium.png';
    }
    return $path;
}

// ── Notícias ───────────────────────────────────────
function getNews(string $category = '', int $limit = 100): array {
    if ($category && $category !== 'all') {
        return dbFetchAll(
            'SELECT * FROM news WHERE is_published=1 AND category=? ORDER BY published_at DESC LIMIT ?',
            [$category, $limit]
        );
    }
    return dbFetchAll('SELECT * FROM news WHERE is_published=1 ORDER BY published_at DESC LIMIT ?', [$limit]);
}
function getNewsBySlug(string $slug): ?array {
    return dbFetch('SELECT * FROM news WHERE slug=? AND is_published=1', [$slug]);
}

// ── Jogos ──────────────────────────────────────────
function getAllMatches(string $compId = '', string $season = SEASON): array {
    if ($compId) {
        return dbFetchAll(
            'SELECT * FROM matches WHERE competition_id=? AND season=? ORDER BY match_date ASC, round ASC',
            [$compId, $season]
        );
    }
    return dbFetchAll('SELECT * FROM matches WHERE season=? ORDER BY match_date ASC', [$season]);
}
function getNextMatch(): ?array {
    $row = dbFetch(
        "SELECT * FROM matches WHERE status='scheduled' AND (home_is_gdv=1 OR away_is_gdv=1)
         AND match_date >= ? ORDER BY match_date ASC LIMIT 1",
        [date('Y-m-d')]
    );
    return $row ? normalizeMatch($row) : null;
}
function getLastResult(): ?array {
    $row = dbFetch(
        "SELECT * FROM matches WHERE status='finished' AND (home_is_gdv=1 OR away_is_gdv=1)
         ORDER BY match_date DESC LIMIT 1"
    );
    return $row ? normalizeMatch($row) : null;
}
function normalizeMatch(array $r): array {
    $r['home_team'] = ['name'=>$r['home_team'],'short'=>$r['home_short'],'is_gdv'=>(bool)$r['home_is_gdv']];
    $r['away_team'] = ['name'=>$r['away_team'],'short'=>$r['away_short'],'is_gdv'=>(bool)$r['away_is_gdv']];
    $r['scorers']   = json_decode($r['scorers'] ?? '[]', true);
    $r['date']      = $r['match_date'];
    $r['time']      = $r['match_time'];
    return $r;
}
function getMatchesForPage(string $compId): array {
    return array_map('normalizeMatch', getAllMatches($compId));
}

// ── Classificação ───────────────────────────────────
function getStandings(string $compId = 'distrital-honra', string $season = SEASON): array {
    $rows = dbFetchAll(
        'SELECT * FROM standings WHERE competition_id=? AND season=? ORDER BY position ASC',
        [$compId, $season]
    );
    foreach ($rows as &$r) {
        $r['form']      = json_decode($r['form'] ?? '[]', true);
        $r['goal_diff'] = $r['goals_for'] - $r['goals_against'];
        $r['is_gdv']    = (bool)$r['is_gdv'];
    }
    return $rows;
}

function recalculateStandings(string $compId, string $season): void {
    $existingRows = dbFetchAll(
        'SELECT * FROM standings WHERE competition_id=? AND season=?',
        [$compId, $season]
    );
    $matches = dbFetchAll(
        'SELECT * FROM matches WHERE competition_id=? AND season=? ORDER BY match_date ASC, round ASC, id ASC',
        [$compId, $season]
    );
    $teams = [];

    foreach ($existingRows as $row) {
        $key = mb_strtolower(trim($row['team']), 'UTF-8');
        if ($key === '') continue;
        $teams[$key] = [
            'id' => (int)$row['id'],
            'team' => $row['team'],
            'short' => $row['short'] ?? '',
            'is_gdv' => (int)$row['is_gdv'],
            'played' => 0,
            'won' => 0,
            'drawn' => 0,
            'lost' => 0,
            'goals_for' => 0,
            'goals_against' => 0,
            'points' => 0,
            'form' => [],
        ];
    }

    foreach ($matches as $match) {
        foreach ([
            [$match['home_team'], $match['home_short'], $match['home_is_gdv']],
            [$match['away_team'], $match['away_short'], $match['away_is_gdv']],
        ] as [$name, $short, $isGdv]) {
            $key = mb_strtolower(trim($name), 'UTF-8');
            if ($key === '' || isset($teams[$key])) continue;
            $teams[$key] = [
                'id' => null,
                'team' => $name,
                'short' => $short ?? '',
                'is_gdv' => (int)$isGdv,
                'played' => 0,
                'won' => 0,
                'drawn' => 0,
                'lost' => 0,
                'goals_for' => 0,
                'goals_against' => 0,
                'points' => 0,
                'form' => [],
            ];
        }

        if ($match['status'] !== 'finished' || $match['home_score'] === null || $match['away_score'] === null) {
            continue;
        }

        $homeKey = mb_strtolower(trim($match['home_team']), 'UTF-8');
        $awayKey = mb_strtolower(trim($match['away_team']), 'UTF-8');
        if ($homeKey === $awayKey || !isset($teams[$homeKey], $teams[$awayKey])) continue;

        $homeGoals = (int)$match['home_score'];
        $awayGoals = (int)$match['away_score'];
        $homeOutcome = $homeGoals <=> $awayGoals;

        foreach ([
            [$homeKey, $homeGoals, $awayGoals, $homeOutcome],
            [$awayKey, $awayGoals, $homeGoals, -$homeOutcome],
        ] as [$key, $goalsFor, $goalsAgainst, $outcome]) {
            $teams[$key]['played']++;
            $teams[$key]['goals_for'] += $goalsFor;
            $teams[$key]['goals_against'] += $goalsAgainst;
            if ($outcome > 0) {
                $teams[$key]['won']++;
                $teams[$key]['points'] += 3;
                $teams[$key]['form'][] = 'W';
            } elseif ($outcome === 0) {
                $teams[$key]['drawn']++;
                $teams[$key]['points']++;
                $teams[$key]['form'][] = 'D';
            } else {
                $teams[$key]['lost']++;
                $teams[$key]['form'][] = 'L';
            }
        }
    }

    $teams = array_values($teams);
    usort($teams, static function (array $a, array $b): int {
        return ($b['points'] <=> $a['points'])
            ?: (($b['goals_for'] - $b['goals_against']) <=> ($a['goals_for'] - $a['goals_against']))
            ?: ($b['goals_for'] <=> $a['goals_for'])
            ?: ($b['won'] <=> $a['won'])
            ?: strnatcasecmp($a['team'], $b['team']);
    });

    $db = getDB();
    $db->beginTransaction();
    try {
        $update = $db->prepare('UPDATE standings SET position=?,played=?,won=?,drawn=?,lost=?,goals_for=?,goals_against=?,points=?,form=? WHERE id=?');
        $insert = $db->prepare('INSERT INTO standings (competition_id,season,position,team,short,played,won,drawn,lost,goals_for,goals_against,points,form,is_gdv) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)');

        foreach ($teams as $index => $team) {
            $values = [
                $index + 1,
                $team['played'],
                $team['won'],
                $team['drawn'],
                $team['lost'],
                $team['goals_for'],
                $team['goals_against'],
                $team['points'],
                json_encode(array_slice($team['form'], -5)),
            ];
            if ($team['id'] !== null) {
                $update->execute([...$values, $team['id']]);
            } else {
                $insert->execute([
                    $compId, $season, $index + 1, $team['team'], $team['short'],
                    $team['played'], $team['won'], $team['drawn'], $team['lost'],
                    $team['goals_for'], $team['goals_against'], $team['points'],
                    json_encode(array_slice($team['form'], -5)), $team['is_gdv'],
                ]);
            }
        }
        $db->commit();
    } catch (Throwable $error) {
        if ($db->inTransaction()) $db->rollBack();
        throw $error;
    }
}

// ── Competições ────────────────────────────────────
function getCompetitions(string $season = SEASON): array {
    return dbFetchAll('SELECT * FROM competitions WHERE season=?', [$season]);
}

// ── Patrocinadores ─────────────────────────────────
function getSponsors(): array {
    return dbFetchAll(
        "SELECT * FROM sponsors WHERE active=1
         ORDER BY CASE tier WHEN 'principal' THEN 1 WHEN 'gold' THEN 2 WHEN 'silver' THEN 3 ELSE 4 END"
    );
}

// ── Fallback JSON (jogadores ainda em JSON) ────────
function loadData(string $filename): array {
    $path = __DIR__ . '/../data/' . $filename;
    if (!file_exists($path)) return [];
    return json_decode(file_get_contents($path), true) ?? [];
}

// ── Helpers de URLs e Rotas ─────────────────────────
function getBasePath(): string {
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $dir = dirname($script);
    if (str_ends_with($dir, '/pages') || str_ends_with($dir, '/admin')) {
        $dir = dirname($dir);
    }
    return rtrim($dir, '/') . '/';
}

function url(string $path = ''): string {
    $base = getBasePath();
    $path = ltrim($path, '/');
    if ($path === '' || $path === 'index.php') {
        return $base;
    }
    return $base . $path;
}

