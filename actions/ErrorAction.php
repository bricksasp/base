<?php
namespace bricksasp\base\actions;

use Yii;

class ErrorAction extends Action
{
    public function init()
    {
    	parent::init();
    }

    public function run()
    {
        $msg = Yii::$app->getErrorHandler()->exception->getMessage();
        if (is_numeric($msg)) return $this->fail(Yii::t('base',$msg), (int)$msg);
        return $this->fail($msg);
    }
}