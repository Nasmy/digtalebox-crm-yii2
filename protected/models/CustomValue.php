<?php

namespace app\models;

use app\components\MDate;
use Yii;
use yii\db\Exception;
use yii\db\Query;

/**
 * This is the model class for table "CustomValue".
 *
 * @property int $id
 * @property int $customFieldId
 * @property int $relatedId
 * @property string $fieldValue
 * @property string $createdAt
 */
class CustomValue extends \yii\db\ActiveRecord
{
    public $fieldStyle = '';
    public $fieldName;
    public $fieldLabel;
    public $fieldType;
    public $fieldTag;
    public $fieldIsRequired;
    public $fieldIsUnique;
    public $fieldIsPreview;
    public $fieldDefaultValue;
    public $listValues;
    public $duplicateRecords = array();
    public $submittedRecords = array();

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'CustomValue';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules[] =[['customFieldId', 'relatedId', 'fieldValue', 'createdAt'], 'safe']; //Safe attributes

        if ($this->fieldIsRequired)
            $rules[] = [['fieldValue'], 'required'];

        if ($this->fieldIsPreview) //check only for required validation when preview
            return $rules;

        else {
            switch ($this->fieldType) {
                case 'int':
                    $rules[] = [['fieldValue'], 'string','max' => 20];
                    $rules[] = [['fieldValue'], 'number', 'integerOnly' => true];
                    break;
                case 'float':
                    $rules[] = [['fieldValue'], 'string','max' => 20];
                    $rules[] =[['fieldValue'], 'number'];
                    break;
                case 'text':
                case 'list':
                    $rules[] = [['fieldValue'], 'string', 'max' => 512];
                    break;
                case 'date':
                    $rules[] = [['fieldValue'], 'string', 'max' => 20];
                    break;
                case 'boolean':
                    $rules[] = [['fieldValue'], 'string', 'max' => 1];
                    if ($this->fieldIsRequired) {
                        $rules[]=[['fieldValue'], 'compare', 'compareValue' => 1, 'message' => $this->fieldLabel . ' is required.'];
                    }
                    break;
                case 'url':
                    $rules[] = [['fieldValue'], 'string', 'max' => 512];
                    $rules[] =[['fieldValue'], 'url'];
                    break;
                case 'email':
                    $rules[] = [['fieldValue'], 'string', 'max' => 512];
                    $rules[] = [['fieldValue'], 'email'];
                    break;
            }
        }

