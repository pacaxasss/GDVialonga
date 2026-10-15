<?php
// Script para extrair marcadores e assistências de cada jogo do GD Vialonga
// e actualizar as estatísticas no players.json

$ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36';
$baseDir = dirname(__DIR__);
$playersJsonPath = $baseDir . '/data/players.json';

$data = json_decode(file_get_contents($playersJsonPath), true);

function zzGet(string $url, string $ua, string $referer = 'https://www.zerozero.pt/'): array {
    $c = curl_init($url);
    curl_setopt_array($c, [
        CURLOPT_RETURNTRANSFER => 1, CURLOPT_FOLLOWLOCATION => 1,
        CURLOPT_TIMEOUT => 25, CURLOPT_USERAGENT => $ua,
        CURLOPT_HTTPHEADER => [
            'Accept: text/html,application/xhtml+xml',
            'Accept-Language: pt-PT,pt;q=0.9',
            'Referer: ' . $referer,
        ],
        CURLOPT_SSL_VERIFYPEER => 0,
    ]);
    $body = (string)curl_exec($c);
    $code = (int)curl_getinfo($c, CURLINFO_RESPONSE_CODE);
    curl_close($c);
    return [$code, $body];
}

// Jogos da época 2026/27 do GD Vialonga
$matchUrls = [
    'https://www.zerozero.pt/jogo/2026-09-13-gd-vialonga-at-povoense/12626559',
    'https://www.zerozero.pt/jogo/2026-09-27-gd-vialonga-associacao-murteirense/12626576',
    'https://www.zerozero.pt/jogo/2026-10-04-fut-benfica-gd-vialonga/12626585',
    'https://www.zerozero.pt/jogo/2026-10-11-uniao-mucifalense-gd-vialonga/12626593',
    'https://www.zerozero.pt/jogo/2026-10-18-gd-vialonga-ad-oeiras/12626601',
    'https://www.zerozero.pt/jogo/2026-10-25-ericeirense-gd-vialonga/12626608',
];

// Estatísticas acumuladas: nome_normalizado => [goals, assists, games]
$statsAccum = [];

// Mapeia nome do jogador (normalizado) para o id no JSON
$playerMap = []; // nome_norm -> &$player reference
$playerGames = []; // conta jogos por jogador (presença em sumário)

foreach ($data['squads'] as &$squad) {
    if ($squad['id'] !== 'seniors') continue;
    foreach ($squad['players'] as &$player) {
        $norm = mb_strtolower(preg_replace('/\s+/', ' ', trim($player['name'])), 'UTF-8');
        // Também variantes sem acento
        $normSimple = $norm;
        foreach (['à'=>'a','á'=>'a','â'=>'a','ã'=>'a','é'=>'e','è'=>'e','ê'=>'e',
                  'í'=>'i','ó'=>'o','ô'=>'o','õ'=>'o','ú'=>'u','ç'=>'c','ñ'=>'n'] as $f => $t) {
            $normSimple = str_replace($f, $t, $normSimple);
        }
        $statsAccum[$norm] = ['goals' => 0, 'assists' => 0, 'games' => 0, 'ref' => &$player];
        if ($norm !== $normSimple) {
            $statsAccum[$normSimple] = &$statsAccum[$norm];
        }
        // Também regista só o apelido para matching parcial
        $parts = explode(' ', $norm);
        if (count($parts) >= 2) {
            $firstLast = $parts[0] . ' ' . end($parts);
        }
    }
}

function normalizeName(string $name): string {
    $name = mb_strtolower(trim($name), 'UTF-8');
    $name = preg_replace('/\s+/', ' ', $name);
    foreach (['à'=>'a','á'=>'a','â'=>'a','ã'=>'a','é'=>'e','è'=>'e','ê'=>'e',
              'í'=>'i','ó'=>'o','ô'=>'o','õ'=>'o','ú'=>'u','ç'=>'c','ñ'=>'n'] as $f => $t) {
        $name = str_replace($f, $t, $name);
    }
    return trim($name);
}

