<?php

namespace app\components;

use Yii;
use yii\helpers\Html;

/**
 * Pembantu tampilan: ikon SVG, format tanggal Indonesia, chip keterangan.
 */
class Tampil
{
    const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    const BULAN_PANJANG = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    const HARI = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

    private static $ikon = [
        'home' => '<path d="M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z"/>',
        'mic' => '<rect x="9" y="3" width="6" height="12" rx="3"/><path d="M5 11a7 7 0 0 0 14 0M12 18v3"/>',
        'presentation' => '<path d="M3 4h18M5 4v10h14V4M12 14v4M8 21l4-3 4 3"/>',
        'file' => '<path d="M7 3h7l5 5v13H7z"/><path d="M14 3v5h5"/>',
        'menu' => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'x' => '<path d="M6 6l12 12M18 6 6 18"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
        'edit' => '<path d="M4 20h4L19 9l-4-4L4 16z"/><path d="m13.5 6.5 4 4"/>',
        'trash' => '<path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/>',
        'download' => '<path d="M12 4v11m0 0-4-4m4 4 4-4M5 20h14"/>',
        'upload' => '<path d="M12 16V5m0 0-4 4m4-4 4 4M5 20h14"/>',
        'logout' => '<path d="M10 4H5v16h5M15 8l4 4-4 4M19 12H9"/>',
        'check' => '<path d="m5 12 5 5 9-10"/>',
        'alert' => '<circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16.5v.01"/>',
        'left' => '<path d="m15 6-6 6 6 6"/>',
        'right' => '<path d="m9 6 6 6-6 6"/>',
        'inbox' => '<path d="M4 13 6.5 5h11L20 13v6H4z"/><path d="M4 13h5l1 2h4l1-2h5"/>',
        'eye' => '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
        'eyeOff' => '<path d="M3 3l18 18M10.6 5.2A9.8 9.8 0 0 1 12 5c6.5 0 10 7 10 7a17 17 0 0 1-3.2 4.1M6.5 6.6A17 17 0 0 0 2 12s3.5 7 10 7a9.6 9.6 0 0 0 4.3-1M9.9 9.9a3 3 0 0 0 4.2 4.2"/>',
        'layers' => '<path d="m12 3 9 5-9 5-9-5zM3 13l9 5 9-5M3 17.5l9 5 9-5"/>',
        'paperclip' => '<path d="m20 11-8.5 8.5a5 5 0 0 1-7-7L13 4a3.5 3.5 0 0 1 5 5l-8.5 8.5a2 2 0 0 1-3-3L14 7"/>',
    ];

    /** Ikon garis sederhana (SVG inline), tanpa pustaka tambahan. */
    public static function icon($nama, $ukuran = 20)
    {
        $isi = isset(self::$ikon[$nama]) ? self::$ikon[$nama] : '';
        $u = (int) $ukuran;
        return '<svg class="icon" width="' . $u . '" height="' . $u . '" viewBox="0 0 24 24" fill="none" '
            . 'stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" '
            . 'aria-hidden="true" focusable="false">' . $isi . '</svg>';
    }

    /** 2026-10-14 -> 14 Okt 2026 */
    public static function tanggal($ymd)
    {
        $p = explode('-', (string) $ymd);
        if (count($p) !== 3 || (int) $p[1] < 1 || (int) $p[1] > 12) {
            return $ymd ? (string) $ymd : '-';
        }
        return (int) $p[2] . ' ' . self::BULAN[(int) $p[1] - 1] . ' ' . $p[0];
    }

    /** Rabu, 14 Oktober 2026 */
    public static function tanggalPanjang($ts = null)
    {
        $t = $ts === null ? time() : (int) $ts;
        return self::HARI[(int) date('w', $t)] . ', ' . (int) date('j', $t) . ' '
            . self::BULAN_PANJANG[(int) date('n', $t) - 1] . ' ' . date('Y', $t);
    }

    public static function chip($keterangan)
    {
        return Html::tag('span', Html::encode($keterangan), ['class' => 'chip ket-' . strtolower((string) $keterangan)]);
    }

    /** True bila berkas bisa dibuka langsung di browser (PDF, gambar, teks). */
    public static function bisaDibuka($namaBerkas)
    {
        return (bool) preg_match('/\.(pdf|jpe?g|png|txt)$/i', (string) $namaBerkas);
    }

    /** Alamat css/js dengan penanda waktu ubah supaya cache browser ikut diperbarui. */
    public static function aset($path)
    {
        $file = Yii::getAlias('@webroot/' . $path);
        $v = is_file($file) ? filemtime($file) : 0;
        return Html::encode(Yii::getAlias('@web/' . $path) . '?v=' . $v);
    }
}
