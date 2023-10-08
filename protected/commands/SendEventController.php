<?php

namespace app\commands;

use app\components\WebUser;
use app\models\AuthAssignment;
use app\models\Campaign;
use app\models\CampaignUsers;
use app\models\Configuration;
use app\models\Event;
use app\models\EventReminder;
use app\models\EventReminderTracker;
use app\models\EventUser;
use app\models\MessageTemplate;
use app\models\MsgBox;
use app\models\SearchCriteria;
use app\models\User;
use yii\console\Controller;
use yii\console\Exception;
use yii\console\ExitCode;
use yii\db\Query;
use Yii;
use yii\web\Request;

/**
 * This process send messages to user`s inbox and to email account
 */
class SendEventController extends Controller
{

    // Batch size for users retrieve from database
    private $dbBatchSize = 100;
    // Configuration array
    private $config = array();
    // Whether message related to event section
    private $isEvent = false;
    // Id of the event
    private $eventId = null;

    /**
     * @param $domain
     * @param $modelIdf
     * @param $isEvent
     * @param $eventId
     * @param $action
     * @param $fromEmail
     * @param $fromName
     * @return int
     * @throws \yii\db\Exception
     */
    public function actionIndex($domain, $modelId, $isEvent, $eventId, $action, $fromEmail, $fromName, $language)
    {

        Yii::$app->language = $language;
        Yii::$app->appLog->isConsole = true;
        Yii::$app->appLog->username = 'MESSAGE_SEND_DAEMON';
        Yii::$app->appLog->logType = 2;

        // $domain;
        $msgId = $modelId;
        $this->isEvent = isset($isEvent) ? $isEvent : false;
        $this->eventId = isset($eventId) ? $eventId : null;
        $eventId = isset($eventId) ? $eventId : null;
        $action = isset($action) ? $action : null;
        $fromEmail = isset($fromEmail) ? $fromEmail : null;
        $fromName = isset($fromName) ? $fromName : null;
        $fromName =str_replace('-',' ',$fromName);
        $fromName = addslashes($fromName);  // remove special characters from this string
        Yii::$app->appLog->writeLog("Process started.Domain:{$domain},Message Id:{$msgId},IsEvent:{$this->isEvent}");

        $appModel = Yii::$app->dbMaster->createCommand('SELECT * FROM App WHERE domain=:domain ')
            ->bindValue(':domain', $domain)
            ->queryOne();


        Yii::$app->appLog->appId = $appModel['appId'];

        Yii::$app->appLog->writeLog("Connecting to database.Dbname:{$appModel['dbName']}");
        Yii::$app->toolKit->domain = $appModel['domain'];
        Yii::$app->toolKit->changeDbConnection($appModel['dbName'], $appModel['host'], $appModel['username'], $appModel['password']);

        if (null != $action AND $action != 'x') {
            $this->doSendReminderMessages($msgId, $eventId, $appModel, $action, $fromEmail, $fromName);
        } else {
            $this->doSendMessages($msgId, $eventId, $appModel, $fromEmail, $fromName);
        }
        return ExitCode::OK;
    }

