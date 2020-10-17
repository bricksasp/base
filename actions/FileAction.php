<?php
namespace bricksasp\base\actions;

use bricksasp\base\Config;
use bricksasp\base\models\File;
use bricksasp\helpers\Tools;
use Yii;
use yii\data\ActiveDataProvider;
use yii\validators\FileValidator;
use yii\web\UploadedFile;

/**
 * 文件上传操作
 */
class FileAction extends Action {

	public $config = [
		'allowFiles' => ['png', 'jpg', 'jpeg', 'gif', 'bmp', 'flv', 'swf', 'mkv', 'avi', 'rm', 'rmvb', 'mpeg', 'mpg', 'ogg', 'ogv', 'mov', 'wmv', 'mp4', 'webm', 'mp3', 'wav', 'mid', 'rar', 'zip', 'tar', 'gz', '7z', 'bz2', 'cab', 'iso', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'pdf', 'txt', 'md', 'xml',
		],
		'maxSize' => 2048000,
	];

	public $validatorConfig = [];

	public $base_path = null;
	public $file_path = null;
	public $temp_path = null;

	public function init() {
		if (!$this->base_path) {
			$this->base_path = Config::instance()->file_base_path ? Config::instance()->file_base_path : Yii::$app->basePath . '/web';
		}

		if (!$this->file_path) {
			$this->file_path = Config::instance()->file_path ? Config::instance()->file_path : '/file/' . date('Y') . '/' . date('m');
		}

		if (!$this->temp_path) {
			$this->temp_path = Config::instance()->file_temp_path ? Config::instance()->file_temp_path : '/file/temp';
		}

	}

	public function run() {
		switch ($this->id) {
		case 'fileupload':
			$res = $this->upload();
			break;
		case 'filedelete':
			$res = $this->delete();
			break;
		case 'filechunk':
			$res = $this->uploadChunk();
			break;
		case 'filelist':
			$res = $this->fileList();
			break;

		default:
			$res = $this->fail();
			break;
		}
		header('Content-type:application/json; charset=UTF-8');
		echo json_encode($res);exit();
	}

	/**
	 * 单文件上传
	 */
	protected function upload() {
		$file = $this->checkFile();
		$file_id = Tools::get_sn(10);
		$dir = $this->base_path . $this->file_path;
		Tools::make_dir($dir);

		$file_url = $this->file_path . '/' . md5($file_id) . '.' . $file->extension;
		$real_path = $dir . '/' . md5($file_id) . '.' . $file->extension;
		if ($file->saveAs($real_path)) {
			$img = getimagesize($real_path);
			$model = new File();
			$model->setScenario('create');
			$model->load([
				'id' => $file_id,
				'name' => Yii::$app->request->post('name') ? Yii::$app->request->post('name') : $file->name,
				'mime' => $file->type,
				'ext' => $file->extension,
				'file_size' => $file->size,
				'file_url' => $file_url,
				'photo_width' => empty($img[0]) ? 0 : $img[0],
				'photo_hight' => empty($img[1]) ? 0 : $img[1],
				'user_id' => (int) $this->getUserId(),
			], '');

			if ($model->save()) {
				Tools::format_array($model, ['file_url' => ['implode', ['', [Config::instance()->web_url, '###']], 'array']]);
				$data = $model->toArray();
				// $data['fileId'] = Yii::$app->request->post('fileId');
				// $data['fileChunk'] = Yii::$app->request->post('fileChunk');
				// $data['fileIndex'] = Yii::$app->request->post('fileIndex');
				// $data['fileEnd'] = Yii::$app->request->post('fileEnd');
				return $this->success($data);
			}
			return $this->fail($model->errors);
		}

		return $this->fail();
	}

