<?php


namespace app\components\Validations;


use app\models\Configuration;
use yii\validators\Validator;
use Yii;

class ValidateDonationType extends Validator
{
    /**
     * Validate donation types and trigger required error.
     */
    public function validateAttribute($model, $attribute)
    {

        if (empty($model->paypalId) && in_array(Configuration::DONATION_TYPE_PAYPAL, $model->donationType)) {
            $this->addError($model, $attribute, Yii::t('messages', 'Paypal Id cannot be empty.'));
        }
        return false;
    }
}