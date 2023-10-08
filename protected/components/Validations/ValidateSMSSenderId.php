<?php


namespace app\components\Validations;


use yii\validators\Validator;
use Yii;

class ValidateSMSSenderId extends Validator
{
    public function validateAttribute($model, $attribute)
    {
        if ($model->smsSenderId != '') {
            // numeric can contain 16 characters
            if (ctype_digit($model->smsSenderId) && strlen($model->smsSenderId) > 16) {
                $this->addError($model, $attribute, Yii::t('messages', 'SMS Sender Id is too long (maximum is 16 numeric characters)'));
            } // alpha numeric can contain 11 characters
            else if (strlen($model->smsSenderId) > 11) {
                $this->addError($model, $attribute, Yii::t('messages', 'SMS Sender Id is too long (maximum is 11 alpha-numeric characters)'));
            } else if (!preg_match("/^[0-9a-zA-Z]{4,11}$/", $model->smsSenderId) == 1) {
                $this->addError($model, $attribute, Yii::t('messages', 'Invalid characters. Eg: 0825551234 or 1800House'));
            }
        }
    }

}