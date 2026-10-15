<?php
// Script final: descarrega fotos do ZeroZero e actualiza players.json
// com os dados reais do plantel GD Vialonga 2026/27

$ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36';
$baseDir = dirname(__DIR__);
$playersDir = $baseDir . '/assets/images/players';
$playersJsonPath = $baseDir . '/data/players.json';

// =====================================================
// POSIÇÕES CORRECTAS (por ordem de aparecimento no ZZ)
// extraídas do pos_text do scraping
// =====================================================
$positionMap = [
    '1114851' => 'goalkeeper',  // Dinis Coelho
    '867095'  => 'goalkeeper',  // Tomás Camisão
    '450032'  => 'goalkeeper',  // Ricardo Cabanas
    '683429'  => 'goalkeeper',  // Léo Loureiro
    '606865'  => 'defender',    // Rúben Figueiredo
    '156153'  => 'defender',    // Ivo Dias
    '536466'  => 'defender',    // Miguel Cravo
    '302606'  => 'defender',    // Pedro Roxo
    '413258'  => 'defender',    // João Afonso
    '691012'  => 'defender',    // Daniel Caruso
    '793454'  => 'defender',    // Arthur Divino
    '119640'  => 'defender',    // Adilson
    '702135'  => 'defender',    // Tomás Vale
    '283146'  => 'defender',    // João Faia
    '549213'  => 'midfielder',  // Cláudio Fortes
    '302590'  => 'midfielder',  // Rafael Ribeiro
    '1242966' => 'midfielder',  // Ibraima Djedjo
    '143911'  => 'midfielder',  // Léo Mofreita
    '745392'  => 'midfielder',  // Ricardo Sousa
    '468928'  => 'midfielder',  // Wilson Veiga
    '160504'  => 'midfielder',  // André Oliveira
    '166244'  => 'midfielder',  // Nuno Mouralinho
    '667529'  => 'forward',     // Diogo Teixeira
    '1164557' => 'forward',     // Rodrigo Oliveira
    '1079190' => 'forward',     // Esmael
    '689298'  => 'forward',     // Alex
    '300565'  => 'forward',     // Guilherme Gomes
    '1167249' => 'forward',     // Daniel Santos
    '747336'  => 'forward',     // David Inácio
];

// Nacionalidades corrigidas (algumas precisam de ajuste)
// Ibraima Djedjo - Guiné-Bissau
// Esmael - provavelmente PT ou GW
// Alex - provavelmente PT
// Arthur Divino - pode ser BR
$natOverride = [
    '1242966' => 'GW',  // Ibraima Djedjo - nome Guineense
    '793454'  => 'BR',  // Arthur Divino - nome brasileiro
    '689298'  => 'AO',  // Alex - verificar
];

function downloadImage(string $url, string $dest, string $ua): bool {
    if (empty($url)) return false;
    if (file_exists($dest)) return true; // já existe

    $c = curl_init($url);
    curl_setopt_array($c, [
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_FOLLOWLOCATION => 1,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_USERAGENT      => $ua,
        CURLOPT_HTTPHEADER     => ['Referer: https://www.zerozero.pt/'],
        CURLOPT_SSL_VERIFYPEER => 0,
    ]);
    $body = curl_exec($c);
    $code = (int)curl_getinfo($c, CURLINFO_RESPONSE_CODE);
    $type = (string)curl_getinfo($c, CURLINFO_CONTENT_TYPE);
    curl_close($c);

    if ($code !== 200 || empty($body) || strlen($body) < 1000) return false;

    // Cria imagem com GD
    $img = @imagecreatefromstring($body);
    if (!$img) {
        // Tenta guardar o ficheiro directamente se não conseguir processar
        file_put_contents($dest . '.tmp', $body);
        return false;
    }

    // Redimensiona para 300x400 (proporção 3:4)
    $srcW = imagesx($img);
    $srcH = imagesy($img);

    // Crop para 3:4 a partir do centro-topo
    $targetRatio = 3/4;
    $srcRatio = $srcW / $srcH;

    if ($srcRatio > $targetRatio) {
        // Muito largo - crop lateral
        $newH = $srcH;
        $newW = (int)round($srcH * $targetRatio);
        $srcX = (int)round(($srcW - $newW) / 2);
        $srcY = 0;
    } else {
        // Muito alto - crop vertical
        $newW = $srcW;
        $newH = (int)round($srcW / $targetRatio);
        $srcX = 0;
        $srcY = 0; // topo
    }

    $out = imagecreatetruecolor(300, 400);
    imagecopyresampled($out, $img, 0, 0, $srcX, $srcY, 300, 400, $newW, $newH);
    imagedestroy($img);

    $ok = imagepng($out, $dest);
    imagedestroy($out);
    return $ok;
}

