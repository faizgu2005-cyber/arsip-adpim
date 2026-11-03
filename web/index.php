<?php

// Mode pengembangan hanya aktif saat dijalankan dengan "php yii serve".
// Di Apache atau hosting, aplikasi otomatis berjalan dalam mode produksi.
$dev = PHP_SAPI === 'cli-server';
defined('YII_DEBUG') or define('YII_DEBUG', $dev);
defined('YII_ENV') or define('YII_ENV', $dev ? 'dev' : 'prod');

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/../config/web.php';

(new yii\web\Application($config))->run();
