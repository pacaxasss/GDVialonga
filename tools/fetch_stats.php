<?php
// Script final CORRIGIDO: extrai marcadores reais dos jogos do GD Vialonga 2026/27
// Lógica: lê .match-header-team para saber em qual lado (.left ou .right) está o GDV

$ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36';
$baseDir = dirname(__DIR__);
$playersJsonPath = $baseDir . '/data/players.json';

$data = json_decode(file_get_contents($playersJsonPath), true);

// Primeiro repõe todos os golos e assistências a 0 para recomeçar clean
foreach ($data['squads'] as &$squad) {
    if ($squad['id'] !== 'seniors') continue;
    foreach ($squad['players'] as &$p) {
        $p['goals']   = 0;
        $p['assists'] = 0;
    }
}

function zzGet(string $url, string $ua, string $referer): array {
    $c = curl_init($url);
    curl_setopt_array($c, [
        CURLOPT_RETURNTRANSFER => 1, CURLOPT_FOLLOWLOCATION => 1,
        CURLOPT_TIMEOUT => 25, CURLOPT_USERAGENT => $ua,
        CURLOPT_HTTPHEADER => ['Accept: text/html', 'Accept-Language: pt-PT,pt;q=0.9', 'Referer: ' . $referer],
        CURLOPT_SSL_VERIFYPEER => 0,
    ]);
    $body = (string)curl_exec($c);
    $code = (int)curl_getinfo($c, CURLINFO_RESPONSE_CODE);
    curl_close($c);
    return [$code, $body];
}

function normName(string $n): string {
    $n = mb_strtolower(trim(preg_replace('/\s+/', ' ', $n)), 'UTF-8');
    foreach (['à'=>'a','á'=>'a','â'=>'a','ã'=>'a','é'=>'e','è'=>'e','ê'=>'e',
              'í'=>'i','ó'=>'o','ô'=>'o','õ'=>'o','ú'=>'u','ç'=>'c','ñ'=>'n'] as $f=>$t) {
        $n = str_replace($f, $t, $n);
    }
    return $n;
}

// Build lookup: nome normalizado -> [squad_idx, player_idx]
$playerLookup = [];
foreach ($data['squads'] as $si => $squad) {
    if ($squad['id'] !== 'seniors') continue;
    foreach ($squad['players'] as $pi => $p) {
        $norm = normName($p['name']);
        $playerLookup[$norm] = [$si, $pi];

        $parts = explode(' ', $norm);
        // Último nome
        $last = end($parts);
        if (strlen($last) >= 4 && !isset($playerLookup[$last])) {
            $playerLookup[$last] = [$si, $pi];
        }
        // Primeiro + último
        if (count($parts) >= 2) {
            $fl = $parts[0] . ' ' . $last;
            if (!isset($playerLookup[$fl])) $playerLookup[$fl] = [$si, $pi];
        }
        // Primeiro nome só
        if (strlen($parts[0]) >= 4 && !isset($playerLookup[$parts[0]])) {
            $playerLookup[$parts[0]] = [$si, $pi];
        }
    }
}

function findPlayer(string $zzName, array $lookup): ?array {
    $norm = normName($zzName);
    if (isset($lookup[$norm])) return $lookup[$norm];

    $parts = explode(' ', $norm);
    $last  = end($parts);
    if (strlen($last) >= 4 && isset($lookup[$last])) return $lookup[$last];

    foreach ($lookup as $key => $idx) {
        if (strlen($key) < 4) continue;
        // Nome ZZ contém chave do lookup
        if (str_contains($norm, $key)) return $idx;
        // Chave contém último nome do ZZ
        if (strlen($last) >= 5 && str_contains($key, $last)) return $idx;
    }
    return null;
}

function extractNames(string $text): array {
    // Remove minutos: "73'" ou "90+3'"
    $clean = preg_replace('/\s*\d{1,3}\+?\d*\'[\'′]?\s*/u', "\n", $text);
    // Remove "(g.p.)", "(a.p.)", "(og)" etc
    $clean = preg_replace('/\s*\([^)]+\)\s*/u', ' ', $clean);
    $names = [];
    foreach (preg_split('/[\n\r]+/', $clean) as $line) {
        $line = trim($line);
        if (strlen($line) >= 3) $names[] = $line;
    }
    return $names;
}

// Jogos da época 2026/27
$matchUrls = [
    'https://www.zerozero.pt/jogo/2026-09-13-gd-vialonga-at-povoense/12626559',
    'https://www.zerozero.pt/jogo/2026-09-27-gd-vialonga-associacao-murteirense/12626576',
    'https://www.zerozero.pt/jogo/2026-10-04-fut-benfica-gd-vialonga/12626585',
    'https://www.zerozero.pt/jogo/2026-10-11-uniao-mucifalense-gd-vialonga/12626593',
    'https://www.zerozero.pt/jogo/2026-10-18-gd-vialonga-ad-oeiras/12626601',
    'https://www.zerozero.pt/jogo/2026-10-25-ericeirense-gd-vialonga/12626608',
];