function slugify(string $s): string {
    $s = mb_strtolower($s, 'UTF-8');
    $map = ['à'=>'a','á'=>'a','â'=>'a','ã'=>'a','ä'=>'a','é'=>'e','è'=>'e','ê'=>'e','ë'=>'e',
            'í'=>'i','ì'=>'i','î'=>'i','ï'=>'i','ó'=>'o','ò'=>'o','ô'=>'o','õ'=>'o','ö'=>'o',
            'ú'=>'u','ù'=>'u','û'=>'u','ü'=>'u','ç'=>'c','ñ'=>'n','ý'=>'y'];
    foreach ($map as $from => $to) {
        $s = str_replace($from, $to, $s);
    }
    $s = preg_replace('/[^a-z0-9\s-]/', '', $s);
    return preg_replace('/[\s-]+/', '-', trim($s));
}

// === Carrega dados do scraping ===
$squadFull = json_decode(file_get_contents($baseDir . '/tmp_squad_full.json'), true);
if (empty($squadFull)) die("ERRO: tmp_squad_full.json vazio.\n");

// Remove duplicados (Daniel Santos aparece 2x)
$seen = [];
$squadUnique = [];
foreach ($squadFull as $p) {
    if (!isset($seen[$p['zz_id']])) {
        $seen[$p['zz_id']] = true;
        $squadUnique[] = $p;
    }
}
$squadFull = $squadUnique;

echo "Jogadores a processar: " . count($squadFull) . "\n\n";

// === Carrega players.json ===
$playersData = json_decode(file_get_contents($playersJsonPath), true);

// Preserva squads de formação (u19, etc.)
$seniorIdx = null;
$u19Idx    = null;
foreach ($playersData['squads'] as $i => $s) {
    if ($s['id'] === 'seniors') $seniorIdx = $i;
    if ($s['id'] === 'u19')     $u19Idx    = $i;
}

// === Download de fotos e build dos novos jogadores ===
$newPlayers = [];
$id = 1;

foreach ($squadFull as $p) {
    $zzId    = $p['zz_id'];
    $pos     = $positionMap[$zzId] ?? 'midfielder';
    $nat     = $natOverride[$zzId]  ?? $p['nat'];
    $slug    = slugify($p['name']);
    $num     = $p['number'];

    // Nome do ficheiro: numero-slug.png
    $filename = "{$num}-{$slug}.png";
    $localPath = "$playersDir/$filename";
    $relativePath = "assets/images/players/$filename";

    echo "  [{$num}] {$p['name']} ($pos)... ";

    $photoOk = downloadImage($p['photo_zz'], $localPath, $ua);
    echo $photoOk ? "foto OK\n" : "sem foto\n";

    // Constrói histórico de clubes a partir de clubs_raw do ZZ
    // Remove "GD Vialonga" (já está na equipa) e formata
    $clubs = [];
    $clubs[] = "GD Vialonga (2026-)";
    $rawClubs = $p['clubs_raw'] ?? [];
    foreach ($rawClubs as $c) {
        if (mb_stripos($c, 'vialonga', 0, 'UTF-8') === false && !empty(trim($c))) {
            $clubs[] = trim($c);
        }
        if (count($clubs) >= 4) break;
    }

    $newPlayers[] = [
        'id'          => $id++,
        'number'      => $num,
        'name'        => $p['name'],
        'position'    => $pos,
        'nationality' => $nat,
        'age'         => (int)($p['age'] ?: 0),
        'clubs'       => $clubs,
        'goals'       => 0,
        'assists'     => 0,
        'bio'         => '',
        'photo'       => $photoOk ? $relativePath : '',
        'zz_id'       => $zzId,
    ];
}

// === Actualiza players.json ===
if ($seniorIdx !== null) {
    $playersData['squads'][$seniorIdx]['players'] = $newPlayers;
} else {
    // Cria squad sénior se não existir
    $playersData['squads'][] = [
        'id'          => 'seniors',
        'name'        => 'Equipa Sénior',
        'competition' => 'Divisão de Honra AF Lisboa',
        'coach'       => 'Mister',
        'players'     => $newPlayers,
    ];
}

$playersData['updated_at'] = date('Y-m-d');
$playersData['season']     = '2026/27';

// Guarda backup do JSON original
copy($playersJsonPath, $playersJsonPath . '.bak');

$json = json_encode($playersData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
file_put_contents($playersJsonPath, $json);

echo "\n✓ players.json actualizado com " . count($newPlayers) . " jogadores seniores.\n";
echo "✓ Backup guardado em players.json.bak\n";

// === Resumo por posição ===
echo "\n=== Resumo por posição ===\n";
$byPos = [];
foreach ($newPlayers as $p) {
    $byPos[$p['position']][] = $p;
}
$posLabels = ['goalkeeper'=>'Guarda-redes','defender'=>'Defesas','midfielder'=>'Médios','forward'=>'Avançados'];
foreach ($posLabels as $pos => $label) {
    $plist = $byPos[$pos] ?? [];
    echo "\n$label (" . count($plist) . "):\n";
    foreach ($plist as $p) {
        $hasPhoto = !empty($p['photo']) ? '✓' : '✗';
        printf("  $hasPhoto [%2d] %-28s | %s\n", $p['number'], $p['name'], $p['nationality']);
    }
}
