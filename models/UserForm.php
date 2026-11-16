<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * Formulir tambah pengguna: username unik, password minimal 6 karakter, dan ulangi password.
 */
class UserForm extends Model
{
    const MIN_PASSWORD = 6;
    /** bcrypt hanya membaca 72 byte pertama, jadi lebih dari itu ditolak agar tidak terpotong diam-diam. */
    const MAKS_PASSWORD = 72;

    public $username;
    public $password;
    public $password_ulang;

    public function rules()
    {
        return [
            ['username', 'trim', 'skipOnArray' => true],
            [['username', 'password', 'password_ulang'], 'required', 'message' => '{attribute} wajib diisi.'],
            ['username', 'string', 'min' => 3, 'max' => 50,
                'tooShort' => 'Username minimal {min} karakter.',
                'tooLong' => 'Username maksimal {max} karakter.',
                'message' => 'Username tidak valid.',
            ],
            ['username', 'match', 'pattern' => '/^[A-Za-z0-9._-]+$/',
                'message' => 'Username hanya boleh berisi huruf, angka, titik, garis bawah, dan strip.',
            ],
            ['username', 'unique', 'targetClass' => User::class, 'targetAttribute' => 'username',
                'message' => 'Username sudah dipakai.',
            ],
            ['password', 'string', 'min' => self::MIN_PASSWORD,
                'tooShort' => 'Password minimal {min} karakter.',
                'message' => 'Password tidak valid.',
            ],
            ['password', 'validatePanjangPassword'],
            ['password_ulang', 'compare', 'compareAttribute' => 'password',
                'message' => 'Ulangi password harus sama dengan password.',
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'username' => 'Username',
            'password' => 'Password',
            'password_ulang' => 'Ulangi password',
        ];
    }

    public function validatePanjangPassword($atribut)
    {
        if (strlen((string) $this->$atribut) > self::MAKS_PASSWORD) {
            $this->addError($atribut, 'Password maksimal ' . self::MAKS_PASSWORD . ' karakter.');
        }
    }

    /** Validasi lalu simpan pengguna baru. Mengembalikan true bila berhasil. */
    public function simpan()
    {
        if (!$this->validate()) {
            return false;
        }

        $user = new User();
        $user->username = $this->username;
        $user->setPassword($this->password);
        $user->auth_key = Yii::$app->security->generateRandomString(32);
        $user->created_at = time();

        try {
            return $user->save(false);
        } catch (\yii\db\IntegrityException $e) {
            // Dua orang menambah username yang sama pada saat bersamaan.
            $this->addError('username', 'Username sudah dipakai.');
        } catch (\Throwable $e) {
            Yii::error($e, __METHOD__);
            $this->addError('username', 'Pengguna gagal disimpan. Silakan coba lagi atau hubungi administrator.');
        }
        return false;
    }
}
