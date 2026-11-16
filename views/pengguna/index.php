<?php

use app\components\Tampil;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\User[] $rows */
/** @var int $sayaId */

$this->title = 'Pengguna';
?>
<div class="page-head">
    <div>
        <h1>Pengguna</h1>
        <p>Akun yang dapat masuk ke aplikasi ini. Total <?= count($rows) ?> pengguna.</p>
    </div>
    <a class="btn primary" href="<?= Html::encode(Url::to(['pengguna/create'])) ?>">
        <?= Tampil::icon('userPlus', 18) ?> Tambah Pengguna
    </a>
</div>

<div class="card table-card">
    <div class="table-scroll">
        <table class="table-pengguna">
            <thead>
                <tr><th>No</th><th>Username</th><th>Dibuat</th></tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $i => $r): ?>
                <tr>
                    <td class="no"><?= $i + 1 ?></td>
                    <td class="judul">
                        <span class="row-no"><?= $i + 1 ?>.</span><?= Html::encode($r->username) ?>
                        <?php if ((int) $r->id === $sayaId): ?><span class="chip me">Anda</span><?php endif; ?>
                    </td>
                    <td class="tgl"><?= Html::encode(Tampil::tanggal(date('Y-m-d', (int) $r->created_at))) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
