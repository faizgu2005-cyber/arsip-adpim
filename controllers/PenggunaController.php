<?php

namespace app\controllers;

use app\models\User;
use app\models\UserForm;
use Yii;
use yii\filters\AccessControl;
use yii\web\BadRequestHttpException;
use yii\web\Controller;

/**
 * Daftar pengguna dan penambahan pengguna baru. Semua pengguna yang sudah masuk adalah administrator.
 */
class PenggunaController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['@']]],
            ],
        ];
    }

    public function actionIndex()
    {
        $rows = User::find()->orderBy(['id' => SORT_ASC])->all();
        return $this->render('index', ['rows' => $rows, 'sayaId' => (int) Yii::$app->user->id]);
    }

    public function actionCreate()
    {
        $model = new UserForm();
        $req = Yii::$app->request;

        if ($req->isPost && $model->load($req->post())) {
            foreach (['username', 'password', 'password_ulang'] as $atribut) {
                if (is_array($model->$atribut) || is_object($model->$atribut)) {
                    throw new BadRequestHttpException('Format data pengguna tidak valid.');
                }
            }
            if ($model->simpan()) {
                Yii::$app->session->setFlash('success', "Pengguna {$model->username} ditambahkan");
                return $this->redirect(['pengguna/index']);
            }
        }

        // Password tidak dikirim kembali ke browser.
        $model->password = '';
        $model->password_ulang = '';
        return $this->render('form', ['model' => $model]);
    }
}