$matchLog = [];
echo "=== Jogos GD Vialonga 2026/27 ===\n\n";

foreach ($matchUrls as $url) {
    echo "URL: $url\n";
    usleep(800000);

    [$code, $html] = zzGet($url, $ua, 'https://www.zerozero.pt/equipa/gd-vialonga/3671');
    if ($code !== 200 || strlen($html) < 2000) { echo "  ERRO $code\n\n"; continue; }

    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="UTF-8">' . $html);
    libxml_clear_errors();
    $xp = new DOMXPath($doc);

    // Descobre em que lado (.left ou .right) está o GDV
    $gdvSide = null;
    $teams   = $xp->query('//*[contains(@class,"match-header-team") and (contains(@class,"left") or contains(@class,"right"))]');
    for ($i = 0; $i < $teams->length; $i++) {
        $cls  = $teams->item($i)->getAttribute('class');
        $name = trim($teams->item($i)->textContent);
        if (mb_stripos($name, 'vialonga', 0, 'UTF-8') !== false) {
            if (str_contains($cls, 'right')) $gdvSide = 'right';
            if (str_contains($cls, 'left'))  $gdvSide = 'left';
            break;
        }
    }

    // Extrai resultado do header
    $resultEl = $xp->query('//*[contains(@class,"match-header-result") or contains(@class,"match-header-score")]')->item(0);
    $resultText = trim($resultEl?->textContent ?? '');

    echo "  GDV lado: " . ($gdvSide ?? 'NÃO DETERMINADO') . " | Result: " . preg_replace('/\s+/', ' ', $resultText) . "\n";

    if (!$gdvSide) { echo "  SKIP: lado do GDV não encontrado\n\n"; continue; }

    // Extrai marcadores do lado do GDV
    $scorerNodes = $xp->query("//div[contains(@class,'match-header-scorers') and contains(@class,'$gdvSide')]");
    $scorerText  = trim($scorerNodes->item(0)?->textContent ?? '');
    echo "  Marcadores GDV: '" . str_replace("\n", ' | ', $scorerText) . "'\n";

    $matchGoals = [];
    if (!empty($scorerText)) {
        $names = extractNames($scorerText);
        foreach ($names as $name) {
            if (empty($name)) continue;
            $idx = findPlayer($name, $playerLookup);
            if ($idx !== null) {
                [$si, $pi] = $idx;
                $data['squads'][$si]['players'][$pi]['goals']++;
                $pname = $data['squads'][$si]['players'][$pi]['name'];
                echo "    ⚽ GOLO: '$name' → $pname\n";
                $matchGoals[] = "$name → $pname";
            } else {
                echo "    ⚽ GOLO (não identificado): '$name'\n";
                $matchGoals[] = "? $name";
            }
        }
    }

    // Extrai assistências se disponíveis no texto
    $pageText = preg_replace('/\s+/', ' ', $doc->textContent ?? '');
    $matchAssists = [];
    // Padrão ZZ para assistências no jogo: "(Assist: Nome)" dentro dos scorer links
    $assistLinks = $xp->query("//div[contains(@class,'match-header-scorers') and contains(@class,'$gdvSide')]//a[@title]");
    for ($i = 0; $i < $assistLinks->length; $i++) {
        $title = trim($assistLinks->item($i)->getAttribute('title'));
        if (mb_stripos($title, 'assist', 0, 'UTF-8') !== false) {
            $assistName = preg_replace('/assist[^:]*:\s*/ui', '', $title);
            $assistName = trim($assistName);
            if ($assistName) {
                $idx = findPlayer($assistName, $playerLookup);
                if ($idx) {
                    [$si, $pi] = $idx;
                    $data['squads'][$si]['players'][$pi]['assists']++;
                    $pname = $data['squads'][$si]['players'][$pi]['name'];
                    echo "    🅰️ ASSIST: '$assistName' → $pname\n";
                    $matchAssists[] = "$assistName → $pname";
                }
            }
        }
    }

    $matchLog[] = compact('url', 'gdvSide', 'matchGoals', 'matchAssists');
    echo "\n";
}

// Guarda
file_put_contents($playersJsonPath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
file_put_contents($baseDir . '/tmp_match_log.json', json_encode($matchLog, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// Relatório
echo "\n=== ESTATÍSTICAS FINAIS 2026/27 ===\n";
printf("%-30s %-12s %5s %5s\n", "Jogador", "Posição", "G", "A");
echo str_repeat('-', 56) . "\n";
foreach ($data['squads'] as $squad) {
    if ($squad['id'] !== 'seniors') continue;
    $players = $squad['players'];
    usort($players, fn($a, $b) => ($b['goals'] * 2 + $b['assists']) <=> ($a['goals'] * 2 + $a['assists']));
    foreach ($players as $p) {
        $star = ($p['goals'] > 0 || $p['assists'] > 0) ? '⭐' : '  ';
        printf("%s %-28s %-12s %5d %5d\n", $star, $p['name'], $p['position'], $p['goals'], $p['assists']);
    }
}
echo "\n✓ players.json actualizado!\n";
