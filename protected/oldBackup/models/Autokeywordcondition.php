<?php

namespace app\models;

use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Query;

/**
 * This is the model class for table "autokeywordcondition".
 *
 * @property int $keywordId
 * @property int $ruleId
 * @property int $status 0 - active, 1 - inactive, 2 - deleted
 * @property string $lastUpdate Last update timestamp after autofillkeyword process complete
 * @property string $params Extra params in json ecoded format 
 */
class AutoKeywordCondition extends \yii\db\ActiveRecord
{
    const APPLY_FB_FRIENDS = 1;
    const APPLY_FB_UNFRIENDS = 2;
    const APPLY_TW_FOLLOWER = 3;
    const APPLY_TW_UNFOLLOWER = 4;
    const APPLY_TEAMS = 5;

    public $rules = array (
        self::APPLY_FB_FRIENDS => 'Apply to Facebook Friends',
        self::APPLY_FB_UNFRIENDS => 'Apply to Facebook Un Friends',
        self::APPLY_TW_FOLLOWER => 'Apply to Twitter Followers',
        self::APPLY_TW_UNFOLLOWER => 'Apply to Twitter Un Followers',
        self::APPLY_TEAMS => 'Apply to Selected Team(s)'
    );

    const AUTO_KEY_COND_ACTIVE = 0;
    const AUTO_KEY_COND_INACTIVE = 1;

    /**
     * @return string the associated database table name
     */
    public static function tableName()
    {
        return 'AutoKeywordCondition';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['keywordId', 'ruleId'], 'required'],
            [['keywordId','ruleId'], 'integer'],
            [['lastUpdate'], 'safe'],
//            [['params'], 'string'],
//            [['keywordId', 'ruleId'], 'unique', 'targetAttribute' => ['keywordId', 'ruleId']],
            [['keywordId','ruleId','lastUpdate'],'safe', 'on'=>'search']
        ];
    }


    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'keywordId' => 'Keyword',
            'ruleId' => 'Rule',
//            'status' => '0 - active, 1 - inactive, 2 - deleted',
//            'lastUpdate' => 'Last update timestamp after autofillkeyword process complete',
//            'params' => 'Extra params in json ecoded format ',
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

        $query = AutoKeywordCondition::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [ 'pageSize' => 10 ],
        ]);

        $query->filterWhere(['keywordId'=>$this->keywordId,'ruleId'=>$this->ruleId]);
        return $dataProvider;

    }



    /**
     * {@inheritdoc}
     * @return AutokeywordconditionQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new AutokeywordconditionQuery(get_called_class());
    }
}
