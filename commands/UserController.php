<?php

namespace app\commands;

use app\models\User;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

/**
 * Mengelola akun pengguna dari terminal.
 *
 *   php yii user/ganti-password                -> ganti password admin
 *   php yii user/ganti-password budi           -> ganti password pengguna "budi"
 *   php yii user/tambah budi                   -> tambah pengguna baru
 *   php yii user/daftar                        -> lihat daftar pengguna
 */
class UserController extends Controller
{
    public function actionDaftar()
    {
        foreach (User::find()->orderBy('id')->all() as $u) {
            $this->stdout($u->id . "\t" . $u->username . "\n");
        }
        return ExitCode::OK;
    }

    public function actionGantiPassword($username = 'admin')
    {
        $user = User::findByUsername($username);
        if (!$user) {
            $this->stderr("Pengguna '$username' tidak ditemukan.\n", Console::FG_RED);
            return ExitCode::DATAERR;
        }
        $baru = $this->mintaPassword();
        if ($baru === null) {
            return ExitCode::DATAERR;
        }
        $user->setPassword($baru);
        $user->auth_key = Yii::$app->security->generateRandomString(32);
        $user->save(false);
        $this->stdout("Password '$username' berhasil diganti.\n", Console::FG_GREEN);
        return ExitCode::OK;
    }

    public function actionTambah($username)
    {
        if (User::findByUsername($username)) {
            $this->stderr("Pengguna '$username' sudah ada.\n", Console::FG_RED);
            return ExitCode::DATAERR;
        }
        $baru = $this->mintaPassword();
        if ($baru === null) {
            return ExitCode::DATAERR;
        }
        $user = new User();
        $user->username = $username;
        $user->setPassword($baru);
        $user->auth_key = Yii::$app->security->generateRandomString(32);
        $user->created_at = time();
        $user->save(false);
        $this->stdout("Pengguna '$username' dibuat.\n", Console::FG_GREEN);
        return ExitCode::OK;
    }

    private function mintaPassword()
    {
        $a = Console::prompt('Password baru (min. 6 karakter):');
        if (mb_strlen($a) < 6) {
            $this->stderr("Password terlalu pendek.\n", Console::FG_RED);
            return null;
        }
        $b = Console::prompt('Ulangi password:');
        if ($a !== $b) {
            $this->stderr("Password tidak sama.\n", Console::FG_RED);
            return null;
        }
        return $a;
    }
}
