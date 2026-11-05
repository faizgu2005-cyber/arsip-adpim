<?php

namespace app\controllers;

use app\models\Arsip;
use Yii;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

/**
 * Satu controller untuk Sambutan dan Paparan; bedanya hanya parameter `jenis`.
 */
class ArsipController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['@']]],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['delete' => ['post']],
            ],
        ];
    }

    public function actionIndex($jenis)
    {
        $label = $this->label($jenis);
        $req = Yii::$app->request;
        $q = $req->get('q', '');
        $q = is_string($q) ? trim($q) : '';
        $ket = $req->get('ket', '');
        $ket = is_string($ket) ? $ket : '';
        if (!in_array($ket, Arsip::KETERANGAN, true)) {
            $ket = '';
        }

        $query = Arsip::find()->where(['jenis' => $jenis]);
        if ($q !== '') {
            $query->andWhere(['or', ['like', 'judul', $q], ['like', 'file_name', $q]]);
        }
        if ($ket !== '') {
            $query->andWhere(['keterangan' => $ket]);
        }

        $pagination = new Pagination([
            'totalCount' => (int) $query->count(),
            'pageSize' => (int) Yii::$app->params['pageSize'],
            'forcePageParam' => false,
            'pageSizeParam' => false,
        ]);
        $rows = $query->orderBy(['tanggal' => SORT_DESC, 'id' => SORT_DESC])
            ->offset($pagination->offset)->limit($pagination->limit)->all();

        return $this->render('index', compact('jenis', 'label', 'rows', 'pagination', 'q', 'ket'));
    }

    public function actionCreate($jenis)
    {
        $label = $this->label($jenis);
        $model = new Arsip([
            'jenis' => $jenis,
            'tanggal' => date('Y-m-d'),
            'keterangan' => Arsip::KETERANGAN[0],
        ]);
        if ($this->simpan($model)) {
            Yii::$app->session->setFlash('success', "$label disimpan");
            return $this->redirect(['arsip/index', 'jenis' => $jenis]);
        }
        return $this->render('form', ['model' => $model, 'jenis' => $jenis, 'label' => $label, 'baru' => true]);
    }

    public function actionUpdate($jenis, $id)
    {
        $label = $this->label($jenis);
        $model = $this->cari($jenis, $id);
        if ($this->simpan($model)) {
            Yii::$app->session->setFlash('success', "$label disimpan");
            return $this->redirect(['arsip/index', 'jenis' => $jenis]);
        }
        return $this->render('form', ['model' => $model, 'jenis' => $jenis, 'label' => $label, 'baru' => false]);
    }

    public function actionDelete($jenis, $id)
    {
        $label = $this->label($jenis);
        $this->cari($jenis, $id)->delete();
        Yii::$app->session->setFlash('success', "$label dihapus");
        return $this->redirect(['arsip/index', 'jenis' => $jenis]);
    }

    /**
     * Menyajikan berkas. PDF, gambar, dan teks dibuka langsung di browser;
     * jenis lain (Word, Excel, PowerPoint) terunduh. Tambahkan ?unduh=1 untuk memaksa unduh.
     */
    public function actionBerkas($id, $unduh = 0)
    {
        $model = Arsip::findOne($id);
        $path = $model ? $model->pathBerkas() : null;
        if (!$path || !is_file($path)) {
            throw new NotFoundHttpException('Berkas tidak ditemukan.');
        }

        $ext = strtolower(pathinfo($model->file_stored, PATHINFO_EXTENSION));
        $inline = isset(Arsip::DAPAT_DIBUKA[$ext]) && !$unduh;
        $mime = isset(Arsip::DAPAT_DIBUKA[$ext]) ? Arsip::DAPAT_DIBUKA[$ext] : 'application/octet-stream';

        $res = Yii::$app->response;
        $res->headers->set('X-Content-Type-Options', 'nosniff');
        $res->sendFile($path, $model->file_name ?: $model->file_stored, ['inline' => $inline, 'mimeType' => $mime]);
        // sendFile menimpa header cache; berkas ini hanya untuk pengguna yang sudah masuk.
        $res->headers->set('Cache-Control', 'private, max-age=0, must-revalidate');
        $res->headers->remove('Pragma');
        return $res;
    }

    private function simpan(Arsip $model)
    {
        $req = Yii::$app->request;
        if (!$req->isPost || !$model->load($req->post())) {
            return false;
        }
        foreach (['judul', 'tanggal', 'keterangan', 'hapus_file'] as $atribut) {
            if (is_array($model->$atribut) || is_object($model->$atribut)) {
                throw new \yii\web\BadRequestHttpException('Format data arsip tidak valid.');
            }
        }
        $model->berkas = UploadedFile::getInstance($model, 'berkas');
        return $model->simpan();
    }

    private function label($jenis)
    {
        if (!isset(Arsip::JENIS[$jenis])) {
            throw new NotFoundHttpException('Halaman tidak ditemukan.');
        }
        return Arsip::JENIS[$jenis];
    }

    private function cari($jenis, $id)
    {
        $model = Arsip::findOne(['id' => $id, 'jenis' => $jenis]);
        if (!$model) {
            throw new NotFoundHttpException('Data tidak ditemukan.');
        }
        return $model;
    }
}
