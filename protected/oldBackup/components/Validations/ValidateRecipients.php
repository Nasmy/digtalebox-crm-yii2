<?php


namespace app\components\Validations;


use yii\validators\Validator;
use Yii;

class ValidateRecipients extends Validator
{
    public function validateAttribute($model, $attribute)
    {
        if ("" == $model->userlist && "" == $model->criteriaId) {
            $this->addError($model, $attribute, Yii::t('messages', 'Please select a Criteria or enter Receipients'));
        }
    }
}