<?php


namespace app\components\Validations;


use app\components\ToolKit;
use app\models\Configuration;
use yii\validators\Validator;
use Yii;

class ValidateEnablePayment extends Validator
{
    /**
     * Validate if enable payment is checked
     */
    public function validateAttribute($model, $attribute)
    {
        $stripeClientId = Configuration::findOne(Configuration::STRIPE_CLIENT_ID);
        $stripeSecretId = Configuration::findOne(Configuration::STRIPE_SECRET_ID);
        if (!empty($model->enablePayment) && (ToolKit::isEmpty($stripeClientId->value) || ToolKit::isEmpty($stripeSecretId->value))) {
            $this->addError($model, $attribute, Yii::t('messages', 'Stripe Client Id and Secret Id cannot be empty to enable payment. (System -> Configurations)'));
        }
        if (!empty($model->enablePayment) && !$model->hasErrors('enablePayment')) { //if stripe client id & enable is set
            if (empty($model->isMembership) && empty($model->isDonation)) {
                $this->addError($model, $attribute, Yii::t('messages', '{isMembership} or {isDonation} cannot be empty', array('isMembership' => $model->getAttributeLabel('isMembership'), 'isDonation' => $model->getAttributeLabel('isDonation'))));
            }
        }
    }
}