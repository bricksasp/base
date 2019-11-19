<?php
namespace bricksasp\base;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * base module definition class
 */
class Config extends \yii\base\BaseObject {
	public $web_url = 'http://localhost:8080'; //web地址

	public $file_base_path = null; //文件根路径

	public $file_temp_path = null; //文件分片缓存路径

	public $file_path = null; //文件相对路径

	/**
	 * @var self Instance of self
	 */
	private static $_instance;

	/**
	 * Create instance of self
	 * @return static
	 */
	public static function instance() {
		if (self::$_instance === null) {
			$type = ArrayHelper::getValue(Yii::$app->params, 'bricksasp.configs', []);

			if (is_array($type) && empty($type['class'])) {
				$type['class'] = static::className();
			}

			return self::$_instance = Yii::createObject($type);
		}

		return self::$_instance;
	}
}