    /**
     * This function initialize message sending process depending on message type.
     * Execute saved search criteria and save out put to CampaignUsers table
     *
     * @param integer $msgId Message id
     * @param $eventId
     * @throws \yii\db\Exception
     */
    private function doSendMessages($msgId, $eventId, $appModel, $fromEmail, $fromName)
    {
        $recipientCount = 0;
        $model = MsgBox::findOne($msgId);
        $senderModel = User::findOne($model->senderUserId);
        $modelEvent = Event::findOne($eventId);
        $startDateTime = User::convertDBTime($modelEvent->startDate . " " . $modelEvent->startTime);
        $endDateTime = User::convertDBTime($modelEvent->startDate . " " . $modelEvent->endTime);
        $modelEvent->startTime = date('H:i:s', strtotime($startDateTime));
        $modelEvent->endTime = date('H:i:s', strtotime($endDateTime));

        if (null != $model->userlist) {
            $recipients = explode(',', $model->userlist);
            $recipientCount = count($recipients);
            $this->saveUserCount($recipientCount, $model);
            foreach ($recipients as $userId) {
                $modelUser = User::findOne($userId);

                $this->sendMessage($modelUser, $model, $senderModel, $modelEvent, $appModel, $fromEmail, $fromName);
            }
        }

        if (0 != $model->criteriaId) {

            $modelSearchCriteria = SearchCriteria::findOne($model->criteriaId);
            $criteria = SearchCriteria::getCriteria($modelSearchCriteria);
            $criteria->andWhere('email != ""');
            $criteria->andWhere('isUnsubEmail = 0');
            $userCriteria = $criteria;

            $userCount = $userCriteria->from('User t')->count();
            $this->saveUserCount($userCount, $model);
            Yii::$app->appLog->writeLog("User count:{$userCount}");

            $startIndex = 0;
            $batchSize = 20;
            $exit = false;


            do {
                Yii::$app->appLog->writeLog("Retrieve batch, offset:{$startIndex},limit:{$batchSize}");
                $criteria->limit = $batchSize;
                $criteria->offset = $startIndex;
                $modelUsers = $criteria->from('User t')->all();

                if (null != $modelUsers) {
                    foreach ($modelUsers as $modelUser) {
                        $this->sendMessage($modelUser, $model, $senderModel, $modelEvent, $appModel, $fromEmail, $fromName);
                    }

                } else {
                    Yii::$app->appLog->writeLog("Batch processing over");
                    $exit = true;
                }
                $startIndex += $batchSize;
            } while (!$exit);
        }

    }

    /**
     * Save recipient count
     * @param integer $count User count
     * @param $msgBoxModel
     */
    private function saveUserCount($count, $msgBoxModel)
    {
        try {
            $msgBoxModel->totalRecipient = $count;
            $msgBoxModel->save(false);
        } catch (Exception $e) {

        }
    }

    /**
     * Update deliver count
     * @param $msgBoxModel
     */
    private function updateDeliverCount($msgBoxModel)
    {
        try {
            $msgBoxModel->deliveredCount = $msgBoxModel->deliveredCount + 1;
            $msgBoxModel->save(false);
        } catch (Exception $e) {

        }
    }

    /**
     * Add message to database and when add success send email notification
     * @param User $modelUser User model
     * @param MsgBox $modelMsgBox MsgBox model
     * @param User $senderModel User model of sender
     * @throws \yii\db\Exception
     */
    private function sendMessage($modelUser, $modelMsgBox, $senderModel, $modelEvent, $appModel, $fromEmail, $fromName)
    {
        $model = new MsgBox();
        $model->senderUserId = $modelMsgBox->senderUserId;
        $model->receiverUserId = $modelUser['id'];
        $model->message = '';
        $model->refMsgId = $modelMsgBox->id;
        $model->subject = $modelMsgBox->subject;
        $model->dateTime = date('Y-m-d H:i:s');
        $model->status = MsgBox::MSG_STATUS_NEW;
        $model->folder = MsgBox::FOLDER_INBOX;
        $model->userlist = '';
        $model->criteriaId = 0;


        try {
            if ($model->save(false)) {
                $this->updateDeliverCount($modelMsgBox);
                Yii::$app->appLog->writeLog("Message added to database. Sender id:{$model->senderUserId}, receiver id:{$model->receiverUserId}");
                $this->sendEmail($modelUser, $modelMsgBox, $senderModel, $model, $modelEvent, $appModel, $fromEmail, $fromName);
                if ($this->isEvent) {
                    $EventUserModel = new EventUser();
                    $EventUserModel->invite($this->eventId, $modelUser['id']);
                }
            } else {
                Yii::$app->appLog->writeLog("Message add failed to database. Sender id:{$model->senderUserId}, receiver id:{$model->receiverUserId}");
            }
        } catch (Exception $e) {
            Yii::$app->appLog->writeLog("Message add failed to database. Sender id:{$model->senderUserId}, receiver id:{$model->receiverUserId}, Error:{$e->getMessage()}");
        }

    }

