<?php

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/vendor/yiisoft/yii2/Yii.php';
new yii\console\Application(require dirname(__DIR__) . '/config/console.php');

function periksa($kondisi, $pesan)
{
    if (!$kondisi) {
        throw new RuntimeException($pesan);
    }
    echo '[OK] ', $pesan, PHP_EOL;
}

// Validasi formulir tidak boleh mengakses database untuk input yang salah.
foreach ([['username' => ['nested' => ['x']], 'password' => 'x'],
    ['username' => 'admin', 'password' => ['x']],
    ['username' => '   ', 'password' => 'x']] as $input) {
    $login = new app\models\LoginForm($input);
    periksa(!$login->login(), 'Input login tidak valid ditolak tanpa mengakses database');
}
$login = new app\models\LoginForm(['username' => ' admin ', 'password' => ' secret ']);
periksa($login->validate() && $login->username === 'admin' && $login->password === ' secret ', 'Username dinormalisasi dan spasi password dipertahankan');

// Formulir tambah pengguna. Aturan keunikan username memerlukan database, jadi dilepas di sini.
class UserFormLokal extends app\models\UserForm
{
    public function rules()
    {
        return array_values(array_filter(parent::rules(), function ($aturan) {
            return $aturan[1] !== 'unique';
        }));
    }
}
$uji = new UserFormLokal(['username' => ' budi.santoso ', 'password' => 'rahasia1', 'password_ulang' => 'rahasia1']);
periksa($uji->validate() && $uji->username === 'budi.santoso', 'Pengguna baru valid dan username dinormalisasi');
$uji = new UserFormLokal(['username' => 'budi', 'password' => 'abc', 'password_ulang' => 'abc']);
periksa(!$uji->validate() && $uji->hasErrors('password'), 'Password kurang dari 6 karakter ditolak');
$uji = new UserFormLokal(['username' => 'budi', 'password' => 'rahasia1', 'password_ulang' => 'rahasia2']);
periksa(!$uji->validate() && $uji->hasErrors('password_ulang'), 'Ulangi password yang berbeda ditolak');
$uji = new UserFormLokal(['username' => 'budi', 'password' => str_repeat('a', 73), 'password_ulang' => str_repeat('a', 73)]);
periksa(!$uji->validate() && $uji->hasErrors('password'), 'Password lebih dari 72 karakter ditolak');
foreach (['ab', 'budi santoso', 'budi<script>', str_repeat('a', 51), '   '] as $nama) {
    $uji = new UserFormLokal(['username' => $nama, 'password' => 'rahasia1', 'password_ulang' => 'rahasia1']);
    periksa(!$uji->validate() && $uji->hasErrors('username'), 'Username tidak valid ditolak: ' . var_export($nama, true));
}
$uji = new UserFormLokal(['username' => ['x'], 'password' => ['y'], 'password_ulang' => 'z']);
periksa(!$uji->validate(), 'Isian berupa array ditolak');

// Schema mandiri agar aturan model dapat diperiksa tanpa database produksi.
class ArsipValidasi extends app\models\Arsip
{
    public function attributes()
    {
        return ['id', 'jenis', 'judul', 'tanggal', 'keterangan', 'file_name', 'file_stored', 'created_at', 'updated_at'];
    }
}
$arsip = new ArsipValidasi(['jenis' => 'tor', 'judul' => ' Contoh ', 'tanggal' => '2026-11-03', 'keterangan' => 'Gub']);
periksa($arsip->validate(), 'Arsip valid diterima');
$arsip->load(['ArsipValidasi' => ['jenis' => 'paparan', 'file_stored' => 'jahat.php']]);
periksa($arsip->jenis === 'tor' && $arsip->file_stored === null, 'Jenis arsip dan lokasi berkas tidak dapat diubah melalui mass assignment');
foreach (['2026-02-30', '0001-01-01', '10000-01-01'] as $tanggal) {
    $arsip->tanggal = $tanggal;
    periksa(!$arsip->validate(['tanggal']), 'Tanggal tidak valid ditolak: ' . $tanggal);
}
$arsip->judul = [['x']];
periksa(!$arsip->validate(['judul']), 'Judul berupa array ditolak');
$arsip->berkas = new yii\web\UploadedFile(['name' => 'script.php', 'tempName' => __FILE__, 'size' => filesize(__FILE__), 'error' => UPLOAD_ERR_OK]);
periksa(!$arsip->validate(['berkas']), 'Ekstensi berkas terlarang ditolak');
$arsip->berkas = new yii\web\UploadedFile(['name' => 'besar.pdf', 'tempName' => __FILE__, 'size' => 21 * 1024 * 1024, 'error' => UPLOAD_ERR_OK]);
periksa(!$arsip->validate(['berkas']), 'Berkas di atas 20 MB ditolak');
echo 'Semua pemeriksaan regresi lulus.', PHP_EOL;
