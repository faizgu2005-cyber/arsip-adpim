<?php

// Persiapan direktori yang sering tidak ikut terunggah karena masih kosong.
return static function ($basePath) {
    foreach (['runtime', 'runtime/logs', 'runtime/cache', 'web/assets'] as $directory) {
        $path = $basePath . '/' . $directory;
        if (!is_dir($path) && !@mkdir($path, 0775, true) && !is_dir($path)) {
            throw new \RuntimeException('Tidak dapat membuat direktori hosting: ' . $path);
        }
        if (!is_writable($path)) {
            throw new \RuntimeException('Direktori harus dapat ditulis oleh PHP: ' . $path);
        }
    }

    $keyFile = $basePath . '/runtime/cookie.key';
    // Kunci dari lingkungan server dapat dipakai tanpa menyimpannya dalam kode.
    $environmentKey = getenv('ARSIP_COOKIE_VALIDATION_KEY');
    if (is_string($environmentKey) && trim($environmentKey) !== '') {
        return trim($environmentKey);
    }

    // Kunci dibuat di dalam lock agar permintaan bersamaan memakai kunci sama.
    $handle = @fopen($keyFile, 'c+');
    if ($handle === false) {
        throw new \RuntimeException('File kunci cookie harus dapat dibaca dan ditulis PHP: ' . $keyFile);
    }
    try {
        if (!flock($handle, LOCK_EX)) {
            throw new \RuntimeException('Tidak dapat mengunci file kunci cookie: ' . $keyFile);
        }
        $key = trim((string) stream_get_contents($handle));
        if ($key === '') {
            $key = bin2hex(random_bytes(32));
            rewind($handle);
            if (fwrite($handle, $key) !== strlen($key) || !fflush($handle)) {
                throw new \RuntimeException('Tidak dapat menyimpan kunci cookie: ' . $keyFile);
            }
            ftruncate($handle, strlen($key));
        }
        return $key;
    } finally {
        flock($handle, LOCK_UN);
        fclose($handle);
    }
};
