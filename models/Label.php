<?php
namespace bricksasp\base\models;

use Yii;

/**
 * This is the model class for table "{{%label}}".
 *
 * @property int $id
 * @property string $name 标签名称
 * @property string $style 标签样式
 * @property int $user_id
 * @property int $created_at
 * @property int $updated_at
 */
class Label extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%label}}';
    }

    public function behaviors()
    {
        return [
            [
                'class' => \yii\behaviors\TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
            ],
            [
                'class' => \bricksasp\helpers\behaviors\UidBehavior::className(),
                'createdAtAttribute' => 'user_id',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'created_at', 'updated_at'], 'integer'],
            [['name', 'style'], 'string', 'max' => 16],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'style' => 'Style',
            'user_id' => 'User ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * 
     * @OA\Schema(
     *   schema="label",
     *   description="标签结构",
     *   allOf={
     *     @OA\Schema(
     *       @OA\Property(property="name", type="integer", description="标签名称"),
     *       @OA\Property(property="style", type="string", description="标签样式/颜色"),
     *     )
     *   }
     * )
     */
}
