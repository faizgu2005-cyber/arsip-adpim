<?php

namespace app\controllers;

use app\models\Arsip;
use app\models\LoginForm;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

class SiteController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'logout'],
                'rules' => [['allow' => true, 'roles' => ['@']]],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['logout' => ['post']],
            ],
        ];
    }

    public function actions()
    {
        return ['error' => ['class' => 'yii\web\ErrorAction']];
    }

    public function actionIndex()
    {
        return $this->render('index', ['ringkas' => Arsip::ringkasan()]);
    }

    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $this->layout = 'login';

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post())) {
            if (!is_string($model->username) || !is_string($model->password)) {
                throw new \yii\web\BadRequestHttpException('Format username atau password tidak valid.');
            }
            if ($model->login()) {
                return $this->goBack();
            }
        }
        $model->password = '';
        return $this->render('login', ['model' => $model]);
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->redirect(['site/login']);
    }
}
