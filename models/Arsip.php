<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;

/**
 * Satu baris arsip: sambutan, paparan, atau TOR.
 *
 * @property int $id
 * @property string $jenis        sambutan | paparan | tor
 * @property string $judul
 * @property string $tanggal      Y-m-d
 * @property string $keterangan   Gub | Wagub | Sekda
 * @property string|null $file_name    nama asli berkas (untuk ditampilkan)
 * @property string|null $file_stored  nama acak di folder uploads
 * @property int $created_at
 * @property int $updated_at
 */
class Arsip extends ActiveRecord
{
    const JENIS = ['sambutan' => 'Sambutan', 'paparan' => 'Paparan', 'tor' => 'TOR'];
    const KETERANGAN = ['Gub', 'Wagub', 'Sekda'];
    const EKSTENSI = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'txt'];
    const MAKS_MB = 20;

    /** Jenis berkas yang dibuka langsung di browser, lengkap dengan MIME yang kita tetapkan sendiri. */
    const DAPAT_DIBUKA = [
        'pdf' => 'application/pdf',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'txt' => 'text/plain; charset=utf-8',
    ];

    /** @var UploadedFile|string|null berkas yang diunggah pada formulir */
    public $berkas;
    /** @var mixed kotak centang "Hapus file saat ini" */
    public $hapus_file;

    public static function tableName()
    {
        return '{{%arsip}}';
    }

    public function behaviors()
    {
        return [TimestampBehavior::class];
    }

    public function rules()
    {
        return [
            ['judul', 'trim'],
            [['judul', 'tanggal', 'keterangan'], 'required'],
            ['judul', 'string', 'max' => 500],
            ['tanggal', 'date', 'format' => 'php:Y-m-d', 'message' => 'Tanggal tidak valid.'],
            ['keterangan', 'in', 'range' => self::KETERANGAN, 'message' => 'Keterangan tidak valid.'],
            ['hapus_file', 'boolean'],
            ['berkas', 'file',
                'skipOnEmpty' => true,
                'extensions' => self::EKSTENSI,
                // Berkas Office sering terbaca sebagai "zip", jadi cukup periksa ekstensinya.
                'checkExtensionByMimeType' => false,
                'maxSize' => self::MAKS_MB * 1024 * 1024,
                'wrongExtension' => 'Jenis berkas tidak diizinkan. Gunakan PDF, Word, PowerPoint, Excel, gambar, atau teks.',
                'tooBig' => 'Ukuran berkas maksimal ' . self::MAKS_MB . ' MB.',
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'judul' => isset(self::JENIS[$this->jenis]) ? self::JENIS[$this->jenis] : 'Judul',
            'tanggal' => 'Tanggal',
            'keterangan' => 'Keterangan',
            'berkas' => 'File',
        ];
    }

    public static function folderUnggah()
    {
        $dir = Yii::getAlias('@app/uploads');
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        return $dir;
    }

    /** Lokasi berkas di disk, atau null bila tidak ada berkas. */
    public function pathBerkas()
    {
        return $this->file_stored ? self::folderUnggah() . DIRECTORY_SEPARATOR . basename($this->file_stored) : null;
    }

    /**
     * Validasi, simpan berkas (bila ada), lalu simpan baris ke database.
     * Berkas lama dihapus hanya setelah penyimpanan baru berhasil.
     */
    public function simpan()
    {
        if (!$this->validate()) {
            return false;
        }

        $lama = $this->file_stored;
        $baru = null;
        $unggah = $this->berkas instanceof UploadedFile ? $this->berkas : null;

        if ($unggah) {
            $baru = bin2hex(random_bytes(16)) . '.' . strtolower($unggah->extension);
            if (!$unggah->saveAs(self::folderUnggah() . DIRECTORY_SEPARATOR . $baru)) {
                $this->addError('berkas', 'Berkas gagal disimpan di server. Periksa izin folder uploads.');
                return false;
            }
            $this->file_name = mb_substr(basename(str_replace('\\', '/', $unggah->name)), 0, 255);
            $this->file_stored = $baru;
        } elseif ($this->hapus_file && $lama) {
            $this->file_name = null;
            $this->file_stored = null;
        }

        if (!$this->save(false)) {
            if ($baru) {
                @unlink(self::folderUnggah() . DIRECTORY_SEPARATOR . $baru);
            }
            return false;
        }

        if ($lama && $lama !== $this->file_stored) {
            @unlink(self::folderUnggah() . DIRECTORY_SEPARATOR . basename($lama));
        }
        return true;
    }

    public function afterDelete()
    {
        parent::afterDelete();
        $path = $this->pathBerkas();
        if ($path && is_file($path)) {
            @unlink($path);
        }
    }

    /** Angka untuk dashboard: total per jenis, jumlah per keterangan, berkas, dan entri terbaru. */
    public static function ringkasan()
    {
        $hasil = ['jenis' => [], 'denganBerkas' => 0, 'terbaru' => []];
        foreach (array_keys(self::JENIS) as $j) {
            $hasil['jenis'][$j] = ['total' => 0, 'per' => array_fill_keys(self::KETERANGAN, 0)];
        }

        $baris = static::find()
            ->select(['jenis', 'keterangan', 'n' => 'COUNT(*)'])
            ->groupBy(['jenis', 'keterangan'])
            ->asArray()->all();
        foreach ($baris as $b) {
            if (!isset($hasil['jenis'][$b['jenis']])) {
                continue;
            }
            $hasil['jenis'][$b['jenis']]['total'] += (int) $b['n'];
            if (isset($hasil['jenis'][$b['jenis']]['per'][$b['keterangan']])) {
                $hasil['jenis'][$b['jenis']]['per'][$b['keterangan']] += (int) $b['n'];
            }
        }

        $hasil['denganBerkas'] = (int) static::find()->where(['not', ['file_stored' => null]])->count();
        $hasil['terbaru'] = static::find()
            ->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC])
            ->limit(5)->all();
        return $hasil;
    }
}
