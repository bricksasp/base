<?php
namespace bricksasp\base\models;

use Yii;

/**
 * This is the model class for table "{{%file}}".
 *
 * @property string $id
 * @property string $name
 * @property string $file_url
 * @property int $photo_width
 * @property int $photo_hight
 * @property int $user_id
 * @property int $file_size
 * @property string $mime
 * @property string $ext 扩展名
 * @property int $status 1 有用 0 删除的
 * @property int $created_at
 * @property int $updated_at
 */
class File extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%file}}';
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
            [
                'class' => \bricksasp\helpers\behaviors\SnBehavior::className(),
                'attribute' => 'id',
                'type' => \bricksasp\helpers\behaviors\SnBehavior::SN_FILE,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['photo_width', 'photo_hight', 'user_id', 'file_size', 'status', 'created_at', 'updated_at'], 'integer'],
            [['id', 'name'], 'string', 'max' => 64],
            [['mime', 'file_url'], 'string', 'max' => 255],
            [['ext'], 'string', 'max' => 32],
            [['id'], 'unique'],
            [['status'], 'default', 'value' => 1],
            [['name', 'status'], 'required', 'on' => ['update','create']]
        ];
    }

    public function scenarios()
    {
        return [
            'update' => ['name', 'status'],
            'create' => ['id', 'status', 'name', 'mime', 'ext', 'file_size', 'file_url', 'photo_width', 'photo_hight','user_id', 'created_at', 'updated_at']
        ];
    }

    /**
     * 
     *
     * @OA\Schema(
     *  schema="file",
     *  description="文件结构",
     *  @OA\Property(
     *     property="file_url",
     *     type="string",
     *     description="访问地址"
     *  ),
     *  @OA\Property(
     *     property="mime",
     *     type="string",
     *     description="mime"
     *  )
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
            'file_url' => 'File Url',
            'photo_width' => 'Photo Width',
            'photo_hight' => 'Photo Hight',
            'user_id' => 'User ID',
            'file_size' => 'File Size',
            'mime' => 'Mime',
            'ext' => 'Ext',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

}
