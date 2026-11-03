<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

// Kunci cookie dibuat otomatis sekali saja dan disimpan di folder runtime
// (di luar folder web), jadi tidak perlu disunting manual.
$keyFile = dirname(__DIR__) . '/runtime/cookie.key';
if (!is_file($keyFile)) {
    if (!is_dir(dirname($keyFile))) {
        @mkdir(dirname($keyFile), 0775, true);
    }
    file_put_contents($keyFile, bin2hex(random_bytes(32)));
}
$cookieKey = trim((string) file_get_contents($keyFile));

return [
    'id' => 'arsip-sekretariat',
    'name' => 'Arsip Sekretariat',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'language' => 'id',
    'timeZone' => 'Asia/Jakarta',
    'defaultRoute' => 'site/index',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            'cookieValidationKey' => $cookieKey,
            'csrfParam' => '_csrf-arsip',
        ],
        'session' => [
            'name' => 'ARSIPSESS',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => false,
            'authTimeout' => 8 * 3600,
            'loginUrl' => ['site/login'],
            'identityCookie' => ['name' => '_identity-arsip', 'httpOnly' => true],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'assetManager' => [
            // Menambahkan ?v=waktu pada css/js agar perubahan langsung terlihat.
            'appendTimestamp' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '' => 'site/index',
                'masuk' => 'site/login',
                'keluar' => 'site/logout',
                'berkas/<id:\d+>' => 'arsip/berkas',
                '<jenis:(sambutan|paparan|tor)>' => 'arsip/index',
                '<jenis:(sambutan|paparan|tor)>/tambah' => 'arsip/create',
                '<jenis:(sambutan|paparan|tor)>/<id:\d+>/edit' => 'arsip/update',
                '<jenis:(sambutan|paparan|tor)>/<id:\d+>/hapus' => 'arsip/delete',
            ],
        ],
    ],
    'params' => $params,
];
