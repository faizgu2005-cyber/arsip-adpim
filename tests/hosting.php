<?php

$prepare = require dirname(__DIR__) . '/config/hosting.php';
$root = sys_get_temp_dir() . '/arsip-hosting-' . bin2hex(random_bytes(8));
$previousKey = getenv('ARSIP_COOKIE_VALIDATION_KEY');
putenv('ARSIP_COOKIE_VALIDATION_KEY');

$check = static function ($condition, $message) {
    if (!$condition) {
        throw new RuntimeException($message);
    }
};

try {
    $key = $prepare($root);
    $check(strlen($key) === 64, 'Kunci baru harus berisi 64 karakter.');
    $check(is_dir($root . '/web/assets'), 'Folder aset harus dibuat.');
    $check($prepare($root) === $key, 'Kunci harus tetap sama antar-request.');
    file_put_contents($root . '/runtime/cookie.key', '');
    $repairedKey = $prepare($root);
    $check(strlen($repairedKey) === 64, 'Kunci kosong harus diperbaiki.');
    $check($prepare($root) === $repairedKey, 'Kunci yang diperbaiki harus tersimpan.');
    putenv('ARSIP_COOKIE_VALIDATION_KEY=test-environment-key');
    $check($prepare($root) === 'test-environment-key', 'Kunci lingkungan harus dipakai.');
    echo "OK: direktori hosting, persistensi kunci, pemulihan kunci kosong, dan kunci lingkungan.\n";
} finally {
    putenv($previousKey === false ? 'ARSIP_COOKIE_VALIDATION_KEY' : 'ARSIP_COOKIE_VALIDATION_KEY=' . $previousKey);
    @unlink($root . '/runtime/cookie.key');
    foreach (['runtime/logs', 'runtime/cache', 'web/assets', 'runtime', 'web', ''] as $directory) {
        @rmdir($root . '/' . $directory);
    }
}
