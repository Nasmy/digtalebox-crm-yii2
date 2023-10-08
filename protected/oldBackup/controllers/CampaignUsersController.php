<?php

namespace app\controllers;

use app\components\ToolKit;
use app\models\Activity;
use app\models\AdvanceBulkInsert;
use app\models\Campaign;
use app\models\CampaignUsersQuery;
use app\models\Keyword;
use app\models\User;
use borales\extensions\phoneInput\PhoneInputBehavior;
use Throwable;
use Yii;
use app\models\CampaignUsers;
use app\models\CampaignUsersSearch;
use yii\base\ExitException;
use yii\db\Exception;
use yii\db\Query;
use yii\db\StaleObjectException;
use yii\filters\AccessControl;
use yii\helpers\Html;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use \app\controllers\WebUserController;

/**
 * CampaignUsersController implements the CRUD actions for CampaignUsers model.
 */
class CampaignUsersController extends WebUserController
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

    /**
     * Lists all CampaignUsers models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CampaignUsersSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    public function actionGetExportFile()
    {
        $this->actionExport();
        // setting the random key
        $randomString = rand();
        $fileName = $randomString . '_export.csv'; // file name

        //send file to export and save
        Yii::$app->response->sendContentAsFile(Yii::$app->session->get('export'), $fileName, ['mimeType' => 'csv', 'inline' => false]);
        Yii::$app->session->remove('export');

    }

    public function actionExport()
    {
        $id = $_GET['id'];
        $fp = fopen('php://temp', 'w');
        $modelCampaign = Campaign::find()->where(['id' => $id])->one();
        $headers = array();
        if ($modelCampaign->campType == Campaign::CAMP_TYPE_EMAIL || $modelCampaign->campType == Campaign::CAMP_TYPE_ALL) {
            $headers = array(
                'name',
                'email',
                'clickedUrls',
                'keywords',
                'createdAt',
                'emailStatus',
            );
        } else if ($modelCampaign->campType == Campaign::CAMP_TYPE_SMS) {
            $headers = array(
                'name',
                'mobile',
                'keywords',
                'createdAt',
                'status',
            );
        }

        $row = array();
        $CampaignUsers = new CampaignUsers();
        foreach ($headers as $header) {
            $row[] = $CampaignUsers->getAttributeLabel($header);
        }
        fputcsv($fp, $row);

        $SearchModel = new CampaignUsersSearch();
        $SearchModel->scenario = 'search';
        $SearchModel->campaignId = $id;
        $SearchModel->campType = $modelCampaign->campType;

        if (isset($_GET['CampaignUsers'])) {
            $SearchModel->attributes = $_GET['CampaignUsers'];
        }
        $db = $SearchModel->search(Yii::$app->request->queryParams);
        $formatArray = array('email', 'mobile', 'keywords', 'emailStatus', 'status');

        $models = $db->getModels();

        foreach ($models as $model) {
            $userInfo = User::find()->where(['id' => $model->userId])->one();
            $row = array();
            foreach ($headers as $head) {
                $val = Html::getAttributeValue($model, $head);
                if (in_array($head, $formatArray)) {
                    switch ($head) {
                        case 'email':
                            $val = $userInfo->email;
                            break;
                        case 'mobile':
                            $val = $userInfo->mobile;
                            break;
                        case 'keywords':
                            $value = explode(",", $val);
                            $keywordArr = array();
                            foreach ($value as $keyword) {
                                if (!empty($keyword)) {
                                    $keywordArr[] = Keyword::findOne($keyword)->name;
                                }
                            }
                            $val = implode(",", $keywordArr);
                            break;
                        case 'emailStatus':
                            $val = $model->getEmailStatusText();
                            break;
                        case 'status':
                            $val = $model->getSmsStatusText();
                            break;
                    }
                }
                $row[] = ToolKit::isEmpty($val) ? "N/A" : $val;
            }
            fputcsv($fp, $row);
        }
        rewind($fp);
        Yii::$app->session->set('export', stream_get_contents($fp));
        fclose($fp);
    }

    /**
     * Manages all models.
     * @param $id
     * @return string
     * @throws ExitException
     * @throws Exception
     */
    public function actionAdmin($id)
    {
        if (Yii::$app->request->get('export')) {
            $this->actionExport();
            Yii::$app->end();
        }

        $modelCampaign = Campaign::findOne($id);
        $totalCount = $modelCampaign->totalUsers;
        $model = new CampaignUsersSearch();
        $model->scenario = 'search';
        $model->campaignId = $id;
        $model->campType = $modelCampaign->campType;

        if (isset($_GET['CampaignUsersSearch'])) {
            $model->attributes = $_GET['CampaignUsersSearch'];
        }

        $dataProvider = $model->search(Yii::$app->request->queryParams);
        // sent email
        $sentEmailResults = CampaignUsers::getEmailStatusCount($id, CampaignUsers::EMAIL_SENT, $totalCount);
        $sentCountTotal = $sentEmailResults['countTotal'];
        $sentCount = $sentEmailResults['count'];

        // Clicked email
        $sentEmailResults = CampaignUsers::getEmailStatusCount($id, CampaignUsers::EMAIL_CLICKED, $totalCount);
        $clickedCountTotal = $sentEmailResults['countTotal'];
        $clickedCount = $sentEmailResults['count'];

        // Opened email
        $sentEmailResults = CampaignUsers::getEmailStatusCount($id, CampaignUsers::EMAIL_OPENED, $totalCount);
        $openedCountTotal = $sentEmailResults['countTotal'];
        $openedCount = $sentEmailResults['count'];

        // Bounced email
        $sentEmailResults = CampaignUsers::getEmailStatusCount($id, CampaignUsers::EMAIL_BOUNCED, $totalCount);
        $bouncedCountTotal = $sentEmailResults['countTotal'];
        $bouncedCount = $sentEmailResults['count'];

        // Blocked email
        $sentEmailResults = CampaignUsers::getEmailStatusCount($id, CampaignUsers::EMAIL_BLOCKED, $totalCount);
        $blockedCountTotal = $sentEmailResults['countTotal'];
        $blockedCount = $sentEmailResults['count'];

        // Spam email
        $sentEmailResults = CampaignUsers::getEmailStatusCount($id, CampaignUsers::EMAIL_SPAM, $totalCount);
        $spamCountTotal = $sentEmailResults['countTotal'];
        $spamCount = $sentEmailResults['count'];

        // Failed email
        $sentEmailResults = CampaignUsers::getEmailStatusCount($id, CampaignUsers::EMAIL_FAILED, $totalCount);
        $failedCountTotal = $sentEmailResults['countTotal'];
        $failedCount = $sentEmailResults['count'];

        // Unsubscribe Email
        $query = new Query();

        $query->SELECT(['u.*', 'cu.*'])
            ->leftJoin('User u', 'cu.userId = u.id')
            ->where('u.isUnsubEmail = :isUnsubEmail AND cu.campaignId = :campaignId', [':isUnsubEmail' => 1, ':campaignId' => $id])
            ->from('CampaignUsers cu')->all();

        $unsubscribedCountTotal = $query->count();
        $unsubscribedCount = ($totalCount > 0) ? round((($unsubscribedCountTotal / $totalCount) * 100), 2) . '%' : 0;

        $tagList = [];
        $keywords = Keyword::getActiveKeywords();
        $tmpKeywords = array();

        foreach ($keywords as $behaviour => $behaviours) {
            foreach ($behaviours as $key => $keyword) {
                switch ($behaviour) {
                    case Keyword::KEY_AUTO:
                        $tagList[] = array('value' => $key, 'text' => $keyword, 'disabled' => true);
                        break;

                    default:
                        $tagList[] = array('value' => $key, 'text' => $keyword);
                        break;
                };
            }
            $tmpKeywords[Keyword::getBehaviourOptions($behaviour)] = $behaviours;
        }

        return $this->render('admin', array(
            'model' => $model,
            'dataProvider' => $dataProvider,
            'sentCount' => $sentCount,
            'sentCountTotal' => $sentCountTotal,
            'clickedCount' => $clickedCount,
            'clickedCountTotal' => $clickedCountTotal,
            'openedCount' => $openedCount,
            'openedCountTotal' => $openedCountTotal,
            'bouncedCount' => $bouncedCount,
            'bouncedCountTotal' => $bouncedCountTotal,
            'blockedCount' => $blockedCount,
            'blockedCountTotal' => $blockedCountTotal,
            'spamCount' => $spamCount,
            'spamCountTotal' => $spamCountTotal,
            'failedCount' => $failedCount,
            'failedCountTotal' => $failedCountTotal,
            'unsubscribedCount' => $unsubscribedCount,
            'unsubscribedCountTotal' => $unsubscribedCountTotal,
            'tagList' => $tagList,
            'keywords' => $tmpKeywords
        ));
    }

    /**
     * Displays a single CampaignUsers model.
     * @param integer $campaignId
     * @param integer $userId
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($campaignId, $userId)
    {
        return $this->render('view', [
            'model' => $this->findModel($campaignId, $userId),
        ]);
    }

    /**
     * Creates a new CampaignUsers model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new CampaignUsers();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'campaignId' => $model->campaignId, 'userId' => $model->userId]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing CampaignUsers model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $campaignId
     * @param integer $userId
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($campaignId, $userId)
    {
        $model = $this->findModel($campaignId, $userId);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'campaignId' => $model->campaignId, 'userId' => $model->userId]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionUpdateStatistic($id)
    {
        // TODO needs to implement
        $campaignInfo = Campaign::find()->where(['id' => $id])->one();
        if ($campaignInfo->status === Campaign::CAMP_FINISH) {
            (new AdvanceBulkInsert())->runCommand(CampaignUsers::CRON_COMMAND, $id);
            Yii::$app->session->setFlash('success', Yii::t('messages', 'Update process started. It will update in a while. Note: If the latest updates are not available , previous stats will remain same.'));
        } else {
            Yii::$app->session->setFlash('error', Yii::t('messages', 'You campaign is in progress'));
        }
        // (new AdvanceBulkInsert())->runCommand(CampaignUsers::CRON_COMMAND, $id);
        return $this->redirect(['admin', 'id' => $id]);
    }

    /**
     * @param $id
     * @return string
     * @throws ExitException
     */
    public function actionSmsStat($id)
    {
        if (Yii::$app->request->get('export')) {
            $this->actionExport();
            Yii::$app->end();
        }

        $model = new CampaignUsersSearch();
        $model->scenario = 'search';
        $model->campaignId = $id;

        if (isset($_GET['CampaignUsers'])) {
            $model->attributes = $_GET['CampaignUsers'];
        }

        $dataProvider = $model->search(Yii::$app->request->queryParams);

        $pendingCount = CampaignUsers::find()->where(['smsStatus' => CampaignUsers::SMS_PENDING, 'campaignId' => $id])->count();
        $deliveredCount = CampaignUsers::find()->where(['smsStatus' => CampaignUsers::SMS_DELIVERED, 'campaignId' => $id])->count();
        $failedCount = CampaignUsers::find()->where(['smsStatus' => CampaignUsers::SMS_FAILED, 'campaignId' => $id])->count();

        $tagList = array();
        $keywords = Keyword::getActiveKeywords();
        $tmpKeywords = array();
        foreach ($keywords as $behaviour => $behaviours) {
            foreach ($behaviours as $key => $keyword) {
                switch ($behaviour) {
                    case Keyword::KEY_AUTO:
                        $tagList[] = array('value' => $key, 'text' => $keyword, 'disabled' => true);
                        break;

                    default:
                        $tagList[] = array('value' => $key, 'text' => $keyword);
                        break;
                };
            }
            $tmpKeywords[Keyword::getBehaviourOptions($behaviour)] = $behaviours;
        }

        Yii::$app->toolKit->addActivity(Yii::$app->user->id, Activity::ACT_SEARCH_PEOPLE, Yii::$app->session->get('teamId'));

        return $this->render('smsStat', array(
            'dataProvider' => $dataProvider,
            'model' => $model,
            'tagList' => $tagList,
            'keywords' => $tmpKeywords,
            'pendingCount' => $pendingCount,
            'deliveredCount' => $deliveredCount,
            'failedCount' => $failedCount,
        ));
    }

    /**
     * Deletes an existing CampaignUsers model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $campaignId
     * @param integer $userId
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionDelete($campaignId, $userId)
    {
        $this->findModel($campaignId, $userId)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the CampaignUsers model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $campaignId
     * @param integer $userId
     * @return CampaignUsers the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($campaignId, $userId)
    {
        if (($model = CampaignUsers::findOne(['campaignId' => $campaignId, 'userId' => $userId])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
