<?php

use app\components\Tampil;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $jenis */
/** @var string $label */
/** @var app\models\Arsip[] $rows */
/** @var yii\data\Pagination $pagination */
/** @var string $q */
/** @var string $ket */

$this->title = $label;
$total = $pagination->totalCount;
$awal = $pagination->offset;
$punyaFilter = $q !== '' || $ket !== '';
$halaman = $pagination->getPage() + 1;
$jumlahHalaman = $pagination->getPageCount();
$kecil = $jenis === 'tor' ? 'TOR' : mb_strtolower($label);
?>
<div class="page-head">
    <div>
        <h1><?= Html::encode($label) ?></h1>
        <p>Daftar <?= Html::encode($kecil) ?> yang sudah diarsipkan.</p>
    </div>
    <a class="btn primary" href="<?= Html::encode(Url::to(['arsip/create', 'jenis' => $jenis])) ?>">
        <?= Tampil::icon('plus', 18) ?> Tambah <?= Html::encode($label) ?>
    </a>
</div>

<div class="card table-card">
    <?= Html::beginForm(['arsip/index', 'jenis' => $jenis], 'get', ['class' => 'toolbar', 'role' => 'search']) ?>
        <div class="search">
            <button class="icon-btn" type="submit" aria-label="Cari"><?= Tampil::icon('search', 18) ?></button>
            <input class="input" type="search" name="q" value="<?= Html::encode($q) ?>" aria-label="Cari"
                   placeholder="Cari judul atau nama file" enterkeyhint="search">
        </div>
        <select class="select" name="ket" aria-label="Filter keterangan" data-kirim-otomatis>
            <option value="">Semua keterangan</option>
            <?php foreach (\app\models\Arsip::KETERANGAN as $k): ?>
                <option value="<?= Html::encode($k) ?>"<?= $k === $ket ? ' selected' : '' ?>><?= Html::encode($k) ?></option>
            <?php endforeach; ?>
        </select>
    <?= Html::endForm() ?>

    <?php if ($total === 0): ?>
        <div class="empty">
            <div class="ring"><?= Tampil::icon('inbox', 26) ?></div>
            <strong><?= $punyaFilter ? 'Tidak ada hasil' : 'Belum ada ' . Html::encode($kecil) ?></strong>
            <span><?= $punyaFilter
                ? 'Coba kata kunci lain atau ubah filter keterangan.'
                : 'Pilih Tambah ' . Html::encode($label) . ' untuk mencatat yang pertama.' ?></span>
        </div>
    <?php else: ?>
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>No</th><th><?= Html::encode($label) ?></th><th>Tanggal</th><th>Keterangan</th><th>File</th>
                        <th class="col-act">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $i => $r): $no = $awal + $i + 1; $judul = Html::encode($r->judul); ?>
                    <tr>
                        <td class="no"><?= $no ?></td>
                        <td class="judul"><span class="row-no"><?= $no ?>.</span><?= $judul ?></td>
                        <td class="tgl"><?= Html::encode(Tampil::tanggal($r->tanggal)) ?></td>
                        <td class="ket"><?= Tampil::chip($r->keterangan) ?></td>
                        <td class="file">
                            <?php if ($r->file_name): $buka = Tampil::bisaDibuka($r->file_name); ?>
                                <a class="file" href="<?= Html::encode(Url::to(['arsip/berkas', 'id' => $r->id])) ?>"
                                   <?= $buka ? 'target="_blank" rel="noopener"' : '' ?>
                                   title="<?= $buka ? 'Buka' : 'Unduh' ?> <?= Html::encode($r->file_name) ?>">
                                    <?= Tampil::icon($buka ? 'eye' : 'download', 14) ?>
                                    <span><?= Html::encode($r->file_name) ?></span>
                                </a>
                            <?php else: ?>
                                <span class="none">Belum ada</span>
                            <?php endif; ?>
                        </td>
                        <td class="act">
                            <a class="btn sm row-btn" href="<?= Html::encode(Url::to(['arsip/update', 'jenis' => $jenis, 'id' => $r->id])) ?>"
                               aria-label="Edit <?= $judul ?>" title="Edit">
                                <?= Tampil::icon('edit', 16) ?><span class="lbl">Edit</span>
                            </a>
                            <?= Html::beginForm(['arsip/delete', 'jenis' => $jenis, 'id' => $r->id], 'post', [
                                'onsubmit' => "return confirm('Hapus " . $kecil . " ini?')",
                            ]) ?>
                                <button class="btn sm row-btn text-danger" type="submit" data-hapus data-nama="<?= $judul ?>"
                                        aria-label="Hapus <?= $judul ?>" title="Hapus">
                                    <?= Tampil::icon('trash', 16) ?><span class="lbl">Hapus</span>
                                </button>
                            <?= Html::endForm() ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="pager">
            <span>Menampilkan <?= $awal + 1 ?>–<?= $awal + count($rows) ?> dari <?= $total ?></span>
            <?php if ($jumlahHalaman > 1): ?>
                <div class="ctrl">
                    <?php if ($halaman > 1): ?>
                        <a class="icon-btn" href="<?= Html::encode($pagination->createUrl($halaman - 2)) ?>" aria-label="Halaman sebelumnya"><?= Tampil::icon('left', 18) ?></a>
                    <?php else: ?>
                        <span class="icon-btn is-disabled" aria-hidden="true"><?= Tampil::icon('left', 18) ?></span>
                    <?php endif; ?>
                    <span><?= $halaman ?> / <?= $jumlahHalaman ?></span>
                    <?php if ($halaman < $jumlahHalaman): ?>
                        <a class="icon-btn" href="<?= Html::encode($pagination->createUrl($halaman)) ?>" aria-label="Halaman berikutnya"><?= Tampil::icon('right', 18) ?></a>
                    <?php else: ?>
                        <span class="icon-btn is-disabled" aria-hidden="true"><?= Tampil::icon('right', 18) ?></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<dialog class="dialog small" id="dlg-hapus" aria-labelledby="dlg-judul">
    <div class="confirm-body">
        <div class="ring"><?= Tampil::icon('trash', 24) ?></div>
        <h3 id="dlg-judul">Hapus <?= Html::encode($kecil) ?> ini?</h3>
        <p><b id="dlg-nama"></b><br>Data dan filenya akan dihapus permanen.</p>
    </div>
    <div class="dialog-foot">
        <button type="button" class="btn" data-tutup>Batal</button>
        <?= Html::beginForm('', 'post', ['id' => 'dlg-form']) ?>
            <button type="submit" class="btn danger">Hapus</button>
        <?= Html::endForm() ?>
    </div>
</dialog>
