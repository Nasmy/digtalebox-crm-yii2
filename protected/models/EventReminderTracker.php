<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "eventremindertracker".
 *
 * @property int $id
 * @property int $eventReminderId
 * @property int $eventId
 * @property int $userId
 * @property int $emailTransactionId
 * @property int $emailStatus
 * @property int $emailType 1- Reminder, 2- Event Invitation
 * @property string $createAt
 */
class EventReminderTracker extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'EventReminderTracker';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['eventReminderId', 'eventId', 'userId', 'emailTransactionId', 'emailStatus', 'emailType','createAt'], 'required'],
            [['eventReminderId', 'eventId', 'userId', 'emailTransactionId', 'emailStatus'], 'integer'],
            [['emailTransactionId'], 'string','max' => 20],
            [['emailTransactionId'], 'string','max' => 20],
            [['id','eventReminderId','eventId','userId','emailTransactionId','emailStatus','createAt'], 'safe','on' => 'search'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'eventReminderId' => Yii::t('messages', 'Event Reminder'),
            'eventId' => Yii::t('messages', 'Event'),
            'userId' => Yii::t('messages', 'User'),
            'emailTransactionId' => Yii::t('messages', 'Email Transaction Id'),
            'emailStatus' => Yii::t('messages', 'Email Status'),
            'createAt' => Yii::t('messages', 'Create At'),
        ];
    }

    public static function getCount($eventId, $userId, $emailType) {
        $count = EventReminderTracker::find()->where("eventId = :eventId AND userId = :userId AND emailType = :emailType", array(
            ":eventId" => $eventId, ":userId" => $userId, ":emailType" => $emailType))->count();
        return $count;
    }


    public static function convertFRdateTime($dateTime) {
        $systemDateTime = new \DateTime($dateTime);
        $fr_time = new \DateTimeZone('Europe/Paris');
        $systemDateTime->setTimezone($fr_time);
        $systemDateTime = $systemDateTime->format('Y-m-d H:i:s');
        return date('Y-m-d', strtotime($systemDateTime)).' '.date('H:i', strtotime($systemDateTime));
    }

    /**
     * {@inheritdoc}
     * @return EventReminderTrackerQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new EventReminderTrackerQuery(get_called_class());
    }
}
