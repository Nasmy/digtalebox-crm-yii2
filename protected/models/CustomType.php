<?php

namespace app\models;

use app\components\ToolKit;
use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "CustomType".
 *
 * @property int $id
 * @property string $typeName
 * @property string $display
 */
class CustomType extends \yii\db\ActiveRecord
{
    const CF_TYPE_INT = 'int';
    const CF_TYPE_FLOAT = 'float';
    const CF_TYPE_TEXT = 'text';
    const CF_TYPE_TEXTAREA = 'textarea';
    const CF_TYPE_DATE = 'date';
    const CF_TYPE_EMAIL = 'email';
    const CF_TYPE_URL = 'url';
    const CF_TYPE_LIST = 'list';
    const CF_TYPE_DROPDOWN = 'dropdown';
    const CF_TYPE_RADIOLIST = 'radiobutton';
    const CF_TYPE_CHECKBOXLIST = 'checkbox';
    const CF_TYPE_BOOL = 'boolean';

    const CF_PEOPLE = 'people';

    const CF_SUB_VOLUNTEER_SIGN_UP = 'volunteersignup';
    const CF_SUB_PEOPLE_BULK_INSERT = 'peoplebulkinsert';
    const CF_SUB_PEOPLE_BASIC_SEARCH = 'peoplebasicsearch';
    const CF_SUB_PEOPLE_ADVANCED_SEARCH = 'peopleadvancedsearch';
    const CF_SUB_PEOPLE_MAP_VIEW = 'peoplemapview';
    const CF_SUB_PEOPLE_FORM_BUILDER = 'peopleformbuilder';
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'CustomType';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['typeName'], 'length', 'max' => 64],
            [['display'], 'length', 'max' => 256],
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            [['id, typeName, display'], 'safe', 'on' => 'search'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'typeName' => Yii::t('messages', 'Type Name'),
            'display' => Yii::t('messages', 'Display'),
        ];
    }

    public static function getTypes()
    {
        $list = ArrayHelper::map(CustomType::find()->orderBy(['display' => SORT_DESC])->all(), 'id', 'display');

        foreach ($list as $key => $val)
            $list[$key] = Yii::t('messages', $val);

        return $list;
    }


    public function getDependentCustomTypes($type)
    {
        switch ($type) {
            default:
                return ArrayHelper::map(CustomType::find()->select(['id','typeName','display'])->all(), 'id', 'display');
            break;
        }
    }

    public function getTypeHints($type)
    {
         $hints = array(
            self::CF_TYPE_INT => 'Should be a number.',
            self::CF_TYPE_FLOAT => 'Should be a decimal. Ex: 100.00',
            self::CF_TYPE_TEXT => 'TEXT',
            self::CF_TYPE_TEXTAREA => 'TEXT',
            self::CF_TYPE_DATE => 'YYYY-MM-DD',
            self::CF_TYPE_EMAIL => 'Should be a email. Ex: admin@digitaleBox.ar',
            self::CF_TYPE_URL => 'Should be a URL. Ex: http://www.digitaleBox.ar',
            self::CF_TYPE_LIST => '',
            self::CF_TYPE_DROPDOWN => '',
            self::CF_TYPE_RADIOLIST => '',
            self::CF_TYPE_CHECKBOXLIST => '',
            self::CF_TYPE_BOOL => 'Unknown-N/A, Yes-1, No-0.',
        );
        return $hints[$type];
    }

    public static function getAreas($key = null)
    {
        $areas = array(
            self::CF_PEOPLE => Yii::t('messages', 'People'),
        );

        if (empty($key))
            return $areas;
        else
            return $areas[$key];
    }

    public static function getSubAreas($key)
    {
        $areas = array(
            self::CF_PEOPLE => array(
                self::CF_SUB_VOLUNTEER_SIGN_UP => Yii::t('messages', 'Volunteer Sign Up'),
                self::CF_SUB_PEOPLE_BULK_INSERT => Yii::t('messages', 'People Bulk Insert'),
                self::CF_SUB_PEOPLE_ADVANCED_SEARCH => Yii::t('messages', 'People Advanced Search'),
//                self::CF_SUB_PEOPLE_BASIC_SEARCH => Yii::t('messages', 'People Basic Search'),
//                self::CF_SUB_PEOPLE_MAP_VIEW => Yii::t('messages', 'People Map View')
                self::CF_SUB_PEOPLE_FORM_BUILDER => Yii::t('messages', 'Form Builder'),

            ));

        if (empty($key))
            return array();
        else
            return $areas[$key];
    }

    public static function getAreaList($emptyOption = false)
    {
        $data = array();
        $emptyVal = array('' => Yii::t('messages', '-- Related Areas --'));

        $data = self::getAreas();

        if ($emptyOption) {
            $data = $emptyVal + $data;
        }

        return $data;
    }

    public static function getSubAreaList($emptyOption = false)
    {
        $data = array();
        $emptyVal = array('' => Yii::t('messages', '-- Related Areas --'));

        $data = self::getAreas();

        if ($emptyOption) {
            $data = $emptyVal + $data;
        }

        return $data;
    }

    public static function getListByArea($emptyOption = false, $areaId = null)
    {
        $data = array();
        $emptyVal = array('' => Yii::t('messages', '-- Custom Types --'));
        $emptyArr = array('' => Yii::t('messages', 'Related Areas first...'));

        if ($areaId !== null && !empty($areaId)) {

            switch ($areaId) {
                default:
                    $data =  ArrayHelper::map(self::find()->select(['id','typeName','display'])->all(), 'id', 'display');
                    $translatedData = array();
                    foreach($data as $key => $item)
                    {
                        $translatedData[$key]=Yii::t('messages',$item);
                    }
                    $data = $translatedData;
                    break;
            }
            if ($emptyOption) {
                $data = $emptyVal + $data;
            }

        } else {
            if ($emptyOption) {
                $data = $emptyArr;
            }
        }

        return $data;
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
     * @return CActiveDataProvider the data provider that can return the models
     * based on the search/filter conditions.
     */
    public function search()
    {
        // @todo Please modify the following code to remove attributes that should not be searched.

        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id);
        $criteria->compare('typeName', $this->typeName, true);
        $criteria->compare('display', $this->display, true);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return CustomType the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /*
     * Function to get the custom field type
     * @param integer $customFieldId
     * @return String $typeName
     */
    public static function getCustomFieldType($customFieldId){
        $typeName = null;
        $query = new Query();
        $results = $query->select('CustomType.*')
            ->innerJoin('CustomField cf', 'cf.customTypeId = CustomType.id')
            ->where(['cf.id' => $customFieldId])
            ->from('CustomType')->all();

        if(!ToolKit::isEmpty($results)) {
            foreach ($results as $result) {
                $typeName = $result['typeName'];
            }
        }

        return $typeName;
    }

    /**
     * {@inheritdoc}
     * @return CustomTypeQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CustomTypeQuery(get_called_class());
    }
}