    /**
     * Send notification email to message receiver
     * @param User $modelUser User model
     * @param MsgBox $modelMsgBox MsgBox model
     * @param User $senderModel User model of sender
     * @return int
     * @throws \yii\db\Exception
     */
    private function sendEmail($modelUser, $modelMsgBox, $senderModel, $modelNewMessage, $modelEvent, $appModel, $fromEmail, $fromName)
    {

        $messageUrl = str_replace('{domain}', Yii::$app->toolKit->domain, Yii::$app->params['absUrl']);
        $messageLink = "<a href='{$messageUrl}' >" . Yii::t('messages', 'here') . "</a>";
        $loginMsg = Yii::t('messages', 'Click {here} to access your DigitaleBox account and reply for message.', array('{here}' => $messageLink));
        $isExpired = false;
        $shareUri = str_replace('{domain}', Yii::$app->toolKit->domain, Yii::$app->params['absUrl']) . '/event/event-view?code=' . base64_encode('{"event":' . $modelEvent->id . '}');
        $acceptUri = str_replace('{domain}', Yii::$app->toolKit->domain, Yii::$app->params['absUrl']) . '/event/confirm?q=' . base64_encode('{"event":' . $modelEvent->id . ', "member":' . $modelUser['id'] . ', "invitation": "3"}');
        $rejectUri = str_replace('{domain}', Yii::$app->toolKit->domain, Yii::$app->params['absUrl']) . '/event/confirm?q=' . base64_encode('{"event":' . $modelEvent->id . ', "member":' . $modelUser['id'] . ', "invitation": "4"}');
        $maybeUri = str_replace('{domain}', Yii::$app->toolKit->domain, Yii::$app->params['absUrl']) . '/event/confirm?q=' . base64_encode('{"event":' . $modelEvent->id . ', "member":' . $modelUser['id'] . ', "invitation": "2"}');
        $shareByEmailDetails = array('subject' => Yii::t('messages', 'DigitaleBox Event'), 'body' => Yii::t('messages', 'Event: ') . $shareUri);
        Yii::$app->toolKit->setResourceInfo();
        $imgUrl = Yii::$app->toolKit->getWebRootUrl() . Yii::$app->toolKit->resourcePathRelative . $modelEvent->imageName;
        $path = Yii::getAlias('@app') . '/views/emailTemplates/eventInvitationTemplate.php';

        $message = Yii::$app->controller->renderFile($path, array(
            'model' => $modelEvent,
            'isExpired' => $isExpired,
            'domain' => $appModel['domain'],
            'shareUri' => $shareUri,
            'acceptUri' => $acceptUri,
            'rejectUri' => $rejectUri,
            'maybeUri' => $maybeUri,
            'type' => 'invite',
            'imgUrl' => $imgUrl,
            'isInvite' => false,
            'memberId' => $modelUser['id'],
            'confirm' => base64_encode('{"event":' . $modelEvent->id . ', "member":' . $modelUser['id'] . '}'),
            'shareByEmailDetails' => $shareByEmailDetails,
            'alertMsg' => '<strong>' . Yii::t('messages', 'Sorry!') . '</strong> ' . Yii::t('messages', 'This event canceled by the event organiser. Thank you.'),
        ), true
        );

        $emailStatus = CampaignUsers::EMAIL_SENT;
        $politicianInfo = AuthAssignment::find()->where(['itemname' => WebUser::POLITICIAN_ROLE_NAME])->one();
        $politicianUserInfo = User::find()->where(['id' => $politicianInfo->userid])->one();
        $unsubinfo = array(
            'userId' => $modelUser['id'],
            'domain' => $appModel['domain'],
            'clientName' => ucfirst($politicianUserInfo['firstName']),
        );
        $fromName =str_replace('-',' ',$fromName);
        $emailSendStatus = Yii::$app->toolKit->sendEmail(
            array(
                $modelUser['email']
            ), $modelMsgBox['subject'], $message, $unsubinfo, null, $fromName, $fromEmail
        );

        if ($emailSendStatus) {
            Yii::$app->appLog->writeLog("Email sent. Email:{$modelUser['email']},Email from email:{$fromEmail} from name:{$fromName}");
        } else {
            $emailStatus = CampaignUsers::EMAIL_FAILED;
            Yii::$app->appLog->writeLog("Email sent failed.Email:{$modelUser['email']}");
        }
        return $emailStatus;
    }

    /**
     * @param $template
     * @param array $data
     * @return string the rendering result.
     * @throws Exception
     */
    public function render($template, $data = array())
    {
        $path = Yii::getAlias('@app') . '/views/emailTemplates/' . $template . '.php';
        if (!file_exists($path))
            throw new Exception('Template ' . $path . ' does not exist.');
        return $this->renderFile($path, $data);
    }

