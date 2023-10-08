<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "followup".
 *
 * @property int $id
 * @property string $requestedDateTime
 * @property int $supporterUserId
 * @property int $prospectUserId
 * @property int $connectionType 1 - friends on fb 2 - friends on twitter 3 - follower on twitter 4 - follower on fb
 * @property int $status 1 - pending 2 - accepted 3 - rejected
 * @property int $notificationType 1-Twitter message,2-Fb message, 3- email
 * @property int $delStatus
 * @property int $requestType 1 - Request, 2 - Reminder
 * @property int $createdBy
 * @property string $createdAt
 * @property int $updatedBy
 * @property string $updatedAt
 */
class FollowUp extends \yii\db\ActiveRecord
{

    // Followup statuses
    const PENDING = 1;
    const ACCEPTED = 2;
    const REJECTED = 3;

    // Followup connection types
    const CONTYPE_FB_FRIEND = 1;
    const CONTYPE_TW_FRIEND = 2;
    const CONTYPE_TW_FOLLOWER = 3;
    const CONTYPE_FB_FOLLOWER = 4;
    const CONTYPE_LN_CONNECTION = 5;

    // Followup notification types
    const NOTITYPE_TW_MESSAGE = 1;
    const NOTITYPE_FB_MESSAGE = 2;
    const NOTITYPE_EMAIL = 3;
    const NOTITYPE_LN_MESSAGE = 4;

    const DELETE = 1;
    const NOTDELETE = 0;

    const REQ_TYPE_REQUEST = 1;
    const REQ_TYPE_REMINDER = 2;

    public $fromDate = null;
    public $toDate = null;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'FollowUp';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['requestedDateTime', 'supporterUserId', 'prospectUserId', 'connectionType', 'status', 'notificationType', 'id'], 'required'],
            [['requestedDateTime', 'status', 'id'], 'number','integerOnly' => true],
            [['requestType'], 'safe'],
            [['id','requestedDateTime','supporterUserId','prospectUserId','connectionType','status','notificationType','delStatus','fromDate','toDate'], 'safe','on' => 'search'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'requestedDateTime' => 'Requested Date Time',
            'supporterUserId' => 'Supporter User ID',
            'prospectUserId' => 'Prospect User ID',
            'connectionType' => 'Connection Type',
            'status' => 'Status',
            'notificationType' => 'Notification Type',
            'delStatus' => 'Del Status',
            'requestType' => 'Request Type',
            'createdBy' => 'Created By',
            'createdAt' => 'Created At',
            'updatedBy' => 'Updated By',
            'updatedAt' => 'Updated At',
        ];
    }

    /**
     * {@inheritdoc}
     * @return FollowupQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new FollowupQuery(get_called_class());
    }
}
