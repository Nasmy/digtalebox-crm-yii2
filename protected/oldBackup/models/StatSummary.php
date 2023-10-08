<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "statsummary".
 *
 * @property string $date
 * @property int $newSupporterCount
 * @property int $dataUsage
 * @property int $newRegistrationCount
 * @property int $feedCount Feed count per day
 * @property int $supporterCount Supporter count for each day
 * @property int $prospectCount Prospect count for each day
 */
class StatSummary extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'StatSummary';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date', 'supporterCount', 'prospectCount'], 'required'],
            [['date'], 'safe'],
            [['newSupporterCount', 'dataUsage', 'newRegistrationCount', 'feedCount', 'supporterCount', 'prospectCount'], 'integer'],
            [['date'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'date' => 'Date',
            'newSupporterCount' => 'New Supporter Count',
            'dataUsage' => 'Data Usage',
            'newRegistrationCount' => 'New Registration Count',
            'feedCount' => 'Feed Count',
            'supporterCount' => 'Supporter Count',
            'prospectCount' => 'Prospect Count',
        ];
    }

    /**
     * Prepare statistics summary for last 7 days
     * @return Array of stat summary
     */
    public function getStatSummary($days = 7)
    {
//        $criteria = new Q();
//        $criteria->order = '`date` DESC';
//        $criteria->limit = $days;
//        $models = StatSummary::model()->findAll($criteria);
//
//        $newSupCount = array();
//        $newRegCount = array();
//        $dataUsage = array();
//        $feedCount = array();
//
//        if (null != $models) {
//            foreach ($models as $model) {
//                $date = date('m/j', strtotime($model->date));
//                $newSupCount[] = array($date, (int)$model->newSupporterCount);
//                $newRegCount[] = array($date, (int)$model->newRegistrationCount);
//                $dataUsage[] = array($date, (int)$model->dataUsage);
//                $feedCount[] = array($date, (int)$model->feedCount);
//            }
//        }
//
//        return array(
//            'newSupCount' => array_reverse($newSupCount),
//            'newRegCount' => array_reverse($newRegCount),
//            'dataUsage' => array_reverse($dataUsage),
//            'feedCount' => array_reverse($feedCount)
//        );
    }

    /**
     * {@inheritdoc}
     * @return StatSummaryQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new StatSummaryQuery(get_called_class());
    }
}
