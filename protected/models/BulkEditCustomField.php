<?php

namespace app\models;

use app\models\CustomField;
use Yii;
use yii\db\Query;
use yii\data\ActiveDataProvider;

class BulkEditCustomField extends \yii\base\Model
{
    public $fieldStyle = '';
    public $fieldName;
    public $fieldLabel;
    public $fieldType;
    public $fieldTag;
    public $fieldIsRequired;
    public $fieldDefaultValue;
    public $listValues;

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {

    }

    /**
     * @return array relational rules.s
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'CustomField' => array(self::BELONGS_TO, 'CustomField', 'customFieldId'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'customFieldId' => Yii::t('messages', 'Custom Field'),
            'fieldValue' => $this->fieldLabel,
        );
    }

    /*
    * Get custom fields
    */
    public static function getCustomData($relatedTable, $relatedId, $actionType, $postdata = null, $subarea = null)
    {
        $criteria = CustomField::find();
        $criteria->select('cf.id, cf.customTypeId, cf.fieldName,cf.label,cf.required,cf.defaultValue,cf.listValues, ctyp.typeName, cfv.id as customId, cfv.customFieldId, cfv.relatedId, cfv.fieldValue, cfv.createdAt');
        $criteria->from('CustomField cf');
        $criteria->join('INNER JOIN', 'CustomType ctyp', 'ctyp.id = cf.customTypeId');
        $criteria->join('LEFT OUTER JOIN', 'CustomValue cfv', 'cf.id = cfv.customFieldId and cfv.relatedId = "' . (empty($relatedId) ? 0 : $relatedId) . '"');
        $criteria->andWhere(['cf.relatedTable' => $relatedTable]);
        $criteria->andWhere(['cf.enabled' => 1]);

        //Subarea conditions
        if (!is_null($subarea)) {
            $criteria->join('LEFT JOIN', 'CustomFieldSubArea cfsa', 'cfsa.customFieldId = cf.id');
            $criteria->andWhere(['cfsa.subarea' => $subarea]);
        }
        $criteria->orderBy('cf.sortOrder, cf.label');
        //Get data
        $rows = $criteria->all();
        $CustomFieldValues = array();
        foreach ($rows as $k => $row) {
            if (empty($row['customId'])) {
                // Don't check empty in second condition, arrays with 0 values taking as empty
                $val = empty($postdata) ? $row['defaultValue'] : (isset($postdata[$row['id']]['fieldValue']) ? $postdata[$row['id']]['fieldValue'] : null);

                if (is_array($val))
                    $val = implode(",", $val);

                $value = new CustomValue();
                $value->customFieldId = $row['id'];
                $value->fieldValue = ($val !== '') ? $val : null;
                $value->fieldName = $row['fieldName'];
                $value->fieldLabel = $row['label'];
                $value->fieldType = CustomType::find()->where(['id' => $row['customTypeId']])->one()->typeName;
                $value->fieldDefaultValue = $row['defaultValue'];
                $value->listValues = $row['listValues'];

                $CustomFieldValues[] = $value;
            }
        }

        return $CustomFieldValues;
    }

    /*
    * Before Save
    */
    public function beforeSave()
    {
        $this->createdAt = User::convertSystemTime();
        return parent::beforeSave();
    }

    /**
     * @return dynamicaly generated validation rules array.
     */
    public function getValidators($attribute = NULL)
    {
        return $this->createValidators($attribute);
    }

}
