<?php
namespace bricksasp\base\controllers;

use bricksasp\base\BaseController;
use bricksasp\base\Config;
use bricksasp\base\models\Brand;
use bricksasp\helpers\Tools;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\web\HttpException;

/**
 * LableController implements the CRUD actions for Brand model.
 */
class BrandController extends BaseController {
	/**
	 * {@inheritdoc}
	 */
	public function behaviors() {
		return array_merge(parent::behaviors(), [
			'verbs' => [
				'class' => VerbFilter::className(),
				'actions' => [
					'delete' => ['POST'],
				],
			],
		]);
	}

	/**
	 * Lists all Brand models.
	 * @return mixed
	 */
	public function actionIndex() {
		$params = Yii::$app->request->queryParams;
		$dataProvider = new ActiveDataProvider([
			'query' => Brand::find($this->dataOwnerUid())->with('image')->select(['id', 'name', 'logo', 'status', 'created_at', 'updated_at']),
			'pagination' => [
				'pageSize' => $params['pageSize'] ?? 10,
			],
			'sort' => [
				'defaultOrder' => [
					'id' => SORT_DESC,
				],
			],
		]);
		return $this->pageFormat($dataProvider, ['image' => [['file_url' => ['implode', ['', [Config::instance()->web_url, '###']], 'array']]]]);
	}

	/**
	 * Displays a single Brand model.
	 * @param integer $id
	 * @return mixed
	 * @throws HttpException if the model cannot be found
	 */
	public function actionView() {
		$model = $this->findModel(Yii::$app->request->get('id'));
		$data = $model->toArray();
		$data['image'] = $model['image'] ? Tools::format_array($model['image'], ['file_url' => ['implode', ['', [Config::instance()->web_url, '###']], 'array']]) : (object) [];
		return $this->success($data);
	}

	/**
	 * Creates a new Brand model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 * @return mixed
	 */
	public function actionCreate() {
		$model = new Brand();

		if ($model->load(Yii::$app->request->post(), '') && $model->save()) {
			return $this->success();
		}

		return $this->fail($model->errors);
	}

	/**
	 * Updates an existing Brand model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id
	 * @return mixed
	 * @throws HttpException if the model cannot be found
	 */
	public function actionUpdate() {
		$model = $this->findModel(Yii::$app->request->post('id'));

		if ($model->load(Yii::$app->request->post(), '') && $model->save()) {
			return $this->success();
		}

		return $this->fail();
	}

	/**
	 * Deletes an existing Brand model.
	 * If deletion is successful, the browser will be redirected to the 'index' page.
	 * @param integer $id
	 * @return mixed
	 * @throws HttpException if the model cannot be found
	 */
	public function actionDelete() {
		return $this->findModel(Yii::$app->request->post('id'))->delete() !== false ? $this->success() : $this->fail();
	}

	/**
	 * Finds the Brand model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 * @param integer $id
	 * @return Brand the loaded model
	 * @throws HttpException if the model cannot be found
	 */
	protected function findModel($id) {
		if (($model = Brand::findOne($id)) !== null) {
			return $model;
		}

		throw new HttpException(200, Yii::t('base', 40001));
	}
}
