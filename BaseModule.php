<?php
namespace bricksasp\base;

use Yii;

/**
 * base module definition class
 */
class BaseModule extends \yii\base\Module
{
    public function init()
    {
        parent::init();
        
        Yii::configure(Yii::$app->errorHandler, ['errorAction' => $this->id . '/default/error']);
        Yii::configure(Yii::$app->response, ['on beforeSend' => function ($event) {
            $response = $event->sender;
            if ($response->format != yii\web\Response::FORMAT_XML && is_array($response->data)) {
                $response->format = yii\web\Response::FORMAT_JSON;
            }
        }]);
    }

}
