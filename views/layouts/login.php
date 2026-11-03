<?php

use app\components\Tampil;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var string $content */

$this->beginPage();
?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0e2a1e">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?> · Arsip Sekretariat</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600&family=Source+Serif+4:opsz,wght@8..60,600;8..60,700&display=swap">
    <link rel="stylesheet" href="<?= Tampil::aset('css/app.css') ?>">
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
<?= $content ?>
<script src="<?= Tampil::aset('js/app.js') ?>" defer></script>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
