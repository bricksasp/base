<?php
namespace bricksasp\base\controllers;

use Yii;
use bricksasp\base\models\Region;
use yii\data\ActiveDataProvider;
use bricksasp\base\BaseController;
use yii\web\HttpException;
use yii\filters\VerbFilter;

/**
 * RegionController implements the CRUD actions for Region model.
 */
class RegionController extends BaseController
{

    public function allowNoLoginAction() {
        return [
            'area',
            'tree'
        ];
    }
    /**
     * Lists all Region models.
     * @OA\Get(path="/base/region/tree",
     *   summary="地区树",
     *   tags={"base模块"},
     *   @OA\Response(
     *     response=200,
     *     description="列表结构",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/regionList"),
     *     ),
     *   ),
     * )
     *
     * @OA\Schema(
     *   schema="regionList",
     *   description="地区树结构",
     *   allOf={
     *     @OA\Schema(
     *       @OA\Property(property="id", type="integer", description="地区id"),
     *       @OA\Property(property="code", type="integer", description="编码"),
     *       @OA\Property( property="name", type="string", description="名称"),
     *       @OA\Property( property="children", type="array", description="名称", @OA\Items(
     *            @OA\Property(property="id", type="integer", description="地区id"),
     *            @OA\Property(property="code", type="integer", description="编码"),
     *            @OA\Property( property="name", type="string", description="名称"),
     *         ),
     *       ),
     *     )
     *   }
     * )
     */
    public function actionTree()
    {
        // return $this->success(Region::setPid());
        $model = new Region();
        return $this->success($model->tree());
    }

    /**
     * Displays a single Region model.
     * @param integer $id
     * @return mixed
     * @throws HttpException if the model cannot be found
     */
    public function actionView()
    {
        return $this->success($this->findModel(Yii::$app->request->get('id')));
    }

    /**
     * Creates a new Region model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Region();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->success();
        }

        return $this->fail($model->errors);
    }

    /**
     * Updates an existing Region model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws HttpException if the model cannot be found
     */
    public function actionUpdate()
    {
        $model = $this->findModel(Yii::$app->request->post('id'));

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->success();
        }

        return $this->fail();
    }

    /**
     * Deletes an existing Region model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws HttpException if the model cannot be found
     */
    public function actionDelete()
    {
        return $this->findModel(Yii::$app->request->post('id'))->delete() !== false ? $this->success() : $this->fail();
    }

    /**
     * Finds the Region model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Region the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Region::findOne($id)) !== null) {
            return $model;
        }

        throw new HttpException(200,Yii::t('base',40001));
    }

    /**
     * 区域级联选择
     * @OA\Get(path="/base/region/area",
     *   summary="区域级联选择(省市区乡)",
     *   tags={"base模块"},
     *   @OA\Parameter(
     *     description="地区id",
     *     name="id",
     *     in="query",
     *     required=true,
     *     @OA\Schema(
     *       type="integer",
     *       default="0",
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="列表结构",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(@OA\Property(property="data", type="array", description="地区id", @OA\Items(ref="#/components/schemas/region"))),
     *     ),
     *   ),
     * )
     *
     * @OA\Schema(
     *   schema="region",
     *   description="地区树结构",
     *   @OA\Property(property="id", type="integer", description="地区id"),
     *   @OA\Property(property="code", type="integer", description="编码"),
     *   @OA\Property( property="name", type="string", description="名称"),
     * )
     */
    public function actionArea($id=0)
    {
        return $this->success(Region::find()->select(['id','name'])->where(['parent_id' => $id])->all());
    }
}
