<?php

namespace app\models;

use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Query;

/**
 * This is the model class for table "eventreminder".
 *
 * @property int $id
 * @property int $eventId
 * @property string $subject
 * @property int $messageTemplateId
 * @property string $rsvpStatus
 * @property int $totalRecipient
 * @property int $deliveredCount
 * @property string $createdAt
 * @property string $updatedAt
 */
class EventReminder extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'EventReminder';
    }

    const REMINDER = 1;
    const EVENT_INVITATION = 2;

    public $name = '';
    public $description = '';
    public $type = '';
    public $createdBy = '';

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['eventId', 'messageTemplateId', 'rsvpStatus', 'totalRecipient', 'createdAt'], 'required', 'on' => 'sendReminder'],
            [['subject', 'rsvpStatus', 'totalRecipient'], 'required', 'on' => 'sendEvent'],
            [['eventId', 'messageTemplateId'], 'integer'],
            [['updatedAt'], 'safe'],
            [['subject'], 'string', 'max' => 500],
            [['rsvpStatus'], 'string', 'max' => 100],
            [['id', 'eventId', 'messageTemplateId', 'rsvpStatus', 'createdAt', 'updatedAt'], 'safe', 'on' => 'search'],

        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'eventId' => Yii::t('messages', 'Event'),
            'messageTemplateId' => Yii::t('messages', 'Email Template'),
            'rsvpStatus' => Yii::t('messages', 'rsvp Status'),
            'createdAt' => Yii::t('messages', 'Created At'),
            'updatedAt' => Yii::t('messages', 'Updated At'),
            'subject' => Yii::t('messages', 'Subject'),
        ];
    }

    /*
	 * function to get send reminders emails to grid per user
     * @param $eventId, $rsvpStatus, $userId - integer
	 */
    public static function getEmailSend($eventId, $rsvpStatus, $userId)
    {

        $query = new Query();
        $query->select(['mt.*']);
        $query->from('EventReminder er');
        $query->innerJoin('MessageTemplate mt', 'mt.id = er.messageTemplateId');
        $query->innerJoin('EventReminderTracker ert', 'ert.eventReminderId = er.id');
        $query->where(['er.eventId' => $eventId]);
        $query->andWhere(['er.rsvpStatus' => $rsvpStatus]);
        $query->andWhere(['ert.userId' => $userId]);
        $query->andWhere(['ert.emailType' => EventReminder::REMINDER]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);


        return $dataProvider;
    }


    /**
     * {@inheritdoc}
     * @return EventReminderQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new EventReminderQuery(get_called_class());
    }
}
