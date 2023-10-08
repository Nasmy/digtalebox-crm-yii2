<?php

namespace app\models;

use app\components\RActiveRecord;
use http\Exception;
use Yii;

/**
 * This is the model class for table "FeedLimit".
 *
 * @property int $id
 * @property string $date
 * @property int $requestsRemain
 */
class FeedLimit extends RActiveRecord
{
    // Maximum Twitter direct messages per day
    const MAX_DM_PER_DAY = 240;

    // Maximum Facebook private messages per day
    const MAX_FBM_PER_DAY = 240;

    // Maximum LinkedIn private messages per day
    const MAX_LN_PER_DAY = 10;

    // Maximum SMS per day
    const MAX_SMS_PER_DAY = 240;

    // Twitter record id
    const TW_REC_ID = 1;

    // Facebook record id
    const FB_REC_ID = 2;

    // LinkedIn record id
    const LN_REC_ID = 3;

    // SMS record id
    const SMS_REC_ID = 4;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'FeedLimit';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'date', 'requestsRemain'], 'required'],
            [['id', 'requestsRemain'], 'integer'],
            [['date'], 'safe'],
            [['id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'date' => 'Date',
            'requestsRemain' => 'Requests Remain',
        ];
    }

    /**
     * Check whether system exceeded daily feed limit
     * @param integer $recordId Social network identifier. ie:TW_REC_ID or FB_REC_ID
     * @return boolean true if exceeded otherwise false
     */
    public function checkLimit($recId)
    {
        $isLimitExceeded = false;
        $utcDate = gmdate('Y-m-d');

        $model = FeedLimit::findOne($recId);

        $maxRecPerDay = 0;

        if (self::TW_REC_ID == $recId) {
            $maxRecPerDay = self::MAX_DM_PER_DAY;
        } else if (self::LN_REC_ID == $recId) {
            $maxRecPerDay = self::MAX_LN_PER_DAY;
        } else if (self::SMS_REC_ID == $recId) {
            $maxRecPerDay = self::MAX_SMS_PER_DAY;
        } else {
            $maxRecPerDay = self::MAX_FBM_PER_DAY;
        }

        if (null == $model) {
            // No record in the database
            $model = new FeedLimit();
            $model->id = $recId;
            $model->date = $utcDate;
            $model->requestsRemain = ($maxRecPerDay - 1);
        } else {
            if ($model->date != $utcDate) {
                // Record available but new day reset limit
                $model->date = $utcDate;
                $model->requestsRemain = ($maxRecPerDay - 1);
            } else {
                if (0 == $model->requestsRemain) {
                    // Limit exceeded
                    $isLimitExceeded = true;
                } else {
                    // Record available same day, just reduce rquests remain
                    $model->requestsRemain = $model->requestsRemain - 1;
                }
            }
        }

        if (!$isLimitExceeded) {
            try {
                $model->save(false);
            } catch (Exception $e) {

            }
        }

        return $isLimitExceeded;
    }

    /**
     * {@inheritdoc}
     * @return FeedLimitQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new FeedLimitQuery(get_called_class());
    }
}
