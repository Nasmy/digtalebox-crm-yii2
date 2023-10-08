<?php
namespace app\models;

use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\helpers\Html;
use app\components\RActiveRecord;


/**
 * This is the model class for table "MapZone".
 *
 * The followings are the available columns in table 'MapZone':
 * @property integer $id
 * @property string $title
 * @property string $zoneLongLat
 * @property string $fullAddress
 * @property string $searchType
 * @property string $keywordsExclude
 * @property string $keywords
 * @property integer $status
 * @property string $age
 * @property string $zip
 * @property integer $userType
 * @property integer $gender
 * @property string $countryCode
 * @property string $city
 * @property string $lastName
 * @property string $firstName
 */
class MapZone extends RActiveRecord
{

    public $teamZoneData;
    public $searchFormData;

    /**
     * @return string the associated database table name
     */
    public static function tableName()
    {
        return 'MapZone';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
//			array('title, zoneLongLat', 'required'),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('teamZoneData, firstName, lastName, city, countryCode, gender, userType, zip, age, status, keywords, keywordsExclude, searchType, fullAddress', 'safe'),
            array('id, title, zoneLongLat, teamZoneData', 'safe', 'on'=>'search'),
            array('id, title, zoneLongLat, teamZoneData', 'safe', 'on'=>'update'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'title' => Yii::t('messages','Title'),
            'zoneLongLat' => Yii::t('messages','Zone Long Lat'),
            'fullAddress' => Yii::t('messages','Full Address'),
            'searchType' => Yii::t('messages','Search Type'),
            'keywordsExclude' => Yii::t('messages','Keywords Exclude'),
            'keywords' => Yii::t('messages','Keywords'),
            'status' => Yii::t('messages','Status'),
            'age' => Yii::t('messages','Age'),
            'zip' => Yii::t('messages','Zip'),
            'userType' => Yii::t('messages','User Type'),
            'gender' => Yii::t('messages','Gender'),
            'countryCode' => Yii::t('messages','Country Code'),
            'city' => Yii::t('messages','City'),
            'lastName' => Yii::t('messages','Last Name'),
            'firstName' => Yii::t('messages','First Name'),
        );
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
        $query=new Query();
        $query->select('*');
        $query->from('MapZone')
            ->andFilterWhere([
                'searchType'=>$this->searchType,
                'keywordsExclude'=>$this->keywordsExclude,
                'keywords'=>$this->keywords,
                'age'=>$this->age,
                'zip'=>$this->zip,
                'userType' => $this->userType,
                'gender'=>$this->gender,
                'countryCode'=>$this->countryCode,
                'city'=>$this->city,
                'lastName'=>$this->lastName,
                'firstName'=>$this->firstName,
            ]);
        $query
            ->andFilterWhere(['like', 'searchType', $this->searchType])
            ->andFilterWhere(['like', 'keywords', $this->keywords])
            ->andFilterWhere(['like', 'age', $this->age])
            ->andFilterWhere(['like', 'zip', $this->zip])
            ->andFilterWhere(['like', 'userType', $this->userType])
            ->andFilterWhere(['like', 'gender', $this->gender])
            ->andFilterWhere(['like', 'countryCode', $this->countryCode])
            ->andFilterWhere(['like', 'city', $this->city])
            ->andFilterWhere(['like', 'lastName', $this->lastName])
            ->andFilterWhere(['like', 'firstName', $this->firstName]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $dataProvider;

    }

    /*
     * function to get filters applied for s mapZone
    */
    function getSingleMapZone($id)
    {
        $query=new Query();
        $query->select('*');
        $query->from('MapZone')->where(['id'=>$id])->one();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        return $dataProvider;
    }

    /**
     * {@inheritdoc}
     * @return MapZoneQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new MapZoneQuery(get_called_class());
    }

    /**
     * @param array $coordinates
     */
    public function createPolygon($coordinates = []) {
       $polygonSets = null;
       $coordinateCount = count($coordinates);
       $coordinateSeparator = ',';
       $i = 1;
       foreach ($coordinates as $coordinate) {
           if(!empty($coordinate)) {
               $latitude = $coordinate[0];
               $longitude = $coordinate[1];
               $polygonSets = $polygonSets.$latitude.' '.$longitude;
           }
           if($coordinateCount != $i) {
               $polygonSets = $polygonSets.$coordinateSeparator;
           }
           $i++;
       }
       return "POLYGON(($polygonSets))";
    }

    /**
     * Prepare Zones selecting dropdown menu.
     * @return array $options Zone options
     */
    public function getMapZoneDropdown()
    {
        $options = array();
        $options[''] = Yii::t('messages','- Zone -');
        $models = MapZone::find()->orderBy(['title'=>SORT_ASC])->all();

        foreach($models as $model) {
            $options[$model->id] = Yii::t('messages', $model->title);
        }

        return $options;
    }


}
