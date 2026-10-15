<?php
// Fase 3: Parse completo da página da equipa + páginas individuais de jogadores
// Recolhe posições, fotos e dados completos

$ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36';

function zzGet(string $url, string $ua, string $referer = 'https://www.zerozero.pt/'): array {
    $c = curl_init($url);
    curl_setopt_array($c, [
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_FOLLOWLOCATION => 1,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_USERAGENT      => $ua,
        CURLOPT_HTTPHEADER     => [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: pt-PT,pt;q=0.9',
            'Referer: ' . $referer,
        ],
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_HEADER         => 0,
    ]);
    $body = (string)curl_exec($c);
    $code = (int)curl_getinfo($c, CURLINFO_RESPONSE_CODE);
    curl_close($c);
    return [$code, $body];
}

function parseHtml(string $html): array {
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="UTF-8">' . $html);
    libxml_clear_errors();
    return [$doc, new DOMXPath($doc)];
}

// Mapeia posição em texto para o sistema do site
function mapPosition(string $pos): string {
    $pos = mb_strtolower(trim($pos), 'UTF-8');
    if (str_contains($pos, 'guarda') || str_contains($pos, 'goalkeeper')) return 'goalkeeper';
    if (str_contains($pos, 'defesa') || str_contains($pos, 'defender') || str_contains($pos, 'lateral')) return 'defender';
    if (str_contains($pos, 'médio') || str_contains($pos, 'medio') || str_contains($pos, 'midfielder')) return 'midfielder';
    if (str_contains($pos, 'avança') || str_contains($pos, 'forward') || str_contains($pos, 'ata') || str_contains($pos, 'ponta')) return 'forward';
    if (str_contains($pos, 'treina') || str_contains($pos, 'coach') || str_contains($pos, 'técnico')) return 'coach';
    return 'midfielder'; // default
}

// Mapeia país para código ISO 2
function mapNat(string $country): string {
    $map = [
        'Portugal' => 'PT', 'portuguese' => 'PT',
        'Brasil' => 'BR', 'Brazil' => 'BR', 'Brazilian' => 'BR',
        'Angola' => 'AO', 'Angolan' => 'AO',
        'Senegal' => 'SN', 'Senegalese' => 'SN',
        'Guiné-Bissau' => 'GW', 'Guinea-Bissau' => 'GW',
        'Cabo Verde' => 'CV', 'Cape Verde' => 'CV',
        'Moçambique' => 'MZ', 'Mozambique' => 'MZ',
        'São Tomé' => 'ST', 'São Tomé e Príncipe' => 'ST',
        'Guiné Equatorial' => 'GQ',
        'Nigéria' => 'NG', 'Nigeria' => 'NG',
        'Marrocos' => 'MA', 'Morocco' => 'MA',
        'Espanha' => 'ES', 'Spain' => 'ES',
        'França' => 'FR', 'France' => 'FR',
        'Argentina' => 'AR',
        'Venezuela' => 'VE',
        'Itália' => 'IT', 'Italy' => 'IT',
        'Ucrânia' => 'UA', 'Ukraine' => 'UA',
        'Gâmbia' => 'GM', 'Gambia' => 'GM',
        'Mauritânia' => 'MR',
        'Filipinas' => 'PH',
        'Turquia' => 'TR',
    ];
    foreach ($map as $k => $v) {
        if (mb_stripos($country, $k, 0, 'UTF-8') !== false) return $v;
    }
    return 'PT';
}

// ==== PASSO 1: Parse da página da equipa ====
echo "=== Parse da página da equipa ===\n";
$html = file_get_contents(__DIR__ . '/../tmp_squad_html.html');

[$doc, $xp] = parseHtml($html);

// Percorre staff_line e staff nodes para extrair posição + jogador
$squadData = [];
$currentPos = '';
$allNodes = $xp->query('//*[contains(@class,"staff")]');

