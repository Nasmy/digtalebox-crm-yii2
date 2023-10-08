<?php

namespace app\controllers;

use app\components\ThresholdChecker;
use app\components\ToolKit;
use app\models\Activity;
use app\models\AdvanceBulkInsert;
use app\models\App;
use app\models\BroadcastMessage;
use app\models\Configuration;
use app\models\CustomField;
use app\models\CustomType;
use app\models\CustomValue;
use app\models\FbProfile;
use app\models\Feed;
use app\models\Keyword;
use app\models\PeopleStat;
use app\models\StatSummary;
use app\models\StatSummarySearch;
use app\models\TwProfile;
use app\models\User;
use borales\extensions\phoneInput\PhoneInputBehavior;
use kartik\form\ActiveForm;
use Yii;
use yii\base\ErrorException;
use yii\db\Expression;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\web\HttpException;
use app\components\WebUser;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use app\controllers\WebUserController;

class PeopleController extends WebUserController
{
    public $layout = '/column1';
    public $titleDescription;

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


    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $this->layout = 'dialog';
        return $this->renderAjax('view', array(
            'model' => $this->loadModel($id),
        ));
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        try {
            $this->layout = 'dialog';
            $model = $this->loadModel($id);
            $model->scenario = User::SENARIO_UPDATE_PEOPLE;
            $redirect = 'dashboard/dashboard';
            //Custom field data
            $customFields = CustomValue::getCustomDataWithoutDuplicates(CustomType::CF_PEOPLE, $id, CustomField::ACTION_EDIT, ToolKit::post('CustomValue'));
            if (isset($_POST['User'])) {
                if (!empty($_POST['User']['keywords'])) {
                    $keywords = implode(',', $_POST['User']['keywords']);
                }
                $modelOld = clone $model;
                $model->attributes = $_POST['User'];
                $attributes = $model->attributes;
                $tc = new ThresholdChecker(Yii::$app->session->get('packageType'), Yii::$app->session->get('smsPackageType'));
                if ($tc->isThresholdExceeded(ThresholdChecker::EMAIL_CONTACTS) && $modelOld->email == '' && $model->email != '') {
                    $this->redirect(array('site/upgrade/', 'thresholdType' => ThresholdChecker::EMAIL_CONTACTS));
                }
                $model->keywords = (!empty($_POST['User']['keywords'])) ? $keywords : '';
                $model->updatedAt = User::convertSystemTime();
                $model = $this->loadModel($id);
                $model->scenario = User::SENARIO_UPDATE_PEOPLE;
                $model->userType = (trim($_POST['User']['userType']))?trim($_POST['User']['userType']):User::UNKNOWN;
                $model->address1 = trim($_POST['User']['address1']);
                $model->mobile = trim($_POST['User']['mobile']);
                $model->firstName = trim($_POST['User']['firstName']);
                $model->lastName = trim($_POST['User']['lastName']);
                $model->email = trim($_POST['User']['email']);
                $model->gender = trim($_POST['User']['gender']);
                $model->zip = trim($_POST['User']['zip']);
                $model->city = trim($_POST['User']['city']);
                $model->countryCode = trim($_POST['User']['countryCode']);
                $model->dateOfBirth = trim($_POST['User']['dateOfBirth']);
                $model->keywords = (!empty($_POST['User']['keywords'])) ? $keywords : '';
                $model->notes = trim($_POST['User']['notes']);
                $model->updatedAt = User::convertSystemTime();
                $model->longLat ='';

                // Check email contacts limitations
                $attributesArr = array('address1', 'mobile', 'name', 'firstName', 'lastName', 'username', 'password', 'email',
                    'gender', 'zip', 'countryCode', 'joinedDate', 'signUpDate', 'supporterDate', 'userType', 'signup', 'isSysUser', 'dateOfBirth',
                    'reqruiteCount', 'keywords', 'delStatus', 'city', 'isUnsubEmail', 'isManual', 'isSignupConfirmed', 'profImage',
                    'totalDonations', 'isMcContact', 'emailStatus', 'notes', 'longLat', 'updatedAt');
                $valid_values = $model->validate();
                $valid = CustomField::validateCustomFieldList($customFields);
                if ($valid_values && $valid && $model->saveWithCustomData($customFields, $attributesArr, false)) {
                    Yii::$app->session->setFlash('success', Yii::t('messages', 'People updated'));
                    Yii::$app->appLog->writeLog("People updated. People data:" . json_encode($attributes));
                    $params = array(
                        'person' => $model->firstName,
                    );
                    Yii::$app->toolKit->addActivity(
                        Yii::$app->user->getId(),
                        Activity::ACT_UPDATE_PEOPLE,
                        Yii::$app->session->get('teamId'),
                        Json::encode($params)
                    );

                    $model->changeDobToNull();
                    Yii::$app->session->set('ajaxupdate', 1); //Advanced search filter modifications ...
                    return $this->refresh();

                } else {
                    Yii::$app->appLog->writeLog("People update failed. People data:" . json_encode($attributes));
                    Yii::$app->session->setFlash('error', Yii::t('messages', 'People update failed'));

                }
            }

            $keywords = Keyword::getActiveKeywords();
            $tmpKeywords = array();
            foreach ($keywords as $behaviour => $behaviours) {
                $tmpKeywords[Keyword::getBehaviourOptions($behaviour)] = $behaviours;
            }
            if ($model->keywords !== null) {
                $model->keywords = explode(",", $model->keywords);
            }

            return $this->render('update', array(
                'model' => $model,
                'keywords' => $tmpKeywords,
                'closeUrl' => $redirect,
                'customFields' => $customFields,
            ));

        } catch (ErrorException $e) {
            echo 'Caught an Error: <i>', $e->getMessage() . "</i> , file => " . $e->getFile() . ", on line number => " . $e->getLine() . "\n";
            Yii::$app->appLog->writeLog("Exception : " . $e->getLine());
        }

    }

    /**
     * @description People create with related fields
     * @return string
     */
    public function actionCreate()
    {
        try {
            $model = new User();
            $model->scenario = 'people';
            $customFields = CustomValue::getCustomData(CustomType::CF_PEOPLE, 0, CustomField::ACTION_CREATE, ToolKit::post('CustomValue'));
            // $this->performAjaxValidation($model, 'user-form', $customFields);

            if (isset($_POST['User'])) {
                $model->attributes = $_POST['User'];
                $model->mobile = $_POST['User']['mobile'];
                $model->joinedDate = User::convertSystemTime();
                $model->supporterDate = User::convertSystemTime();
                $model->signUpDate = User::convertSystemTime();
                $model->createdAt = User::convertSystemTime();
                if ($_POST['User']['gender'] == null) {
                    $model->gender = 0;
                }

                if($_POST['User']['userType'] == null) {
                    $model->userType = User::UNKNOWN;
                }

                $model->isManual = 1;
                $attributes = $model->attributes;

                $valid = $model->validate();
                $valid = CustomField::validateCustomFieldList($customFields) && $valid;
                if ($valid && $model->saveWithCustomData($customFields, null, false)) {
                    Yii::error("People created. People data:" . Json::encode($attributes));
                    $params = array(
                        'person' => $model->firstName,
                    );

                    Yii::$app->toolKit->addActivity(
                        Yii::$app->user->id,
                        Activity::ACT_CRT_NEW_PEOPLE,
                        Yii::$app->session->get('teamId'),
                        Json::encode($params)
                    );
                    Yii::$app->toolKit->addActivity(Yii::$app->user->id, Activity::ACT_PEOPLE_CREATE);
                    return $this->refresh(Yii::$app->session->setFlash('success', Yii::t('messages', 'People created')));
                } else {
                    Yii::error("People create failed. People data:" . Json::encode($attributes));
                    Yii::$app->session->setFlash('error', Yii::t('messages', 'People create failed'));
                }
            } else {
                $model->userType = User::SUPPORTER;
            }
            $keywords = Keyword::getActiveKeywords();
            unset($keywords[Keyword::KEY_AUTO]);

            $tmpKeywords = array();
            foreach ($keywords as $behaviour => $behaviours) {
                $tmpKeywords[Keyword::getBehaviourOptions($behaviour)] = $behaviours;
            }

            return $this->render('create', [
                    'model' => $model,
                    'keywords' => $tmpKeywords,
                    'closeUrl' => '/dashboard/dashboard',
                    'customFields' => $customFields
                ]
            );

        } catch (ErrorException $e) {
            echo 'Caught an Error: <i>', $e->getMessage() . "</i> , file => " . $e->getFile() . ", on line number => " . $e->getLine() . "\n";
            Yii::$app->appLog->writeLog("Exception : " . $e->getLine());
        }
    }

    /**
     * Deletes a particular model. Update user delete status
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be deleted
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */

    public function actionDelete($id)
    {
        $attributes='';
        $model = User::find()->where(['id' => $id])->one();
        $attributes = $model->attributes;
        if ($model) {
            if ($model->deleteWithCustomData()) {
                Yii::$app->appLog->writeLog("People deleted. People data:" . Json::encode($attributes));
                Yii::$app->toolKit->setAjaxFlash('success', Yii::t('messages', 'People deleted'));
                $params = array(
                    'person' => $model->firstName,
                );
                Yii::$app->toolKit->addActivity(
                    Yii::$app->user->id,
                    Activity::ACT_CRT_NEW_PEOPLE,
                    Yii::$app->session->get('teamId'),
                    Json::encode($params)
                );
            } else {
                Yii::$app->appLog->writeLog("People delete failed. People data:" . Json::encode($attributes));
                Yii::$app->toolKit->setAjaxFlash('error', Yii::t('messages', 'People delete failed'));
            }
        }
    }

    /**
     * Get people statistics (supporters per day & category)
     */
    public function actionStatistics()
    {
        Yii::$app->toolKit->addActivity(Yii::$app->user->id, Activity::ACT_VIEW_STAT);

        $model = new StatSummary();
        $statSummary = $model->getStatSummary(7);

        $searchModel = new StatSummarySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $modelbr = new BroadcastMessage();
        $modelbr->scenario = 'search';
        $modelbr->loadDefaultValues();  // clear any default value

        if (isset($_GET['BroadcastMessage']))
            $modelbr->attributes = $_GET['BroadcastMessage'];


        // Cumulative
        $statSumModels = (new \yii\db\Query())
            ->from('StatSummary')
            ->limit(10)
            ->all();

        $supporters = array();
        $newReg = array();
        $newSupporters = array();
        $prospects = array();
        $t_supporters = array();

        $weeksup = null;

        if (null != $statSumModels) {
            foreach ($statSumModels as $key => $statSumModel) {
                $supporters[] = array((int)$statSumModel['supporterCount']);
                $newReg[] = array($statSumModel['date'], (int)$statSumModel['newRegistrationCount']);
                $newSupporters[] = array($statSumModel['date'], (int)$statSumModel['newSupporterCount']);
                $prospects[] = array((int)$statSumModel['supporterCount'] + (int)$statSumModel['prospectCount']);
                if ($key < 30) {
                    $m_supporters[] = array((int)$statSumModel['supporterCount']);
                    $m_newReg[] = array($statSumModel['date'], (int)$statSumModel['newRegistrationCount']);
                    $m_newSupporters[] = array($statSumModel['date'], (int)$statSumModel['newSupporterCount']);
                    $m_prospects[] = array((int)$statSumModel['supporterCount'] + (int)$statSumModel['prospectCount']);
                }
                if ($key < 90) {
                    $t_supporters = array((int)$statSumModel['supporterCount']);
                    $t_newReg[] = array($statSumModel['date'], (int)$statSumModel['newRegistrationCount']);
                    $t_newSupporters[] = array($statSumModel['date'], (int)$statSumModel['newSupporterCount']);
                    $t_prospects[] = array((int)$statSumModel['supporterCount'] + (int)$statSumModel['prospectCount']);

                }
                if ($key < 7) {

                    $w_supporters[] = array((int)$statSumModel['supporterCount']);
                    $w_newReg[] = array($statSumModel['date'], (int)$statSumModel['newRegistrationCount']);
                    $w_newSupporters[] = array($statSumModel['date'], (int)$statSumModel['newSupporterCount']);
                    $w_prospects[] = array((int)$statSumModel['supporterCount'] + (int)$statSumModel['prospectCount']);

                }
                if ($key == 7) {
                    $weeksup = (int)$statSumModel['supporterCount'];
                }
            }
        }

        // Render
        return $this->render('statistics', array(
            'modelbr' => $modelbr,
            'statSummary' => $statSummary,
            'supporters' => Json::encode(array_reverse($supporters)),
            'newReg' => Json::encode(array_reverse($newReg)),
            'newSupporters' => Json::encode(array_reverse($newSupporters)),
            'prospects' => Json::encode(array_reverse($prospects)),
            't_supporters' => Json::encode(array_reverse($t_supporters)),
            't_newReg' => Json::encode(array_reverse($t_newReg)),
            't_newSupporters' => Json::encode(array_reverse($t_newSupporters)),
            't_prospects' => Json::encode(array_reverse($t_prospects)),
            'm_supporters' => Json::encode(array_reverse($m_supporters)),
            'm_newReg' => Json::encode(array_reverse($m_newReg)),
            'm_newSupporters' => Json::encode(array_reverse($m_newSupporters)),
            'm_prospects' => Json::encode(array_reverse($m_prospects)),
            'w_supporters' => Json::encode(array_reverse($w_supporters)),
            'w_newReg' => Json::encode(array_reverse($w_newReg)),
            'w_newSupporters' => Json::encode(array_reverse($w_newSupporters)),
            'w_prospects' => Json::encode(array_reverse($w_prospects)),
            'nowsup' => $supporters,
            'weeksup' => $weeksup,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ));
    }

    public function actionAnalytics()
    {
        // Cumulative
        $modelStat = new StatSummary();
        $statSummary = $modelStat->getStatSummary(7);

        $statSumModels = (new \yii\db\Query())
            ->from('StatSummary')
            ->limit(180)
            ->orderBy('date', SORT_DESC)
            ->all();

        $supporters = array();
        $feeds = array();
        $newReg = array();
        $newSupporters = array();

        if (null != $statSumModels) {
            foreach ($statSumModels as $key => $statSumModel) {
                $supporters[] = array((int)$statSumModel['supporterCount']);
                $newReg[] = array($statSumModel['date'], (int)$statSumModel['newRegistrationCount']);
                $newSupporters[] = array($statSumModel['date'], (int)$statSumModel['newSupporterCount']);
                $prospects[] = array((int)$statSumModel['supporterCount'] + (int)$statSumModel['prospectCount']);

                if ($key < 30) {
                    $m_supporters[] = array((int)$statSumModel['supporterCount']);
                    $m_newReg[] = array($statSumModel['date'], (int)$statSumModel['newRegistrationCount']);
                    $m_newSupporters[] = array($statSumModel['date'], (int)$statSumModel['newSupporterCount']);
                    $m_prospects[] = array((int)$statSumModel['supporterCount'] + (int)$statSumModel['prospectCount']);
                }

                if ($key < 90) {
                    $t_supporters[] = array((int)$statSumModel['supporterCount']);
                    $t_newReg[] = array($statSumModel['date'], (int)$statSumModel['newRegistrationCount']);
                    $t_newSupporters[] = array($statSumModel['date'], (int)$statSumModel['newSupporterCount']);
                    $t_prospects[] = array((int)$statSumModel['supporterCount'] + (int)$statSumModel['prospectCount']);

                }

                if ($key < 7) {

                    $w_supporters[] = array((int)$statSumModel['supporterCount']);
                    $w_newReg[] = array($statSumModel['date'], (int)$statSumModel['newRegistrationCount']);
                    $w_newSupporters[] = array($statSumModel['date'], (int)$statSumModel['newSupporterCount']);
                    $w_prospects[] = array((int)$statSumModel['supporterCount'] + (int)$statSumModel['prospectCount']);

                }
            }
        }

        return $this->render('analytics', array(
            'newReg' => Json::encode(array_reverse($newReg)),
            'newSupporters' => Json::encode(array_reverse($newSupporters)),
            'supporters' => Json::encode(array_reverse($supporters)),
            'statSummary' => $statSummary,
            'prospects' => Json::encode(array_reverse($prospects)),
            'nowsup' => $supporters,
            't_supporters' => Json::encode(array_reverse($t_supporters)),
            't_newReg' => Json::encode(array_reverse($t_newReg)),
            't_newSupporters' => Json::encode(array_reverse($t_newSupporters)),
            't_prospects' => Json::encode(array_reverse($t_prospects)),
            'm_supporters' => Json::encode(array_reverse($m_supporters)),
            'm_newReg' => Json::encode(array_reverse($m_newReg)),
            'm_newSupporters' => Json::encode(array_reverse($m_newSupporters)),
            'm_prospects' => Json::encode(array_reverse($m_prospects)),
            'w_supporters' => Json::encode(array_reverse($w_supporters)),
            'w_newReg' => Json::encode(array_reverse($w_newReg)),
            'w_newSupporters' => Json::encode(array_reverse($w_newSupporters)),
            'w_prospects' => Json::encode(array_reverse($w_prospects)),
        ));

    }

    public function actionPosts()
    {

        $model = new BroadcastMessage();
        $model->scenario = 'search';
        $model->loadDefaultValues();  // clear any default values

        if (isset($_GET['BroadcastMessage']))
            $model->attributes = $_GET['BroadcastMessage'];

        if (Yii::$app->session->get('packageTypeId') == App::FREEMIUM) {
            $tc = new ThresholdChecker(Yii::$app->session->get('packageType'));
            $usedCount = $tc->getCount(ThresholdChecker::BROADCAST_LIMIT);
            $remainingCount = $tc->getRemainingCount(ThresholdChecker::BROADCAST_LIMIT);
            $total = $usedCount + $remainingCount;

            Yii::$app->session->setFlash('info', "You have {$remainingCount} broadcast messages remaining out of {$total}");
        }

        // To launch the site guide when freemium user first access the site
        $isSiteGuideViewed = true;
        if (isset($_GET['isSiteGuideViewed']) && !$_GET['isSiteGuideViewed']) {
            $isSiteGuideViewed = false;
        }

        return $this->render('posts', array(
            'model' => $model,
            'isSiteGuideViewed' => $isSiteGuideViewed,
            'attributeLabels' => $model->attributeLabels()
        ));
    }


    /**
     * function to get charts
     * @return string
     */
    public function actionPopulation()
    {
        ini_set('memory_limit', '1048M');
        ini_set('max_execution_time', 120); //300 seconds = 5 minutes

        $model = new User();
        $model->scenario = 'people';

        $tmpKeywords = array();

        /* Keyword Chart from Ajax Call */
        $keywords = Keyword::getActiveKeywords();
        if (isset($_GET['keywords'])) {
            $this->layout = 'dialog';
            $keywordChartTitle = Yii::t('messages', "People Statistics by Keywords");
            $keywordsString = $_GET['keywords']; // user selected keywords
            $keywordChartRecord = PeopleStat::getKeyWordPieResult($keywordsString);
            $keywordOrder = (isset($keywordChartRecord['keywordOrder'])) ? Json::encode($keywordChartRecord['keywordOrder']) : array();
            $keywordData = (isset($keywordChartRecord['keywordData'])) ? Json::encode($keywordChartRecord['keywordData']) : array();
            $keywordTotalCount = Yii::t('messages', "Total Users With Keywords: ") . $keywordChartRecord['keywordTotalCount'];
            $isKeywordNoData = ($keywordChartRecord['keywordTotalCount'] == 0) ? 1 : 0;
//            return
//                $this->renderPartial('_pieChart', array('isNoData' => $isKeywordNoData, 'category' => $keywordOrder, 'data' => $keywordData,
//                'name' => $keywordChartTitle, 'total' => $keywordTotalCount, 'container' => 'keywordChart',
//                'script_name' => 'keyword-Chart', 'type' => PeopleStat::KEYWORD_GRAPH, 'xAxis' => Yii::t('messages', 'Keywords')),false,true);
        } else {
            foreach ($keywords as $behaviour => $behaviours) {
                $tmpKeywords[Keyword::getBehaviourOptions($behaviour)] = $behaviours;
            }
            $peopleStatResult = PeopleStat::getPeopleStatResult();
            /* Age Chart */
            $ageChartTitle = addslashes(Yii::t('messages', "People Statistics by Age"));
            $ageRangeOrder = (isset($peopleStatResult['ageGraph']['category'])) ? $peopleStatResult['ageGraph']['category'] : array();
            $ageData = (isset($peopleStatResult['ageGraph']['graphData'])) ? $peopleStatResult['ageGraph']['graphData'] : array();
            $totalCount = (isset($peopleStatResult['ageGraph']['totalCount'])) ? Yii::t('messages', "Total Users With Age: ") . $peopleStatResult['ageGraph']['totalCount'] : array();
            $isAgeNoData = (isset($peopleStatResult['ageGraph']['totalCount'])) ? ($peopleStatResult['ageGraph']['totalCount'] == 0) ? 1 : 0 : 1;

            /* Keywords Chart */
            $keywordChartTitle = Yii::t('messages', "People Statistics by Keywords");
            $keywordOrder = (isset($peopleStatResult['top5Keyword']['keywordLabel'])) ? Json::encode($peopleStatResult['top5Keyword']['keywordLabel']) : array();
            $model->keywords = (isset($peopleStatResult['top5Keyword']['category'])) ? json_decode($peopleStatResult['top5Keyword']['category']) : array();
            $keywordData = (isset($peopleStatResult['top5Keyword']['graphData'])) ? $peopleStatResult['top5Keyword']['graphData'] : array();
            $keywordTotalCount = (isset($peopleStatResult['top5Keyword']['totalCount'])) ? Yii::t('messages', "Total Users With Keywords: ") . $peopleStatResult['top5Keyword']['totalCount'] : 0;
            $isKeywordNoData = (isset($peopleStatResult['top5Keyword']['totalCount'])) ? ($peopleStatResult['top5Keyword']['totalCount'] == 0) ? 1 : 0 : 1;

            /* Top 5 Zip Chart */
            $zipOrder = (isset($peopleStatResult['top5Zip']['category'])) ? $peopleStatResult['top5Zip']['category'] : array();
            $zipData = (isset($peopleStatResult['top5Zip']['graphData'])) ? $peopleStatResult['top5Zip']['graphData'] : array();
            $zipTotalCount = (isset($peopleStatResult['top5Zip']['totalCount'])) ? Yii::t('messages', "Total Users With Zip: ") . $peopleStatResult['top5Zip']['totalCount'] : array();
            $zipChartTitle = Yii::t('messages', "People Statistics by Top 5 Zipcode");
            $isZipNoData = (isset($peopleStatResult['top5Zip']['totalCount'])) ? ($peopleStatResult['top5Zip']['totalCount'] == 0) ? 1 : 0 : 1;

            /* Top 5 City Chart */
            $cityOrder = (isset($peopleStatResult['top5City']['category'])) ? $peopleStatResult['top5City']['category'] : array();
            $cityData = (isset($peopleStatResult['top5City']['graphData'])) ? $peopleStatResult['top5City']['graphData'] : array();
            $cityTotalCount = (isset($peopleStatResult['top5City']['totalCount'])) ? Yii::t('messages', "Total Users With City: ") . $peopleStatResult['top5City']['totalCount'] : '';
            $cityChartTitle = Yii::t('messages', "People Statistics by Top 5 Cities");
            $isCityNoData = (isset($peopleStatResult['top5City']['totalCount'])) ? ($peopleStatResult['top5City']['totalCount'] == 0) ? 1 : 0 : 1;

            /* Gender Chart */
            $genderOrder = (isset($peopleStatResult['genderGraph']['category'])) ? $peopleStatResult['genderGraph']['category'] : array();
            $genderData = (isset($peopleStatResult['genderGraph']['graphData'])) ? $peopleStatResult['genderGraph']['graphData'] : array();
            $genderTotalCount = (isset($peopleStatResult['genderGraph']['totalCount'])) ? Yii::t('messages', "Total Users With Gender: ") . $peopleStatResult['genderGraph']['totalCount'] : '';
            $genderChartTitle = Yii::t('messages', "People Statistics by Gender");
            $isGenderNoData = (isset($peopleStatResult['genderGraph']['totalCount'])) ? ($peopleStatResult['genderGraph']['totalCount'] == 0) ? 1 : 0 : 1;

            /* User Type Chart */
            $typeData = (isset($peopleStatResult['userTypeGraph']['graphData'])) ? $peopleStatResult['userTypeGraph']['graphData'] : array();
            $typeTotalCount = (isset($peopleStatResult['userTypeGraph']['totalCount'])) ? Yii::t('messages', "Total Users: ") . $peopleStatResult['userTypeGraph']['totalCount'] : '';
            $typeChartTitle = Yii::t('messages', "People Statistics by Category");
            $isTypeNoData = (isset($peopleStatResult['userTypeGraph']['totalCount'])) ? ($peopleStatResult['userTypeGraph']['totalCount'] == 0) ? 1 : 0 : 1;

            /* Team Chart*/
            $teamChartTitle = Yii::t('messages', "People Statistics by Top 5 Teams");
            $teamOrder = (isset($peopleStatResult['teamGraph']['category'])) ? $peopleStatResult['teamGraph']['category'] : array();
            $teamData = (isset($peopleStatResult['teamGraph']['graphData'])) ? $peopleStatResult['teamGraph']['graphData'] : array();
            $teamTotalCount = (isset($peopleStatResult['teamGraph']['totalCount'])) ? Yii::t('messages', "Total Users in Top 5 Team: ") . $peopleStatResult['teamGraph']['totalCount'] : '';
            $isTeamNoData = (isset($peopleStatResult['teamGraph']['totalCount'])) ? ($peopleStatResult['teamGraph']['totalCount'] == 0) ? 1 : 0 : 1;

            /* Contact Chart */
            $contactOrder = (isset($peopleStatResult['contactGraph']['category'])) ? $peopleStatResult['contactGraph']['category'] : array();
            $contactData = (isset($peopleStatResult['contactGraph']['graphData'])) ? $peopleStatResult['contactGraph']['graphData'] : array();
            $contactTotalCount = (isset($peopleStatResult['contactGraph']['totalCount'])) ? Yii::t('messages', "Total Users for Campaign: ") . $peopleStatResult['contactGraph']['totalCount'] : '';
            $contactChartTitle = Yii::t('messages', "Campaigns Statistics by Media");
            $isContactNoData = (isset($peopleStatResult['contactGraph']['totalCount'])) ? ($peopleStatResult['contactGraph']['totalCount'] == 0) ? 1 : 0 : 1;

            /* Email Chart */
            $emailData = (isset($peopleStatResult['emailGraph']['graphData'])) ? $peopleStatResult['emailGraph']['graphData'] : array();
            $emailTotalCount = (isset($peopleStatResult['emailGraph']['totalCount'])) ? Yii::t('messages', "Total Email Users: ") . $peopleStatResult['emailGraph']['totalCount'] : '';
            $emailChartTitle = Yii::t('messages', "Statistics by Email");
            $isEmailNoData = (isset($peopleStatResult['emailGraph']['totalCount'])) ? ($peopleStatResult['emailGraph']['totalCount'] == 0) ? 1 : 0 : 1;


            return $this->render('people-statistic', array(
                'ageRangeOrder' => $ageRangeOrder,
                'ageData' => $ageData,
                'ageChartTitle' => $ageChartTitle,
                'total' => $totalCount,
                'tagList' => $tmpKeywords,
                'model' => $model,
                'keywordChartTitle' => $keywordChartTitle,
                'keywordOrder' => $keywordOrder,
                'keywordData' => $keywordData,
                'keywordTotalCount' => $keywordTotalCount,
                'zipOrder' => $zipOrder,
                'zipData' => $zipData,
                'zipTotalCount' => $zipTotalCount,
                'zipChartTitle' => $zipChartTitle,
                'cityOrder' => $cityOrder,
                'cityData' => $cityData,
                'cityTotalCount' => $cityTotalCount,
                'cityChartTitle' => $cityChartTitle,
                'genderOrder' => $genderOrder,
                'genderData' => $genderData,
                'genderTotalCount' => $genderTotalCount,
                'genderChartTitle' => $genderChartTitle,
                'typeData' => $typeData,
                'typeTotalCount' => $typeTotalCount,
                'typeChartTitle' => $typeChartTitle,
                'teamOrder' => $teamOrder,
                'teamData' => $teamData,
                'teamTotalCount' => $teamTotalCount,
                'teamChartTitle' => $teamChartTitle,
                'contactOrder' => $contactOrder,
                'contactData' => $contactData,
                'contactTotalCount' => $contactTotalCount,
                'contactChartTitle' => $contactChartTitle,
                'emailData' => $emailData,
                'emailTotalCount' => $emailTotalCount,
                'emailChartTitle' => $emailChartTitle,
                'isAgeNoData' => $isAgeNoData,
                'isKeywordNoData' => $isKeywordNoData,
                'isZipNoData' => $isZipNoData,
                'isCityNoData' => $isCityNoData,
                'isGenderNoData' => $isGenderNoData,
                'isTypeNoData' => $isTypeNoData,
                'isTeamNoData' => $isTeamNoData,
                'isContactNoData' => $isContactNoData,
                'isEmailNoData' => $isEmailNoData
            ));
        }
    }


    /**
     * Performs the AJAX validation.
     * @param User $model the model to be validated
     */
    private function performAjaxValidation($model, $formName, $customFields = array())
    {
        if (isset($_POST['ajax']) && $_POST['ajax'] === $formName) {
            $validateJson = \yii\widgets\ActiveForm::validate($model);
            $validateList = json_decode(Json::encode($validateJson), true);
            foreach ($customFields as $k => $customField) {
                $success = $customField->validate();

                if (!$success) {
                    $validateList['CustomValue_' . $customField->customFieldId . '_fieldValue'][] = $customField->errors['fieldValue'][0];
                }
            }
            echo Json::encode($validateList);
            Yii::$app->end();
        }
    }

    /**
     * Updates a particular model using AJAX.
     * If update is successful, data grid will update.
     * @param integer $id the ID of the model to be updated
     * @param null $check
     * @return string
     * @throws \Throwable
     * @throws \yii\base\ExitException
     * @throws \yii\db\StaleObjectException
     */
    public function actionUpdatePeopleAjax($id = null, $check = null)
    {
        $this->layout = '/advancedSearch';

        if (!is_null($check)) {
            $check = $_GET['check'];
        } else {
            $check = $_GET['check'];
        }

        if (null != $id) {
            $model = User::find()->where(['id' => $id])->one();
        } else {
            $model = User::find()->where(['id' => $_POST['User']['id']])->one();
        }

        switch ($check) {
            case 1: // load Keywords
                if (null != $id) {
                    //keywordEdit
                    $keyword_model = new Keyword();
                    $keywords = $keyword_model->getActiveKeywords();
                    $tmpKeywords = array();

                    foreach ($keywords as $behaviour => $behaviours) {
                        $tmpKeywords[$keyword_model->getBehaviourOptions($behaviour)] = $behaviours;
                    }
                    if ($model->keywords !== null) {
                        $model->keywords = explode(",", $model->keywords);
                    }
                    return $this->render('_popupEdit', array(
                        'model' => $model,
                        'keywords' => $tmpKeywords,
                        'label' => Yii::t('messages', 'Edit Keywords'),
                        'keywordEdit' => true,
                    ));

                    //userTypeEdit
                }
                break;
            case 2: // update Keyword
                parse_str($_POST['data'], $keywords);
                $model->scenario = 'people';
                $oldKeywords = $model->keywords;
                $keywordsList = (isset($keywords['User']['keywords'])) ? $keywords['User']['keywords'] : '';
                $model->keywords = (!empty($keywordsList)) ? implode(',', $keywordsList) : '';
                $model->attributes = array('id' => $model->id, 'keywords' => (!empty($keywordsList)) ? implode(',', $keywordsList) : '');
                if (!Keyword::isAutoKeywordsExist($oldKeywords, $model->keywords)) {
                    // Yii::$app->appLog->writeLog("Not allowed to delete auto keywords. People data:" . Json::encode(array('attributes' => $model->attributes)));
                    echo Json::encode(array('success' => false, 'msg' => Yii::t('messages', 'Not allowed to delete auto keyword(s)')));
                    Yii::$app->end();
                }
                if ($model->update(false)) {
                    // Yii::$app->appLog->writeLog("People grid updated. People data:" . Json::encode(array('attributes' => $model->attributes)));
                    $params = array(
                        'person' => $model->firstName,
                    );
                    Yii::$app->toolKit->addActivity(
                        Yii::$app->user->id,
                        Activity::ACT_UPDATE_PEOPLE,
                        Yii::$app->session->get('teamId'),
                        Json::encode($params)
                    );
                    echo Json::encode(array('success' => true, 'id' => $model->primaryKey));


                } else {
                    $errors = array_map(function ($v) {
                        return join(', ', $v);
                    }, $model->getErrors());
                    echo Json::encode(array('errors' => $errors));
                    // Yii::$app->appLog->writeLog("People grid update failed. People data:" . Json::encode(array('attributes' => $model->attributes)));

                }
                break;

            case 3: // Load UserType
                return $this->render('_popupEdit', array(
                    'model' => $model,
                    'label' => Yii::t('messages', 'Edit Category'),
                    'keywordEdit' => false,
                ));
                break;

            case 4: // update User type
                parse_str($_POST['data'], $Category);
                $model->userType = (isset($Category['User']['userType'])) ? $Category['User']['userType'] : '';
                $model->attributes = array('id' => $model->id, 'userType' => (isset($Category['User']['userType'])) ? $Category['User']['userType'] : '');
                if ($model->update(false)) {
                    // Yii::$app->appLog->writeLog("People grid updated. People data:" . Json::encode(array('attributes' => $model->attributes)));

                    $fbModule = FbProfile::findAll(array('userId' => $model->id));
                    if ($fbModule != null) {
                        Feed::updateFeedUserType($fbModule->fbUserId, $model->userType);
                    }

                    $twModule = TwProfile::findAll(array('userId' => $model->id));
                    if ($twModule != null) {
                        Feed::updateFeedUserType($twModule->twUserId, $model->userType);
                    }

                    $params = array(
                        'person' => $model->firstName,
                    );
                    Yii::$app->toolKit->addActivity(
                        Yii::$app->user->id,
                        Activity::ACT_UPDATE_PEOPLE,
                        Yii::$app->session->get('teamId'),
                        Json::encode($params)
                    );
                    echo Json::encode(array('success' => true, 'id' => $model->primaryKey));

                } else {
                    $errors = array_map(function ($v) {
                        return join(', ', $v);
                    }, $model->getErrors());
                    echo Json::encode(array('errors' => $errors));
                    // Yii::$app->appLog->writeLog("People grid update failed. People data:" . Json::encode(array('attributes' => $model->attributes)));
                }
        }

    }

    public function actionUpdatePeopleAjaxMap($id = null, $check = null)
    {
        $this->layout = '/advancedSearch';

        if (!is_null($check)) {
            $check = $_GET['check'];
        } else {
            $check = $_GET['check'];
        }

        if (null != $id) {
            $model = User::find()->where(['id' => $id])->one();
        } else {
            $model = User::find()->where(['id' => $_POST['User']['id']])->one();
        }

        switch ($check) {
            case 1: // load Keywords
                if (null != $id) {
                    //keywordEdit
                    $keywords = Keyword::getActiveKeywords();
                    $tmpKeywords = array();
                    foreach ($keywords as $behaviour => $behaviours) {
                        $tmpKeywords[Keyword::getBehaviourOptions($behaviour)] = $behaviours;
                    }
                    if ($model->keywords !== null) {
                        $model->keywords = explode(",", $model->keywords);
                    }
                    return $this->render('_popupEditMap', array(
                        'model' => $model,
                        'keywords' => $tmpKeywords,
                        'label' => Yii::t('messages', 'Edit Keywords'),
                        'keywordEdit' => true,
                    ));

                    //userTypeEdit
                }
                break;
            case 2: // update Keyword
                parse_str($_POST['data'], $keywords);
                $model->scenario = 'people';
                $oldKeywords = $model->keywords;
                $keywordsList = (isset($keywords['User']['keywords'])) ? $keywords['User']['keywords'] : '';
                $model->keywords = (!empty($keywordsList)) ? implode(',', $keywordsList) : '';
                $model->attributes = array('id' => $model->id, 'keywords' => (!empty($keywordsList)) ? implode(',', $keywordsList) : '');
                if (!Keyword::isAutoKeywordsExist($oldKeywords, $model->keywords)) {
                    Yii::$app->appLog->writeLog("Not allowed to delete auto keywords. People data:" . Json::encode(array('attributes' => $model->attributes)));
                    echo Json::encode(array('success' => false, 'msg' => Yii::t('messages', 'Not allowed to delete auto keyword(s)')));
                    Yii::$app->end();
                }
                if ($model->update(false)) {
                    Yii::$app->appLog->writeLog("People grid updated. People data:" . Json::encode(array('attributes' => $model->attributes)));
                    $params = array(
                        'person' => $model->firstName,
                    );
                    Yii::$app->toolKit->addActivity(
                        Yii::$app->user->id,
                        Activity::ACT_UPDATE_PEOPLE,
                        Yii::$app->session->get('teamId'),
                        Json::encode($params)
                    );
                    echo Json::encode(array('success' => true, 'id' => $model->primaryKey));


                } else {
                    $errors = array_map(function ($v) {
                        return join(', ', $v);
                    }, $model->getErrors());
                    echo Json::encode(array('errors' => $errors));
                    Yii::$app->appLog->writeLog("People grid update failed. People data:" . Json::encode(array('attributes' => $model->attributes)));

                }
                break;

            case 3: // Load UserType
                return $this->render('_popupEditMap', array(
                    'model' => $model,
                    'label' => Yii::t('messages', 'Edit Category'),
                    'keywordEdit' => false,
                ));
                break;

            case 4: // update User type
                parse_str($_POST['data'], $Category);
                $model->userType = (isset($Category['User']['userType'])) ? $Category['User']['userType'] : '';
                $model->attributes = array('id' => $model->id, 'userType' => (isset($Category['User']['userType'])) ? $Category['User']['userType'] : '');
                if ($model->update(false)) {
                    Yii::$app->appLog->writeLog("People grid updated. People data:" . Json::encode(array('attributes' => $model->attributes)));

                    $fbModule = FbProfile::findAll(array('userId' => $model->id));
                    if ($fbModule != null) {
                        Feed::updateFeedUserType($fbModule->fbUserId, $model->userType);
                    }

                    $twModule = TwProfile::findAll(array('userId' => $model->id));
                    if ($twModule != null) {
                        Feed::updateFeedUserType($twModule->twUserId, $model->userType);
                    }

                    $params = array(
                        'person' => $model->firstName,
                    );
                    Yii::$app->toolKit->addActivity(
                        Yii::$app->user->id,
                        Activity::ACT_UPDATE_PEOPLE,
                        Yii::$app->session->get('teamId'),
                        Json::encode($params)
                    );
                    echo Json::encode(array('success' => true, 'id' => $model->primaryKey));

                } else {
                    $errors = array_map(function ($v) {
                        return join(', ', $v);
                    }, $model->getErrors());
                    echo Json::encode(array('errors' => $errors));
                    Yii::$app->appLog->writeLog("People grid update failed. People data:" . Json::encode(array('attributes' => $model->attributes)));
                }
        }

    }

    /**
     * Show volunteers
     */
    public function actionVolunteers()
    {
        $model = new User();
        $model->scenario = 'searchVolunteers';

        $modelConfig = Configuration::findOne(Configuration::CURRENCY);
        $currencySymbol = Yii::$app->toolKit->getCurrencyInfo('SYMBOL', $modelConfig['value']);

        $model->loadDefaultValues();
        if (isset($_GET['User']))
            $model->attributes = $_GET['User'];

        return $this->render('volunteers', array(
            'model' => $model,
            'currencySymbol' => $currencySymbol
        ));
    }

    public function actionBulkInsert()
    {

        if (isset($_GET['status_file'])) {
            $src = Yii::$app->params['fileUpload']['error']['path'] . $_GET['status_file'];
            if (@file_exists($src)) {

                $path_parts = @pathinfo($src);

                $mime = 'text/csv';

                header('Content-Description: File Transfer');

                header('Content-Type: application/octet-stream');

                header('Content-Type: ' . $mime);

                header('Content-Disposition: attachment; filename=' . basename($src));

                header('Content-Transfer-Encoding: binary');

                header('Expires: 0');

                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

                header('Pragma: public');

                header('Content-Length: ' . filesize($src));

                ob_clean();

                flush();

                readfile($src);

            } else {

                header("HTTP/1.0 404 Not Found");

                exit();

            }

        }
        exit();
    }

    /**
     * @param $id
     * @return User|null
     * @throws NotFoundHttpException
     */
    protected function loadModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * Finds the StatSummary model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return StatSummary the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = StatSummary::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
