<?php

namespace app\models;

use app\components\ToolKit;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Query;

/**
 * This is the model class for table "CampaignUsers".
 *
 * @property int $campaignId Campaign id
 * @property int $userId Recipient user id
 * @property string $email
 * @property string $mobile
 * @property int $status 1 - pending, 2 - sent, 3 - fail
 * @property int $emailStatus 1-send,2-open,3-click,4-bounce,5-spam,6-block
 * @property int $smsStatus 0 - pending, 1-delivered, 3- failed
 * @property string $clickedUrls
 * @property int $emailTransactionId Mailjet Email transaction id, Used to update with mailjet callbacks
 * @property string $smsId SMS transaction id
 * @property string $createdAt Record added date time
 */
class CampaignUsers extends \yii\db\ActiveRecord
{
    const MESSAGE_SENT = 2;
    const MESSAGE_SENT_FAIL = 3;

    // Email statuses
    const EMAIL_FAILED = 0; //API submission failed
    const EMAIL_SENT = 1;
    const EMAIL_OPENED = 2;
    const EMAIL_CLICKED = 3;
    const EMAIL_BOUNCED = 4;
    const EMAIL_SPAM = 5;
    const EMAIL_BLOCKED = 6;
    const EMAIL_UNSUBSCRIBED = 7;
    const EMAIL_QUEUED = 8;
    const EMAIL_RETRYING = 9;

    // SMS statuses
    const SMS_PENDING = 0;
    const SMS_DELIVERED = 1;
    const SMS_FAILED = 2;
    const SMS_QUEUED = 3;

    const CRON_COMMAND = 'mass-mail-jet';

