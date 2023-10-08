<?php


namespace app\components\Validations;


use yii\validators\Validator;
use Yii;

class LocationEscapeSpecialCharacters extends Validator
{

    /**
     * Validate strings to avoid ; character
     */
    public function validateAttribute($model, $attribute)
    {

        if (preg_match('/[\'^£$%&*()}{#?><>|=+¬]/', $attribute)) { // Allowed correctors ';', '@', '~', '_', -
            // one or more of the 'special characters' found in $string
            $this->addError($model, $attribute, Yii::t('messages', "$attribute contains not allowed characters."));
        }
        /*$notAllowedList = array(';', '@', '~');
        foreach ($notAllowedList as $letter) {
            if (strpos($model->$attribute, $letter) !== false && !isset($this->errors['address1'])) {
                $this->addError($model, $attribute, Yii::t('messages', 'Street address contains not allowed characters.'));
            }
            if (strpos($model->$attribute, $letter) !== false && !isset($this->errors['zip'])) {
                $this->addError($model, $attribute, Yii::t('messages', 'Zip contains not allowed characters.'));
            }
            if (strpos($model->$attribute, $letter) !== false && !isset($this->errors['city'])) {
                $this->addError($model, $attribute, Yii::t('messages', 'City contains not allowed characterss.'));
            }
        }*/

    }

}