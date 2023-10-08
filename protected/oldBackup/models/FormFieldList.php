<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "FormFieldList".
 *
 * @property int $id
 * @property string $name
 * @property string $displayName
 *
 * @property FormField[] $formFields
 */
class FormFieldList extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'FormFieldList';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'displayName'], 'required'],
            [['name', 'displayName'], 'string', 'max' => 128],
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
            'displayName' => 'Display Name',
        ];
    }

    /**
     * Gets query for [[FormFields]].
     *
     * @return \yii\db\ActiveQuery|FormFieldQuery
     */
    public function getFormFields()
    {
        return $this->hasMany(FormField::className(), ['fieldId' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return FormFieldListQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new FormFieldListQuery(get_called_class());
    }
}
