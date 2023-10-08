<?php

namespace app\controllers;

use app\components\ToolKit;
use app\models\Campaign;
use app\models\CampaignUsers;
use app\models\Configuration;
use app\models\MessageTemplate;
use app\models\SearchCriteria;
use app\models\User;
use Yii;
use app\models\ABTestingCampaign;
use app\models\ABTestingCampaignSearch;
use yii\base\ErrorException;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use \app\controllers\WebUserController;
/**
 * ABTestingController implements the CRUD actions for ABTestingCampaign model.
 */
class ABTestingController extends WebUserController
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
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                 ],
            ],
        ];
    }

    /**
     * Lists all ABTestingCampaign models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ABTestingCampaignSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single ABTestingCampaign model.
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
     * Creates a new ABTestingCampaign model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreateCamp()
    {
        try {
        $model = new ABTestingCampaign();
        $startDate = date('Y-m-d', time());
        if (isset($_POST['ABTestingCampaign'])) {
            // TODO need to implement this functionality
            if (!Configuration::isClientSmtpSet()) {
                Yii::$app->session->setFlash('error', Yii::t('messages', 'Please configure your Mailjet Account first. Click {hear} to configure.', [
                    'hear' => Html::a('here', array('configuration/update', 'hlt' => base64_encode('mailjetUsername,mailjetPassword')), ['id' => 'hear'])
                ]));
            } else {
                $totalTestEmails = 0;
                $model->attributes = $_POST['ABTestingCampaign'];
                $valid = $model->validate();
                $attribute = $model->attributes;
                if ($valid) {
                    $query = new Query();
                    $query->where(['=', 'campType', Campaign::CAMP_FINISH])
                        ->andWhere(['!=', 'status', Campaign::CAMP_TEST_STOP])
                        ->andWhere(['!=', 'status', Campaign::CAMP_TYPE_AB_TEST_EMAIL]);

                    if ((ToolKit::isEmpty($query->from('Campaign')->all()))) {
                        $userCount = $model->userCount;
                        if ($userCount > ABTestingCampaign::MINIMUM) {
                            $startDate = $model->startDate;
                            $endDateTime = $startDate . " 23:59:59";
                            $model->startDate = $startDate . " " . date('H:i:s');
                            $model->createdBy = Yii::$app->user->id;
                            $model->createdAt = User::convertSystemTime();
                            $model->countRemain = 0;

                            $totalTestEmails = (int)$model->countA + (int)$model->countB;
                            $model->countRemain = (int)$userCount - $totalTestEmails;

                            if ($totalTestEmails < $userCount && $totalTestEmails != $userCount) {
                                $condition = true;
                            } else {
                                $condition = false;
                            }
                            if ($condition && $totalTestEmails <= ABTestingCampaign::MAXIMUM && $totalTestEmails >= ABTestingCampaign::MINIMUM) {
                                try {
                                    if ($model->save(false)) {
                                        $campModel = new Campaign();
                                        // messageTemplateId is not stored here , It be from ABTesting Campaign
                                        $campModel->searchCriteriaId = $model->searchCriteriaId;
                                        $campModel->status = 0;
                                        $campModel->aBTestId = $model->id;
                                        $campModel->startDateTime = $model->startDate;
                                        $campModel->endDateTime = $endDateTime;
                                        $campModel->campType = Campaign::CAMP_TYPE_AB_TEST_EMAIL;
                                        $campModel->createdBy = Yii::$app->user->id;
                                        $campModel->createdAt = date('Y-m-d H:i:s');

                                        if ($campModel->save(false)) {
                                            Yii::$app->session->setFlash("success", "Test Campaign Created");
                                            Yii::$app->appLog->writeLog("Test Campaign Created. Campaign data:" . json_encode($attributes));
                                        }

                                    } else {
                                        Yii::$app->session->setFlash("error", "Test Campaign create Error");
                                        Yii::$app->appLog->writeLog("Test Campaign create Error:" . CJSON::encode($model->errors));
                                    }
                                } catch (Exception $e) {
                                    // $success = false;
                                    Yii::$app->session->setFlash("error", "Campaign could not be saved.");
                                }
                            } else {
                                if (!$condition) {
                                    Yii::$app->session->setFlash('error', Yii::t('messages', 'Campaign add failed. Conflict - Number of Test user records ({totalTestEmails}) are greater than the available user records ({userCount})', [
                                        'totalTestEmails' => $totalTestEmails,
                                        'userCount' => $userCount
                                    ]));
                                } else {
                                    Yii::$app->session->setFlash('error', Yii::t('messages', 'Campaign add failed. Test Campaign Allow 10 to 1000 Email contacts only'));
                                }
                            }
                        } else {
                            Yii::$app->session->setFlash('error', Yii::t('messages', 'The search criteria doesn\'t have enough users to do a Test Campaign.'));
                        }
                    } else {
                         Yii::$app->appLog->writeLog("Campaign add failed.There is a pending campaign ");
                        Yii::$app->session->setFlash('error', Yii::t('messages', 'There is already a AB Pending / AB Winner Campaign Stopped.'));
                    }
                } else {
                    Yii::$app->session->setFlash('error', Yii::t('messages', 'Please fill all the fields'));
                }
            }
        } else {
            $model->fromA = Configuration::findOne(Configuration::FROM_NAME)->value;
            $model->startDate = $startDate;
        }
        return $this->render('admin', ['model' => $model]);

        } catch (ErrorException $e) {
            echo 'Caught an Error: ',  $e->getMessage(), "\n";
            Yii::$app->appLog->writeLog("Exception : ".$e->getLine());
        }
    }

    /**
     * Updates an existing ABTestingCampaign model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public
    function actionUpdate($id)
    {
        try {

            $model = $this->findModel($id);

            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }

            return $this->render('update', [
                'model' => $model,
            ]);

        } catch (ErrorException $e) {
            echo 'Caught an Error: ',  $e->getMessage(), "\n";
            Yii::$app->appLog->writeLog("Exception : ".$e->getLine());
        }
    }

    /*
    * Saved search User count
    */
    public function actionGetSaveSearchUserCount($id = null)
    {
        $userCount = 0;
        $modelCriteria = SearchCriteria::findOne($id);
        if (null != $modelCriteria) {
            $criteria = SearchCriteria::getCriteria($modelCriteria);
            $criteria->andWhere(['!=', 'email', '']);
            $criteria->andWhere(['=', 'isUnsubEmail', 0]);
            $criteria->andWhere('(emailStatus IS NULL OR emailStatus = 0)');
            $userCount = $criteria->from('User')->count();
        }
        echo $userCount;

    }

    /*
    *Get message template
    */
    public function actionGetMessageTemplateById($id = null)
    {
        $template = MessageTemplate::find()->where(['=', 'id', $id])->one();
        return Json::encode($template);
    }

    /**
     * Deletes an existing ABTestingCampaign model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    public function actionViewCampaignStats($id)
    {
        $bestTemplate = ABTestingCampaign::TEMPLATE_DEFAULT;
        $reason1 = Yii::t('messages', "Sent, Opened & Clicked Email count is Higher.");
        $reason2 = Yii::t('messages', "You can select either A or B but check for Failed, Bounced & Spam Email Count.");
        $reason3 = Yii::t('messages', "The Number of Emails used in the A/B test is different.");
        $reason = $reason2;
        $this->layout = 'dialog';
        $model = Campaign::find()->where(['id' => $id])->one();
        $aBTestCampaign = ABTestingCampaign::find()->where(['id' => $model['aBTestId']])->one();
        $messageTemplateAModel = MessageTemplate::find()->where(['id' => $aBTestCampaign['messageTemplateIdA']])->one();
        $messageTemplateBModel = MessageTemplate::find()->where(['id' => $aBTestCampaign['messageTemplateIdB']])->one();

        if (isset($_POST['ABTestingCampaign'])) {
            if (isset($_POST['ABTestingCampaign']['aBTestTemplateId'])) {
                $aBTestTemplateId = $_POST['ABTestingCampaign']['aBTestTemplateId'];
                if ('' != $aBTestTemplateId && "0" != $aBTestTemplateId) {
                    $aBTestCampaign->fromRemain = (ABTestingCampaign::TEMPLATE_A == $aBTestTemplateId) ? $aBTestCampaign->fromA : $aBTestCampaign->fromB;
                    $aBTestCampaign->subjectRemain = (ABTestingCampaign::TEMPLATE_A == $aBTestTemplateId) ? $aBTestCampaign->subjectA : $aBTestCampaign->subjectB;

                    $aBTestCampaign->updatedAt = date('Y-m-d H:i:s');
                    $aBTestCampaign->updatedBy = Yii::$app->user->id;
                    $model->updatedAt = User::convertSystemTime();
                    $model->updatedBy = Yii::$app->user->id;

                    $model->endDateTime = $_POST['ABTestingCampaign']['endDateTime'] . " 23:59:59";;
                    $model->status = 0;
                    if ($aBTestCampaign->save(false)) {
                        if ($model->save(false)) {
                            Yii::$app->session->setFlash('success', Yii::t('messages', 'Campaign updated'));
                        }
                    } else {
                        Yii::$app->session->setFlash('error', Yii::t('messages', 'Failed Campaign updated'));
                    }
                }
            }
        }

        $totalUsers = $model->totalUsers;

        $campaignAResults = ABTestingCampaign::getCampaignResults($id, $aBTestCampaign['countA'], 0);

        $sendCountFailedA = 0;
        $sendCountSendA = 0;
        $sendCountOpenedA = 0;
        $sendCountClickedA = 0;
        $sendCountBouncedA = 0;
        $sendCountSpamA = 0;
        $sendCountBlockedA = 0;

        if ($campaignAResults) {
            foreach ($campaignAResults as $campaignAResult) {
                $sendCount = $campaignAResult['sendCount'];
                $sendCountFailedA = ($campaignAResult['emailStatus'] == CampaignUsers::EMAIL_FAILED) ? $sendCount : $sendCountFailedA;
                $sendCountSendA = ($campaignAResult['emailStatus'] == CampaignUsers::EMAIL_SENT) ? $sendCount : $sendCountSendA;
                $sendCountOpenedA = ($campaignAResult['emailStatus'] == CampaignUsers::EMAIL_OPENED) ? $sendCount : $sendCountOpenedA;
                $sendCountClickedA = ($campaignAResult['emailStatus'] == CampaignUsers::EMAIL_CLICKED) ? $sendCount : $sendCountClickedA;
                $sendCountBouncedA = ($campaignAResult['emailStatus'] == CampaignUsers::EMAIL_BOUNCED) ? $sendCount : $sendCountBouncedA;
                $sendCountSpamA = ($campaignAResult['emailStatus'] == CampaignUsers::EMAIL_SPAM) ? $sendCount : $sendCountSpamA;
                $sendCountBlockedA = ($campaignAResult['emailStatus'] == CampaignUsers::EMAIL_BLOCKED) ? $sendCount : $sendCountBlockedA;
            }
        }

        $campaignBResults = ABTestingCampaign::getCampaignResults($id, $aBTestCampaign['countB'], $aBTestCampaign['countA']);

        $sendCountFailedB = 0;
        $sendCountSendB = 0;
        $sendCountOpenedB = 0;
        $sendCountClickedB = 0;
        $sendCountBouncedB = 0;
        $sendCountSpamB = 0;
        $sendCountBlockedB = 0;

        if ($campaignBResults) {
            foreach ($campaignBResults as $campaignBResult) {
                $sendCount = $campaignBResult['sendCount'];
                $sendCountFailedB = ($campaignBResult['emailStatus'] == CampaignUsers::EMAIL_FAILED) ? $sendCount : $sendCountFailedB;
                $sendCountSendB = ($campaignBResult['emailStatus'] == CampaignUsers::EMAIL_SENT) ? $sendCount : $sendCountSendB;
                $sendCountOpenedB = ($campaignBResult['emailStatus'] == CampaignUsers::EMAIL_OPENED) ? $sendCount : $sendCountOpenedB;
                $sendCountClickedB = ($campaignBResult['emailStatus'] == CampaignUsers::EMAIL_CLICKED) ? $sendCount : $sendCountClickedB;
                $sendCountBouncedB = ($campaignBResult['emailStatus'] == CampaignUsers::EMAIL_BOUNCED) ? $sendCount : $sendCountBouncedB;
                $sendCountSpamB = ($campaignBResult['emailStatus'] == CampaignUsers::EMAIL_SPAM) ? $sendCount : $sendCountSpamB;
                $sendCountBlockedB = ($campaignBResult['emailStatus'] == CampaignUsers::EMAIL_BLOCKED) ? $sendCount : $sendCountBlockedB;

            }
        }
        $totalA = $sendCountFailedA + $sendCountOpenedA + $sendCountClickedA + $sendCountBouncedA + $sendCountSpamA + $sendCountBlockedA + $sendCountSendA;
        $totalB = $sendCountFailedB + $sendCountOpenedB + $sendCountClickedB + $sendCountBouncedB + $sendCountSpamB + $sendCountBlockedB + $sendCountSendB;

        $fitnessAPlus = $sendCountOpenedA + $sendCountClickedA + $sendCountSendA;
        $fitnessAMinus = $sendCountFailedA + $sendCountBouncedA + $sendCountSpamA + $sendCountBlockedA;

        $fitnessBPlus = $sendCountOpenedB + $sendCountClickedB + $sendCountSendB;
        $fitnessBMinus = $sendCountFailedB + $sendCountBouncedB + $sendCountSpamB + $sendCountBlockedB;

        $fitnessA = $fitnessAPlus - $fitnessAMinus;
        $fitnessB = $fitnessBPlus - $fitnessBMinus;

        if ($totalA == $totalB) {
            $bestTemplate = ($fitnessA > $fitnessB) ? ABTestingCampaign::TEMPLATE_A : (($fitnessA < $fitnessB) ? ABTestingCampaign::TEMPLATE_B : ABTestingCampaign::TEMPLATE_DEFAULT);
            $reason = Yii::t('messages', "Better: ") . $bestTemplate . " (" . $reason1 . ")";
            if ($fitnessA == $fitnessB) {
                $reason = $reason2;
            }
        } else if ($totalA != $totalB) {
            $bestTemplate = ABTestingCampaign::TEMPLATE_DEFAULT;
            $reason = $reason3;
        }
        if (0 == $totalUsers) {
            $progressA = 0;
            $progressB = 0;
        } else {
            $progressA = ceil((($totalA) / (!empty($aBTestCampaign['countA']) ? $aBTestCampaign['countA'] : 1)) * 100);
            $progressB = ceil((($totalB) / (!empty($aBTestCampaign['countB']) ? $aBTestCampaign['countB'] : 1)) * 100);
        }

        $isRescheduled = false;

        if ((Campaign::CAMP_TYPE_AB_TEST_EMAIL == $model->campType) && Campaign::CAMP_PENDING == $model->status && strtotime($model->startDateTime) > time()) {
            $isRescheduled = true;
        }

        $aBTestCampaign['aBTestTemplateId'] = $bestTemplate;
        $aBTestCampaign['endDateTime'] = date('Y-m-d', strtotime($model->endDateTime));
        $isScheduleRemain = (isset($aBTestCampaign['fromRemain'])) ? true : false;
        $searchModel = new ABTestingCampaignSearch();
        $dataProvider = $searchModel->search($aBTestCampaign->id);

        return $this->render('stat', array(
            'model' => $model,
            'abTestModel' => $aBTestCampaign,
            'totalUsers' => $totalUsers,
            'totalA' => $totalA,
            'sendCountFailedA' => $sendCountFailedA,
            'sendCountOpenedA' => $sendCountOpenedA,
            'sendCountClickedA' => $sendCountClickedA,
            'sendCountBouncedA' => $sendCountBouncedA,
            'sendCountSpamA' => $sendCountSpamA,
            'sendCountBlockedA' => $sendCountBlockedA,
            'sendCountSendA' => $sendCountSendA,
            'totalB' => $totalB,
            'sendCountFailedB' => $sendCountFailedB,
            'sendCountOpenedB' => $sendCountOpenedB,
            'sendCountClickedB' => $sendCountClickedB,
            'sendCountBouncedB' => $sendCountBouncedB,
            'sendCountSpamB' => $sendCountSpamB,
            'sendCountBlockedB' => $sendCountBlockedB,
            'sendCountSendB' => $sendCountSendB,
            'isRescheduled' => $isRescheduled,
            'isScheduleRemain' => $isScheduleRemain,
            'templateA' => $messageTemplateAModel['name'],
            'templateB' => $messageTemplateBModel['name'],
            'progressA' => $progressA,
            'progressB' => $progressB,
            'reason' => $reason,
            'dataProvider' => $dataProvider
        ));

    }

    /**
     * Finds the ABTestingCampaign model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ABTestingCampaign the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected
    function findModel($id)
    {
        if (($model = ABTestingCampaign::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
