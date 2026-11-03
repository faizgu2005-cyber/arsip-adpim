<?php

use app\components\Tampil;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\LoginForm $model */

$this->title = 'Masuk';
$galat = $model->hasErrors() ? implode(' ', $model->getFirstErrors()) : '';
?>
<div class="login">
    <div class="login-box">
        <?= Html::beginForm(['site/login'], 'post', ['class' => 'login-card']) ?>
            <header class="login-brand">
                <div class="brand-mark"><?= Tampil::icon('layers', 24) ?></div>
                <h1>Arsip Sekretariat</h1>
                <p>Sambutan, Paparan, dan TOR</p>
            </header>

            <h2>Masuk</h2>
            <p class="sub">Gunakan akun yang diberikan administrator.</p>

            <div class="field">
                <label for="u">Username</label>
                <?= Html::activeTextInput($model, 'username', [
                    'class' => 'input', 'id' => 'u', 'autocomplete' => 'username', 'required' => true,
                    'autofocus' => true, 'autocapitalize' => 'none', 'spellcheck' => 'false',
                ]) ?>
            </div>

            <div class="field">
                <label for="p">Password</label>
                <div class="input-wrap">
                    <?= Html::activePasswordInput($model, 'password', [
                        'class' => 'input', 'id' => 'p', 'autocomplete' => 'current-password', 'required' => true,
                    ]) ?>
                    <button type="button" class="icon-btn" id="lihat-password" aria-label="Tampilkan password">
                        <?= Tampil::icon('eye', 18) ?>
                    </button>
                </div>
            </div>

            <p class="err" role="alert"><?= Html::encode($galat) ?></p>
            <button class="btn primary" type="submit" style="width:100%">Masuk</button>
        <?= Html::endForm() ?>
        <p class="login-foot">Sekretariat Daerah</p>
    </div>
</div>
