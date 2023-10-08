<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "FormField".
 *
 * @property int $id
 * @property int $formId
 * @property int $fieldId
 *
 * @property Form $form
 * @property FormFieldList $field
 */
class FormField extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'FormField';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['formId', 'fieldId'], 'required'],
            [['formId', 'fieldId'], 'integer'],
            // @todo Please remove those attributes that should not be searched.
            [['id','formId','fieldId'], 'safe', 'on'=>'search']
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
            'fieldId' => 'Field ID',
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
     * Gets query for [[Field]].
     *
     * @return \yii\db\ActiveQuery|FormFieldListQuery
     */
    public function getField()
    {
        return $this->hasOne(FormFieldList::className(), ['id' => 'fieldId']);
    }

    /**
     * {@inheritdoc}
     * @return FormFieldQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new FormFieldQuery(get_called_class());
    }
}