    /**
     * This function initialize message sending process depending on reminder message type.
     * @param $reminderId , $eventId - integer,  $appModel - String
     *
     * @throws \yii\db\Exception
     * @throws \Exception
     */
    private function doSendReminderMessages($reminderId, $eventId, $appModel, $action, $fromEmail, $fromName)
    {
        $deliveredCount = 0;
        $emailStatus = 0;

        $eventReminderModel = Yii::$app->db->createCommand('SELECT * FROM EventReminder WHERE id=:id')
            ->bindValue(':id', $reminderId)
            ->queryOne();
        if ($eventReminderModel['rsvpStatus'] == 1) {
            $eventReminderModel['rsvpStatus'] = 0;
        }

        $query = new Query();
        $query->select('*')
            ->from('EventUser')
            ->where(["eventId" => $eventReminderModel['eventId']])
            ->andWhere(["rsvpStatus" => $eventReminderModel['rsvpStatus']]);

        $startIndex = 0;
        $batchSize = 20;
        $exit = false;
        $eventUserModel = $query->all();
        $eventUserCount = count($eventUserModel);

        $query->limit($batchSize)
            ->offset($startIndex);

        do {

            Yii::$app->appLog->writeLog("Retrieve batch, offset:{$startIndex},limit:{$batchSize}");

            if (!empty($eventUserModel) && $eventUserCount > 0) {
                foreach ($eventUserModel as $eventUser) {
                    $modelUser = User::findOne($eventUser['userId']);
                    if (EventReminder::REMINDER == $action) {
                        $emailStatus = $this->sendReminderEmail($modelUser, $eventReminderModel, $appModel, $fromEmail, $fromName);
                    } elseif (EventReminder::EVENT_INVITATION == $action) {

                        if ($eventReminderModel['messageTemplateId'] != 0) {
                            $emailStatus = $this->sendReminderEmailInvitation($modelUser, $eventReminderModel, $appModel, $fromEmail, $fromName);
                        } else {
                            $modelEvent = Event::findOne($eventId);
                            $emailStatus = $this->sendEmail($modelUser, $eventReminderModel, '', '', $modelEvent, $appModel, $fromEmail, $fromName);
                        }

                    }
                    if ($emailStatus) {
                        $emailTransactionId = Yii::$app->toolKit->emailTransactionId;
                        $deliveredCount++;
                        $this->addEventReminderTrack($reminderId, $eventId, $modelUser->id, $emailTransactionId, $emailStatus, $action);
                        Yii::$app->db->createCommand()
                            ->update('EventReminder', ['deliveredCount' => $deliveredCount], ['id' => $reminderId])
                            ->execute();
                    }

                    $eventUserCount--;
                }
            } else {
                Yii::$app->appLog->writeLog("Batch processing over");
                $exit = true;
            }
            $startIndex += $batchSize;
        } while (!$exit);
    }

    /**
     * @param $modelUser
     * @param $eventReminderModel
     * @param $appModel
     * @param $fromEmail
     * @param $fromName
     * @return int
     * @throws \yii\db\Exception
     */
    private function sendReminderEmail($modelUser, $eventReminderModel, $appModel, $fromEmail, $fromName)
    {

        $modelMessageTemplate = Yii::$app->db->createCommand('SELECT * FROM MessageTemplate WHERE id=:id ')
            ->bindValue(':id', $eventReminderModel['messageTemplateId'])
            ->queryOne();


        Yii::$app->toolKit->setResourceInfo();
        Yii::$app->appLog->writeLog("MassMessage resourcePathAbsolute:" . Yii::$app->toolKit->resourcePathAbsolute);
        $templateContent = file_get_contents(Yii::$app->toolKit->resourcePathAbsolute . $eventReminderModel['messageTemplateId'] . '.html');
        $campaignModel = new Campaign();
        $message = $campaignModel->getMessage($modelUser, $templateContent);
        $emailStatus = CampaignUsers::EMAIL_SENT;
        $fromName =str_replace('-',' ',$fromName);
        $emailSendStatus = Yii::$app->toolKit->sendEmail(
            array(
                $modelUser->email
            ), $modelMessageTemplate['subject'], $message, null, null, $fromName, $fromEmail
        );

        if ($emailSendStatus) {
            Yii::$app->appLog->writeLog("Email sent. Email:{$modelUser->email},Email from email:{$fromEmail} from name:{$fromName}");
        } else {
            $emailStatus = CampaignUsers::EMAIL_FAILED;
            Yii::$app->appLog->writeLog("Email sent failed.Email:{$modelUser->email}");
        }
        return $emailStatus;
    }


