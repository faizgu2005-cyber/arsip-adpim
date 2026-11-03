<?php

// Salin file ini menjadi db-local.php lalu sesuaikan.
return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=127.0.0.1;port=3306;dbname=arsip_sekretariat',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
];
