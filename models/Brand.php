<?php
namespace bricksasp\base\models;

use Yii;

/**
 * This is the model class for table "{{%brand}}".
 *
 * @property int $id 品牌ID
 * @property string $name 品牌名称
 * @property string $logo 品牌LOGO 图片ID
 * @property int $sort 品牌排序 越小越靠前
 * @property int $status
 * @property int $user_id
 * @property int $created_at 更新时间
 * @property int $updated_at 删除标志 有数据代表删除
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
