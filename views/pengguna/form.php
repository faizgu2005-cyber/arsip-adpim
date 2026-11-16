<?php

use app\components\Tampil;
use app\models\UserForm;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\UserForm $model */

$this->title = 'Tambah Pengguna';
$galat = function ($atribut) use ($model) {
    $e = $model->getFirstError($atribut);
    return $e ? '<p class="err" role="alert">' . Html::encode($e) . '</p>' : '';
};
?>
<div class="page-head">
    <div>
        <h1>Tambah Pengguna</h1>
        <p>Buat akun baru agar orang lain bisa masuk ke aplikasi.</p>
    </div>
</div>

<div class="card card-pad form-card">
    <?= Html::beginForm(['pengguna/create'], 'post') ?>
        <div class="field">
            <label for="f-user">Username</label>
            <?= Html::activeTextInput($model, 'username', [
                'class' => 'input', 'id' => 'f-user', 'required' => true, 'maxlength' => 50, 'minlength' => 3,
                'autofocus' => true, 'autocomplete' => 'off', 'autocapitalize' => 'none', 'spellcheck' => 'false',
                'placeholder' => 'contoh: budi.santoso',
            ]) ?>
            <?= $galat('username') ?>
            <p class="hint">3–50 karakter: huruf, angka, titik, garis bawah, atau strip.</p>
        </div>

        <div class="field">
            <label for="f-pass">Password</label>
            <div class="input-wrap">
                <?= Html::activePasswordInput($model, 'password', [
                    'class' => 'input', 'id' => 'f-pass', 'required' => true, 'autocomplete' => 'new-password',
                    'minlength' => UserForm::MIN_PASSWORD, 'maxlength' => UserForm::MAKS_PASSWORD,
                ]) ?>
                <button type="button" class="icon-btn" data-lihat="f-pass" aria-label="Tampilkan password">
                    <?= Tampil::icon('eye', 18) ?>
                </button>
            </div>
            <?= $galat('password') ?>
            <p class="hint">Minimal <?= UserForm::MIN_PASSWORD ?> karakter.</p>
        </div>

        <div class="field">
            <label for="f-ulang">Ulangi password</label>
            <div class="input-wrap">
                <?= Html::activePasswordInput($model, 'password_ulang', [
                    'class' => 'input', 'id' => 'f-ulang', 'required' => true, 'autocomplete' => 'new-password',
                    'maxlength' => UserForm::MAKS_PASSWORD,
                ]) ?>
                <button type="button" class="icon-btn" data-lihat="f-ulang" aria-label="Tampilkan password">
                    <?= Tampil::icon('eye', 18) ?>
                </button>
            </div>
            <?= $galat('password_ulang') ?>
        </div>

        <div class="form-foot">
            <a class="btn" href="<?= Html::encode(Url::to(['pengguna/index'])) ?>">Batal</a>
            <button type="submit" class="btn primary">Simpan</button>
        </div>
    <?= Html::endForm() ?>
</div>
