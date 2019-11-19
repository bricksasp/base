<?php
namespace bricksasp\base\models;

use Yii;

/**
 * This is the model class for table "{{%setting}}".
 *
 * @property string $key
 * @property string $val
 * @property string $description
 */
class Setting extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%setting}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['key'], 'required'],
            [['key'], 'string', 'max' => 64],
            [['val', 'description'], 'string', 'max' => 255],
            [['key'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'key' => 'Key',
            'val' => 'Val',
            'description' => 'Description',
        ];
    }

    /**
     * 保存设置
     */
    public static function saveData($data,$uid,$keyPrefix)
    {
        $transaction = self::getDb()->beginTransaction();
        try {
            self::deleteAll(['and', ['user_id' => $uid], ['like', 'key', $keyPrefix]]);
        
            $shop_setting = [];
            foreach ($data as $key => $value) {
                if (strpos($key, $keyPrefix) === false) {
                    continue;
                }
                $row['key'] = $key;
                $row['val'] = is_array($value) ? json_encode($value) : $value;
                $row['user_id'] = $uid;
                $shop_setting[] = $row;
            }
            self::getDb()->createCommand()
            ->batchInsert(self::tableName(), ['key', 'val', 'user_id'], $shop_setting)
            ->execute();
            $transaction->commit();
            return true;
        } catch(\Exception $e) {
            $transaction->rollBack();
            throw $e;
        } catch(\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
        return false;
    }

    /**
     * @OA\Schema(
     *   schema="setting",
     *   description="设置数据结构",
     *   allOf={
     *     @OA\Schema(
     *       @OA\Property(property="key-name", type="string", description="key-val键值对"),
     *     )
     *   }
     * )
     */
    public static function getSetting($uid, $keyPrefix)
    {
        $data = array_column(self::find($uid)->andWhere(['like', 'key', $keyPrefix])->all(), 'val', 'key');
        foreach ($data as $key => $value) {
            if (strpos($value, '[') !== false) {
                $value = json_decode($value);
            }
            $data[$key] = $value;
        }

        return $data;
    }
}
