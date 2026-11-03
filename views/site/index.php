<?php

use app\components\Tampil;
use app\models\Arsip;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var array $ringkas */

$this->title = 'Dashboard';
$j = $ringkas['jenis'];
// ikon, warna kartu, warna batang grafik per jenis
$meta = [
    'sambutan' => ['mic', 'teal', 'a'],
    'paparan' => ['presentation', 'blue', 'b'],
    'tor' => ['file', 'gold', 'c'],
];
$total = 0;
$maks = 1;
foreach ($j as $nama => $d) {
    $total += $d['total'];
    $maks = max($maks, max($d['per']));
}
?>
<div class="page-head">
    <div>
        <h1>Dashboard Overview</h1>
        <p>Selamat datang, <?= Html::encode(Yii::$app->user->identity->username) ?>.<span class="hide-sm"> <?= Html::encode(Tampil::tanggalPanjang()) ?>.</span></p>
    </div>
</div>

<div class="stats">
    <div class="stat">
        <span class="stat-icon green"><?= Tampil::icon('layers', 22) ?></span>
        <span class="stat-body"><span class="stat-value"><?= $total ?></span><span class="stat-label">Total</span></span>
    </div>
    <?php foreach ($meta as $jenis => [$ikon, $warna]): ?>
        <a class="stat" href="<?= Html::encode(Url::to(['arsip/index', 'jenis' => $jenis])) ?>">
            <span class="stat-icon <?= $warna ?>"><?= Tampil::icon($ikon, 22) ?></span>
            <span class="stat-body"><span class="stat-value"><?= $j[$jenis]['total'] ?></span><span class="stat-label"><?= Html::encode(Arsip::JENIS[$jenis]) ?></span></span>
        </a>
    <?php endforeach; ?>
    <div class="stat">
        <span class="stat-icon gray"><?= Tampil::icon('paperclip', 22) ?></span>
        <span class="stat-body"><span class="stat-value"><?= $ringkas['denganBerkas'] ?></span><span class="stat-label">Berkas</span></span>
    </div>
</div>

<div class="dash-grid">
    <section class="card card-pad">
        <div class="card-head">
            <h3>Arsip per keterangan</h3>
            <div class="legend">
                <?php foreach ($meta as $jenis => [$ikon, $warna, $kelas]): ?>
                    <span><i class="<?= $kelas ?>"></i> <?= Html::encode(Arsip::JENIS[$jenis]) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="chart" role="img" aria-label="Jumlah arsip per keterangan">
            <?php foreach (Arsip::KETERANGAN as $k): ?>
                <div>
                    <div class="chart-bars">
                        <?php foreach ($meta as $jenis => [$ikon, $warna, $kelas]):
                            $v = $j[$jenis]['per'][$k];
                            $r = number_format($v / $maks, 4, '.', ''); ?>
                            <div class="chart-col">
                                <span class="chart-val"><?= $v ?></span>
                                <div class="chart-bar <?= $v ? $kelas : 'zero' ?>" style="--r:<?= $r ?>"
                                     title="<?= Html::encode(Arsip::JENIS[$jenis] . ' ' . $k . ': ' . $v) ?>"></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="chart-label"><?= Html::encode($k) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="card card-pad">
        <div class="card-head"><h3>Entri terbaru</h3></div>
        <?php if ($ringkas['terbaru']): ?>
            <ul class="recent">
                <?php foreach ($ringkas['terbaru'] as $r): $m = $meta[$r->jenis]; ?>
                    <li>
                        <span class="ico <?= $m[2] ?>"><?= Tampil::icon($m[0], 18) ?></span>
                        <div class="t">
                            <strong><?= Html::encode($r->judul) ?></strong>
                            <span><?= Html::encode(Arsip::JENIS[$r->jenis]) ?> · <?= Html::encode(Tampil::tanggal($r->tanggal)) ?></span>
                        </div>
                        <?= Tampil::chip($r->keterangan) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="none">Belum ada entri.</p>
        <?php endif; ?>
    </section>
</div>
