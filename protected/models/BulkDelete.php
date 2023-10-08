<?php
namespace app\models;

use Yii;
use yii\data\ActiveDataProvider;


/**
 * This is the model class for table "BulkDelete".
 *
 * The followings are the available columns in table 'BulkDelete':
 * @property integer $id
 * @property integer $searchCriteriaId
 * @property string $createdAt
 * @property integer $totalRecords
 * @property integer $lastRecord
 * @property string $status
 * @property string $startAt
 * @property string $finishedAt
 * @property integer $createdBy
 */
class BulkDelete extends \yii\db\ActiveRecord
{
	/**
	 * status
	 */
	const PENDING = 0;
	const IN_PROGRESS = 1;
	const FINISHED = 2;

	const CRON_COMMAND = 'mass-bulk-delete';
	/**
	 * @return string the associated database table name
	 */
	public static function tableName()
	{
		return 'BulkDelete';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return [
			[['searchCriteriaId, totalRecords, status, createdBy'], 'required'],
			[['searchCriteriaId, totalRecords, lastRecord, createdBy'], 'numerical', 'integerOnly'=>true],
			[['status'], 'length', 'max'=>1],
			[['createdAt, startAt, finishedAt'], 'safe'],
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			[['id, searchCriteriaId, createdAt, totalRecords, lastRecord, status, startAt, finishedAt, createdBy'], 'safe', 'on'=>'search'],
		];
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
		return [
			'id' => 'ID',
			'searchCriteriaId' => 'Search Criteria',
			'createdAt' => 'Created At',
			'totalRecords' => 'Total Records',
			'lastRecord' => 'Last Record',
			'status' => 'Status',
			'startAt' => 'Start At',
			'finishedAt' => 'Finished At',
			'createdBy' => 'Created By',
		];
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
        $query=self::find();
        $query->andFilterWhere(['id'=>$this->id]);
        $query->andFilterWhere(['searchCriteriaId'=>$this->searchCriteriaId]);
        $query->andFilterWhere(['createdAt'=>$this->createdAt,true]);
        $query->andFilterWhere(['totalRecords'=>$this->totalRecords]);
        $query->andFilterWhere(['lastRecord'=>$this->lastRecord]);
        $query->andFilterWhere(['status'=>$this->status]);
        $query->andFilterWhere(['startAt',$this->startAt]);
        $query->andFilterWhere(['finishedAt',$this->finishedAt]);
        $query->andFilterWhere(['createdBy'=>$this->createdBy]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 10],
        ]);

        return $dataProvider;
	}


}
