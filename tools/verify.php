<?php
$data = json_decode(file_get_contents('data/players.json'), true);
$seniors = null;
foreach ($data['squads'] as $s) {
    if ($s['id']==='seniors') { $seniors = $s; break; }
}
echo 'Total seniors: ' . count($seniors['players']) . PHP_EOL;

// Check for David Inacio
$found = false;
foreach ($seniors['players'] as $p) {
    if (mb_stripos($p['name'], 'inac') !== false || mb_stripos($p['name'], 'david') !== false) {
        echo 'Found David: ' . $p['name'] . PHP_EOL;
        $found = true;
    }
}
if (!$found) echo 'David Inacio NAO encontrado' . PHP_EOL;

// Check photos exist
$missing = 0;
foreach ($seniors['players'] as $p) {
    if (!empty($p['photo']) && !file_exists($p['photo'])) {
        echo 'MISSING PHOTO: ' . $p['photo'] . PHP_EOL;
        $missing++;
    }
}
echo 'Missing photos: ' . $missing . PHP_EOL;
echo PHP_EOL . 'Lista completa:' . PHP_EOL;
foreach ($seniors['players'] as $p) {
    printf("  [%2d] %-28s | %-12s | %s | foto: %s\n",
        $p['number'], $p['name'], $p['position'], $p['nationality'],
        $p['photo'] ? basename($p['photo']) : 'SEM FOTO');
}
