<?php
namespace bricksasp\base;

use Yii;

/**
 * base module definition class
 */
class Module extends BaseModule
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'bricksasp\base\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
    }

}
