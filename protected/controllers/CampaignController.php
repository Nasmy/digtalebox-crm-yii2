<?php
/**
 * Campaign Controller.
 *
 * This class illustrate the campaign actions
 *
 * @author Nasmy Ahamed
 * Date: 7/22/2019
 * @copyright Copyright &copy; Keeneye solutions (PVT) LTD
 */

namespace app\controllers;

use app\components\ThresholdChecker;
use app\models\Activity;
use app\models\CampaignUsers;
use app\models\Configuration;
use app\models\KeywordUrl;
use app\models\MessageTemplate;
use app\models\SearchCriteria;
use app\models\User;
use Yii;
use app\models\Campaign;
use app\models\CampaignSearch;
use yii\console\Exception;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use \app\controllers\WebUserController;


class CampaignController extends WebUserController
{

    public $layout = 'column1';

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => Yii::$app->user->checkAccess("SendBulkMessages"),
                        'roles' => ['@'],
                        'actions' => ['create-camp', 'admin','stop-campaign','resume-campaign', 'add-campaign', 'send-test-message', 'view-campaign-stats', 'delete']
                    ],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'Test' : null,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    /**
     * @return string
     */
    public function allowed()
    {
        $allowed = array();
        if (Yii::$app->user->checkAccess("SendBulkMessages")) {
            $allowed = array_merge($allowed, array('Campaign.AddCampaign', 'Campaign.CreateCamp', 'Campaign.SendTestMessage'));
        }

        return implode(',', $allowed);
    }


    /**
     * Lists all Campaign models.
     * @return mixed
     */
    public function actionAdmin()
    {
        $searchModel = new CampaignSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

       return $this->render('admin', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'savedSearches' => SearchCriteria::getSavedSearchOptions(),
            'templates' => MessageTemplate::getTemplateOptions(),
        ]);
    }


    /**
     * Displays a single Campaign model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Campaign model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Campaign();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Campaign Create Wizard
     */

    public function actionCreateCamp()
    {
        $model = new Campaign();
        $modelConfig = Configuration::findOne(Configuration::FROM_EMAIL);
        $tc = new ThresholdChecker(Yii::$app->session->get('packageType'), Yii::$app->session->get('smsPackageType'));
        $isDefEmailChanged = true; // check the default email is changed. Default email is configured in params.
        if ('' == $modelConfig->value || Yii::$app->params['smtp']['defaultClientEmail'] == $modelConfig->value) {
            $isDefEmailChanged = false;
        }

        $isLimitedCampaign = false;
        $isLimitedSms = false;
        $isSmsConfiguration = false;

        if (!Configuration::isClientSmtpSet()) {
            $isLimitedCampaign = true;
        }

        if(!Configuration::isClientSmsSet()) {
            $isSmsConfiguration = true;
        }

        if($tc->isThresholdExceeded(ThresholdChecker::MONTH_SMS_LIMIT)) {
            $isLimitedSms = true;
        }

        return $this->render('campWizard', array(
            'model' => $model,
            'isDefEmailChanged' => $isDefEmailChanged,
            'isLimitedCampaign' => $isLimitedCampaign,
            'isLimitedSms' => $isLimitedSms,
            'isSmsConfiguration' => $isSmsConfiguration
        ));
    }

    /**
     * Updates an existing Campaign model.
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
     * Use to send test email campaigns
     * @throws \yii\db\Exception
     */
    public function actionSendTestMessage()
    {

        $modelCriteria = SearchCriteria::findOne($_POST['criteriaId']);
        $templateId = Campaign::getTemplateIdByPost($_POST);
        $templateModel = MessageTemplate::findOne($templateId);

        $clientProfiles = User::getClientProfile();
        $criteria = SearchCriteria::getCriteria($modelCriteria);
        $criteria->andWhere('email != ""');
        $criteria->andWhere('isUnsubEmail = 0');
        $criteria->andWhere('(emailStatus IS NULL OR emailStatus = 0)'); // Ignore bounced/blocked emails
        $modelUsers = $criteria->from('User t')->one();


        $email = $_POST['email'];
        $phoneNumber = $_POST['phoneNumber'];
        $type = $_POST['type'];
        $fromEmail = Configuration::getEmailById($_POST['fromEmail']);
        $fromName = Configuration::getEmailNameById($_POST['fromEmail']);
        Yii::$app->appLog->writeLog("Test campaign. Attributes:" . json_encode($_POST));
        $msg = Yii::t('messages', 'Message sending status.');
        if (!Configuration::isClientSmtpSet()) {
            $fromEmail = Yii::$app->params['campaign']['campaignEmail'];
            $fromName = Yii::$app->params['smtp']['senderLabel'];
        }
        switch ($type) {
            case Campaign::CAMP_TYPE_EMAIL:
                Yii::$app->toolKit->setResourceInfo();
                $templateContent = file_get_contents(Yii::$app->toolKit->resourcePathAbsolute . $templateModel->id . '.html');
                Yii::$app->appLog->writeLog("TemplateId: $templateModel->id");
                $campaignModel = new Campaign();
                $templateContent = $campaignModel->getMessage($modelUsers, $templateContent, $clientProfiles['modelUser']);

                if (Yii::$app->toolKit->sendEmail(array($email), $templateModel->subject, $templateContent, null,
                    null, $fromName, $fromEmail)
                ) {
                    $msg .= Yii::t('messages', 'Email:Success') . ',';
                    Yii::$app->appLog->writeLog("Test email sent.");
                } else {
                    $msg .= Yii::t('messages', 'Email:Failed') . ',';
                    Yii::$app->appLog->writeLog("Test email sending failed.");
                }

                break;

            case Campaign::CAMP_TYPE_SMS:
                $messageList = Campaign::getSmsMessageList($templateModel);
                $stopSms = Configuration::getSmsOpt();
                $countSms = count($messageList);
                $i = 0;
                foreach ($messageList as $message) {
                    $i++;
                    if ($i == $countSms) {
                        $message = $message . "\n" . $stopSms;
                    }
                    $smsResponse = Yii::$app->toolKit->sendSms($phoneNumber, $message);
                    if($smsResponse == false) {
                        break;
                    }
                }

                if ($smsResponse != false) {
                    $msg .= Yii::t('messages', 'SMS:Success') . ',';
                    Yii::$app->appLog->writeLog("Test SMS sent.");
                } else {
                    $msg .= Yii::t('messages', 'SMS:Failed') . ',';
                    Yii::$app->appLog->writeLog("Test SMS sending failed.");
                }
        }
        $msg = rtrim($msg, ',');

        $res = array('message' => Yii::$app->toolKit->setAjaxFlash('info', $msg, true));
        echo json_encode($res);
    }

    /**
     * Deletes an existing Campaign model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     * @throws \Throwable
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        try {
            $model->delete();
            CampaignUsers::deleteAll(['campaignId' => $id]);
            Yii::$app->appLog->writeLog("Campaign deleted.Id:{$id}");
            Yii::$app->session->setFlash('success', Yii::t('messages', 'Campaign deleted'));
        } catch (\Exception $e) {
            Yii::$app->appLog->writeLog("Campaign delete failed.Id:{$id},error:{$e->getMessage()}");
            Yii::$app->session->setFlash('success', Yii::t('messages', 'Campaign delete failed'));
        }
    }

    /**
     * Stop campaign
     * @param integer $id Campaign id.
     * @throws NotFoundHttpException
     */
    public function actionStopCampaign($id)
    {
        $model = $this->findModel($id);

        if ($model->campType == Campaign::CAMP_TYPE_AB_TEST_EMAIL) {
            $model->status = Campaign::CAMP_TEST_STOP;
            if ($model->batchOffsetEmail > 0) {
                $model->status = Campaign::CAMP_STOP;
            }
        } else {
            $model->status = Campaign::CAMP_STOP;
        }

        if ($model->status != Campaign::CAMP_FINISH) {
            try {
                if ($model->save(false)) {
                    Yii::$app->appLog->writeLog("Campaign stopped.Id:{$id}");
                    return Yii::$app->toolKit->setAjaxFlash('success', Yii::t('messages', 'Campaign stopped'));
                } else {
                    Yii::$app->appLog->writeLog("Campaign stop failed.Id:{$id}");
                    return Yii::$app->toolKit->setAjaxFlash('error', Yii::t('messages', 'Campaign stop failed'));
                }
            } catch (Exception $e) {
                Yii::$app->appLog->writeLog("Campaign stop failed.Id:{$id},Error:{$e->getMessage()}");
                return Yii::$app->toolKit->setAjaxFlash('error', Yii::t('messages', 'Campaign stop failed'));
            }
        }
    }

    /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionViewCampaignStats($id)
    {
        $this->layout = 'dialog';
        $model = $this->findModel($id);
        $totalUsers = $model->totalUsers;
        $successCount = CampaignUsers::find()->where("status = :status AND campaignId = :campaignId", array(
            ":status" => CampaignUsers::MESSAGE_SENT,
            ":campaignId" => $id,
        ))->count();

        $failedCount = CampaignUsers::find()->where("status = :status AND campaignId = :campaignId", array(
            ":status" => CampaignUsers::MESSAGE_SENT_FAIL,
            ":campaignId" => $id,
        ))->count();

        if ($totalUsers < ($successCount + $failedCount)) {
            // For long running campaigns when campaign get ended, total count may less than actually processed.
            // This is to avoid it.
            try {
                $totalUsers = $successCount + $failedCount;
                $model->totalUsers = $totalUsers;
                $model->save(false);
            } catch (Exception $e) {
            }
        }

        $pendingCount = $totalUsers - ($successCount + $failedCount);

        if (0 == $totalUsers) {
            $progress = 0;
        } else {
            $progress = ceil((($successCount + $failedCount) / $totalUsers) * 100);
        }

        $isRescheduled = false;

        if ((Campaign::CAMP_TYPE_TWITTER == $model->campType || Campaign::CAMP_TYPE_FACEBOOK == $model->campType || Campaign::CAMP_TYPE_SMS == $model->campType) && Campaign::CAMP_PENDING == $model->status && strtotime($model->startDateTime) > time()) {
            $isRescheduled = true;
        }

        return $this->render('stat', array(
            'model' => $model,
            'totalUsers' => $totalUsers,
            'failedCount' => $failedCount,
            'successCount' => $successCount,
            'pendingCount' => $pendingCount,
            'progress' => $progress,
            'isRescheduled' => $isRescheduled
        ));
    }

    /**
     * Resume campaign
     * @param integer $id Campaign id.
     * @throws NotFoundHttpException
     */
    public function actionResumeCampaign($id)
    {
        if (!Configuration::isClientSmtpSet()) {
            Yii::$app->toolKit->setAjaxFlash('error', Yii::t('messages', 'Please configure your MailJet Account. It 
            has reached the limit of {emails} emails, Click {here} to configure',
                array(
                    '{here}' => CHtml::link('here', array('configuration/update', 'hlt' => base64_encode('mailjetUsername,mailjetPassword')
                    ), array('id' => 'here')),
                    '{emails}' => Yii::$app->params['campaign']['limit']
                )));
        } else {
            $model = $this->findModel($id);
            $model->status = Campaign::CAMP_PENDING;

            try {
                if ($model->save(false)) {
                    Yii::$app->appLog->writeLog("Campaign resumed.Id:{$id}");
                    return Yii::$app->toolKit->setAjaxFlash('success', Yii::t('messages', 'Campaign resumed'));
                } else {
                    Yii::$app->appLog->writeLog("Campaign resume failed.Id:{$id}");
                    Yii::$app->toolKit->setAjaxFlash('error', Yii::t('messages', 'Campaign resume failed'));
                }
            } catch (Exception $e) {
                Yii::$app->appLog->writeLog("Campaign resume failed.Id:{$id},Error:{$e->getMessage()}");
                Yii::$app->toolKit->setFlash('error', Yii::t('messages', 'Campaign resume failed'));
            }
        }
    }

    /**
     * Add Campaign into queue. Cron will process the rest
     * @throws \yii\db\Exception
     */
    public function actionAddCampaign()
    {
        $templateId = Campaign::getTemplateIdByPost($_POST);
        if (!empty($templateId)) {
            $tc = new ThresholdChecker(Yii::$app->session->get('packageType'), Yii::$app->session->get('smsPackageType'));
            $criteriaId = $_POST['criteriaId'];
            $type = $_POST['type'];
            $checkPendingCampaign = Campaign::find()->where(array(
                'messageTemplateId' => $templateId,
                'searchCriteriaId' => $criteriaId,
                'campType' => $type
            ))->andWhere(['!=', 'status', Campaign::CAMP_FINISH])->all();
            if ((null == $checkPendingCampaign)) {
                $model = new Campaign();
                $model->messageTemplateId = $templateId;
                $model->searchCriteriaId = $criteriaId;
                $model->status = 0;
                $model->startDateTime = User::convertSystemTime();
                $model->campType = $type;
                $model->createdBy = Yii::$app->user->id;
                $model->createdAt = User::convertSystemTime();
                $fromEmail = Configuration::getEmailById($_POST['fromEmail']);
                $fromName = Configuration::getEmailNameById($_POST['fromEmail']);
                $fromName = addslashes($fromName);  // remove special characters from this string
                $emailArray = Configuration::getConfigFromEmailOptions();
                try {
                    if ($model->save(false)) {
                        if (count($emailArray) > 2)  // Check is there other from emails options available.
                        {
                            $model->updateFromEmailById($fromName, $fromEmail, $model->id);
                        }
                        Yii::$app->toolKit->addActivity(Yii::$app->user->id, Activity::ACT_CRT_NEW_CAMPAIGN);
                        $msg = Campaign::getCampaignMessage($model->campType, $tc);
                        Yii::$app->appLog->writeLog("Campaign added.Data:" . Json::encode($model->attributes));
                        $res = array('status' => 0, 'message' => Yii::$app->toolKit->setAjaxFlash('success', $msg, true));
                    } else {
                        Yii::$app->appLog->writeLog("Campaign add failed.Data:" . Json::encode($model->attributes));
                        $res = array('status' => 1, 'message' => Yii::$app->toolKit->setAjaxFlash('success', Yii::t('messages', 'Campaign create failed'), true));
                    }
                } catch (\Exception $e) {
                    Yii::$app->appLog->writeLog("Campaign add failed.Error:{$e->getMessage()},Data:" . Json::encode($model->attributes));
                    $res = array('status' => 1, 'message' => Yii::$app->toolKit->setAjaxFlash('success', Yii::t('messages', 'Campaign create failed'), true));
                }
            } else {
                Yii::$app->appLog->writeLog("Campaign add failed.There is a pending campaign ");
                $res = array('status' => 1, 'message' => Yii::$app->toolKit->setAjaxFlash('warning', Yii::t('messages', 'There is already a pending campaign for your selection.'), true));
            }
        } else {
            $res = array('status' => 1, 'message' => Yii::$app->toolKit->setAjaxFlash('error', Yii::t('messages', 'Please select a template'), true));
        }

        echo Json::encode($res);
    }

    /**
     * Finds the Campaign model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Campaign the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Campaign::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
