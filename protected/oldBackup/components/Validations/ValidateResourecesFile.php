<?php


namespace app\components\Validations;


use yii\validators\FileValidator;
use yii\validators\Validator;
use Yii;

class ValidateResourecesFile extends Validator
{
    public $imgType;
    public $documentType;

    /**
     * Validate upload file type according to selected type
     */
    public function validateAttribute($model, $attribute)
    {
        if (null != $model->file) {
            if ($this->imgType == $model->type) {
                $validator = new FileValidator(['extensions' => 'png,jpg,gif,jpeg', 'wrongExtension' => "The file {$model->file->name} cannot be uploaded. Only files with these extensions are allowed: jpg, jpeg, gif, png."]);
                $valid = $validator->validate($model->file);

                if (!$valid) {
                    $this->addError($model, $attribute, $validator->wrongExtension);
                }

            } else if ($this->documentType == $model->type) {
                $validator = new FileValidator(['extensions' => 'doc,docx,pdf,xls,xlsx', 'wrongExtension' => "The file {$model->file->name} cannot be uploaded. Only files with these extensions are allowed: doc,docx,pdf,xls,xlsx"]);
                $valid = $validator->validate($model->file);

                if (!$valid) {
                    $this->addError($model, $attribute, $validator->wrongExtension);
                }
            }
        }

    }
}