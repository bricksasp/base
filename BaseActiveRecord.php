<?php
namespace bricksasp\base;

use Yii;
use bricksasp\helpers\Tools;
use yii\db\ActiveQuery;

class BaseActiveRecord extends \yii\db\ActiveRecord
{
    public function load($data,$formName = '')
    {
        return parent::load($data,$formName);
    }
    /**
     * 数据区分
     * @param  array|mix  $condition 
     * @return model ActiveRecord            
     */
    public static function find($condition=[])
    {
        $model = parent::find();

        $map = [];
        if ($condition){
            if (is_array($condition)) 
                $map = array_merge($map, $condition); 
            elseif (is_numeric($condition)) 
                $map['user_id'] = $condition;
        }
        $model->andFilterWhere($map);
        return $model;
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if (!$this->isNewRecord && $this->hasAttribute('user_id')) {
            $uid = Yii::$app->getUser()->getId();
            if ($uid && $this->user_id && $this->user_id != $uid) {
                Tools::exceptionBreak(Yii::t('base',40003));
            }
        }
        return true;
    }

    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        if ($this->hasAttribute('user_id')) {
            $uid = Yii::$app->getUser()->getId();
            if ($this->user_id && $this->user_id != $uid) {
                Tools::exceptionBreak(Yii::t('base',40003));
            }
        }
        return true;
    }

    /**
     * 关联数据排序
     */
    public static function sortItem($data,$sort)
    {
        $items = $data[0];
        $data = array_column($items, $data[1]);
        $sort = array_column($sort[0], $sort[1], $sort[2]);
        $k = [];

        foreach ($data as $v) {
            $k[] = $sort[$v];
        }
        $items = array_combine($k, $items);
        ksort($items);
        return array_values($items);
    }

}