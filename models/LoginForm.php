<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * Formulir masuk dengan pembatasan percobaan salah per alamat IP.
 */
class LoginForm extends Model
{
    public $username;
    public $password;

    public function rules()
    {
        return [
            ['username', 'trim', 'skipOnArray' => true],
            [['username', 'password'], 'required', 'message' => '{attribute} wajib diisi.'],
            ['username', 'string', 'max' => 50],
            ['password', 'string', 'max' => 4096],
        ];
    }

    public function attributeLabels()
    {
        return ['username' => 'Username', 'password' => 'Password'];
    }

    public function login()
    {
        if (!$this->validate()) {
            return false;
        }

        $ip = (string) Yii::$app->request->userIP;
        if ($this->terblokir($ip)) {
            $this->addError('password', 'Terlalu banyak percobaan gagal. Coba lagi beberapa menit lagi.');
            return false;
        }

        $user = User::findByUsername(trim($this->username));
        if (!$user || !$user->validatePassword($this->password)) {
            $this->catatGagal($ip);
            $this->addError('password', 'Username atau password salah.');
            return false;
        }

        Yii::$app->db->createCommand()->delete('{{%login_gagal}}', ['ip' => $ip])->execute();
        return Yii::$app->user->login($user, 0);
    }

    private function terblokir($ip)
    {
        $batas = time() - (int) Yii::$app->params['loginBlokirDetik'];
        $jumlah = (new \yii\db\Query())
            ->from('{{%login_gagal}}')
            ->where(['ip' => $ip])->andWhere(['>', 'waktu', $batas])
            ->count();
        return (int) $jumlah >= (int) Yii::$app->params['loginMaxGagal'];
    }

    private function catatGagal($ip)
    {
        $db = Yii::$app->db;
        $db->createCommand()->insert('{{%login_gagal}}', ['ip' => $ip, 'waktu' => time()])->execute();
        // Bersihkan catatan lama agar tabel tidak membesar.
        $db->createCommand()->delete('{{%login_gagal}}', ['<', 'waktu', time() - 86400])->execute();
    }
}