// Tenta fazer match de um nome do ZZ com um jogador do plantel
function findPlayer(string $zzName, array &$statsAccum): ?string {
    $norm = normalizeName($zzName);

    // Match exacto
    if (isset($statsAccum[$norm])) return $norm;

    // Match parcial: verifica se algum nome do plantel está contido no nome ZZ ou vice-versa
    foreach (array_keys($statsAccum) as $key) {
        if (str_ends_with($key, 'ref')) continue;
        // Nome ZZ contém o nome do plantel (ou parte)
        $parts = explode(' ', $key);
        $lastName = end($parts);
        if (strlen($lastName) > 3 && str_contains($norm, $lastName)) return $key;
        // Reverso
        $zzParts = explode(' ', $norm);
        $zzLast  = end($zzParts);
        if (strlen($zzLast) > 3 && str_contains($key, $zzLast)) return $key;
    }
    return null;
}

// Abre cada jogo e extrai eventos
$matchData = [];

foreach ($matchUrls as $matchUrl) {
    echo "\n=== Jogo: $matchUrl ===\n";
    usleep(800000); // 0.8s entre pedidos

    [$code, $html] = zzGet($matchUrl, $ua, 'https://www.zerozero.pt/equipa/gd-vialonga/3671');
    if ($code !== 200 || strlen($html) < 2000) {
        echo "ERRO HTTP $code\n";
        continue;
    }

    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="UTF-8">' . $html);
    libxml_clear_errors();
    $xp = new DOMXPath($doc);

    $pageText = preg_replace('/\s+/', ' ', $doc->textContent ?? '');

    // Extrai resultado e adversário
    // Guarda HTML de debug para o primeiro jogo
    static $firstMatch = true;
    if ($firstMatch) {
        file_put_contents($baseDir . '/tmp_match_debug.html', $html);
        $firstMatch = false;
        echo "(HTML guardado para debug)\n";
    }

    // === Procura eventos de golo ===
    // ZeroZero usa estrutura com ícone de bola + minuto + nome + (assistência)
    // Classes típicas: "event", "goal_event", "goal", "evento"

    $eventNodes = $xp->query(
        '//*[contains(@class,"event") or contains(@class,"golo") or contains(@class,"goal") or ' .
        'contains(@class,"acontecimento") or contains(@class,"incident")]'
    );
    echo "Nodes de evento: " . $eventNodes->length . "\n";

    // Extrai classes únicas para debug
    $clsSeen = [];
    for ($i = 0; $i < min(20, $eventNodes->length); $i++) {
        $cls = $eventNodes->item($i)->getAttribute('class');
        if (!isset($clsSeen[$cls])) {
            $clsSeen[$cls] = true;
            $txt = substr(trim(preg_replace('/\s+/', ' ', $eventNodes->item($i)->textContent)), 0, 150);
            echo "  class='$cls': $txt\n";
        }
    }

    // === Procura no texto por padrões de golo ===
    // ZZ texto: "⚽ 45' Nome Jogador (assist: Nome2)"
    // Padrão: minuto + nome + eventual "(Nome assistência)"
    echo "\nTexto relevante do jogo:\n";
    // Extrai secção "Golos" ou "Resumo" do jogo
    if (preg_match('/(?:Golos|Goals|Marcadores?|Resumo do Jogo|Eventos)(.*?)(?:Cartões|Substituições|Onze|Estatísticas|$)/su', $pageText, $m)) {
        $goalsSection = substr($m[1], 0, 800);
        echo $golosSection = $goalsSection . "\n";
    } else {
        // Mostra um excerto do texto em torno de "GD Vialonga"
        if (preg_match('/GD Vialonga\s*\d+\s*-\s*\d+.*?(?=GD Vialonga|$)/su', $pageText, $m)) {
            echo substr($m[0], 0, 500) . "\n";
        }
    }

    // Guarda dados crus para análise
    $matchData[] = [
        'url'      => $matchUrl,
        'html_len' => strlen($html),
        'excerpt'  => substr($pageText, 0, 2000),
    ];
}

// Guarda para análise
file_put_contents($baseDir . '/tmp_matches_data.json', json_encode($matchData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "\n\nDados guardados em tmp_matches_data.json\n";
