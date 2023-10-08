<?php

namespace app\models;

use Yii;
use app\components\RActiveRecord;
use app\models\MsgBoxQuery;

/**
 * This is the model class for table "msgbox".
 *
 * @property int $id
 * @property int $senderUserId
 * @property int $receiverUserId
 * @property string $message
 * @property int $refMsgId
 * @property string $subject
 * @property string $dateTime
 * @property int $status 0-n/a,1-new,2-red
 * @property int $folder 1-sent,2-inbox
 * @property string $userlist Comma separated recepient list
 * @property int $criteriaId
 * @property int $totalRecipient
 * @property int $deliveredCount
 */
class MsgBox extends RActiveRecord
{
    const FOLDER_SENT = 1;
    const FOLDER_INBOX = 2;
    const MSG_STATUS_NEW = 1;
    const MSG_STATUS_RED = 2;
    const MSG_STATUS_DELETED = 3;
    const MSG_STATUS_NA = 0;

    public $fromDate = null;
    public $toDate = null;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'MsgBox';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return [
            [['senderUserId', 'receiverUserId', 'message', 'refMsgId', 'subject', 'dateTime', 'status', 'folder'], 'required', 'on' => 'compose'],
            [['refMsgId','status', 'folder','criteriaId'],'integer', 'on' => 'compose'],
            [['senderUserId','receiverUserId'], 'integer','max' => 20, 'on' => 'compose'],
            [['subject'], 'required', 'on' => 'sendEvent'],
            [['userlist'] ,'validateRecipients', 'on' => 'compose'],
            [['subject'], 'string', 'max' => 64, 'on' => 'compose'],
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            [['id', 'senderUserId', 'receiverUserId', 'message', 'refMsgId', 'subject', 'dateTime', 'status', 'folder', 'userlist', 'criteriaId', 'fromDate', 'toDate'], 'safe', 'on' => 'search'],
            [['id', 'senderUserId', 'receiverUserId', 'message', 'subject', 'dateTime', 'fromDate', 'toDate'], 'safe', 'on' => 'searchInbox'],
        ];
    }

    public function validateRecipients() {
        if ("" == $this->userlist && "" == $this->criteriaId) {
            $this->addError('userlist', Yii::t('messages', 'Please select a Criteria or enter Receipients'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'senderUserId' => Yii::t('messages', 'Sender'),
            'receiverUserId' => Yii::t('messages', 'Receiver'),
            'message' => Yii::t('messages', 'Message'),
            'refMsgId' => 'Ref Msg',
            'subject' => Yii::t('messages', 'Subject'),
            'dateTime' => Yii::t('messages', 'Date & Time'),
            'status' => 'Status',
            'folder' => 'Folder',
            'userlist' => Yii::t('messages', 'Receivers'),
            'criteriaId' => Yii::t('messages', 'Criteria'),
            'fromDate' => Yii::t('messages', 'From Date'),
            'toDate' => Yii::t('messages', 'To Date'),
            'emailTemplate' => Yii::t('messages', 'Email Template'),
        ];
    }

    /**
     * Returns the usernames when their ids given
     * @param string $$useIdList Comma separated userid list.
     * @return string $userIdStr Comma separated user names list
     */
    public function getUserNames($useIdList) {
        $userIdStr = '';
        $userIdList = explode(",", $useIdList);

        if (!empty($userIdList)) {
            foreach ($userIdList as $userId) {
                $model = User::findOne(['id'=>$userId]);
                if (null != $model) {
                    $userIdStr .= "{$model->getName()},";
                }
            }
        }

        return rtrim($userIdStr, ",");
    }

    /**
     * Returns the criteria name when its ids given
     * @param string $criteriaId Criteria id
     * @return string $criteriaName Name of the criteria
     */
    public function getCriteriaName($criteriaId) {
        $model = SearchCriteria::findOne(['id'=>$criteriaId]);
        if (null != $model) {
            return $model->criteriaName;
        }
        return '';
    }


    /**
     * {@inheritdoc}
     * @return MsgboxQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new MsgBoxQuery(get_called_class());
    }
}
