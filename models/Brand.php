<?php
namespace bricksasp\base\models;

use Yii;

/**
 * This is the model class for table "{{%brand}}".
 *
 */
class Brand extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%brand}}';
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
            [['sort', 'status', 'user_id', 'created_at', 'updated_at'], 'integer'],
            [['name', 'logo'], 'string', 'max' => 64],
        ];
    }

    /**
     * 
     * @OA\Schema(
     *   schema="brand",
     *   description="品牌结构",
     *   allOf={
     *     @OA\Schema(
     *       @OA\Property(property="name", type="integer", description="名称"),
     *       @OA\Property(property="logo", ref="#/components/schemas/file", description="logo图片"),
     *     )
     *   }
     * )
     */

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'logo' => 'Logo',
            'sort' => 'Sort',
            'status' => 'Status',
            'user_id' => 'User ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getImage()
    {
        return $this->hasOne(File::className(), ['id' => 'logo'])->select(['id','file_url']);
    }
}
