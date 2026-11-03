<?php

use app\components\Tampil;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $content */

$guest = Yii::$app->user->isGuest;
$nama = $guest ? '' : Yii::$app->user->identity->username;
$inisial = mb_strtoupper(mb_substr($nama !== '' ? $nama : '?', 0, 1));
$ctrl = Yii::$app->controller->id;
$aksi = Yii::$app->controller->action ? Yii::$app->controller->action->id : '';
$jenisAktif = Yii::$app->request->get('jenis');

$menu = [
    ['Dashboard', 'home', ['site/index'], $ctrl === 'site' && $aksi === 'index'],
    ['Sambutan', 'mic', ['arsip/index', 'jenis' => 'sambutan'], $ctrl === 'arsip' && $jenisAktif === 'sambutan'],
    ['Paparan', 'presentation', ['arsip/index', 'jenis' => 'paparan'], $ctrl === 'arsip' && $jenisAktif === 'paparan'],
    ['TOR', 'file', ['arsip/index', 'jenis' => 'tor'], $ctrl === 'arsip' && $jenisAktif === 'tor'],
];

$this->beginPage();
?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#146c43">
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

<?php if ($guest): ?>
    <div class="guest-wrap"><main><?= $content ?></main></div>
<?php else: ?>
    <div class="scrim" id="scrim" hidden></div>

    <aside class="sidebar" id="sidebar" aria-label="Menu utama">
        <div class="brand-row">
            <div class="brand-mark"><?= Tampil::icon('layers', 22) ?></div>
            <div>
                <strong>Arsip Sekretariat</strong>
                <span>Sambutan, Paparan, dan TOR</span>
            </div>
        </div>

        <nav>
            <div class="nav-label">Menu</div>
            <?php foreach ($menu as [$label, $ikon, $url, $aktif]): ?>
                <a class="navlink<?= $aktif ? ' active' : '' ?>" href="<?= Html::encode(Url::to($url)) ?>"<?= $aktif ? ' aria-current="page"' : '' ?>>
                    <?= Tampil::icon($ikon) ?><?= Html::encode($label) ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="side-user">
            <div class="avatar"><?= Html::encode($inisial) ?></div>
            <div class="who">
                <strong><?= Html::encode($nama) ?></strong>
                <span>Administrator</span>
            </div>
            <?= Html::beginForm(['site/logout'], 'post') ?>
                <button class="icon-btn" type="submit" aria-label="Keluar" title="Keluar"><?= Tampil::icon('logout') ?></button>
            <?= Html::endForm() ?>
        </div>
    </aside>

    <div class="content">
        <header class="topbar">
            <button class="icon-btn burger" id="burger" type="button" aria-label="Buka menu" aria-expanded="false" aria-controls="sidebar">
                <?= Tampil::icon('menu') ?>
            </button>
            <h2><?= Html::encode($this->title) ?></h2>
            <div class="avatar" title="<?= Html::encode($nama) ?>"><?= Html::encode($inisial) ?></div>
        </header>
        <main><?= $content ?></main>
    </div>
<?php endif; ?>

<?php foreach (Yii::$app->session->getAllFlashes() as $jenis => $pesan): ?>
    <div class="toast <?= $jenis === 'error' ? 'error' : 'success' ?>" role="status">
        <?= Tampil::icon($jenis === 'error' ? 'alert' : 'check', 18) ?><?= Html::encode(is_array($pesan) ? implode(' ', $pesan) : $pesan) ?>
    </div>
<?php endforeach; ?>

<script src="<?= Tampil::aset('js/app.js') ?>" defer></script>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
