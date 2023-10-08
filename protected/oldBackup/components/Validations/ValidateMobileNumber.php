<?php


namespace app\components\Validations;


use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;
use yii\validators\Validator;
use Yii;

class ValidateMobileNumber extends Validator
{

    /**
     * Validate mobile number in international format using intl-tel-input extension
     */
    public function validateAttribute($model, $attribute)
    {
        $mobile = $model->mobile;
        $countryCode = $model->countryCode;

        Yii::setAlias('@libphonenumber', '@app/vendor/borales/yii2-phone-input');
        $phoneUtil = PhoneNumberUtil::getInstance();

        try {
            $phoneNumber = $phoneUtil->parse($mobile, $countryCode);
            $phoneNumber = $phoneUtil->format($phoneNumber, \libphonenumber\PhoneNumberFormat::INTERNATIONAL);
            $model->mobile = str_replace(' ', '',$phoneNumber);
        } catch (\Exception $e) {
            $this->addError($model, $attribute, Yii::t('messages', 'Invalid mobile number or country code'));
        }

        return true;
    }

}