<?php


namespace app\components\Validations;


use yii\validators\Validator;

class ValidateResourceRequireTypeOptions extends Validator
{
    public $imgType;
    public $documentType;
    public $method;

    /**
     * Require upload file or URL depend on slected type
     */
    public function validateAttribute($model, $attribute, $type = null)
    {

        if ($this->method == "onCreate") {
            if ($this->imgType == $model->type || $this->documentType == $model->type) {
                $validator = Validator::createValidator('required', $model, 'file', []);
                $validator->validate($model);
            } else if (self::VIDEO == $model->type) {
                $validator = Validator::createValidator('required', $model, 'url', []);
                $validator->validate($model);
            }
        } elseif ($this->method == "onUpdate") {
            if ($model->prvResType != $model->type) {
            }
        }

    }
}