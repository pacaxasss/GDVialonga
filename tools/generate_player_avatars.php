<?php
/**
 * GD Vialonga — Gerador de retratos dos jogadores (placeholder)
 * -------------------------------------------------------------
 * Cria uma imagem PNG para cada jogador em data/players.json.
 * A imagem usa as cores do clube e mostra o número de camisola.
 *
 * Os ficheiros gerados são apenas "placeholders": para publicar as
 * fotografias reais, basta substituir o ficheiro indicado no campo
 * "photo" de cada jogador (mesmo nome e formato .png).
 *
 * Uso (linha de comandos):
 *   & 'C:\xampp\php\php.exe' 'tools\generate_player_avatars.php'
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

if (!extension_loaded('gd')) {
    fwrite(STDERR, "A extensão GD do PHP é necessária para gerar os retratos.\n");
    exit(1);
}

$root       = dirname(__DIR__);
$playerFile = $root . '/data/players.json';
$outputDir  = $root . '/assets/images/players';

if (!is_file($playerFile)) {
    fwrite(STDERR, "Não foi encontrado o ficheiro data/players.json\n");
    exit(1);
}

$data   = json_decode((string)file_get_contents($playerFile), true);
$squads = $data['squads'] ?? [];

if (!is_dir($outputDir) && !mkdir($outputDir, 0755, true) && !is_dir($outputDir)) {
    fwrite(STDERR, "Não foi possível criar a pasta assets/images/players\n");
    exit(1);
}

/** Procura uma fonte TrueType disponível no sistema. */
function gdvFont(): ?string {
    $candidates = [
        'C:/Windows/Fonts/arialbd.ttf',
        'C:/Windows/Fonts/Arialbd.ttf',
        'C:/Windows/Fonts/segoeuib.ttf',
        'C:/Windows/Fonts/calibrib.ttf',
        '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
        '/Library/Fonts/Arial Bold.ttf',
    ];
    foreach ($candidates as $font) {
        if (is_file($font)) return $font;
    }
    return null;
}

/** Desenha texto a centrar, com fallback para a fonte interna. */
function gdvDrawCentered($img, float $y, string $text, int $size, int $color, ?string $font): void {
    $width = imagesx($img);
    if ($font !== null) {
        $box = imagettfbbox($size, 0, $font, $text);
        $textWidth = abs($box[2] - $box[0]);
        $x = (int)(($width - $textWidth) / 2);
        imagettftext($img, $size, 0, $x, (int)$y, $color, $font, $text);
        return;
    }
    $fontIndex = 5;
    $textWidth = imagefontwidth($fontIndex) * strlen($text);
    imagestring($img, $fontIndex, (int)(($width - $textWidth) / 2), (int)($y - imagefontheight($fontIndex)), $text, $color);
}

/** Gera um retrato para o jogador indicado. */
function gdvMakeAvatar(array $player, string $dest, ?string $font): void {
    $w = 400;
    $h = 520;
    $img = imagecreatetruecolor($w, $h);
    imageantialias($img, true);

    $isStaff = in_array($player['position'] ?? '', ['coach', 'staff'], true);

    $top    = $isStaff ? [127, 29, 29] : [11, 47, 92];     // #7f1d1d / #0b2f5c
    $bottom = $isStaff ? [206, 17, 38] : [40, 120, 190];   // #CE1126 / #2878BE

    // Fundo com gradiente vertical.
    for ($y = 0; $y < $h; $y++) {
        $ratio = $y / ($h - 1);
        $r = (int)round($top[0] + ($bottom[0] - $top[0]) * $ratio);
        $g = (int)round($top[1] + ($bottom[1] - $top[1]) * $ratio);
        $b = (int)round($top[2] + ($bottom[2] - $top[2]) * $ratio);
        imageline($img, 0, $y, $w, $y, imagecolorallocate($img, $r, $g, $b));
    }

    // Vinheta / faixa diagonal subtil.
    $shade = imagecolorallocatealpha($img, 0, 0, 0, 105);
    imagefilledpolygon($img, [0, 0, 200, 0, 0, 300], $shade);

    // Camisola (corpo + mangas).
    $jerseyBody   = imagecolorallocate($img, $isStaff ? 245 : 250, $isStaff ? 245 : 250, $isStaff ? 245 : 250);
    $jerseyShadow = imagecolorallocatealpha($img, 255, 255, 255, 60);

    // Mangas
    imagefilledpolygon($img, [160, 150, 100, 185, 78, 258, 142, 292, 160, 245], $jerseyBody);
    imagefilledpolygon($img, [240, 150, 300, 185, 322, 258, 258, 292, 240, 245], $jerseyBody);
    // Corpo
    imagefilledpolygon($img, [160, 150, 240, 150, 262, 168, 262, 430, 138, 430, 138, 168], $jerseyBody);

    // Acentos vermelhos nos ombros.
    $accent = imagecolorallocate($img, 206, 17, 38);
    imagefilledpolygon($img, [160, 150, 100, 185, 112, 200, 162, 168], $accent);
    imagefilledpolygon($img, [240, 150, 300, 185, 288, 200, 238, 168], $accent);

    // Gola em V.
    $collar = imagecolorallocate($img, $isStaff ? 127 : 18, $isStaff ? 29 : 60, $isStaff ? 29 : 118);
    imagefilledpolygon($img, [180, 150, 220, 150, 200, 190], $collar);

    // Número de camisola.
    $ink     = imagecolorallocate($img, $isStaff ? 127 : 18, $isStaff ? 29 : 60, $isStaff ? 29 : 118);
    $number  = (string)($player['number'] ?? 0);
    if ($number !== '' && $number !== '0') {
        gdvDrawCentered($img, 360, $number, 130, $ink, $font);
    } else {
        gdvDrawCentered($img, 340, 'GDV', 60, $ink, $font);
    }

    // Nome do clube no fundo.
    gdvDrawCentered($img, 495, 'GD VIALONGA', 26, $jerseyShadow, $font);

    imagepng($img, $dest);
    imagedestroy($img);
}

$font  = gdvFont();
$count = 0;

foreach ($squads as $squad) {
    foreach ($squad['players'] ?? [] as $player) {
        $photo = $player['photo'] ?? '';
        if ($photo === '' || !str_starts_with($photo, 'assets/images/players/')) {
            continue;
        }
        $dest = $root . '/' . $photo;
        if (!is_dir(dirname($dest))) {
            mkdir(dirname($dest), 0755, true);
        }
        gdvMakeAvatar($player, $dest, $font);
        $count++;
    }
}

printf("Retratos gerados: %d (pasta assets/images/players/).\n", $count);
