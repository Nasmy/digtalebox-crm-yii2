<?php


namespace app\components\Validations;


use app\models\FormFieldList;
use yii\validators\Validator;
use Yii;

class ValidateRequiredFields extends Validator
{
    /**
     * Validate if required fields are selected
     */

    public function validateAttribute($model, $attribute)
    {
        $firstName = FormFieldList::find()->where(['name' => 'firstName'])->all();
        $lastName = FormFieldList::find()->where(['name' => 'lastName'])->all();
        $email = FormFieldList::find()->where(['name' => 'email'])->all();

        if (!empty($model->fieldList)) {

            if (!in_array($firstName[0]->id, $model->fieldList)) {
                $this->addError($model, $attribute, Yii::t('messages', 'First name must be selected.'));
            }
            if (!in_array($lastName[0]->id, $model->fieldList)) {
                $this->addError($model, $attribute, Yii::t('messages', 'Last name must be selected.'));
            }
            if (!in_array($email[0]->id, $model->fieldList)) {
                $this->addError($model, $attribute, Yii::t('messages', 'Email must be selected.'));
            }
            return false;
        }
        return true;
    }
}