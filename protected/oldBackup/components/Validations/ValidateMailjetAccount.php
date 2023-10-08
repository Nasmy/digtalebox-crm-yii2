<?php


namespace app\components\Validations;


use yii\validators\Validator;
use Yii;

class ValidateMailjetAccount extends Validator
{
    /**
     * Validate donation types and trigger required error.
     */
    public function validateAttribute($model, $attribute)
    {
        if ('' != $model->mailjetUsername && '' == $model->mailjetPassword) {
            $this->addError($model, "mailjetPassword", Yii::t('messages', 'Mailjet password required'));
        } else if ('' == $model->mailjetUsername && '' != $model->mailjetPassword) {
            $this->addError($model, "mailjetUsername", Yii::t('messages', 'Mailjet username required'));
        }
    }
}