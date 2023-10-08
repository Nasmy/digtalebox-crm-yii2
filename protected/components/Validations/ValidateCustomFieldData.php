<?php


namespace app\components\Validations;


use app\models\CustomField;
use app\models\CustomType;
use yii\helpers\ArrayHelper;
use yii\validators\Validator;
use Yii;

class ValidateCustomFieldData extends Validator
{

    /**
     * validateData method refactored.
     */
    public function validateAttribute($model, $attribute)
    {
        $result = true;

        if (!empty($model->customTypeId)) {
            $types = ArrayHelper::map(CustomType::find()->select(['id', 'typeName'])->all(), 'id', 'typeName');
            if ($types[$model->customTypeId] == 'list') {
                $model->listValues = null;
                $model->width = null;
                $model->height = null;
            } elseif (in_array($types[$model->customTypeId], array('dropdown', 'radiobutton', 'checkbox'))) {
                $model->listItemTag = null;
                $model->width = null;
                $model->height = null;

                if (empty($model->listValues)) {
                    $this->addError($model, "listValues", Yii::t("messages", "List Items can not be blank."));
                    $result = false;
                }
            } elseif ($types[$model->customTypeId] == 'textarea') {
                $model->listItemTag = null;
                $model->listValues = null;
            } else {
                $model->listItemTag = null;
                $model->listValues = null;
                $model->width = null;
                $model->height = null;
            }

            // Unique condition
            $count = CustomField::find()->where('lower(fieldName)=lower(:fieldName) AND lower(relatedTable)=lower(:relatedTable)', [':fieldName' => $model->fieldName, ':relatedTable' => $model->relatedTable])->count();

            if (($model->isNewRecord && $count > 0) || (!$model->isNewRecord && $count > 1)) {
                $this->addError($model, "fieldName", Yii::t("messages", "The Combination of Field Name and Area should be unique."));
                $result = false;
            }
        }
        return $result;
    }
}