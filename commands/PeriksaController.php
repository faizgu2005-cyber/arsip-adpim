<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

/** Pemeriksaan instalasi tanpa mengubah database atau berkas arsip. */
class PeriksaController extends Controller
{
    public function actionIndex()
    {
        $gagal = false;
        $lapor = function ($baik, $pesan) use (&$gagal) {
            $this->stdout(($baik ? '[OK] ' : '[GAGAL] ') . $pesan . PHP_EOL);
            $gagal = $gagal || !$baik;
        };

        foreach (['fileinfo', 'mbstring', 'pdo_mysql'] as $ekstensi) {
            $lapor(extension_loaded($ekstensi), 'Ekstensi PHP ' . $ekstensi);
        }
        foreach (['runtime', 'uploads', 'web/assets'] as $folder) {
            $path = Yii::getAlias('@app/' . $folder);
            $lapor(is_dir($path) && is_writable($path), 'Folder dapat ditulis: ' . $folder);
        }

        try {
            Yii::$app->db->open();
            $lapor(true, 'Koneksi database');
            foreach (['user', 'arsip', 'login_gagal'] as $tabel) {
                $lapor(Yii::$app->db->schema->getTableSchema('{{%' . $tabel . '}}', true) !== null, 'Tabel ' . $tabel);
            }
        } catch (\Throwable $e) {
            $lapor(false, 'Database belum siap. Periksa kredensial di config/db-local.php, layanan MySQL, dan jalankan php yii migrate.');
        }

        return $gagal ? ExitCode::UNSPECIFIED_ERROR : ExitCode::OK;
    }
}
