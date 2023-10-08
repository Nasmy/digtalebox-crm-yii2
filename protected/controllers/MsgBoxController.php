<?php

namespace app\controllers;

use app\components\ToolKit;
use app\components\WebUser;
use app\models\Activity;
use app\models\Configuration;
use app\models\Event;
use app\models\SearchCriteria;
use app\models\User;
use borales\extensions\phoneInput\PhoneInputBehavior;
use Yii;
use app\models\MsgBox;
use app\models\MsgBoxSearch;
use yii\base\ErrorException;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\widgets\ActiveForm;
use app\controllers\WebUserController;

/**
 * MsgBoxController implements the CRUD actions for MsgBox model.
 */
class MsgBoxController extends WebUserController
{
    /**
     * {@inheritdoc}
     */

    public function behaviors()
    {
        return [
            [
                'class' => PhoneInputBehavior::className(),
                'countryCodeAttribute' => 'countryCode',
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],

                ],
            ],
        ];
    }

    public $layout = 'column1';

    private $config = array();


    /**
     * Show users inbox
     */
    public function actionInbox()
    {
        $model = new MsgBox();
        $searchModel = new MsgBoxSearch();
        $dataProviderInbox = $searchModel->searchInbox(Yii::$app->request->queryParams);
        $model->loadDefaultValues();  // clear any default values
        if (isset($_GET['MsgBox']))
            $model->attributes = $_GET['MsgBox'];

        $model->receiverUserId = Yii::$app->user->id;
        $model->folder = MsgBox::FOLDER_INBOX;
        Yii::$app->toolKit->addActivity(Yii::$app->user->id, Activity::ACT_VIEW_INBOX_MSG, Yii::$app->session->get('teamId'));

        return $this->render('inbox', array(
            'model' => $model,
            'dataProvider' => $dataProviderInbox,
            'attributeLabels' => $model->attributeLabels()
        ));
    }

    /**
     * Displays a single MsgBox model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionViewInboxMsg($id)
    {
        $thisMsgModel = MsgBox::findOne(['id' => $id]);
        $userModel = User::findOne(['id' => $thisMsgModel->senderUserId]);
        $refMsgModel = MsgBox::findOne($thisMsgModel->refMsgId);

        if (null == $refMsgModel || '' == $refMsgModel->message) {
            $message = $thisMsgModel->message;
        } else {
            $message = $refMsgModel->message;
        }

        $thisMsgModel->status = MsgBox::MSG_STATUS_RED;

        Yii::$app->toolKit->addActivity(Yii::$app->user->id, Activity::ACT_VIEW_INBOX_MSG, Yii::$app->session->get('teamId'));

        try {
            $thisMsgModel->save(false);
        } catch (Exception $e) {

        }

        return $this->render('viewInbox', array(
            'model' => $this->findModel($id),
            'thisMsgModel' => $thisMsgModel,
            'userModel' => $userModel,
            'message' => $message
        ));


    }

    /**
     * Creates a new MsgBox model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCompose($message = null, $eventId = null, $isEvent = false)
    {
        try {

            $model = new MsgBox();
            $SearchCriteria = new SearchCriteria();
            $model->scenario = 'compose';
            $userlist = array();

            //  $this->performAjaxValidation($model);
            if (isset($_POST['MsgBox'])) {

                $model->attributes = $_POST['MsgBox'];
                $model->userlist = $_POST['MsgBox']['userlist'];
                $model->message = $_POST['MsgBox']['message'];
                $model->subject = $_POST['MsgBox']['subject'];
                $model->criteriaId = $_POST['MsgBox']['criteriaId'];
                $model->senderUserId = Yii::$app->user->id;
                $model->receiverUserId = 0;
                $model->refMsgId = 0;
                $model->dateTime = User::convertSystemTime();
                $model->status = MsgBox::MSG_STATUS_NA;
                $model->folder = MsgBox::FOLDER_SENT;
                $model->folder = MsgBox::FOLDER_SENT;
                if ($model->validate()) {
                    try {
                        if ($model->save()) {
                            Yii::$app->session->setFlash('success', Yii::t('messages', 'Your message added to message queue and will be sent shortly.'));
                            Yii::$app->appLog->writeLog("Message sent.Data:" . json_encode($model->attributes));

                            Yii::$app->toolKit->addActivity(Yii::$app->user->id, Activity::ACT_SENT_NEW_MSG, Yii::$app->session->get('teamId'));

                            $domain = Yii::$app->toolKit->domain;
                            if (Yii::$app->toolKit->osType == ToolKit::OS_WINDOWS) {
                                $cmd = "php " . Yii::$app->params['consolePath'] . "console.php Message {$domain} {$model->id} {$isEvent} {$eventId}";
                                pclose(popen("start /B " . $cmd, "r")); // For windows
                            } else {
                                $cmd = "php " . Yii::$app->params['consolePath'] . "console.php Message {$domain} {$model->id} {$isEvent} {$eventId} > /dev/null";
                                exec($cmd, $output, $status); // For Linux
                            }

                            Yii::$app->appLog->writeLog("Command:{$cmd}");

                            return $this->redirect(['msg-box/inbox']);
                        } else {

                            Yii::$app->session->setFlash('error', Yii::t('messages', 'Message sent failed'));
                            Yii::$app->appLog->writeLog("Message sent failed.Data:" . json_encode($model->attributes));
                        }
                    } catch (Exception $e) {
                        Yii::$app->session->setFlash('error', Yii::t('messages', 'Message sent failed'));
                        Yii::$app->appLog->writeLog("Message sent failed.Error:" . json_encode($e->getMessage()));
                    }

                } else {
                    Yii::$app->appLog->writeLog("Message send failed.Validation errors:" . json_encode($model->errors));
                }

                $userIdList = explode(',', $model->userlist);

                if (!empty($model->userlist)) {
                    foreach ($userIdList as $id) {
                        $userModel = User::findOne(['id' => $id]);
                        $userlist[] = array('id' => (int)$id, 'name' => "{$userModel->getName()} - {$userModel->email}");
                    }
                }

            } else {
                $model->message = $message;
            }

            $criteriaOptions = $SearchCriteria->getSavedSearchOptions(Yii::$app->user->id, SearchCriteria::ADVANCED);

            return $this->render('compose', array(
                'model' => $model,
                'criteriaOptions' => $criteriaOptions,
                'userlist' => json_encode($userlist),
            ));

        } catch (ErrorException $e) {
            echo 'Caught an Error: ', $e->getMessage(), "\n";
            Yii::$app->appLog->writeLog("Exception : " . $e->getLine());
        }
    }

    /**
     * Ajax request to retrieve names
     * @return json encoded response of matching user list
     */
    public function actionGetNames($q)
    {

        $query = new Query();
        if (Yii::$app->user->checkAccess(WebUser::SUPPORTER_ROLE_NAME) && !Yii::$app->session->get('is_super_admin')) {
            $query->select('u.*')
                ->from('User u')
                ->join('INNER JOIN', 'TeamMember TM', 'u.id = TM.memberUserId')
                ->filterWhere(['like', 'u.firstName', $q])
                ->andFilterWhere(['like', 'TM.teamId', Yii::$app->session->get('teamId')])
                ->orFilterWhere(['like', 'u.email', $q])
                ->andFilterWhere(['!=', 'u.email', ''])
                ->andFilterWhere(['like', 'u.isUnsubEmail', '0']);

        } elseif (Yii::$app->user->checkAccess(WebUser::TEAM_LEAD_ROLE_NAME) && !Yii::$app->session->get('is_super_admin')) {
            $arrTeamIds = Team::getTeamIdsByTeamLeaderId(Yii::$app->user->id);
            $query->select('u.*')
                ->from('User u')
                ->join('LEFT JOIN', 'TeamMember TM', 'u.id = TM.memberUserId')
                ->filterWhere(['like', 'u.firstName', $q])
                ->andFilterWhere(['like', 'TM.teamIds', implode(',', $arrTeamIds)])
                ->orFilterWhere(['like', 'u.email', $q])
                ->andFilterWhere(['!=', 'u.email', ''])
                ->andFilterWhere(['like', 'u.isUnsubEmail', '0']);

        } else {
            // Any other user, superadmin, client, client admin or user created system user
            $query->select('u.*')
                ->from('User u')
                ->where(['like', 'u.firstName', $q])
                ->orWhere(['like', 'u.email', $q])
                ->andWhere(['!=', 'u.email', '']);
        }

        $models = $query->all();

        $users = array();

        foreach ($models as $model) {
            $users[] = array(
                'id' => $model['id'],
                'name' => "{$model['firstName']} - {$model['email']}",
            );
        }

        return Json::encode($users);
    }

    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     * @return string
     * @throws HttpException
     */
    public function actionViewSentItems($id)
    {
        return $this->render('viewSentItems', array(
            'model' => $this->loadModel($id),
        ));
    }

    /**
     * Show message sending progress
     * @param integer $id Message id
     * @return string
     * @throws HttpException
     */
    public function actionProgress($id)
    {
        $this->layout = 'dialog';
        $model = $this->loadModel($id);

        $msg = '';
        $progress = 0;

        if (0 == $model->deliveredCount) {
            $msg = Yii::t('messages', 'No messages sent. Selected recipients may not have email addresses');
        } else {
            $progress = ceil(($model->deliveredCount / $model->totalRecipient) * 100);
        }

        return $this->render('progress', array(
            'progress' => $progress,
            'msg' => $msg,
        ));
    }

    /**
     * Manages all models.
     */
    public function actionSentItems()
    {
        $model = new MsgBox();
        $model->scenario = 'search';
        $searchModel = new MsgBoxSearch();
        $dataProvider = $searchModel->searchSent(Yii::$app->request->queryParams);

        $model->loadDefaultValues();  // clear any default values
        if (isset($_GET['MsgBox']))
            $model->attributes = $_GET['MsgBox'];

        $model->senderUserId = Yii::$app->user->id;
        $model->folder = MsgBox::FOLDER_SENT;

        Yii::$app->toolKit->addActivity(Yii::$app->user->id, Activity::ACT_VIEW_SENT_MSG, Yii::$app->session->get('teamId'));

        return $this->render('sentItems', array(
            'model' => $model,
            'dataProvider' => $dataProvider,
            'attributeLabels' => $model->attributeLabels()
        ));
    }

    /**
     * Updates an existing MsgBox model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be deleted
     * @throws HttpException
     */
    public function actionDeleteSentMsg($id)
    {
        $model = $this->loadModel($id);
        $model->status = MsgBox::MSG_STATUS_DELETED;
        try {
            if ($model->save()) {
                Yii::$app->toolKit->setAjaxFlash('success', Yii::t('messages', 'Message deleted.'));
                Yii::$app->toolKit->addActivity(Yii::$app->user->id, Activity::ACT_DEL_SENT_MSG, Yii::$app->session->get('teamId'));
                Yii::error("Message flagged as deleted.Id:{$id}");
            } else {
                Yii::$app->toolKit->setAjaxFlash('error', Yii::t('messages', 'Message delete failed.'));
                Yii::error("Message could not be flagged as deleted.Id:{$id}");
            }
        } catch (Exception $e) {
            Yii::$app->toolKit->setAjaxFlash('error', Yii::t('messages', 'Message delete failed.'));
            Yii::error("Message could not be flagged as deleted.Id:{$id},Errors:{$e->getMessage()}");
        }
    }

    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be deleted
     * @throws HttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDeleteInboxMsg($id)
    {
        $model = $this->loadModel($id);

        if ($model->delete()) {
            Yii::$app->toolKit->setAjaxFlash('success', Yii::t('messages', 'Message deleted.'));
            Yii::error("Message deleted.Id:{$id}");
            Yii::$app->toolKit->addActivity(Yii::$app->user->id, Activity::ACT_DEL_RCVD_MSG, Yii::$app->session->get('teamId'));
        } else {
            Yii::$app->toolKit->setAjaxFlash('error', Yii::t('messages', 'Message delete failed.'));
            Yii::error("Message delete failed.Id:{$id}");
        }
    }

    /**
     * Reply for message
     * @param integer $id the ID of the model to be replied
     * @return string|\yii\web\Response
     * @throws HttpException
     * @throws \yii\base\ExitException
     */
    public function actionReply($id)
    {
        $configuration = new Configuration();
        $modelOldMsg = MsgBox::findOne(['id' => $id]);
        $modelNewMsg = new MsgBox();
        $modelNewMsg->scenarios('compose');

        $modelSentMsg = new MsgBox();
        $modelSentMsg->scenarios('compose');

        $this->performAjaxValidation($modelNewMsg);
        if (isset($_POST['MsgBox'])) {

            // Adding to sent items
            $modelSentMsg->subject = $_POST['MsgBox']['subject'];
            $modelSentMsg->message = $_POST['MsgBox']['message'];
            $modelSentMsg->senderUserId = Yii::$app->user->id;
            $modelSentMsg->receiverUserId = 0;
            $modelSentMsg->refMsgId = 0;
            $modelSentMsg->dateTime = User::convertSystemTime();
            $modelSentMsg->status = MsgBox::MSG_STATUS_NA;
            $modelSentMsg->folder = MsgBox::FOLDER_SENT;
            $modelSentMsg->userlist = $modelOldMsg->senderUserId;
            $modelSentMsg->criteriaId = 0;
            $modelSentMsg->totalRecipient = 1;

            if ($modelSentMsg->save(false)) {
                Yii::error("Message added to sent items");
            } else {
                Yii::error("Message add failed to sent items");
            }

            // Adding to inbox of the reciever
            $modelNewMsg->subject = $_POST['MsgBox']['subject'];
            $modelNewMsg->message = $_POST['MsgBox']['message'];
            $modelNewMsg->senderUserId = Yii::$app->user->id;
            $modelNewMsg->receiverUserId = $modelOldMsg->senderUserId;
            $modelNewMsg->refMsgId = 0;
            $modelNewMsg->dateTime = User::convertSystemTime();
            $modelNewMsg->status = MsgBox::MSG_STATUS_NEW;
            $modelNewMsg->folder = MsgBox::FOLDER_INBOX;
            $modelNewMsg->userlist = '';
            $modelNewMsg->criteriaId = 0;

            Yii::$app->toolKit->addActivity(Yii::$app->user->id, Activity::ACT_REPLY_MSG, Yii::$app->session->get('teamId'));

            if ($modelNewMsg->save(false)) {
                Yii::error("Message added to inbox");
                Yii::$app->session->get('success', Yii::t('messages', 'Message sent'));
                $config = $configuration->getConfigurations();

                $receiverUserModel = User::findOne(['id' => $modelOldMsg->senderUserId]);
                $messageUrl = str_replace('{domain}', Yii::$app->toolKit->domain, Yii::$app->params['absUrl']);
                $messageLink = "<a href='{$messageUrl}' >" . Yii::t('messages', 'here') . "</a>";
                $loginMsg = Yii::t('messages', 'Click {here} to access your DigitaleBox account and reply for message.',['here' => $messageLink]);
                $message = "{$modelNewMsg->message} <br /> {$loginMsg}";

                $emailSendStatus = Yii::$app->toolKit->sendEmail(
                    array($receiverUserModel->email), 'DigitaleBox', $message, null, null, $config['FROM_NAME_NOTIFICATION'], $config['FROM_EMAIL_NOTIFICATION']
                );

                if ($emailSendStatus) {
                    $modelSentMsg->deliveredCount = 1;
                    if ($modelSentMsg->save(false)) {
                        Yii::error("Delivered count updated");
                    } else {
                        Yii::error("Delivered count update failed");
                    }
                    Yii::error("Notification email sent. Email:{$receiverUserModel->email}");
                }

                return $this->redirect(['msg-box/inbox']);
            } else {
                Yii::$app->session->get('success', Yii::t('messages', 'Message sent failed'));
                Yii::error("Message add failed to inbox");
            }
        }

        $modelNewMsg->subject = Yii::t('messages', 'Re:') . $modelOldMsg->subject;

        $message = $modelOldMsg->message;
        $date = $modelOldMsg->dateTime;
        $subject = $modelOldMsg->subject;

        if ('' == $message) {
            $modelRefMessage = $this->loadModel($modelOldMsg->refMsgId);
            $message = $modelRefMessage->message;
            $date = $modelRefMessage->dateTime;
            $subject = $modelRefMessage->subject;
        }

        $modelNewMsg->message = "<br />---------------------------------------------<br />";
        $modelNewMsg->message .= Yii::t('messages', 'Date & Time:') . $date . "<br />";
        $modelNewMsg->message .= Yii::t('messages', 'Subject:') . $subject . "<br />";
        $modelNewMsg->message .= "<br />{$message}";

        return $this->render('reply', array(
            'model' => $modelNewMsg,
        ));
    }


    /**
     * Performs the AJAX validation.
     * @param CModel the model to be validated
     * @throws \yii\base\ExitException
     */
    protected function performAjaxValidation($model)
    {
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'msg-queue-form') {
            echo ActiveForm::validate($model);
            Yii::$app->end();
        }
    }

    public function actionComposeEvent($message = null, $eventId = null, $isEvent = false)
    {
        Yii::$app->appLog->writeLog("Compose Event" . json_encode($_POST));
        $model = new MsgBox();
        $model->scenario = 'sendEvent';
        $userlist = array();
        $res = '';

        if (isset($_POST['MsgBox'])) {
            Yii::$app->appLog->writeLog("Compose Event 2");
            $model->attributes = $_POST['MsgBox'];
            $model->subject = $_POST['MsgBox']['subject'];
            $model->senderUserId = Yii::$app->user->id;
            $model->receiverUserId = 0;
            $model->refMsgId = 0;
            $model->dateTime = User::convertSystemTime();
            $model->status = MsgBox::MSG_STATUS_NA;
            $model->folder = MsgBox::FOLDER_SENT;

            if ($model->validate() && isset($_POST['MsgBox']['fromEmail']) && $_POST['MsgBox']['fromEmail'] != "") {
                $model->userlist = $_POST['MsgBox']['userlist'];
                $model->criteriaId = ($_POST['MsgBox']['criteriaId'] == null) ? 0 : $_POST['MsgBox']['criteriaId'];
                $fromEmail = Configuration::getEmailById($_POST['MsgBox']['fromEmail']);
                $fromName = Configuration::getEmailNameById($_POST['MsgBox']['fromEmail']);
                $fromName =str_replace(' ','-',$fromName);
                $fromName = addslashes($fromName);  // remove special characters from this string
                $action = 'x';
                try {
                    if ($model->save()) {
                        $status = 0;
                        $output = [];
                        $res = Yii::$app->toolKit->setAjaxFlash('success', Yii::t('messages', 'Your message added to message queue and will be sent shortly.'));
                        Yii::$app->appLog->writeLog("Message sent.Data:" . json_encode($model->attributes));
                        Yii::$app->toolKit->addActivity(Yii::$app->user->id, Activity::ACT_SENT_NEW_MSG, Yii::$app->session->get('teamId'));
                        $language = Yii::$app->language;
                        $domain = Yii::$app->toolKit->domain;
                        if (Yii::$app->toolKit->osType == ToolKit::OS_WINDOWS) {
                            $cmd = "php " . Yii::$app->params['consolePath'] . "console.php send-event {$domain} {$model->id} {$isEvent} {$eventId} {$action} {$fromEmail} {$fromName} {$language}";
                            pclose(popen("start /B " . $cmd, "r")); // For windows
                            Yii::$app->appLog->writeLog("Command Type Windows");
                        } else {
                            $cmd = "php " . Yii::$app->params['consolePath'] . "console.php send-event {$domain} {$model->id} {$isEvent} {$eventId} {$action} {$fromEmail} {$fromName} {$language}> /dev/null 2>&1 & echo $!";
                            exec($cmd, $output, $status); // For Linux
                            Yii::$app->appLog->writeLog("Command Type Linux");

                        }
                        Yii::$app->appLog->writeLog("Command:{$cmd}");

                    } else {
                        $res = Yii::$app->toolKit->setAjaxFlash('error', Yii::t('messages', 'Message sent failed'));
                        Yii::$app->appLog->writeLog("Message sent failed.Data:" . json_encode($model->attributes));
                    }
                } catch (Exception $e) {
                    $res = Yii::$app->toolKit->setAjaxFlash('error', Yii::t('messages', 'Message sent failed'));
                    Yii::$app->appLog->writeLog("Message sent failed.Error:" . json_encode($e->getMessage()));
                }
            } else {
                $res = Yii::$app->toolKit->setAjaxFlash('error', Yii::t('messages', 'Message sent failed, Please Fill the required fields'));
                Yii::$app->appLog->writeLog("Message send failed.Validation errors:" . json_encode($model->errors));
            }

            $userIdList = explode(',', $model->userlist);
            if (!empty($model->userlist)) {
                foreach ($userIdList as $id) {
                    $userModel = User::findOne($id);
                    $userlist[] = array('id' => (int)$id, 'name' => "{$userModel->getName()} - {$userModel->email}");
                }
            }
        } else {
            $model->message = $message;
        }
        echo $res;
    }

    /**
     * Compose new email message.
     * Event invitations also send via this action.
     * @param string $message Intial message that should show on compose page
     * @param integer $eventId Id of the event
     * @param boolean $isEvent Whether this action called by event section
     */
    public function actionComposeTestEvent()
    {
        Yii::$app->appLog->writeLog("Compose Test Event" . json_encode($_POST));
        if (isset($_POST['MsgBox'])) {
            try {
                $postdata = $_POST['MsgBox'];
                $subject = $postdata['subject'];
                $email = $postdata['email'];
                $eventId = $postdata['id'];
                $fromEmail = Configuration::getEmailById($_POST['MsgBox']['fromEmail']);
                $fromName = Configuration::getEmailNameById($_POST['MsgBox']['fromEmail']);
                $fromName = addslashes($fromName);  // remove special characters from this string
                if (isset($postdata['subject']) && $postdata['subject'] != "" && isset($postdata['fromEmail']) && $postdata['fromEmail'] != "") {
                    if (isset($postdata['email']) && $postdata['email'] != "") {
                        $res = Yii::$app->toolKit->setAjaxFlash('success', Yii::t('messages', 'Your message added to message queue and will be sent shortly.'));
                        Yii::$app->appLog->writeLog("Message Start sent.Data:" . json_encode($postdata));
                        Yii::$app->toolKit->addActivity(Yii::$app->user->id, Activity::ACT_SENT_NEW_MSG, Yii::$app->session->get('teamId'));
                        $domain = Yii::$app->toolKit->domain;
                        $userId = Yii::$app->user->id;
                        $this->config = Configuration::getConfigurations();
                        Yii::$app->language = $this->config['LANGUAGE'];
                        $senderModel = User::findOne(Yii::$app->user->id);
                        $modelEvent = Event::findOne($eventId);
                        $startDateTime = User::convertDBTime($modelEvent->startDate . " " . $modelEvent->startTime);
                        $endDateTime = User::convertDBTime($modelEvent->startDate . " " . $modelEvent->endTime);
                        $modelEvent->startTime = date('H:i:s', strtotime($startDateTime));
                        $modelEvent->endTime = date('H:i:s', strtotime($endDateTime));
                        $isExpired = false;
                        $shareUri = '';
                        $acceptUri = '';
                        $rejectUri = '';
                        $maybeUri = '';
                        $shareByEmailDetails = array('subject' => Yii::t('messages', 'DigitaleBox Event'), 'body' => Yii::t('messages', 'Event: ') . $shareUri);
                        Yii::$app->toolKit->setResourceInfo();
                        $imgUrl = Yii::$app->toolKit->getWebRootUrl() . Yii::$app->toolKit->resourcePathRelative . $modelEvent->imageName;
                        $path = Yii::getAlias('@app') . '/views/emailTemplates/eventInvitationTemplate.php';
                        $message = yii::$app->controller->renderFile($path, array(
                            'imgUrl' => $imgUrl,
                            'model' => $modelEvent,
                            'isExpired' => $isExpired,
                            'domain' => $domain,
                            'shareUri' => $shareUri,
                            'acceptUri' => $acceptUri,
                            'rejectUri' => $rejectUri,
                            'maybeUri' => $maybeUri,
                            'isInvite' => false,
                            'memberId' => $senderModel->id,
                            'confirm' => base64_encode('{"event":' . $modelEvent->id . ', "member":' . $senderModel->id . '}'),
                            'shareByEmailDetails' => $shareByEmailDetails,
                            'alertMsg' => '<strong>' . Yii::t('messages', 'Sorry!') . '</strong> ' . Yii::t('messages', 'This event canceled by the event organiser. Thank you.'),
                        ), true);

                        $emailSendStatus = Yii::$app->toolKit->sendEmail(
                            array(
                                $email
                            ), $subject, $message, null, null, $fromName, $fromEmail
                        );
                        if ($emailSendStatus) {
                            Yii::$app->appLog->writeLog(" $imgUrl");

                            Yii::$app->appLog->writeLog("Email sent. Email:{$email},Email from email:{$fromEmail} from name:{$fromName}");
                        } else {
                            Yii::$app->appLog->writeLog("Email sent failed.Email:{$email}");
                        }
                    } else {
                        $res = Yii::$app->toolKit->setAjaxFlash('error', Yii::t('messages', 'Message sent failed, Please Fill the required fields'));
                        Yii::$app->appLog->writeLog("Message sent failed.Error:");
                    }
                } else {
                    $res = Yii::$app->toolKit->setAjaxFlash('error', Yii::t('messages', 'Message sent failed, Please Fill the required fields'));
                    Yii::$app->appLog->writeLog("Message sent failed.Error: Subject not filled");
                }
            } catch (Exception $e) {
                $res = Yii::$app->toolKit->setAjaxFlash('error', Yii::t('messages', 'Message sent failed'));
                Yii::$app->appLog->writeLog("Message sent failed.Error:" . json_encode($e->getMessage()));
            }
        } else {
            $res = Yii::$app->toolKit->setAjaxFlash('error', Yii::t('messages', 'Message sent failed'));
            Yii::$app->appLog->writeLog("Message sent failed.Error:");
        }
        echo $res;
    }


    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer the ID of the model to be loaded
     * @return MsgBox|null
     * @throws HttpException
     */
    public function loadModel($id)
    {
        $model = MsgBox::findOne(['id' => $id]);
        if ($model === null)
            throw new HttpException(404, 'The requested page does not exist.');
        return $model;
    }

    /**
     * Finds the MsgBox model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return MsgBox the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = MsgBox::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
