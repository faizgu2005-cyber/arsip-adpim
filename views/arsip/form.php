<?php

use app\components\Tampil;
use app\models\Arsip;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Arsip $model */
/** @var string $jenis */
/** @var string $label */
/** @var bool $baru */

$this->title = ($baru ? 'Tambah ' : 'Edit ') . $label;
$kecil = $jenis === 'tor' ? 'TOR' : mb_strtolower($label);
$galat = function ($atribut) use ($model) {
    $e = $model->getFirstError($atribut);
    return $e ? '<p class="err" role="alert">' . Html::encode($e) . '</p>' : '';
};
$daftarKet = array_combine(Arsip::KETERANGAN, Arsip::KETERANGAN);
$kembali = Url::to(['arsip/index', 'jenis' => $jenis]);
$namaAwal = $model->file_name ?: 'Pilih file atau seret ke sini';
?>
<div class="page-head">
    <div>
        <h1><?= Html::encode($this->title) ?></h1>
        <p><?= $baru ? 'Isi data ' . Html::encode($kecil) . ' yang akan diarsipkan.' : 'Ubah data ' . Html::encode($kecil) . ' ini.' ?></p>
    </div>
</div>

<div class="card card-pad form-card">
    <?= Html::beginForm('', 'post', ['enctype' => 'multipart/form-data']) ?>
        <div class="field">
            <label for="f-judul"><?= Html::encode($label) ?></label>
            <?= Html::activeTextInput($model, 'judul', [
                'class' => 'input', 'id' => 'f-judul', 'required' => true, 'maxlength' => 500,
                'placeholder' => 'Judul ' . $kecil, 'autofocus' => $baru,
            ]) ?>
            <?= $galat('judul') ?>
        </div>

        <div class="field">
            <label for="f-tgl">Tanggal</label>
            <?= Html::activeInput('date', $model, 'tanggal', ['class' => 'input', 'id' => 'f-tgl', 'required' => true]) ?>
            <?= $galat('tanggal') ?>
        </div>

        <div class="field">
            <label for="f-ket">Keterangan</label>
            <?= Html::activeDropDownList($model, 'keterangan', $daftarKet, ['class' => 'select', 'id' => 'f-ket']) ?>
            <?= $galat('keterangan') ?>
        </div>

        <div class="field">
            <span class="label">File</span>
            <div class="drop">
                <?= Html::activeFileInput($model, 'berkas', [
                    'id' => 'f-file', 'aria-label' => 'Pilih file',
                    'accept' => '.' . implode(',.', Arsip::EKSTENSI),
                ]) ?>
                <span class="ico"><?= Tampil::icon('upload', 20) ?></span>
                <span class="txt">
                    <strong id="nama-file" data-awal="<?= Html::encode($namaAwal) ?>"><?= Html::encode($namaAwal) ?></strong>
                    <span>PDF, Word, PowerPoint, Excel, gambar, atau teks. Maksimal <?= Arsip::MAKS_MB ?> MB.</span>
                </span>
            </div>
            <?= $galat('berkas') ?>
            <?php if (!$baru && $model->file_stored): ?>
                <label class="check" id="baris-hapus-file">
                    <?= Html::activeCheckbox($model, 'hapus_file', ['label' => false, 'id' => 'f-hapus']) ?>
                    Hapus file saat ini
                </label>
            <?php endif; ?>
        </div>

        <div class="form-foot">
            <a class="btn" href="<?= Html::encode($kembali) ?>">Batal</a>
            <button type="submit" class="btn primary">Simpan</button>
        </div>
    <?= Html::endForm() ?>
</div>
