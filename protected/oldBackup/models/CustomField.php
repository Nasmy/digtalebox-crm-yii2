<?php

namespace app\models;

use app\components\RActiveRecord;
use app\components\ToolKit;
use app\components\Validations\ValidationUtils;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "CustomField".
 *
 * @property int $id
 * @property int $customTypeId
 * @property string $fieldName
 * @property string $relatedTable
 * @property string $defaultValue
 * @property int $sortOrder
 * @property string $enabled
 * @property string $listItemTag
 * @property string $required
 * @property string $onCreate
 * @property string $onEdit
 * @property string $onView
 * @property string $listValues
 * @property string $label
 * @property string $htmlOptions
 */
class CustomField extends \yii\db\ActiveRecord
{
    public $display;
    public $width;
    public $height;
    public $customFieldId;
    public $subarea = array();
    // public $enabled;
    // public $required;

    const ACTION_CREATE = 'create';
    const ACTION_EDIT = 'edit';
    const ACTION_VIEW = 'view';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'CustomField';
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['customTypeId', 'relatedTable', 'label', 'fieldName'], 'required'],
            [['customTypeId'], 'integer',],
            [['sortOrder'], 'integer', 'skipOnEmpty' => false],
            [['fieldName', 'relatedTable', 'label'], 'string', 'max' => 64],
            [['defaultValue', 'listValues', 'htmlOptions'], 'string'],
            [['label'], 'validateData'],
            [['enabled', 'listItemTag', 'required', 'onCreate', 'onEdit', 'onView'], 'string', 'max' => 1],
            // [['enabled','required'],'safe','on'=>'update'],
            [['customTypeId', 'relatedTable', 'label', ' fieldName', 'defaultValue', 'listValues', 'htmlOptions', 'display', 'subarea', 'sortOrder'], 'safe'],
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            [['id', 'customTypeId', 'fieldName', 'relatedTable', 'defaultValue', 'sortOrder', 'enabled', 'listItemTag', 'required', 'onCreate', 'onEdit', 'onView', 'listValues', 'label', 'htmlOptions', 'display', 'subarea'], 'safe', 'on' => 'search']

        ];
    }


    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('messages', 'ID'),
            'customTypeId' => Yii::t('messages', 'Custom Type'),
            'fieldName' => Yii::t('messages', 'Field Name'),
            'relatedTable' => Yii::t('messages', 'Related Area'),
            'defaultValue' => Yii::t('messages', 'Default Value'),
            'sortOrder' => Yii::t('messages', 'Sort Order'),
            'enabled' => Yii::t('messages', 'Enabled'),
            'listItemTag' => Yii::t('messages', 'List Item Tag'),
            'required' => Yii::t('messages', 'Required'),
            'onCreate' => Yii::t('messages', 'On Create'),
            'onEdit' => Yii::t('messages', 'On Edit'),
            'onView' => Yii::t('messages', 'On View'),
            'listValues' => Yii::t('messages', 'List Values'),
            'label' => Yii::t('messages', 'Label'),
            'htmlOptions' => Yii::t('messages', 'Html Options'),
            'subarea' => Yii::t('messages', 'Display On'),
            'display' => Yii::t('messages', 'Display')
        ];
    }

    public function beforeSave($insert)
    {
        $options = array();
        if (!empty($this->width))
            $options['width'] = $this->width;
        if (!empty($this->height))
            $options['height'] = $this->height;
        if (!empty($options))
            $this->htmlOptions = json_encode($options);
        else
            $this->htmlOptions = '';

        if (!empty($this->listValues)) {
            $list = array();
            $items = str_replace(array("\r", "\n", "%0a", "%0d"), ',', $this->listValues);
            $items = explode(',', $items);
            foreach ($items as $key => $value) {
                if (!empty($value))
                    $list[] = trim($value);
            }

            $this->listValues = implode(',', $list);
        }
        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {
        $this->listValues = str_replace(',', chr(13), $this->listValues);
        $data = $this->subarea;

        CustomFieldSubArea::deleteAll('customFieldId=:customFieldId', [':customFieldId' => $this->id]);

        if (!ToolKit::isEmpty($data)) {
            foreach ($data as $val) {
                $model = new CustomFieldSubArea();
                $model->customFieldId = $this->id;
                $model->subarea = $val;
                $model->save(false);
            }
        }
        parent::afterSave($insert, $changedAttributes);
    }

    public function afterFind()
    {
        if (!empty($this->htmlOptions)) {
            $options = json_decode($this->htmlOptions);
            if (array_key_exists('width', $options))
                $this->width = $options['width'];
            if (array_key_exists('height', $options))
                $this->height = $options['height'];
        }
        $this->listValues = str_replace(',', chr(13), $this->listValues);

        $data = CustomFieldSubArea::find()->where('customFieldId=:customFieldId', [':customFieldId' => $this->id])->all();
        $subAreas = array();
        if ($data) {
            foreach ($data as $key => $val) {
                $subAreas[] = $val['subarea'];
            }
        }
        $this->subarea = $subAreas;

        return parent::afterFind();
    }

    /**
     * Used to validate a list of custom fields
     * @return boolean
     */
    public static function validatePreviewCustomFieldList($customFieldList, $customValuesArr, $customDuplicateValues)
    {
        $customErrors = array();
        foreach ($customFieldList as $k => $customField) {
            $customField->submittedRecords = $customValuesArr;
            $customField->duplicateRecords = $customDuplicateValues;
            $success = $customField->validate();
            if (!$success) {
                $customErrors[] = $customField->errors;
            } else {
                $customValues[$customField->customFieldId] = $customField->fieldValue;
            }
        }
        // TODO: If below code working this commented line will remove
        // return empty($customErrors) ? true : false;
        return ValidationUtils::checkHasError($customErrors);
    }


    /**
     * Used to validate a list of custom fields
     * @param $customFieldList
     * @return boolean
     */
    public static function validateCustomFieldList($customFieldList)
    {
        $customErrors = array();
        if (!isset($customFieldList)) {
            return false;
        }
        foreach ($customFieldList as $k => $customField) {
            $success = $customField->validate();
            if (!$success) {
                $customErrors[] = $customField->errors['fieldValue'][0];
            }
        }
        // TODO: If below code working this commented line will remove
        // return empty($customErrors) ? true : false;
        return ValidationUtils::checkHasError($customErrors);
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * Typical usecase:
     * - Initialize the model fields with values from filter form.
     * - Execute this method to get CActiveDataProvider instance which will filter
     * models according to data in model fields.
     * - Pass data provider to CGridView, CListView or any similar widget.
     *
     * based on the search/filter conditions.
     */
    public function search($params = null)
    {
        $query = (new \yii\db\Query())
            ->select("t.id,t.fieldName,t.label,t.relatedTable,t.defaultValue,t.sortOrder, ct.display, t.enabled, t.required")
            ->from('CustomField t')
            ->join('INNER JOIN', 'CustomType ct', 'ct.id = t.customTypeId');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 10],
        ]);

        $this->load($params);

        $query->filterWhere([
            'fieldName' => $this->fieldName,
            'relatedTable' => $this->relatedTable,
            'defaultValue' => $this->defaultValue,
            'listItemTag' => $this->listItemTag,
            'onCreate' => $this->onCreate,
            'onEdit' => $this->onEdit,
            'onView' => $this->onView,
            'listValues' => $this->listValues,
            'label' => $this->label,
            'htmlOptions' => $this->htmlOptions,
        ]);

        if (!empty($params)) {

            if (isset($params['CustomField']['fieldName'])) {
                $query->andFilterWhere(['like', 't.fieldName', $params['CustomField']['fieldName'], false]);
            }

            if (isset($params['CustomField']['label'])) {
                $query->andFilterWhere(['like', 't.label', $params['CustomField']['label'], false]);
            }

            if (isset($params['CustomField']['relatedTable'])) {
                $query->andFilterWhere(['like', 't.relatedTable', $params['CustomField']['relatedTable'], false]);
            }

            if (isset($params['CustomField']['display'])) {
                $query->andFilterWhere(['=', 'ct.id', $params['CustomField']['display']]);
            }

            if (isset($params['CustomField']['defaultValue'])) {
                $query->andFilterWhere(['like', 't.defaultValue', $params['CustomField']['defaultValue']]);
            }

            if (isset($params['CustomField']['enabled'])) {
                $query->andFilterWhere(['=', 't.enabled', $params['CustomField']['enabled']]);
            }

        }

        return $dataProvider;
    }

    /**
     * @return Validation result.
     */
    public function validateData()
    {
        $result = true;
        if (!empty($this->customTypeId)) {
            $types = ArrayHelper::map(CustomType::find()->select(['id', 'typeName'])->all(), 'id', 'typeName');
            if ($types[$this->customTypeId] == 'list') {
                $this->listValues = null;
                $this->width = null;
                $this->height = null;
            } elseif (in_array($types[$this->customTypeId], array('dropdown', 'radiobutton', 'checkbox'))) {
                $this->listItemTag = null;
                $this->width = null;
                $this->height = null;

                if (empty($this->listValues)) {
                    $this->addError("listValues", Yii::t("messages", "List Items can not be blank."));
                    $result = false;
                }
            } elseif ($types[$this->customTypeId] == 'textarea') {
                $this->listItemTag = null;
                $this->listValues = null;
            } else {
                $this->listItemTag = null;
                $this->listValues = null;
                $this->width = null;
                $this->height = null;
            }

            // Unique condition
            $count = $this::find()->where('lower(fieldName)=lower(:fieldName) AND lower(relatedTable)=lower(:relatedTable)', [':fieldName' => $this->fieldName, ':relatedTable' => $this->relatedTable])->count();

            if (($this->isNewRecord && $count > 0) || (!$this->isNewRecord && $count > 1)) {
                $this->addError("fieldName", Yii::t("messages", "The Combination of Field Name and Area should be unique."));
                $result = false;
            }
        }
        return $result;
    }

    /**
     * Used to retrieve custom types based on a custom field
     * @return string. An empty array is returned if none is found
     */
    public static function getCustomType($fieldName, $area)
    {
        $data = CustomField::find()->where('fieldName=:fieldName AND relatedTable=:relatedTable', array(':fieldName' => $fieldName, ':relatedTable' => $area))->one();
        return !is_null($data) ? CustomType::findOne($data->customTypeId)->typeName : null;
    }

    /**
     * {@inheritdoc}
     * @return CustomFieldQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CustomFieldQuery(get_called_class());
    }


    /**
     * Used to get the custom fields and values of a record
     * @return array
     */
    public static function getCustomFieldValue($relatedId)
    {
        $results = array();
        $customValues = CustomValue::find()->where(['=', 'relatedId', $relatedId])->asArray()->all();
        foreach ($customValues as $value) {
            if (false == self::isCustomFieldEnable($value['customFieldId']))
                continue;
            $label = self::getCustomFieldLabel($value['customFieldId']);
            if (null != $label)
                $results[$label] = $value['fieldValue'];
        }
        return $results;
    }

    /**
     * Used to get the custom search fields and values of a search record
     * @return array
     */
    public static function getCustomSearchFieldValues($relatedId, $isLabel = true)
    {
        $results = array();
        $customSearchValues = CustomValueSearch::find()->where(['relatedId' => $relatedId])->all();
        foreach ($customSearchValues as $value) {
            $label = self::getCustomFieldLabel($value->customFieldId);
            if (true == $isLabel && null != $label)
                $results[$label] = $value->fieldValue;
            else
                $results[$value->customFieldId] = $value->fieldValue;
        }
        return $results;
    }

    /**
     * @param $relatedId
     * @param $customFieldId
     * @return mixed|string
     */
    public static function getCustomFieldValueById($relatedId, $customFieldId)
    {
        $results = array();
        $customValues = CustomValue::find()->where(['relatedId' => $relatedId, 'customFieldId' => $customFieldId])->all();


        $label = '';
        $customResults = '';
        foreach ($customValues as $value) {
            $label = self::getCustomFieldLabel($value['customFieldId']);
            if (null != $label)
                $results[$label] = $value['fieldValue'];
            if ($results[$label]) {
                $customResults = $results[$label];
            }


        }

        return $customResults;
    }

    /**
     * Used to retrieve custom field label
     * @return string. null is returned if none is found
     */
    public static function getCustomFieldLabel($customFieldId)
    {
        $customField = CustomField::findOne($customFieldId);
        return $customField === null ? null : $customField->fieldName;
    }

    /**
     * Used to retrieve custom field label
     * @return string. null is returned if none is found
     */
    public static function isCustomFieldEnable($customFieldId)
    {
        $customField = CustomField::findOne($customFieldId);
        return $customField === null ? false : $customField->enabled == 1 ? true : false;
    }

    /**
     * Used to check if at least one custom field has a value
     * @return boolean
     */
    public static function issetCustomFields($haystack)
    {
        $success = false;
        foreach ($haystack as $key => $value) {
            if (is_array($value)) {
                if (!ToolKit::isEmpty($value['fieldValue'])) {
                    $success = true;
                    break;
                }
            }
        }
        return $success;
    }

    /**
     * Used to validate a list of custom fields
     * @return boolean
     */
    public static function returnCustomFieldErrors($customFieldList)
    {
        $customErrors = array();
        foreach ($customFieldList as $k => $customField) {
            $success = $customField->validate();
            if (!$success) {
                $customErrors[] = $customField->errors;
                //break; //commented to display all custom field errors. if not only one will be displayed in sign up or create screens
            }
        }

        return $customErrors;
    }

    /**
     * Used to validate a list of custom fields
     * @return boolean
     */
    public static function validateBulkEditCustomFieldList($customFieldList)
    {

        foreach ($customFieldList as $k => $customField) {
            $success = $customField->validate();
            if (!$success) {
                $customErrors[] = $customField->errors;
            } else {
                $customValues[$customField->customFieldId] = $customField->fieldValue;
            }
        }
        return empty($customErrors) ? true : false;
    }
}
