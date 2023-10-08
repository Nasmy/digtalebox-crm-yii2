<?php


namespace app\components\Validations;


use yii\validators\Validator;
use Yii;

class ValidateKeywordsWithType extends Validator
{

    public $massTemplate;

    /**
     * Check whether user has entered valid keywords for the selected template type
     * @param string $attribute Attribute name
     * @param mixed $params Additional parameters to be passed to validation rule
     */
    public function validateAttribute($model, $attribute)
    {
        $content = $model->$attribute;
        $invalidKeywords = $model->type == $this->massTemplate ? $model->getSingleMsgKeywords() : $model->getTemplateKeywords();

        foreach ($invalidKeywords as $keyword => $label) {
            if (strstr($content, $keyword)) {
                $this->addError($model, $attribute, Yii::t('messages', 'Invalid keyword(s)'));
                break;
            }
        }
    }
}