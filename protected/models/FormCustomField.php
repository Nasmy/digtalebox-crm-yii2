<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "FormCustomField".
 *
 * @property int $id
 * @property int $formId
 * @property string $customFieldId Custom Field Name
 *
 * @property Form $form
 */
class FormCustomField extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'FormCustomField';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['formId', 'customFieldId'], 'required'],
            [['formId'], 'integer'],
            [['customFieldId'], 'string', 'max' => 64],
            [['formId'], 'exist', 'skipOnError' => true, 'targetClass' => Form::className(), 'targetAttribute' => ['formId' => 'id']],
            // @todo Please remove those attributes that should not be searched.
            [['id','formId','customFieldId'], 'safe', 'on'=>'search'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'formId' => 'Form ID',
            'customFieldId' => 'Custom Field ID',
        ];
    }

    /**
     * Gets query for [[Form]].
     *
     * @return \yii\db\ActiveQuery|FormQuery
     */
    public function getForm()
    {
        return $this->hasOne(Form::className(), ['id' => 'formId']);
    }

    /**
     * {@inheritdoc}
     * @return FormCustomFieldQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new FormCustomFieldQuery(get_called_class());
    }
}
