<?php

use app\components\Tampil;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $name */
/** @var string $message */
/** @var Exception $exception */

$this->title = $name;
$kode = isset($exception->statusCode) ? (int) $exception->statusCode : 0;
?>
<div class="card card-pad error-card">
    <div class="empty">
        <div class="ring"><?= Tampil::icon('alert', 26) ?></div>
        <strong><?= $kode ? $kode . ' · ' : '' ?><?= Html::encode($name) ?></strong>
        <span><?= nl2br(Html::encode($message)) ?></span>
        <a class="btn primary" style="margin-top:12px" href="<?= Html::encode(Url::to(['site/index'])) ?>">Kembali ke beranda</a>
    </div>
</div>