	/**
	 * 分片文件上传
	 */
	protected function uploadChunk() {
		$count = (int) Yii::$app->request->post('chunkCount'); //总分片数
		$chunk = Yii::$app->request->post('chunkIndex'); //分片数
		if (!$count || !$chunk) {
			return $this->fail(Yii::t('base', 51001));
		}

		$file = $this->checkFile('fileBlob');

		$temp_dir = $this->base_path . $this->temp_path;
		Tools::make_dir($temp_dir);

		$filenamePrefix = '/' . $this->getUserId() . '_' . md5($file->baseName) . '_';

		$temp_file = $temp_dir . $filenamePrefix . $chunk . '.' . $file->extension;
		if ($file->saveAs($temp_file)) {
			$chunks = glob($temp_dir . $filenamePrefix . '*' . '.' . $file->extension);
			if ($count && count($chunks) == $count) {
				$file_id = Tools::get_sn(10);
				$dir = $this->base_path . $this->file_path;
				Tools::make_dir($dir);

				$file_url = $this->file_path . '/' . md5($file_id) . '.' . $file->extension;
				$real_path = $dir . '/' . md5($file_id) . '.' . $file->extension;
				$handle = fopen($real_path, 'a+');

				foreach ($chunks as $fl) {
					fwrite($handle, file_get_contents($fl));
					@unlink($fl);
				}
				fclose($handle);

				$img = getimagesize($real_path);
				$model = new File();
				$model->load([
					'id' => $file_id,
					'name' => Yii::$app->request->post('name') ? Yii::$app->request->get('name') : $file->name,
					'mime' => $file->type,
					'ext' => $file->extension,
					'file_size' => filesize($real_path),
					'file_url' => $file_url,
					'photo_width' => empty($img[0]) ? 0 : $img[0],
					'photo_hight' => empty($img[1]) ? 0 : $img[1],
					'user_id' => (int) $this->getUserId(),
				], '');
				if ($model->save()) {
					Tools::format_array($model, ['file_url' => ['implode', ['', [Config::instance()->web_url, '###']], 'array']]);
					return $this->success($model);
				}
				return $this->fail();
			}
			return $this->success($chunk);
		}
		return $this->fail(Yii::t('base', 51002, $chunk), $chunk);
	}

	/**
	 * 删除文件
	 */
	protected function delete() {
		$id = Yii::$app->request->post('file_id');
		$model = File::findOne(['id' => $id]);
		if (!$model) {
			throw new \yii\web\HttpException(200, Yii::t('spu', 40001));
		}

		if (Tools::deleteFile($this->base_path . $model->file_url)) {
			$model->delete();
			return $this->success();
		}
		return $this->fail();
	}

	/**
	 * 文件检测
	 */
	protected function checkFile($fname = 'file') {
		$file = UploadedFile::getInstanceByName($fname); //接收文件
		$validator = new \yii\validators\FileValidator($this->validatorConfig);
		$validator->extensions = $this->config['allowFiles'];
		$validator->maxSize = $this->config['maxSize'];
		if (!$validator->validate($file, $error)) {
			throw new \yii\web\HttpException(200, $error);
		}

		return $file;
	}

	protected function fileList() {
		$params = Yii::$app->request->queryParams;
		$dataProvider = new ActiveDataProvider([
			'query' => File::find()->select(['name', 'file_url'])
			// ->andFilterWhere(['user_id' => $this->getUserId()])
				->andFilterWhere(['like', 'mime', $params['type']]),
			'pagination' => [
				'pageSize' => empty($params['limit']) ? 10 : $params['limit'],
				'page' => empty($params['page']) ? 0 : $params['page'] - 1,
			],
		]);
		return $this->success([
			'pageCount' => $dataProvider->pagination->pageCount + 1,
			'page' => $dataProvider->pagination->page + 1,
			'limit' => $dataProvider->pagination->limit,
			'list' => $dataProvider->models,
		]);
	}

	/**
	 * 合并分片文件
	 */
	public static function mergeChunkFile($fname = '', $count = 0, $ext = '') {

		return false;
	
    }
}