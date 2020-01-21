<?php
namespace bricksasp\base;

use bricksasp\base\actions\ErrorAction;
use bricksasp\helpers\Tools;
use bricksasp\helpers\traits\BaseTrait;
use bricksasp\rbac\models\redis\Token;
use Yii;
use yii\web\Controller;

/**
 * Base controller for the `Module` module
 */
class BaseController extends Controller {
	use BaseTrait;
	public $uid = null; //当前用户id
	public $ownerId = null; //数据所属商户id
	public $request_identity = null; //请求身份
	public $request_entrance = null; //请求入口

	/**
	 * @inheritdoc
	 */
	public function behaviors() {
		return [
			'authenticator' => [
				'class' => \bricksasp\helpers\behaviors\CompositeAuth::className(),
				'tokenParam' => 'access-token', //商户数据token
				'tokenHeader' => 'X-Token', //用户数据
				'saas_on' => true,
				'rbac_on' => true,
				'saas_owner_id' => 1,
				'exemption' => [
					'logout',
					'login',
					'signup',
				],
			],
		];
	}

	public function actions() {
		return [
			'error' => [
				'class' => ErrorAction::className(),
			],
		];
	}

	/**
	 * 免登录可访问
	 * @return array
	 */
	public function allowNoLoginAction() {
		return [
			'error',
			'index',
			'view',
			'detail',
		];
	}

	/**
	 * 登录可访问 其他需授权
	 * @return array
	 */
	public function allowAction() {
		return [
			'create',
			'update',
			'delete',
		];
	}

	public function init() {

	}

	/**
	 * 数据所属用户uid
	 * @return uid
	 */
	protected function dataOwnerUid() {
		if ($this->request_identity == Token::IDENTITY_CURD) {
			return $this->uid;
		} elseif ($this->request_identity == Token::IDENTITY_AUTHORIZE) {
			return $this->ownerId;
		}

		return $this->ownerId;
	}

	/**
	 *
	 * 分页格式化
	 * @param  modle $dataProvider
	 * @param  array   $with         规则
	 * @param  integer $type         1 处理第一层 2 处理第二层 3 全部
	 * @return array
	 */
	protected function pageFormat($dataProvider, $with = [], $type = 2, $fg=1) {
		$dataProvider->pagination->totalCount = $dataProvider->totalCount;
		$list = [];
		if ($with) {
			switch ($type) {
			case 1:
				$w1 = $with;
				break;
			case 2:
				$w2 = $with;
				break;
			case 3:
				$w1 = $with[0];
				$w2 = $with[1];
				break;
			}
			foreach ($dataProvider->models as $item) {
				$row = $item->toArray();
				if (in_array($type, [1, 3])) {
					$row = Tools::format_array($row, $w1, $fg);
				}
				if (in_array($type, [2, 3])) {
					foreach ($w2 as $relation => $rules) {
						$relrow = $item->$relation;
						if (is_array($rules)) {
							foreach ($rules as $filed => $rule) {
								if ($relrow && !is_array($relrow)) $relrow = $relrow->toArray();

								$relrow = Tools::format_array($relrow ? $relrow : [], $rule, $fg);
							}
						}
						$row[$relation] = $relrow ?? (object)[];
					}
				}
				$list[] = $row;
			}
		} else {
			$list = $dataProvider->models;
		}
		return $this->success([
			'pageCount' => $dataProvider->pagination->pageCount,
			'totalCount' => $dataProvider->pagination->totalCount,
			'page' => $dataProvider->pagination->page + 1,
			'pageSize' => $dataProvider->pagination->limit,
			'list' => $list,
		]);
	}

	/**
	 * 获取查询参数
	 * @return array
	 */
	protected function queryFilters($type=true) {
		if ($type) {
			$params = Yii::$app->request->queryParams;
		}else{
			$params = Yii::$app->request->post();
		}
		$params['user_id'] = $this->dataOwnerUid();
		$params['owner_id'] = $this->ownerId;
		
		if ($this->request_entrance == Token::TOKEN_TYPE_BACKEND) {
			$params['data_all'] = true;
		}elseif ($this->request_entrance == Token::TOKEN_TYPE_FRONTEND) {
			$params['data_all'] = false;
		}else{
			$params['data_all'] = null;
		}

		return $params;
	}

	/**
	 * 数据所属查询条件
	 * @return array
	 */
	protected function ownerCondition()
	{
		return [
			'user_id' => $this->uid,
			'owner_id' => $this->ownerId,
		];
	}
}
