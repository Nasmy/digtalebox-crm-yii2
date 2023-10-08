<?php


namespace app\components\Validations;


use yii\validators\Validator;

class ValidationUtils extends Validator
{
    /**
     * @param $errors
     * @return bool
     */
    public static function checkHasError($errors) {
        if(empty($errors)) {
            return true;
        } else {
            return false;
        }
    }

}