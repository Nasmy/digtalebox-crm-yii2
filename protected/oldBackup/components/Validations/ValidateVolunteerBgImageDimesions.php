<?php


namespace app\components\Validations;


use app\models\CandidateInfo;
use yii\validators\Validator;
use Yii;

class ValidateVolunteerBgImageDimesions extends Validator
{

    public $VolImgMinWidth;     //Volunteer image minimum width size
    public $VolImgMinHeight;    //Volunteer image minimum height size

    /**
     * Validate image width and height
     */
    public function validateAttribute($model, $attribute)
    {

        if ("" != $model->$attribute) {
            $imagehw = getimagesize($model->$attribute->tempName);
            $imagewidth = $imagehw[0];
            $imageheight = $imagehw[1];
            if ($imagewidth < $this->VolImgMinWidth) {
                $this->addError($model, $attribute, Yii::t('messages', 'Image width should be {width}', array('width' => $this->VolImgMinWidth . 'px')));
            } else if ($imageheight < $this->VolImgMinWidth) {
                $this->addError($model, "imageFile", Yii::t('messages', 'Image height should be {height}', array('height' => $this->VolImgMinHeight . 'px')));
            }
        }
    }

}