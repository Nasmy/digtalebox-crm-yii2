<?php
namespace app\models;

use app\models\CustomField;
use app\models\CustomValue;
use app\models\User;
use yii\base\Model;
use yii\db\Query;
use yii;

/**
 * This is the model class for table "CustomValue".
 *
 * The followings are the available columns in table 'CustomValue':
 * @property integer $id
 * @property integer $customFieldId
 * @property integer $relatedId
 * @property string $fieldValue
 * @property string $createdAt
 */
class FileCustomFieldPreview extends Model
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
        return [
            [['customFieldId','relatedId','fieldValue','createdAt'], 'safe'],
            [['fieldValue'], 'required']//Safe attributes
        ];
    }


    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return[
            'customFieldId' => Yii::t('messages', 'Custom Field'),
            'fieldValue' => $this->fieldLabel,
        ];
    }

    /*
    * Get custom fields
    */
    public static function getCustomData($relatedTable, $relatedId, $actionType, $postdata = null, $subarea = null)
    {
        $query = new Query();
        $query->from('CustomField cf')
            ->select('cf.id, cf.customTypeId, cf.fieldName,cf.label,cf.required,cf.defaultValue,cf.listValues, ctyp.typeName, cfv.id as customId, cfv.customFieldId, cfv.relatedId, cfv.fieldValue, cfv.createdAt')
            ->join('INNER JOIN','CustomType ctyp','ctyp.id = cf.customTypeId')
            ->join('LEFT OUTER JOIN','CustomValue cfv','cf.id = cfv.customFieldId and relatedId = :relatedId',[':relatedId' => (empty($relatedId) ? 0 : $relatedId)])
            ->where('relatedTable = :relatedTable and enabled = :enabled',[':relatedTable' => $relatedTable,':enabled' => 1])
            ->orderBy('cf.sortOrder,cf.label');
        //Subarea conditions
        if (!is_null($subarea)) {
            $query->join('LEFT JOIN', 'CustomFieldSubArea cfsa', 'cfsa.customFieldId = cf.id')
                ->andWhere(['=','cfsa.subarea',$subarea]);
         }

        //Get data
        $command = $query->createCommand();
        $rows = $command->queryAll();

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
                $value->fieldType = $row['typeName'];
                $value->fieldIsRequired = true;
                $value->fieldIsPreview = true;
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
