<?php


namespace app\components\Validations;


use yii\validators\Validator;
use Yii;

class ValidateImageDimesions extends Validator
{

    public $MinImgMinWidth;     //Volunteer image minimum width size
    public $MinImgMinHeight;    //Volunteer image minimum height size


    /**
     * Validate image width and height
     */
    public function validateAttribute($model, $attribute)
    {
        $profleImageFile = $model->$attribute;
        if ("" != $profleImageFile) {
            $imageSize = getimagesize($profleImageFile->tempName);
            $imageWidth = $imageSize[0];
            $imageHeight = $imageSize[1];

            if ($imageWidth > $this->MinImgMinWidth) {
                $this->addError($model, $attribute, Yii::t('messages', 'Image width is too large.'));
            } else if ($imageHeight > $this->MinImgMinHeight) {
                $this->addError($model, $attribute, Yii::t('messages', 'Image height is too large.'));
            }
        }
    }
}