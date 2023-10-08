<?php

namespace app\models;

use app\components\RActiveRecord;
use Yii;

/**
 * This is the model class for table "customvaluesearch".
 *
 * @property int $id
 * @property int $customFieldId
 * @property int $relatedId
 * @property string $fieldValue
 * @property string $createdAt
 */
class CustomValueSearch extends RActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'CustomValueSearch';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['customFieldId','relatedId','createdAt'], 'required'],
            [['customFieldId', 'relatedId'], 'integer'],
             [['fieldValue'], 'safe'],
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            [['id','customFieldId','relatedId','fieldValue','createdAt'],'safe', 'on'=>'search'],

        ];
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
        );
    }



    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'customFieldId' => 'Custom Field',
            'relatedId' => 'Related',
            'fieldValue' => 'Field Value',
            'createdAt' => 'Created At',
        ];
    }



    /**
     * {@inheritdoc}
     * @return CustomvaluesearchQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CustomvaluesearchQuery(get_called_class());
    }
}
