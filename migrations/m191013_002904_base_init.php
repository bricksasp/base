<?php

use yii\db\Migration;

/**
 * Class m191013_002904_base_init
 */
class m191013_002904_base_init extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m191013_002904_base_init cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191013_002904_base_init cannot be reverted.\n";

        return false;
    }
    */
}
