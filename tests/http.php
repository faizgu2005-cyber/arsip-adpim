<?php

// Jalankan: php tests/http.php http://localhost/arsip-adpim/web
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/vendor/yiisoft/yii2/Yii.php';
new yii\console\Application(require dirname(__DIR__) . '/config/console.php');

$base = rtrim($argv[1] ?? 'http://localhost/arsip-adpim/web', '/');
$prefix = 'uji_http_' . bin2hex(random_bytes(8));
$password = bin2hex(random_bytes(24));
$user = new app\models\User();
$user->username = $prefix;
$user->setPassword($password);
$user->auth_key = Yii::$app->security->generateRandomString(32);
$user->created_at = time();
$ch = curl_init();
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIEFILE => '', CURLOPT_FOLLOWLOCATION => false, CURLOPT_TIMEOUT => 20]);
$temp = tempnam(Yii::getAlias('@runtime'), 'http_');
file_put_contents($temp, 'Berkas pengujian ' . $prefix);

function cekHttp($baik, $pesan)
{
    if (!$baik) {
        throw new RuntimeException($pesan);
    }
    echo '[OK] ', $pesan, PHP_EOL;
}
function halaman($path, $kode = 200, $data = null)
{
    global $ch, $base;
    curl_setopt($ch, CURLOPT_URL, $base . $path);
    curl_setopt($ch, CURLOPT_POST, $data !== null);
    if ($data !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    $body = curl_exec($ch);
    cekHttp($body !== false && curl_getinfo($ch, CURLINFO_HTTP_CODE) === $kode, $path . ' HTTP ' . $kode);
    return $body;
}
function tokenHttp($html)
{
    if (!preg_match('/name="_csrf-arsip" value="([^"]+)"/', $html, $m)) {
        throw new RuntimeException('Token CSRF tidak ditemukan');
    }
    return html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
}

try {
    cekHttp($user->save(false), 'Akun uji sementara dibuat');
    halaman('/sambutan', 302);
    $csrf = tokenHttp(halaman('/masuk'));
    $body = halaman('/masuk', 200, http_build_query(['_csrf-arsip' => $csrf, 'LoginForm' => ['username' => $prefix, 'password' => 'salah']]));
    cekHttp(strpos($body, 'Username atau password salah') !== false, 'Password salah ditolak');
    halaman('/masuk', 302, http_build_query(['_csrf-arsip' => $csrf, 'LoginForm' => ['username' => $prefix, 'password' => $password]]));
    $dashboard = halaman('/');
    cekHttp(strpos($dashboard, $prefix) !== false, 'Dashboard menampilkan pengguna yang masuk');
    $csrf = tokenHttp($dashboard);

    foreach (['sambutan', 'paparan', 'tor'] as $jenis) {
        halaman('/' . $jenis);
        halaman('/' . $jenis . '/tambah');
        halaman('/' . $jenis . '/tambah', 302, [
            '_csrf-arsip' => $csrf,
            'Arsip[judul]' => $prefix . '_' . $jenis,
            'Arsip[tanggal]' => '2026-11-03',
            'Arsip[keterangan]' => 'Gub',
            'Arsip[berkas]' => new CURLFile($temp, 'text/plain', 'uji.txt'),
        ]);
        $arsip = app\models\Arsip::findOne(['judul' => $prefix . '_' . $jenis]);
        cekHttp($arsip !== null && is_file($arsip->pathBerkas()), 'Data dan unggahan tersimpan: ' . $jenis);
        $id = $arsip->id;
        $pathLama = $arsip->pathBerkas();
        $isi = halaman('/berkas/' . $id);
        cekHttp($isi === file_get_contents($temp), 'Isi berkas sesuai unggahan');
        halaman('/berkas/' . $id . '?unduh=1');
        $hasil = halaman('/' . $jenis . '?q=' . urlencode($prefix) . '&ket=Gub');
        cekHttp(strpos($hasil, $prefix . '_' . $jenis) !== false, 'Pencarian dan filter: ' . $jenis);
        $edit = '/' . $jenis . '/' . $id . '/edit';
        halaman($edit);
        halaman($edit, 302, [
            '_csrf-arsip' => $csrf,
            'Arsip[judul]' => $prefix . '_' . $jenis . '_edit',
            'Arsip[tanggal]' => '2026-11-04',
            'Arsip[keterangan]' => 'Sekda',
            'Arsip[berkas]' => new CURLFile($temp, 'text/plain', 'pengganti.txt'),
        ]);
        $arsip->refresh();
        cekHttp($arsip->keterangan === 'Sekda' && $arsip->file_name === 'pengganti.txt' && !is_file($pathLama), 'Edit dan penggantian berkas: ' . $jenis);
        $pathBaru = $arsip->pathBerkas();
        halaman($edit, 302, http_build_query(['_csrf-arsip' => $csrf, 'Arsip' => [
            'judul' => $prefix . '_' . $jenis . '_edit', 'tanggal' => '2026-11-04', 'keterangan' => 'Sekda', 'hapus_file' => '1',
        ]]));
        $arsip->refresh();
        cekHttp($arsip->file_stored === null && !is_file($pathBaru), 'Hapus lampiran: ' . $jenis);
        halaman('/berkas/' . $id, 404);
        halaman('/' . $jenis . '/' . $id . '/hapus', 405);
        halaman('/' . $jenis . '/' . $id . '/hapus', 302, http_build_query(['_csrf-arsip' => $csrf]));
        cekHttp(app\models\Arsip::findOne($id) === null, 'Hapus arsip: ' . $jenis);
    }
    halaman('/keluar', 405);
    halaman('/keluar', 302, http_build_query(['_csrf-arsip' => $csrf]));
    halaman('/tor', 302);
    echo 'Semua pengujian HTTP lulus.', PHP_EOL;
} finally {
    foreach (app\models\Arsip::find()->where(['like', 'judul', $prefix])->all() as $arsip) {
        $arsip->delete();
    }
    Yii::$app->db->createCommand()->delete('{{%user}}', ['username' => $prefix])->execute();
    if (is_file($temp)) {
        unlink($temp);
    }
    curl_close($ch);
    echo 'Data dan berkas uji sementara dibersihkan.', PHP_EOL;
}
