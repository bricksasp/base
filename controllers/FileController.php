<?php
namespace bricksasp\base\controllers;

use Yii;
use bricksasp\base\Config;
use bricksasp\base\models\File;
use yii\data\ActiveDataProvider;
use bricksasp\base\BaseController;
use yii\web\HttpException;
use yii\filters\VerbFilter;
use bricksasp\base\actions\FileAction;
use bricksasp\helpers\Tools;
use ciniran\excel\SaveExcel;

/**
 * LableController implements the CRUD actions for File model.
 */
class FileController extends BaseController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
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
     * @OA\Post(
     *     path="/base/file/fileupload",
     *     summary="文件上传",
     *     description="",
     *     tags={"base模块"},
     *     operationId="",
     *     @OA\Parameter(
     *       description="登录凭证",
     *       name="X-Token",
     *       in="header",
     *       @OA\Schema(
     *         type="string"
     *       )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     description="file to upload",
     *                     property="file",
     *                     type="file"
     *                 ),
     *                 required={"file"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *       response=200,
     *       description="文件结构",
     *       @OA\MediaType(
     *           mediaType="application/json",
     *           @OA\Schema(ref="#/components/schemas/file"),
     *       ),
     *     ),
     * )
     * */
    public function actions()
    {
        return [
            'fileupload' => [
                'class' => FileAction::className(),
            ],
            'filedelete' => [
                'class' => FileAction::className(),
            ],
            'filechunk' => [
                'class' => FileAction::className(),
            ],
            'filelist' => [
                'class' => FileAction::className(),
            ],
        ];
    }

    /**
     * 登录可访问 其他需授权
     * @return array
     */
    public function allowAction()
    {
        return array_merge(parent::allowAction(),[
            'fileupload',
            'filelist',
            'export',
            'import',
        ]);
    }

    /**
     * Lists all File models.
     * @return mixed
     */
    public function actionIndex()
    {
        $params = Yii::$app->request->queryParams;
        $dataProvider = new ActiveDataProvider([
            'query' => File::find(['user_id'=>$this->dataOwnerUid(), 'status'=>1]),
            'pagination' => [
                'pageSize' => $params['pageSize'] ?? 10,
            ],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC
                ]
            ]
        ]);
        return $this->pageFormat($dataProvider,['file_url'=>['implode',['',[Config::instance()->web_url,'###']],'array']],1);
    }

    /**
     * Displays a single File model.
     * @param integer $id
     * @return mixed
     * @throws HttpException if the model cannot be found
     */
    public function actionView()
    {
        return $this->success($this->findModel(Yii::$app->request->get('id')));
    }

    /**
     * Creates a new File model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new File();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->success();
        }

        return $this->fail($model->errors);
    }

    /**
     * Updates an existing File model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws HttpException if the model cannot be found
     */
    public function actionUpdate()
    {
        $model = $this->findModel(Yii::$app->request->post('id'));
        $model->setScenario('update');
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->success();
        }

        return $this->fail();
    }

    /**
     * Deletes an existing File model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws HttpException if the model cannot be found
     */
    public function actionDelete()
    {
        $model = $this->findModel(Yii::$app->request->post('id'));
        $model->setScenario('update');
        $model->status = 2;
        if ($model->save()) {
            return $this->success();
        }

        return $this->fail();
    }

    /**
     * 导出
     * @return file 
     */
    public function actionExport()
    {
        $searchModel = $this->sourceModel(false);
        $queryParams = $this->queryFilters(false);
        $dataProvider = $searchModel->search($queryParams);
        $file_name = $searchModel::EXCEL_NAME . date('Y-m-d H:i:s');
        $base_path = Config::instance()->file_base_path ? Config::instance()->file_base_path : Yii::$app->basePath . '/web';
        $file_path = '/file/excel/' . date('Ymd');
        // return $this->success($base_path . $file_path . '/' . $file_name);
        Tools::make_dir($base_path . $file_path);
        $excel = new SaveExcel([
             'dataProvider' => $dataProvider,
             //'show' => true,  //是否对值进行转换
             'fields' => $queryParams['fields'],
             'format' => SaveExcel::XLXS, // 输出版本
             'all' => true,  //导出全部数据
             'relation' => [], //模型关系数据
             'fileName' => $file_name
        ]);
        $excel->dataProviderToExcel();
        $xlsData = ob_get_contents();
        $fileName = $file_path . '/' . $file_name . '.' . $excel->format;
        ob_end_clean();
        file_put_contents($base_path . $fileName , $xlsData);
        $model = new File();
        $model->scenario = 'create';
        $model->load([
            'id' => Tools::get_sn(10),
            'name' => $file_name,
            'file_size' => strlen($xlsData),
            'file_url' => $fileName,
            'mime' => SaveExcel::XLXS == $excel->format ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' : 'application/vnd.ms-excel',
            'ext' => $excel->format,
            'user_id' => $this->uid,
        ]);
        $model->save();
        return $this->success(Config::instance()->web_url . $fileName,$model->errors);
    }

    /**
     * 导入
     * @return file 
     */
    public function actionImport()
    {

        return $this->success();
    }

    public function sourceModel($type=true)
    {
        $sourceModel = Yii::$app->request->post('sourceModel');
        list($module, $model) = explode('.', $sourceModel);
        $class = '\\bricksasp\\' . $module . '\\models' . '\\' . ucfirst($model);
        if ($type) {
            return new $class();
        }
        $class .= 'Search';
        return new $class();
    }

    /**
     * 导入导出 字段检测
     * @return Field the model 
     */
    public function actionExcelfield()
    {
        return $this->success($this->sourceModel()->excelField());
    }

    public function actionDestoryfile()
    {
        $model = $this->findModel(Yii::$app->request->post('id'));

        if ($model->delete() !== false) {
            Tools::deleteFile(Yii::$app->basePath . '/web' . $model->file_url);
            return $this->success();
        }
        return $this->fail();
    }

    /**
     * Finds the File model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return File the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = File::findOne($id)) !== null) {
            return $model;
        }

        throw new HttpException(200,Yii::t('base',40001));
    }
}
