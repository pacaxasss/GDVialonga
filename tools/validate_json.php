<?php
$data = json_decode(file_get_contents('data/players.json'), true);
if (!$data) { echo 'ERRO: JSON inválido'; exit(1); }
echo 'JSON válido. Squads: ' . count($data['squads']) . PHP_EOL;
foreach ($data['squads'] as $s) {
    echo '  - ' . $s['name'] . ': ' . count($s['players']) . ' jogadores' . PHP_EOL;
}
echo 'Tudo OK!' . PHP_EOL;