    /**
     * @param $modelUser
     * @param $eventReminderModel
     * @param $appModel
     * @param $fromEmail
     * @param $fromName
     * @return int
     * @throws \yii\db\Exception
     */
    private function sendReminderEmailInvitation($modelUser, $eventReminderModel, $appModel, $fromEmail, $fromName)
    {

        Yii::$app->toolKit->setResourceInfo();
        Yii::$app->appLog->writeLog("MassMessage resourcePathAbsolute:" . Yii::$app->toolKit->resourcePathAbsolute);
        $templateContent = file_get_contents(Yii::$app->toolKit->resourcePathAbsolute . $eventReminderModel['messageTemplateId'] . '.html');
        $campaignModel = new Campaign();
        $message = $campaignModel->getMessage($modelUser, $templateContent);
        $emailStatus = CampaignUsers::EMAIL_SENT;
        $fromName =str_replace('-',' ',$fromName);
        $emailSendStatus = Yii::$app->toolKit->sendEmail(
            array(
                $modelUser->email
            ), $eventReminderModel['subject'], $message, null, null, $fromName, $fromEmail
        );

        if ($emailSendStatus) {
            Yii::$app->appLog->writeLog("Email sent. Email:{$modelUser->email},Email from email:{$fromEmail} from name:{$fromName}");
        } else {
            $emailStatus = CampaignUsers::EMAIL_FAILED;
            Yii::$app->appLog->writeLog("Email sent failed.Email:{$modelUser->email}");
        }
        return $emailStatus;
    }


    /**
     * Find keywords on original message and dynamically assign values to them.
     * @param User $modelUser User object
     * @param string $origMessage Original text
     * @return string $message Final message
     */
    /* private function getMessage($modelUser, $origMessage)
     {
         $find = $this->getFindKeywords();
         $salutation = User::MALE == $modelUser->gender ? Yii::t('messages', 'Mr') : Yii::t('messages', 'Mrs');

         $replacement = array(
             $modelUser->firstName,
             $modelUser->lastName,
             is_null($modelUser->mobile) ? '' : $modelUser->mobile,
             date('Y/m/d'),
             $salutation
         );

         $message = preg_replace($find, $replacement, $origMessage);
         return $message;
     }*/

    /**
     * Format find keywords to replace with dynminc attributes
     * @return Mixed array $find Find keywords array
     */
    /*private function getFindKeywords()
    {
        $find = array(
            '"{' . MessageTemplate::FIRST_NAME . '}"',
            '"{' . MessageTemplate::LAST_NAME . '}"',
            '"{' . MessageTemplate::PHONE_NUMBER . '}"',
            '"{' . MessageTemplate::CURRENT_DATE . '}"',
            '"{' . MessageTemplate::SALUTATION . '}"'
        );

        return $find;
    }*/

    /*
     * Keep track of event reminders send
     * @param $reminderId, $eventId, $modelUserId, $emailTransactionId, $emailStatus integer
     */

    /**
     * @param $reminderId
     * @param $eventId
     * @param $modelUserId
     * @param $emailTransactionId
     * @param $emailStatus
     * @param $action
     * @throws \Exception
     */
    private function addEventReminderTrack($reminderId, $eventId, $modelUserId, $emailTransactionId, $emailStatus, $action)
    {
        $eventReminderTrack = new EventReminderTracker();
        $eventReminderTrack->eventReminderId = $reminderId;
        $eventReminderTrack->eventId = $eventId;
        $eventReminderTrack->userId = $modelUserId;
        $eventReminderTrack->emailTransactionId = $emailTransactionId;
        $eventReminderTrack->emailStatus = $emailStatus;
        $eventReminderTrack->emailType = $action;
        $eventReminderTrack->createAt = User::convertSystemTime();

        $eventReminderTrack->save();
    }

}