        return $rules;
    }

    /**
     * @return array relational rules.s
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return [
            'CustomField' => [self::BELONGS_TO, 'CustomField', 'customFieldId'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'customFieldId' => Yii::t('app', 'Custom Field ID'),
            'relatedId' => Yii::t('app', 'Related ID'),
            'fieldValue' => Yii::t('app', 'Field Value'),
            'createdAt' => Yii::t('app', 'Created At'),
        ];
    }


    /**
     * @param $relatedTable
     * @param $relatedId
     * @param $actionType
     * @param null $postdata
     * @param null $subarea
     * @param bool $isFormBuilder
     * @return array
     * @throws Exception
     */
    public static function getCustomData($relatedTable, $relatedId, $actionType, $postdata = null, $subarea = null, $isFormBuilder = false)
    {
        $query = new Query();
        $customFields = [];

        $query->SELECT(['cf.id', 'cf.customTypeId', 'cf.fieldName', 'cf.label', 'cf.required', 'cf.defaultValue', 'cf.listValues', 'ct.typeName', 'cfv.id as customId', 'cfv.customFieldId', 'cfv.relatedId', 'cfv.fieldValue', 'cfv.createdAt'])
            ->innerJoin('CustomType ct', 'ct.id = cf.customTypeId')
            ->leftJoin('CustomValue cfv', 'cf.id = cfv.customFieldId AND relatedId = :relatedId', [':relatedId' => (empty($relatedId) ? 0 : $relatedId)])
            ->where('relatedTable = :relatedTable AND enabled = :enabled', [':relatedTable' => $relatedTable, ':enabled' => 1])
            ->orderBy('cf.sortOrder, cf.label')
            ->addParams([':relatedTable' => $relatedTable, ':relatedId' => (empty($relatedId) ? 0 : $relatedId), ':enabled' => 1])
            ->from('CustomField cf')->all();

        if ($isFormBuilder && !empty($postdata)) {
            foreach ($postdata as $k => $value) {
                $customFields[] = $k;
            }
            $query->andWhere(['cf.id' => $customFields]);
        }


        // Avoid duplicate custom field in contacts update form
        if ($actionType === CustomField::ACTION_EDIT) {
            $query->groupBy('cfv.customFieldId');
        }
        $query->addParams([':relatedTable' => $relatedTable, ':relatedId' => (empty($relatedId) ? 0 : $relatedId), ':enabled' => 1]);
        $query->from('CustomField cf')->all();

        // Subarea conditions
        if (!is_null($subarea)) {
            $query->join('LEFT JOIN', 'CustomFieldSubArea cfa', 'cfa.customFieldId = cf.id')
                ->andWhere(['=', 'cfa.subarea', $subarea]);
        }

        // Get data
        $command = $query->createCommand();
        $rows = $command->queryAll();

        $CustomFieldValues = array();
        foreach ($rows as $k => $row) {
            if (empty($row['customId'])) {
                // Don't check empty in second condition, arrays with 0 values taking as empty
                $fieldValue = empty($postdata) ? $row['defaultValue'] : (isset($postdata[$row['id']]['fieldValue']) ? $postdata[$row['id']]['fieldValue'] : null);
                $isNewRecord = false;
            } else {
                if (empty($postdata)) {
                    $fieldValue = $row['fieldValue'];
                } else {
                    $fieldValue = isset($postdata[$row['id']]) ? $postdata[$row['id']]['fieldValue'] : null;
                }
                $isNewRecord = true; // TODO It was earlier false. Made it to true for test.
            };
            $CustomFieldValues[] = CustomValue::setCustomValues($row, $fieldValue, $isNewRecord, $isFormBuilder);
        }

        return $CustomFieldValues;
    }

    /**
     * @param $record
     * @param $fieldValue
     * @param bool $isNewRecord
     * @param bool $isFormBuilder
     * @return CustomValue
     */
    public static function setCustomValues($record, $fieldValue, $isNewRecord = false, $isFormBuilder = false)
    {
        if (is_array($fieldValue))
            $val = implode(",", $fieldValue);

        $customValue = new CustomValue();
        if ($isNewRecord) {
            $customValue->id = $record['customId'];
            $customValue->setIsNewRecord(false);
        }

        $customValue->customFieldId = $record['id'];
        $customValue->relatedId = $record['relatedId'];
        $customValue->fieldValue = ($fieldValue !== '') ? $fieldValue : null;
        $customValue->createdAt = $record['createdAt'];
        $customValue->fieldName = $record['fieldName'];
        $customValue->fieldLabel = $record['label'];
        $customValue->fieldType = $record['typeName'];
        $customValue->fieldIsRequired = $record['required'];
        $customValue->fieldDefaultValue = $record['defaultValue'];
        $customValue->listValues = $record['listValues'];

        return $customValue;
    }


    /**
     * @param $relatedTable
     * @param $relatedId
     * @param $actionType
     * @param null $postdata
     * @param null $subarea
     * @throws Exception
     */
    public static function getCustomFieldData($relatedTable, $relatedId, $actionType, $postdata = null, $subarea = null)
    {
        $query = new Query();
        $query->SELECT('cf.id, cf.customTypeId, cf.fieldName,cf.label,cf.required,cf.defaultValue,cf.listValues, ctyp.typeName, cfv.id as customId, cfv.customFieldId, cfv.relatedId, cfv.fieldValue, cfv.createdAt')
            ->innerJoin('CustomType ctyp', 'ctyp.id = cf.customTypeId')
            ->leftJoin('CustomValue cfv', 'cf.id = cfv.customFieldId AND relatedId = :relatedId', [':relatedId' => (empty($relatedId) ? 0 : $relatedId)])
            ->where('relatedTable = :relatedTable AND enabled = :enabled', [':relatedTable' => $relatedTable, ':enabled' => 1])
            ->orderBy('cf.sortOrder, cf.label')
            ->addParams([':relatedTable' => $relatedTable, ':relatedId' => (empty($relatedId) ? 0 : $relatedId), ':enabled' => 1])
            ->from('CustomField cf')->all();

        //Subarea conditions
        if (!is_null($subarea)) {
            $query->join('LEFT JOIN', 'CustomFieldSubArea cfsa', 'cfsa.customFieldId = cf.id')
                ->andWhere(['=', 'cfsa.subarea', $subarea]);
        }


        //Get data
        $command = $query->createCommand();
        $rows = $command->queryAll();

    }

    /**
     * added this new function to remove duplicated custom fields in database as temp solution for that - 14-05-2020- Asiri
     */
    public static function getCustomDataWithoutDuplicates($relatedTable, $relatedId, $actionType, $postdata = null, $subarea = null)
    {
        $query = new Query();
        $query->SELECT(['cf.id', 'cf.customTypeId', 'cf.fieldName', 'cf.label', 'cf.required', 'cf.defaultValue', 'cf.listValues', 'ctyp.typeName'])
            ->innerJoin('CustomType ctyp', 'ctyp.id = cf.customTypeId')
            ->where('relatedTable = :relatedTable AND enabled = :enabled', [':relatedTable' => $relatedTable, ':enabled' => 1])
            ->orderBy('cf.sortOrder, cf.label')
            ->from('CustomField cf')->all();

        // Get data
        $command = $query->createCommand();
        $rows = $command->queryAll();
        $CustomFieldValues = array();
        foreach ($rows as $k => $row) {

            $customValues=self::getCustomValues($relatedId,$row['id']);
            if (empty($customValues)) {
                // Don't check empty in second condition, arrays with 0 values taking as empty
                $val = empty($postdata) ? $row['defaultValue'] : (isset($postdata[$row['id']]['fieldValue']) ? $postdata[$row['id']]['fieldValue'] : null);

                if (is_array($val))
                    $val = implode(",", $val);

                $value = new CustomValue();
                $value->customFieldId = $row['id'];
                $value->fieldValue = ($val !== '') ? $val : null;
                $value->fieldName = $row['fieldName'];
                $value->fieldLabel = $row['label'];
                $value->fieldType = $row['typeName'];
                $value->fieldIsRequired = $row['required'];
                $value->fieldDefaultValue = $row['defaultValue'];
                $value->listValues = $row['listValues'];
                $CustomFieldValues[] = $value;
            } else {
                if (empty($postdata)) {
                    $val = $customValues['fieldValue'];
                } else {
                    $val = isset($postdata[$row['id']]) ? $postdata[$row['id']]['fieldValue'] : null;
                }

                if (is_array($val))
                    $val = implode(",", $val);

                $value = new CustomValue();
                $value->id = $customValues['id'];
                $value->customFieldId = $row['id'];
                $value->relatedId = $customValues['relatedId'];
                $value->fieldValue = ($val !== '') ? $val : null;
                $value->createdAt = $customValues['createdAt'];
                $value->fieldName = $row['fieldName'];
                $value->fieldLabel = $row['label'];
                $value->fieldType = $row['typeName'];
                $value->fieldIsRequired = $row['required'];
                $value->fieldDefaultValue = $row['defaultValue'];
                $value->listValues = $row['listValues'];
                $value->setIsNewRecord(false);
                $CustomFieldValues[] = $value;
            }
        }

        return $CustomFieldValues;
    }


    /**
     * support function for getCustomDataWithoutDuplicates get only latest data - 14-05-2020- Asiri
     */
    public static function getCustomValues($relatedId,$customFieldId)
    {
        $query = new Query();
        $query->SELECT(['*'])
            ->where('customFieldId =:customFieldId AND relatedId = :relatedId', [':customFieldId' => (empty($customFieldId) ? 0 : $customFieldId), ':relatedId' => (empty($relatedId) ? 0 : $relatedId)])
            ->orderBy('id DESC')
            ->from('CustomValue')->one();
        $CustomValues = $query->createCommand()->queryOne();
        return $CustomValues;
    }

    /**
     * {@inheritdoc}
     * @return CustomValueQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CustomValueQuery(get_called_class());
    }
}
