<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/fpf.php';

try {
    $result = syncFpfResults();
    printf(
        "FPF sync OK: %d jogos, %d terminados, %d equipas (%s).\n",
        $result['matches'],
        $result['finished'],
        $result['teams'],
        SEASON
    );
} catch (Throwable $error) {
    fwrite(STDERR, 'FPF sync failed: ' . $error->getMessage() . PHP_EOL);
    exit(1);
}