    // Name of the user
    public $name;
    // User`s email address
    public $email;
    // User`s mobile
    public $mobile;
    // Campaign Type
    public $campType;
    // isUnsubEmail
    public $isUnsubEmail;
    // keywords
    public $keywords;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'CampaignUsers';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['campaignId', 'userId', 'status'], 'required'],
            [['email'], 'email'],
            [['keywords'], 'implodeParams'], //define custom validator to implode checked values into string
            [['campaignId', 'userId', 'status'], 'integer'],
            [['mobile', 'name'], 'safe'],
            [['campaignId', 'userId', 'status', 'emailStatus', 'email', 'name', 'mobile', 'smsStatus'], 'safe', 'on' => 'search'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'campaignId' => Yii::t('app', 'Campaign id'),
            'userId' => Yii::t('app', 'Recipient user id'),
            'email' => Yii::t('app', 'Email'),
            'mobile' => Yii::t('app', 'Mobile'),
            'status' => Yii::t('app', '1 - pending, 2 - sent, 3 - fail'),
            'emailStatus' => Yii::t('app', '1-send,2-open,3-click,4-bounce,5-spam,6-block'),
            'smsStatus' => Yii::t('app', '0 - pending, 1-delivered, 3- failed'),
            'clickedUrls' => Yii::t('app', 'Clicked Urls'),
            'emailTransactionId' => Yii::t('app', 'Mailjet Email transaction id, Used to update with mailjet callbacks'),
            'smsId' => Yii::t('app', 'SMS transaction id'),
            'createdAt' => Yii::t('app', 'Date'),
        ];
    }

    /**
     * Custom validator to implode checked values into string
     * @param string $attribute active record attribute name.
     */
    public function implodeParams($attribute)
    {
        if (is_array($this->$attribute)) {
            $this->$attribute = implode(',', $this->$attribute);
        }
    }

    public function getClickUrl($string)
    {
        preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $string, $match);
        return Yii::$app->toolKit->convertTextUrlsToLinks(implode(" , ", $match[0]));
    }

    /**
     * Retrieve email address from User table
     * @return string content
     */
    public function getAttributeValue($id, $attributeType)
    {
        $result = 'N/A';
        $user = User::findOne($id); // if user
        if ($user && isset($user->$attributeType) && !ToolKit::isEmpty($user->$attributeType))
            $result = $user->$attributeType;
        else {
            $campUser = CampaignUsers::find()->where(['userId' => $id])->all();
            if ($campUser && isset($campUser->$attributeType) && !ToolKit::isEmpty($campUser->$attributeType))
                $result = $campUser->$attributeType;

        }

        return $result;
    }

    /**
     * Retrieve bootstrap label to show on grid
     * @return string bootstrap label html content
     */
    public function getEmailStatusLabel($emailStatus, $isUnsubEmail)
    {
        $cssClass = '';
        $text = '';

        if($isUnsubEmail == true){
            $cssClass = 'badge bg-unsubs';
            $text = Yii::t('messages', 'Unsubscribed');
        }

        else {
            switch ($emailStatus) {
                case self::EMAIL_SENT:
                    $cssClass = 'badge bg-not-opened';
                    $text = Yii::t('messages', 'Sent');
                    break;

                case self::EMAIL_FAILED:
                    $cssClass = 'badge bg-failed';
                    $text = Yii::t('messages', 'Failed');
                    break;

                case self::EMAIL_OPENED:
                    $cssClass = 'badge bg-opened';
                    $text = Yii::t('messages', 'Opened');
                    break;

                case self::EMAIL_CLICKED:
                    $cssClass = 'badge bg-clicked';
                    $text = Yii::t('messages', 'Clicked');
                    break;

                case self::EMAIL_BOUNCED:
                    $cssClass = 'badge bg-bounced';
                    $text = Yii::t('messages', 'Bounced');
                    break;

                case self::EMAIL_SPAM:
                    $cssClass = 'badge bg-spam';
                    $text = Yii::t('messages', 'Spam');
                    break;

                case self::EMAIL_BLOCKED:
                    $cssClass = 'badge bg-blocked';
                    $text = Yii::t('messages', 'Blocked');
                    break;

                case self::EMAIL_QUEUED:
                    $cssClass = 'badge bg-not-opened';
                    $text = Yii::t('messages', 'Queued');
                    break;
                case self::EMAIL_RETRYING:
                    $cssClass = 'badge bg-bounced';
                    $text = Yii::t('messages', 'Retrying');
                    break;
            }
        }

        return "<div class='email-status {$cssClass}'><span class='email-status-text'>{$text}</span></div>";
    }

    /**
     * Retrieve bootstrap label to show on grid
     * @return string bootstrap label html content
     */
    public function getEmailStatusText()
    {
        $text = '';

        if($this->isUnsubEmail == true){
            $text = Yii::t('messages', 'Unsubscribed');
        }
        else {
            switch ($this->emailStatus) {
                case self::EMAIL_SENT:
                    $text = Yii::t('messages', 'Sent');
                    break;

                case self::EMAIL_FAILED:
                    $text = Yii::t('messages', 'Failed');
                    break;

                case self::EMAIL_OPENED:
                    $text = Yii::t('messages', 'Opened');
                    break;

                case self::EMAIL_CLICKED:
                    $text = Yii::t('messages', 'Clicked');
                    break;

                case self::EMAIL_BOUNCED:
                    $text = Yii::t('messages', 'Bounced');
                    break;

                case self::EMAIL_SPAM:
                    $text = Yii::t('messages', 'Spam');
                    break;

                case self::EMAIL_BLOCKED:
                    $text = Yii::t('messages', 'Blocked');
                    break;
            }
        }

        return $text;
    }

    /**
     * to get the label text weather failed, pending or sent
     * @return string text
     */
    public function getSmsStatusText()
    {
        $text = '';
        switch ($this->smsStatus) {
            case self::SMS_DELIVERED:
                $text = Yii::t('messages', 'Delivered');
                break;

            case self::SMS_PENDING:
                $text = Yii::t('messages', 'Pending');
                break;

            case self::SMS_FAILED:
                $text = Yii::t('messages', 'Failed');
                break;
        }

        return $text;
    }

    /**
     * Retrieve mobile no from User table
     * @return string content
     */
    public static function getMobile($id)
    {
        $result = 'N/A';
        $user = User::findOne($id); // if user
        if($user && !ToolKit::isEmpty($user->mobile))
            $result = $user->mobile;
        else
        {
            $campUser = self::findAll(['userId=:userId', 'userId'=>$id]);
            if($campUser && !ToolKit::isEmpty($campUser->mobile))
                $result = $campUser->mobile;

        }
        return $result;
    }


    /**
     * Retrieve bootstrap label to show on grid
     * @return string bootstrap label html content
     */
    public static function getSmsStatusLabel($smsStatus)
    {
        $cssClass = '';
        $text = '';
        switch ($smsStatus) {
            case self::SMS_DELIVERED:
                $cssClass = 'badge bg-opened';
                $text = Yii::t('messages', 'Delivered');
                break;

            case self::SMS_PENDING:
                $cssClass = 'badge bg-not-opened';
                $text = Yii::t('messages', 'Pending');
                break;

            case self::SMS_FAILED:
                $cssClass = 'badge bg-failed';
                $text = Yii::t('messages', 'Failed');
                break;
            case self::SMS_QUEUED:
                $cssClass = 'badge bg-not-opened';
                $text = Yii::t('messages', 'Queued');
        }

        return "<div class='email-status {$cssClass}'><span class='email-status-text'>{$text}</span></div>";
    }

    /**
     * @param $emailStatus
     * @param $totalCount
     * @return array
     * @throws \yii\db\Exception
     */
    public static function getEmailStatusCount($id,$emailStatus, $totalCount) {
        $connection = Yii::$app->db;
        $sql = "SELECT COUNT(*) FROM `CampaignUsers` `t` LEFT JOIN User U ON t.userId = U.id WHERE t.emailStatus=" . $emailStatus . " AND campaignId=$id  AND U.isUnsubEmail ='0' ";
        $command = $connection->createCommand($sql);
        $countTotal = $command->queryScalar();
        $count = ($totalCount > 0) ? round((($countTotal / $totalCount) * 100), 2) . '%' : 0;

        $results = ['countTotal' => $countTotal, 'count' => $count];
        return $results;
    }


    /**
     * @param $userId
     * @return bool
     */
    public function checkUserExist($userId)
    {
        $isExist = User::find()->where( [ 'id' => $userId ] )->exists();
         return $isExist;
    }

    /**
     * Retrieve system event id by Mailjet event name.
     * @param string $eventName Event name pass via Mailjet callback.
     * @return integer system event id
     */
    public function getEventTypeByMjEventName($eventName)
    {
        $eventId = null;
        switch ($eventName) {
            case 'open':
                $eventId = self::EMAIL_OPENED;
                break;

            case 'click':
                $eventId = self::EMAIL_CLICKED;
                break;

            case 'bounce':
                $eventId = self::EMAIL_BOUNCED;
                break;

            case 'spam':
                $eventId = self::EMAIL_SPAM;
                break;

            case 'blocked':
                $eventId = self::EMAIL_BLOCKED;
                break;
        }

        return $eventId;
    }

    /**
     * {@inheritdoc}
     * @return CampaignUsersQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CampaignUsersQuery(get_called_class());
    }

    /**
     * @param $mjEvent
     * @param $userId
     */
    public function updateEmailStatus($mjEvent, $userId)
    {
        if ($mjEvent == 'bounce' || $mjEvent == 'blocked') {
            if ($mjEvent == 'bounce') {
                $emailStatus = User::BOUNCED_EMAIL;
            } else {
                $emailStatus = User::BLOCKED_EMAIL;
            }
            $model = User::findOne($userId);
            if (!empty($model)) {
                try {
                    $model->emailStatus = $emailStatus;
                    if ($model->save(false)) {
                        Yii::$app->appLog->writeLog("Email status updated. User id:{$userId}, Status:{$model->emailStatus}");
                    } else {
                        Yii::$app->appLog->writeLog("Email status update failed. User id:{$userId}, Status:{$emailStatus}");
                    }
                } catch (Exception $e) {
                    Yii::$app->appLog->writeLog("Email status update failed. User id:{$userId}, Status:{$emailStatus}, Error:{$e->getMessage()}");
                }
            }
        }
    }
}
