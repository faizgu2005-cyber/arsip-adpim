<?php

// Pengaturan database. Untuk mengubahnya tanpa menyunting file ini,
// salin db-local.example.php menjadi db-local.php lalu isi sesuai kebutuhan.
$local = __DIR__ . '/db-local.php';
if (is_file($local)) {
    return require $local;
}

// Bawaan Laragon: pengguna root tanpa password.
return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=127.0.0.1;dbname=arsip_sekretariat',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
];

// testing perubahan