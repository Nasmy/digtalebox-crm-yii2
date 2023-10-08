<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "customfieldsubarea".
 *
 * @property int $id
 * @property int $customFieldId
 * @property string $subarea
 */
class CustomFieldSubArea extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'CustomFieldSubArea';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['customFieldId', 'subarea'], 'required'],
            [['customFieldId'], 'integer'],
            [['subarea'], 'string', 'max' => 256],
            [['id', 'customFieldId', 'subarea'],'safe', 'on' => 'search'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'customFieldId' => 'Custom Field',
            'subarea' => 'Subarea',
        ];
    }

    /**
     * @return array of sub area
     */
    public static function getList($area, $id)
    {
        $result = array();
        $data = self::find()->where('customFieldId=:customFieldId', [':customFieldId'=> $id])->all();
        foreach ($data as $val) {
            $subAreas = CustomType::getSubAreas($area);
            if(isset($subAreas[$val['subarea']])) {
                $result[] = Yii::t('messages', $subAreas[$val['subarea']]);
            }

        }
        return $result;
    }


    /**
     * @return string CSV of the sub area list
     */
    public static function getCsvList($area, $id)
    {
        return implode(",", self::getList($area, $id));
    }

    /**
     * {@inheritdoc}
     * @return CustomfieldsubareaQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CustomfieldsubareaQuery(get_called_class());
    }
}