foreach ($allNodes as $node) {
    $cls = $node->getAttribute('class');
    $classes = preg_split('/\s+/', trim($cls));

    if (in_array('staff_line', $classes)) {
        // Separador de posição
        $posText = trim($node->textContent);
        if (!empty($posText)) {
            $currentPos = $posText;
        }
        continue;
    }

    if (in_array('staff', $classes)) {
        // Row de jogador
        $playerLink = $xp->query('.//a[contains(@href,"/jogador/")]', $node)->item(0);
        if (!$playerLink) continue;

        $href = $playerLink->getAttribute('href');
        if (!str_starts_with($href, 'http')) {
            $href = 'https://www.zerozero.pt' . $href;
        }
        $name = trim($playerLink->textContent);
        if (empty($name)) continue;

        preg_match('/\/jogador\/[^\/]+\/(\d+)/', $href, $idM);
        $pid = $idM[1] ?? '';

        // Número
        $numNode = $xp->query('.//*[contains(@class,"number")]', $node)->item(0);
        $number = trim($numNode?->textContent ?? '');

        // Foto
        $photoUrl = '';
        $photoDiv = $xp->query('.//*[contains(@class,"photo")]', $node)->item(0);
        if ($photoDiv) {
            $style = $photoDiv->getAttribute('style');
            if (preg_match('/url\([\'"]?([^\'")\s]+)[\'"]?\)/', $style, $pm)) {
                $photoUrl = $pm[1];
                if (!str_starts_with($photoUrl, 'http')) {
                    $photoUrl = 'https://www.zerozero.pt' . $photoUrl;
                }
            }
        }

        // Idade
        $age = 0;
        foreach ($xp->query('.//span', $node) as $span) {
            $txt = trim($span->textContent);
            if (preg_match('/^(\d+)\s*anos?$/ui', $txt, $am)) {
                $age = (int)$am[1];
                break;
            }
        }

        // Nacionalidade - procura em spans ou imgs com classe de bandeira
        $nat = 'PT';
        foreach ($xp->query('.//*[@class and contains(@class,"flag")]', $node) as $fl) {
            $flCls = $fl->getAttribute('class');
            if (preg_match('/flag[_-]([a-z]{2})/i', $flCls, $nm)) {
                $nat = strtoupper($nm[1]);
                break;
            }
        }
        // Tenta title nos links
        foreach ($xp->query('.//a[@title]', $node) as $a) {
            $t = trim($a->getAttribute('title'));
            if ($t && strlen($t) > 1 && !str_contains(strtolower($t), 'vialonga')) {
                $nat = mapNat($t);
                break;
            }
        }

        $squadData[] = [
            'zz_id'    => $pid,
            'name'     => $name,
            'number'   => (int)$number ?: 0,
            'position' => mapPosition($currentPos),
            'pos_text' => $currentPos,
            'age'      => $age,
            'nat'      => $nat,
            'photo_zz' => $photoUrl,
            'url'      => preg_replace('/\?.*/', '', $href),
        ];
    }
}

echo "Jogadores extraídos: " . count($squadData) . "\n";

// ==== PASSO 2: Vai buscar página de cada jogador para info adicional ====
echo "\n=== Fase 2: Páginas individuais de jogadores ===\n";

foreach ($squadData as &$p) {
    echo "  {$p['name']} ({$p['zz_id']})... ";

    [$code, $playerHtml] = zzGet($p['url'] . '?epoca_id=156', $ua, 'https://www.zerozero.pt/equipa/gd-vialonga/3671');
    if ($code !== 200 || strlen($playerHtml) < 1000) {
        echo "ERRO HTTP $code\n";
        usleep(500000);
        continue;
    }

    [$pdoc, $pxp] = parseHtml($playerHtml);

    // Posição oficial (mais específica)
    $posNode = $pxp->query('//*[contains(@class,"position") or contains(@class,"posicao")]')->item(0);
    if ($posNode) {
        $posText = trim($posNode->textContent);
        if (!empty($posText)) {
            $newPos = mapPosition($posText);
            if ($newPos !== 'midfielder') { // só actualiza se específico
                $p['position'] = $newPos;
            }
        }
    }

    // Nacionalidade via info page
    foreach ($pxp->query('//*[@class and contains(@class,"flag")]') as $fl) {
        $flCls = $fl->getAttribute('class');
        if (preg_match('/flag[_-]([a-z]{2})/i', $flCls, $nm)) {
            $p['nat'] = strtoupper($nm[1]);
            break;
        }
    }
    // Também procura country no texto
    $pageText = preg_replace('/\s+/', ' ', $pdoc->textContent);
    foreach (['Portugal','Brasil','Angola','Guiné-Bissau','Senegal','Cabo Verde','Marrocos','Venezuela','Gâmbia'] as $country) {
        if (mb_stripos($pageText, $country, 0, 'UTF-8') !== false) {
            $p['nat'] = mapNat($country);
            break;
        }
    }

    // Foto de perfil - procura img com classe de foto do jogador
    $profileImg = $pxp->query('//img[contains(@class,"player") or contains(@src,"jogador") or contains(@src,"player")]')->item(0);
    if ($profileImg) {
        $imgSrc = $profileImg->getAttribute('src');
        if ($imgSrc && !str_contains($imgSrc, 'blank') && !str_contains($imgSrc, 'default')) {
            if (!str_starts_with($imgSrc, 'http')) $imgSrc = 'https://www.zerozero.pt' . $imgSrc;
            $p['photo_zz'] = $imgSrc;
        }
    }

    // Clubes anteriores
    $clubs = [];
    $clubLinks = $pxp->query('//a[contains(@href,"/equipa/") or contains(@href,"/clube/")]');
    foreach ($clubLinks as $cl) {
        $clubName = trim($cl->textContent);
        if ($clubName && strlen($clubName) > 2 && !in_array($clubName, $clubs)) {
            $clubs[] = $clubName;
        }
    }
    $p['clubs_raw'] = array_slice($clubs, 0, 5);

    echo "OK (foto=" . (!empty($p['photo_zz']) ? 'SIM' : 'NAO') . ", nat={$p['nat']})\n";
    usleep(800000); // 0.8s entre pedidos para não sobrecarregar o servidor
}

echo "\n=== Resultado final ===\n";
foreach ($squadData as $p) {
    printf("  [%2d] %-28s | %-12s | %s | %s anos | foto=%s\n",
        $p['number'], $p['name'], $p['position'], $p['nat'], $p['age'],
        $p['photo_zz'] ? 'SIM' : 'NAO');
}

file_put_contents(__DIR__ . '/../tmp_squad_full.json', json_encode($squadData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "\nGuardado em tmp_squad_full.json (" . count($squadData) . " jogadores)\n";
