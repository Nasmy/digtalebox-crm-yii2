<?php

namespace app\models;

use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "feedsearchkeyword".
 *
 * @property int $id
 * @property string $keyword
 * @property int $keywordId Id of the Keyword table related to this keyword
 * @property int $threshold maximum number of searches for this key word per day.
 * @property int $collectedCountTw Collected feed count for particular keyword
 * @property int $collectedCountFb Collected feed count for particular keyword
 * @property int $collectedCountGp Collected count for particular keyword
 * @property string $nextQueryFb
 * @property string $twSinceId Twitter since id
 * @property int $collectedCountTwDaily Twitter feed count for today
 * @property int $collectedCountFbDaily Facebook feed count for today
 * @property int $collectedCountGpDaily Collected Google Plus Feeds for a day
 * @property string $dateGp Google Plus Feeds collected date
 * @property string $date current date
 * @property string $dateFb FB feed collected date
 * @property string $lastGpFeedCollectedTime Last feed collected time 
 */
class FeedSearchKeyword extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'FeedSearchKeyword';
    }


    /*
     * Function called before validating
     */
    public function beforeValidate() {
        $this->keyword = trim($this->keyword);

        return parent::beforeValidate();
    }


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['keyword'], 'required'],
            [['keyword'], 'unique'],
            [['threshold','collectedCountTw','collectedCountFb'], 'integer'],
            [['id','keyword','threshold','collectedCountTw','collectedCountFb'],'safe','on'=>'search']
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
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'keyword' => Yii::t('messages','Keyword'),
            'threshold' => Yii::t('messages','Daily Limit'),
            'collectedCountTw' => Yii::t('messages','Collected Feed Count - Twitter'),
            'collectedCountFb' => Yii::t('messages','Collected Feed Count - Facebook'),
            'collectedCountGp' => Yii::t('messages','Collected Feed Count - Google+'),
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

        $query = FeedSearchKeyword::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [ 'pageSize' => 10 ],
        ]);

        return $dataProvider;
    }

    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    /**
     * Equally distribute daily threshold amoung keywords
     */
    public function distributeThreshold()
    {
        $keywordCount = FeedSearchKeyword::find()->count();
        $dbConfig = new Configuration();
        $dbConfig =$dbConfig->getConfigurations();

        if ($keywordCount > 0) {
            $thresholdPerKeyword = ceil($dbConfig['DAILY_FEED_SEARCH_LIMIT']/$keywordCount);
            FeedSearchKeyword::updateAll(['threshold'=>$thresholdPerKeyword]);
        }
    }



    /**
     * {@inheritdoc}
     * @return FeedSearchKeywordQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new FeedSearchKeywordQuery(get_called_class());
    }

    /**
     * Once user authenticated his Twitter account auto add his screen name
     * @param string $screenName Twitter handler
     */
    public function addTwitterHandler($screenName)
    {
        $model = new FeedSearchKeyword();
        $model->keyword = $screenName;

        try {
            if ($model->save()) {
                $model->distributeThreshold();
                Yii::$app->appLog->writeLog("Twitter handler added for keyword search");
                $modelKeyword = Keyword::find()->where(['name'=>$model->keyword])->one();
                if (empty($modelKeyword)) {
                    $modelKeyword = new Keyword();
                    $modelKeyword->name = $model->keyword;
                    $modelKeyword->behaviour = Keyword::KEY_MANUAL;
                    $modelKeyword->status = Keyword::KEY_ACTIVE;
                    $modelKeyword->type = Keyword::KEY_TYPE_SYSTEM;
                    $modelKeyword->lastUpdated = date('Y-m-d H:i:s');
                    $modelKeyword->createdBy = Yii::$app->user->getId();
                    $modelKeyword->createdAt = date('Y-m-d H:i:s');
                } else {
                    $modelKeyword->behaviour = Keyword::KEY_MANUAL;
                    $modelKeyword->status = Keyword::KEY_ACTIVE;
                    $modelKeyword->type = Keyword::KEY_TYPE_SYSTEM;
                    $modelKeyword->lastUpdated = date('Y-m-d H:i:s');
                    $modelKeyword->updatedBy = Yii::$app->user->getId();
                    $modelKeyword->updatedAt = date('Y-m-d H:i:s');
                }

                try {
                    if ($modelKeyword->save()) {
                        FeedSearchKeyword::updateAll(['keywordId' => $modelKeyword->id], ['id' => $model->id]);
                        Yii::$app->appLog->writeLog("Keyword added to Keyword table. Data:" . json_encode($modelKeyword->attributes));
                    } else {
                        Yii::$app->appLog->writeLog("Keyword could not be add to Keyword table. Data:" . json_encode($modelKeyword->attributes));
                    }
                 } catch(Exception $e) {
                    Yii::$app->appLog->writeLog("Keyword could not be add to Keyword table. Data:" . json_encode($modelKeyword->attributes) . ",Error:{$e->getMessage()}");
                }
            } else {
                Yii::$app->appLog->writeLog("Twitter handler add failed. Error:" . json_encode($model->errors));
            }
        } catch (Exception $e) {
            Yii::$app->appLog->writeLog("Twitter handler add failed. Error:{$e->getMessage()}");
        }
    }
}
