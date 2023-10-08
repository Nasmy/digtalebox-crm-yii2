<?php

namespace app\controllers;

use app\components\ToolKit;
use app\components\WebUser;
use app\models\Activity;
use app\models\Configuration;
use app\models\EventReminder;
use app\models\SearchCriteria;
use app\models\User;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use Yii;
use \app\controllers\WebUserController;

class EventReminderController extends WebUserController
{

    public $layout = 'column1';

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
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


    /*
 * Function to send Event Reminders
 * @param $message String, $eventID Integer, $isEvent Boolean
 * @return
 */
    public function actionEventReminder($eventId, $isEvent = false, $action)
    {
        $model = new EventReminder();
        if (EventReminder::REMINDER == $action) {
            $model->scenario = 'sendReminder';
        } else {
            $model->scenario = 'sendEvent';
        }

        $res = '';
        if (isset($_POST['EventReminder'])) {
            $model->attributes = $_POST['EventReminder'];
            $mtId = $_POST['EventReminder']['messageTemplateId']; //  Message Template Id
            $model->messageTemplateId = ($_POST['EventReminder']['messageTemplateId']) == null ? 0 : $_POST['EventReminder']['messageTemplateId'];
            $model->eventId = $eventId;
            $model->createdAt = User::convertSystemTime();
            $fromEmail = Configuration::getEmailById($_POST['EventReminder']['fromEmail']);
            $fromName = Configuration::getEmailNameById($_POST['EventReminder']['fromEmail']);
            $fromName =str_replace(' ','-',$fromName);
            $fromName = addslashes($fromName);  // remove special characters from this string
            if ($model->validate() && isset($_POST['EventReminder']['fromEmail']) && $_POST['EventReminder']['fromEmail'] != "") {
                try {
                    if ($model->save()) {
                        $res = Yii::$app->toolKit->setAjaxFlash('success', Yii::t('messages', 'Your message added to message queue and will be sent shortly.'));
                        Yii::$app->appLog->writeLog("Message sent.Data:" . json_encode($model->attributes));
                        Yii::$app->toolKit->addActivity(Yii::$app->user->id, Activity::ACT_SENT_NEW_MSG, Yii::$app->session->get('teamId'));
                        $language = Yii::$app->language;
                        $domain = Yii::$app->toolKit->domain;
                        if (Yii::$app->toolKit->osType == ToolKit::OS_WINDOWS) {
                            $cmd = "php " . Yii::$app->params['consolePath'] . "console.php send-event {$domain} {$model->id} {$isEvent} {$eventId} {$action} {$fromEmail} {$fromName} {$language} ";
                            pclose(popen("start /B " . $cmd, "r")); // For windows
                        } else {
                            $cmd = "php " . Yii::$app->params['consolePath'] . "console.php send-event {$domain} {$model->id} {$isEvent} {$eventId} {$action} {$fromEmail} {$fromName} {$language}> /dev/null &";
                            exec($cmd, $output, $status); // For Linux
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
            } else if ($_POST['EventReminder']['fromEmail'] == "") {
                $res = Yii::$app->toolKit->setAjaxFlash('error', Yii::t('messages', 'Message sent failed, Please select the email sender'));
                Yii::$app->appLog->writeLog("Message send failed.Validation errors:" . json_encode($model->errors));
            } else {
                $res = Yii::$app->toolKit->setAjaxFlash('error', Yii::t('messages', 'Message sent failed, Please Fill the required fields'));
                Yii::$app->appLog->writeLog("Message send failed.Validation errors:" . json_encode($model->errors));
            }
        } else {
            echo 'Not a post Method';
        }
        echo $res;
    }

    /*
     * function to get send reminders emails to grid per user
     * @param $eventId, $rsvpStatus, $userId - integer
     */
    public function actionViewEventReminder($eventId, $rsvpStatus, $userId)
    {
        $emailSend = EventReminder::getEmailSend($eventId, $rsvpStatus, $userId);

        $this->layout = 'dialog';
        return $this->render('view', array(
            'model' => $emailSend,
        ));
    }
}
