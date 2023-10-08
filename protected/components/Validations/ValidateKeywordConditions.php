<?php


namespace app\components\Validations;


use yii\validators\Validator;
use Yii;

class ValidateKeywordConditions extends Validator
{
    public $keyAuto;

    /**
     * before name validateConditions();
     * Check condition required on selected behaviour
     */

    public function validateAttribute($model, $attribute)
    {
        if ($model->behaviour == $this->keyAuto && null == $model->conditions) {
            $this->addError($model, $attribute, Yii::t('messages', 'Please select condition(s)'));
        }
    }
}