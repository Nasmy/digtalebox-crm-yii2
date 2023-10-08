<?php

namespace app\controllers;

use app\models\AuthItemSearch;
use app\models\BulkEditCustomField;
use app\models\BulkEditPreview;
use app\models\UserMapSearch;
use app\models\UserSearch;
use Yii;
use app\models\Keyword;
use yii\db\Exception;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use app\models\User;
use app\models\AdvanceSearch;
use app\models\SearchCriteria;
use app\models\CustomValue;
use app\models\CustomType;
use app\models\CustomField;
use app\models\Team;
use app\models\Configuration;
use app\models\Activity;
use app\models\Campaign;
use app\models\MsgBox;
use app\models\MapZone;
use app\models\OsmLog;
use app\models\BulkExport;
use app\models\CustomValueSearch;
use app\models\BulkEdit;
use app\models\BulkDelete;
use app\components\WebUser;
use app\components\ThresholdChecker;
use app\components\ToolKit;
use yii\db\Query;
use yii\helpers\Url;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use app\models\AdvanceBulkInsert;
use \app\controllers\WebUserController;
use yii\web\Response;

class AdvancedSearchController extends WebUserController
{

    /**
     * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
     * using two-column layout. See 'protected/views/layouts/column2.php'.
     */
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
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Show particular user's information on a map window
     */

    public function actionLoadMapInfoWindow($id)
    {
        $model = User::find()->where(['id' => $id])->one();
        echo $model->getMapInfoWindow();
    }

    /**
     * @param string $action
     * @return bool|Response
     * @throws BadRequestHttpException
     */
    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }


    /**
     * @return string
     */
    public function actionAccessDenied()
    {
        return $this->render('accessDenied', []);
    }

    /**
     * @description Advance search render admin page
     * @return string
     * @throws Exception
     */
    public function actionAdmin()
    {
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', '120'); //300 seconds = 5 minutes
        $isOwner = true;
        $model = new User();
        $model->loadDefaultValues();  // clear any default values
        $model->userType = '';
        $modelSearchCriteria = new SearchCriteria();
        
        //Custom field data
        $custom = new CustomValue();
        $customFields = $custom->getCustomData(CustomType::CF_PEOPLE, 0, CustomField::ACTION_CREATE, ToolKit::post('CustomValue'), CustomType::CF_SUB_PEOPLE_ADVANCED_SEARCH);
        Yii::$app->session->set('customFields', $customFields);

        // When edit the saved search check available search criteria Id
        if (isset($_GET['savedSearchId'])) {
            $modelSearchCriteria = SearchCriteria::find()->where(['id' => $_GET['savedSearchId']])->one();
            $model->attributes = $modelSearchCriteria['attributes'];
            $model->id = $modelSearchCriteria['id'];
            $model->userType = $modelSearchCriteria['userType'] == 0 ? '' : $modelSearchCriteria['userType'];
            $model->keywords = $modelSearchCriteria['keywords'] == null ? '' : explode(",", $modelSearchCriteria['keywords']);
            $model->keywords2 = $modelSearchCriteria['keywords2'] == null ? '' : explode(",", $modelSearchCriteria['keywords2']);
            $model->isDisplayKeywords2 = $modelSearchCriteria['isDisplayKeywords2'];
            $model->network = ($modelSearchCriteria['network'] == null) ? '' : explode(',', $modelSearchCriteria['network']);
            $model->teams = $modelSearchCriteria['teams'] == null ? '' : explode(",", $modelSearchCriteria['teams']);
            $customAttributes = CustomField::getCustomSearchFieldValues($modelSearchCriteria['id'], false);
            if (!empty($customFields) && !empty($customAttributes)) {   // if custom fields are deactivated or deleted, it cannot be preloaded
                $model->setCustomSearchCriteria($customAttributes, $customFields, false);
            }
            $isOwner = Yii::$app->user->getId() == $modelSearchCriteria['createdBy'] ? true : false;
        }

        //......Advanced search filter modifications 2 ..............................................

        if (isset($_POST['filter'])) {
            $filter = $_POST['filter'];
            Yii::$app->session->set('filter', $_POST['filter']);
            Yii::$app->session->remove('ajaxupdate');
            if (Yii::$app->session->get('User') != '') {
                $filter = Yii::$app->session->get('filter');
                $model->attributes = Yii::$app->session->get('User');
            }
        } else {
            if (isset($_GET['unsetFilter'])) {
                Yii::$app->session->remove('filter');
            }
            if (Yii::$app->session->get('filter') && (Yii::$app->session->get('User'))) {
                $filter = Yii::$app->session->get('filter');
                $model->attributes = Yii::$app->session->get('User');
            } elseif (Yii::$app->session->get('filter')) {
                $filter = Yii::$app->session->get('filter');
            } else {
                $filter = '';
                Yii::$app->session->remove('filter');
            }
        }
        $items = array();
        $searchCriteriaModel = SearchCriteria::find()->where(['critetiaType' => SearchCriteria::ADVANCED])->orderBy(['criteriaName' => SORT_ASC])->asArray()->all();
        foreach ($searchCriteriaModel as $key => $value) {
            $items[$value['id']] = $value['criteriaName'];
        }
        $searchCriteria = $items;
        $searchCriteria = array('' => Yii::t('messages', '- Saved Searches -')) + $searchCriteria;
        $keyword_model = new Keyword();
        $tagList = array();
        $keywords = $keyword_model->getActiveKeywords();
        $tmpKeywords = array();

        foreach ($keywords as $behaviour => $behaviours) {
            foreach ($behaviours as $key => $keyword) {
                switch ($behaviour) {
                    case Keyword::KEY_AUTO:
                        $tagList[] = array('id' => $key, 'text' => $keyword, 'disabled' => true);
                        break;

                    default:
                        $tagList[] = array('id' => $key, 'text' => $keyword);
                        break;
                };
            }
            $tmpKeywords[$keyword_model->getBehaviourOptions($behaviour)] = $behaviours;
        }

        $team_model = new Team();
        $teams = $team_model->getTeamsByLogedUser();
        $modelConfig = Configuration::find()->where(['key' => Configuration::EXCLUDE_PERSONAL_FB_CONTACTS])->one();
        Yii::$app->toolKit->addActivity(Yii::$app->user->getId(), Activity::ACT_ADVAN_SEARCH_PEOPLE);
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->searchPeople(Yii::$app->request->queryParams);
        $searchModel->loadDefaultValues();  // clear any default values
        return $this->render('admin', array(
            'model' => $model,
            'tagList' => $tagList,
            'keywords' => $tmpKeywords,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'searchCriteria' => $searchCriteria,
            'modelSearchCriteria' => $modelSearchCriteria,
            'teams' => $teams,
            'isOwner' => $isOwner,
            'modelConfig' => $modelConfig,
            'customFields' => $customFields,
            'filters' => $filter,
        ));

        // ....End of Advanced search filter modifications 2 ................................

    }

    /**
     * To send mail message, validate advanced search critetia.
     */
    public function actionSendMail()
    {

        if ('' != $_POST['User']['id']) {
            $model = SearchCriteria::find()->where(['id' => $_POST['User']['id']])->one();
            if ($model != null) {
                Yii::$app->appLog->writeLog("People advance search criteria found");
                echo Json::encode(array('status' => 'success', 'url' => Url::to(['search-criteria/show-messages-dialog', 'id' => $model->id, 'type' => Campaign::CAMP_TYPE_EMAIL])));
            } else {
                Yii::$app->appLog->writeLog("Selected criteria not available");
                echo Json::encode(array('status' => 'error',
                    'message' => ToolKit::setAjaxFlash('error', Yii::t('messages', "Please select saved search"), true)));
            }
        } else {
            echo Json::encode(array('status' => 'error',
                'message' => ToolKit::setAjaxFlash('error', Yii::t('messages', "Please select saved search"), true)));
        }

    }


    /**
     * @description  Returns the data model based on the primary key given in the GET variable.
     * - If the data model is not found, an HTTP exception will be raised.
     * @return string
     * @throws Exception
     */

    public function actionGridUpdate()
    {
        $layout = 'dialog';
        $request = Yii::$app->request;
        if ($request->post()) {
            $user_data = $request->post('data');
            Yii::$app->session->set('user', $user_data);
            $filters = $request->post('filters');
            Yii::$app->session->set('filters', $filters);
            $criteriaId = $request->post('criteriaId');
            Yii::$app->session->set('criteriaId', $criteriaId);
        } elseif (Yii::$app->session->get('filter')) {
            $filters = Yii::$app->session->get('filters');
            $user_data = Yii::$app->session->get('user');
            $criteriaId = Yii::$app->session->get('criteriaId');
        } else {
            $filters = array();
            $user_data = array();
            $criteriaId = array();
        }

        $fullOptions = '';
        $model = new User();
        $model->loadDefaultValues();  // clear any default values

        $custom = new CustomValue();
        $customFields = $custom->getCustomData(CustomType::CF_PEOPLE, 0, CustomField::ACTION_CREATE, ToolKit::post('CustomValue'), CustomType::CF_SUB_PEOPLE_ADVANCED_SEARCH);

        //......Advanced search filter modifications 2 ..............................................
        if (isset($filters)) {
            $filter = $filters;
            Yii::$app->session->set('filter', $filters);
            Yii::$app->session->remove('ajaxupdate');
            if (Yii::$app->session->get('User') != '') {
                $filter = Yii::$app->session->get('filter');
                $model->attributes = Yii::$app->session->get('User');
            }
        } else {
            if (Yii::$app->session->get('filter') != '' && Yii::$app->session->get('User') != '') {
                $filter = Yii::$app->session->get('filter');
                $model->attributes = Yii::$app->session->get('User');
            } else if (Yii::$app->session->get('filter') != '' && Yii::$app->session->get('User') != '') {
                $filter = Yii::$app->session->get('filter');
            } else {
                $filter = '';
            }
        }

        $searchModel = new UserSearch();
        $dataProvider = $searchModel->searchPeople($user_data);
        $searchModel->loadDefaultValues();  // clear any default values
        return $this->renderAjax('_grid', array('model' => $model, 'mapView' => false, 'customFields' => $customFields, 'filters' => $filter, 'gridId' => 'people-grid', 'filtersFields' => $fullOptions, 'dataProvider' => $dataProvider));
    }

    /**
     * @description Ajax update map locations according to Search
     * @return string
     */
    public function actionGridUpdateMap()
    {
        $layout = 'dialog';
        $sort = '';
        if (isset($_GET['sort'])) {
            $sort = $_GET['sort'];
        }
        $request = Yii::$app->request;
        if ($request->post()) {
            $userSearchData = [];
            $user_data = $request->post('data');
            parse_str($user_data, $userSearchData);
        } else {
            $user_data = array();
        }

        $fullOptions = '';
        $isOwner = true;
        $model = new User();
        $model->loadDefaultValues();  // clear any default values
        $searchModel = new UserMapSearch();
        $query = $searchModel->searchPeopleMap($userSearchData);
        $order = SORT_DESC;
        $orderBy = "createdAt";
        if ($sort) {
            $sort_order = explode('-', $sort);
            if (isset($sort_order[1])) {
                $order = SORT_ASC;
                $orderBy = $sort_order[0];
            }

        }
        $order = [$orderBy => $order];

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 10, 'route' => 'advanced-search/data-map'],
            'sort' => [
                'defaultOrder' => $order,
                'route' => 'advanced-search/data-map'
            ],

        ]);

        $searchModel->loadDefaultValues();  // clear any default values
        return $this->renderAjax('_gridmap', array('model' => $model, 'mapView' => false, 'gridId' => 'people-map-grid', 'filtersFields' => $fullOptions, 'dataProvider' => $dataProvider));
    }

    /**
     * @description Ajax update map view according to Search
     * @return string
     */
    public function actionGridUpdateMapView()
    {

        $request = Yii::$app->request;
        if ($request->post()) {
            $userSearchData = [];
            $user_data = $request->post('data');
            parse_str($user_data, $userSearchData);
        } else {
            $user_data = array();
        }
        $modelMapZone = new MapZone();
        $modelMapZone->loadDefaultValues(false);  // clear any default values
        $thresholdChecker = new ThresholdChecker(Yii::$app->session->get('packageType'));
        $maxLimit = $thresholdChecker->getRemainingCount(ThresholdChecker::GEO_TAGGING_LIMIT);
        $OsmLog = new OsmLog();
        $osmCanProceed = $OsmLog->checkLimit($maxLimit);
        $localtion = explode(",", Yii::$app->params['defLongLat']);
        $lat = $localtion[0];
        $lon = $localtion[1];
        $searchModel = new UserMapSearch();
        $query = $searchModel->searchPeopleMap($userSearchData);
        $markersLongLat = array();
        $markersLongLat = $searchModel->searchLocationData($user_data);
        return $this->renderAjax('_gridmapview', array('modelMapZone' => $modelMapZone, 'lat' => $lat, 'lon' => $lon, 'osmMaxLimit' => $maxLimit, 'osmCanProceed' => $osmCanProceed, 'gridId' => 'people-grid', 'markersLongLat' => $markersLongLat,));
    }


    /**
     * @description Load data according to the save search criteria id
     * @return string
     * @throws Exception
     */
    public function actionShowCriteria()
    {
        $layout = 'dialog';
        if (!empty($_REQUEST['criteriaId'])) {
            $modelSearchCriteria = SearchCriteria::find()->where(['id' => $_REQUEST['criteriaId']])->all();
            $isOwner = true;
            $criteriaName = '';
            $model = new User();
            $model->loadDefaultValues();  // clear any default values
            $model->isSysUser = '0';
            //Custom field data
            $CustomValue = new CustomValue();
            $customFields = $CustomValue->getCustomData(CustomType::CF_PEOPLE, 0, CustomField::ACTION_CREATE, ToolKit::post('CustomValue'), CustomType::CF_SUB_PEOPLE_ADVANCED_SEARCH);
            if ($modelSearchCriteria) {
                $modelSearchCriteria = $modelSearchCriteria[0];
                $model->setAttributes($modelSearchCriteria['attributes'], false);
                $model->teams = $modelSearchCriteria['teams'];
                $model->excludeFbPersonalContacts = $modelSearchCriteria['excludeFbPersonalContacts'];
                $model->age = $modelSearchCriteria['age'];
                $model->fullAddress = $modelSearchCriteria['fullAddress'];
                $model->searchType = $modelSearchCriteria['searchType'];
                $criteriaName = $modelSearchCriteria['criteriaName'];

                if ($model->keywords != null) {
                    $model->keywords = explode(",", $modelSearchCriteria['keywords']);
                }

                if ($modelSearchCriteria['keywords2'] != null) {
                    $model->keywords2 = explode(",", $modelSearchCriteria['keywords2']);
                }

                if ($modelSearchCriteria['isDisplayKeywords2'] != null) {
                    $model->isDisplayKeywords2 = $modelSearchCriteria['isDisplayKeywords2'];
                }

                if ($modelSearchCriteria['searchType2'] != null) {
                    $model->searchType2 = $modelSearchCriteria['searchType2'];
                }

                if ($modelSearchCriteria['mapZone'] != null) {
                    $model->mapZone = $modelSearchCriteria['mapZone'];
                }

                if ($modelSearchCriteria['keywordsExclude'] != null) {
                    $model->keywordsExclude = explode(",", $modelSearchCriteria['keywordsExclude']);
                }

                if ($modelSearchCriteria['keywordsExclude2'] != null) {
                    $model->keywordsExclude2 = explode(",", $modelSearchCriteria['keywordsExclude2']);
                }

                if ($model->teams != null) {
                    $model->teams = explode(",", $modelSearchCriteria['teams']);
                }

                if ($modelSearchCriteria['network'] != null) {
                    $model->network = explode(',', $modelSearchCriteria['network']);
                }


                $CustomField = new CustomField();
                $customAttributes = $CustomField->getCustomSearchFieldValues($modelSearchCriteria['id'], false);
                if (!empty($customFields) && !empty($customAttributes)) { // if custom fields are deactivated or deleted, it cannot be preloaded
                    $model->setCustomSearchCriteria($customAttributes, $customFields, false);
                }
                $isOwner = Yii::$app->user->getId() == $modelSearchCriteria['createdBy'] ? true : false;
                $model->gender = $modelSearchCriteria['gender'];
            }
            $keyword_model = new Keyword();
            $tagList = array();
            $keywords = $keyword_model->getActiveKeywords();
            $tmpKeywords = array();
            if ($keywords) {
                foreach ($keywords as $behaviour => $behaviours) {
                    foreach ($behaviours as $key => $keyword) {
                        switch ($behaviour) {
                            case Keyword::KEY_AUTO:
                                $tagList[] = array('id' => $key, 'text' => $keyword, 'disabled' => true);
                                break;

                            default:
                                $tagList[] = array('id' => $key, 'text' => $keyword);
                                break;
                        };
                    }
                    $tmpKeywords[$keyword_model->getBehaviourOptions($behaviour)] = $behaviours;
                }

            }
        } else {
            $model = new User();
            $model->loadDefaultValues();  // clear any default values
            $model->isSysUser = '0';
            $model->teams = '';
            $model->age = '';
            $model->gender = '';
            $model->userType = '';
            $model->fullAddress = '';
            $model->searchType = '';
            $criteriaName = '';
            $model->keywords = '';
            $model->keywords2 = '';
            $model->searchType2 = '';
            $model->mapZone = '';
            $model->keywordsExclude = '';
            $model->keywordsExclude2 = '';
            $model->network = '';
            $tmpKeywords = '';
            $isOwner = true;
            $customFields = array();
        }


        $Team = new Team();
        $teams = $Team->getTeamsByLogedUser();
        $modelConfig = Configuration::find()->where(['key' => Configuration::EXCLUDE_PERSONAL_FB_CONTACTS])->all();
        return $this->renderAjax('_search', array('model' => $model,
            'tagList' => $tmpKeywords,
            'data' => $criteriaName,
            'teams' => $teams,
            'isOwner' => $isOwner,
            'modelConfig' => $modelConfig[0],
            'customFields' => $customFields,
        ));
    }

    /**
     * @description Show all users in a map who have a physical address
     * @return string
     */
    public function actionDataMap()
    {
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', '120'); //300 seconds = 5 minutes
        $isOwner = true;
        $model = new User();
        $modelMapZone = new MapZone();
        $modelMapZone->loadDefaultValues(false);  // clear any default values
        $thresholdChecker = new ThresholdChecker(Yii::$app->session->get('packageType'));
        $maxLimit = $thresholdChecker->getRemainingCount(ThresholdChecker::GEO_TAGGING_LIMIT);

        $OsmLog = new OsmLog();
        $osmCanProceed = $OsmLog->checkLimit($maxLimit);
        if (isset($_POST['User'])) {
            $model->attributes = $_POST['User'];

            if (!isset($_POST['User']['keywords'])) {
                $model->keywords = '';
            }

            if (!isset($_POST['User']['teams'])) {
                $model->teams = '';
            } else {
                $model->teams = $_POST['User']['teams'];
            }
        }

        Yii::$app->toolKit->addActivity(Yii::$app->user->getId(), Activity::ACT_ADVAN_SEARCH_USER_MAP);

        // Prepare markers with longLat
        $tagList = array();
        $keyword_model = new Keyword();
        $keywords = $keyword_model->getActiveKeywords();
        $tmpKeywords = array();
        foreach ($keywords as $behaviour => $behaviours) {
            foreach ($behaviours as $key => $keyword) {
                switch ($behaviour) {
                    case Keyword::KEY_AUTO:
                        $tagList[] = array('id' => $key, 'text' => $keyword, 'disabled' => true);
                        break;

                    default:
                        $tagList[] = array('id' => $key, 'text' => $keyword);
                        break;
                };
            }
            $tmpKeywords[$keyword_model->getBehaviourOptions($behaviour)] = $behaviours;
        }
        $team_model = new Team();
        $teams = $team_model->getTeamsByLogedUser();
        $modelConfig = Configuration::find()->where(['key' => Configuration::EXCLUDE_PERSONAL_FB_CONTACTS]);
        // Prepare initial lon lat to zoom the map
        $localtion = explode(",", Yii::$app->params['defLongLat']);
        $lat = $localtion[0];
        $lon = $localtion[1];

        $searchModel = new UserMapSearch();
        $query = $searchModel->searchPeopleMap(Yii::$app->request->queryParams);
        $searchModel->loadDefaultValues();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 10, 'route' => 'advanced-search/data-map'],
            'sort' => [
                'defaultOrder' => [
                    'createdAt' => SORT_DESC,
                ]
            ],
        ]);

        $model->loadDefaultValues(true);
        $searchModel->userType = '';
        $markersLongLat = array();
        if (!empty(Yii::$app->request->queryParams)) {
            $markersLongLat = $searchModel->searchLocationData(Yii::$app->request->queryParams);
        }

        if (isset($_GET['User']))
            $model->attributes = $_GET['User'];


        return $this->render('dataMap', array(
            'model' => $model,
            'markersLongLat' => Json::encode($markersLongLat),
            'tagList' => $tagList,
            'keywords' => $tmpKeywords,
            'teams' => $teams,
            'isOwner' => $isOwner,
            'modelConfig' => $modelConfig,
            'dataProvider' => $dataProvider,
            'modelMapZone' => $modelMapZone,
            'lat' => $lat,
            'lon' => $lon,
            'osmMaxLimit' => $maxLimit,
            'osmCanProceed' => $osmCanProceed
        ));
    }

    /*
    * show the selected map Zone
    */
    public function actionMapZoneView($id)
    {
        $this->layout = 'dialog';
        $params = array();
        if($id != '') {
            $params['User']['mapZone'] = $id;
        }
        $mapZone = MapZone::find()->where(['id' => $id])->one();
        $data = $mapZone->attributes;
        $mapFilters = Json::encode($data);
        $lonLatData = Yii::$app->toolKit->getDefLonLat();
        $lat = $lonLatData['lat'];
        $lon = $lonLatData['lon'];
        $model = new UserSearch();
        $dataProvider = $model->searchPeople($params);
        $thresholdChecker = new ThresholdChecker(Yii::$app->session->get('packageType'));
        $maxLimit = $thresholdChecker->getRemainingCount(ThresholdChecker::GEO_TAGGING_LIMIT);
        $osmCanProceed = OsmLog::checkLimit($maxLimit);
        $zoneDataArr = array();
        if (null != $mapZone) {
            $zoneDataArr[] = json_decode($mapZone->zoneLongLat);
        }

        // Prepare initial lon lat to zoom the map
        if (!empty($zoneDataArr)) {
            $lat = $zoneDataArr[0][0]->coordinates[0][0];
            $lon = $zoneDataArr[0][0]->coordinates[0][1];
        } else {
            $lonLatData = ToolKit::getDefLonLat();
            $lat = $lonLatData['lat'];
            $lon = $lonLatData['lon'];
        }

        $turfArr = json_decode($mapZone['zoneLongLat'], true);
        $cordinates = $turfArr[0]['coordinates'];
        $polygonSet = $mapZone->createPolygon($cordinates);
        // Prepare markers with longLat
        $markersLongLat = array();
        $markersLongLat = User::find()->select(['id', 'SUBSTRING_INDEX(`longLat`, ",", 1) as `longitude`', 'SUBSTRING_INDEX(`longLat`, ",", -1) as `latitude`'])->where("ST_CONTAINS(ST_GEOMFROMTEXT('$polygonSet'), geoPoint)")->asArray()->all();


        return $this->render('zoneMap', array(
            'model' => $mapZone,
            'teamZoneData' => Json::encode($zoneDataArr),
            'long' => $lon,
            'lat' => $lat,
            'mapFilters' => $mapFilters,
            'markersLongLat' => Json::encode($markersLongLat),
            'osmMaxLimit' => $maxLimit,
            'osmCanProceed' => $osmCanProceed,
            'dataProvider' => $dataProvider
        ));
    }

    /*
     * function to update the map zone
     */

    public function actionMapZoneUpdate($id)
    {
        $model = MapZone::find()->where(['id' => $id])->one();
        $model->scenario = 'update';
        $thresholdChecker = new ThresholdChecker(Yii::$app->session->get('packageType'));
        $maxLimit = $thresholdChecker->getRemainingCount(ThresholdChecker::GEO_TAGGING_LIMIT);
        $osmCanProceed = OsmLog::checkLimit($maxLimit);
        if (isset($_POST['MapZone'])) {
            $model->attributes = $_POST['MapZone'];
            if ($model->validate()) {
                try {
                    if ($model->save()) {
                        Yii::$app->appLog->writeLog("Map Zone updated.Data:" . Json::encode($model->attributes));
                        Yii::$app->session->setFlash('success', Yii::t('messages', 'Zone updated'));
                    } else {
                        Yii::$app->session->setFlash('error', Yii::t('messages', 'Zone update failed'));
                        Yii::$app->appLog->writeLog("Map Zone update failed.Data:" . Json::encode($model->attributes));
                    }
                } catch (Exception $e) {
                    Yii::$app->session->setFlash('error', Yii::t('messages', 'Zone update failed'));
                    Yii::$app->appLog->writeLog("Map Zone update failed.Data:" . Json::encode($model->attributes) . ", Errors:{$e->getMessage()}");
                }
                $teamZoneData = $model->zoneLongLat;
            } else {
                Yii::$app->appLog->writeLog("Map Zone update failed.Validation errors:" . Json::encode($model->errors));
            }
        } else {
            $teamZoneData = $model->zoneLongLat;
        }

        //Prepare lon lat to zoom the map when loading

        $zoneDataArr = json_decode($model->zoneLongLat);
        if (!empty($zoneDataArr)) {
            $lat = $zoneDataArr[0]->coordinates[0][0];
            $lon = $zoneDataArr[0]->coordinates[0][1];
        } else {
            $localtion = explode(",", Yii::$app->params['defLongLat']);
            $lat = $localtion[0];
            $lon = $localtion[1];
        }
        return $this->render('MapZonesUpdate', array(
            'model' => $model,
            'teamZoneData' => $teamZoneData,
            'long' => $lon,
            'lat' => $lat,
            'attributeLabels' => $model->attributeLabels(),
            'osmMaxLimit' => $maxLimit,
            'osmCanProceed' => $osmCanProceed
        ));
    }

    /*
     * Function to delete the mapzone if it not used in the campaign
     */

    public function actionMapZoneDelete($id)
    {
        $model = MapZone::find()->where(['id' => $id])->one();
        $name = $model->title;
        $savedSearchResult = SearchCriteria::find()->where(['mapZone' => $id])->one();
        if (null == $savedSearchResult) {
            $modelCamp = null;
            $modelMsgBox = null;
        } else {
            $savedSearchId = $savedSearchResult->id;
            $modelCamp = Campaign::find()->where(['searchCriteriaId' => $savedSearchId])->one();
            $modelMsgBox = MsgBox::find()->where(['criteriaId' => $savedSearchId])->andWhere(['status' => !MsgBox::MSG_STATUS_DELETED])->one();
        }
        if ((null == $modelCamp && null == $modelMsgBox) || null == $savedSearchResult) {
            if ($model->delete()) {
                if (null != $savedSearchResult) {
                    $savedSearchResult->mapZone = null;
                    $savedSearchResult->update();
                }
                Yii::$app->session->setFlash('success', Yii::t('messages', 'Map Zone deleted.'));
                Yii::$app->appLog->writeLog("Map Zone deleted.Name:{$name}");
            } else {
                Yii::$app->session->setFlash('error', Yii::t('messages', 'Map Zone delete failed.'));
                Yii::$app->appLog->writeLog("Map Zone delete failed.Name:{$name}");
            }
        } else {
            Yii::$app->session->setFlash('error', Yii::t('messages', 'Map Zone could not be deleted. Already being assigned for a Campaign or sending messages'));
            Yii::$app->appLog->writeLog("Map Zone delete failed. Already assigned for a campaign or sending messages.Name:{$name}");
        }
        $this->redirect(array('all-map-zones'));
    }

    /**
     * View all map turf cuts.
     * @param integer $id the ID of the model to be updated
     */
    public function actionAllMapZones()
    {
        $mapFilters = '';
        $modelZone = new MapZone();
        $modelZone->loadDefaultValues(false);  // clear any default values
        $model = MapZone::find()->orderBy(['title' => SORT_DESC])->all();
        $zoneDataArr = array();
        $thresholdChecker = new ThresholdChecker(Yii::$app->session->get('packageType'));
        $maxLimit = $thresholdChecker->getRemainingCount(ThresholdChecker::GEO_TAGGING_LIMIT);
        $OsmLog = new OsmLog();
        $osmCanProceed = $OsmLog->checkLimit($maxLimit);
        if (null != $model) {
            foreach ($model as $mapZone) {
                $zoneDataArr[] = json_decode($mapZone->zoneLongLat);
            }
        }
        // Prepare initial lon lat to zoom the map
        if (!empty($zoneDataArr)) {
            $lat = $zoneDataArr[0][0]->coordinates[0][0];
            $lon = $zoneDataArr[0][0]->coordinates[0][1];
        } else {
            $lonLatData = Yii::$app->toolKit->getDefLonLat();
            $lat = $lonLatData['lat'];
            $lon = $lonLatData['lon'];
        }

        return $this->render('zoneMap', array(
            'model' => $modelZone,
            'teamZoneData' => Json::encode($zoneDataArr),
            'long' => $lon,
            'lat' => $lat,
            'mapFilters' => $mapFilters,
            'markersLongLat' => Json::encode(array()),
            'osmCanProceed' => $osmCanProceed,
            'osmMaxLimit' => $maxLimit,
        ));
    }

    public function actionDelete($id)
    {
        $model = BulkExport::find()->where(['id' => $id])->one();
        if ($model->delete()) {
            ToolKit::setAjaxFlash('success', Yii::t('messages', 'Contacts Export deleted.'), true);
        } else {
            ToolKit::setAjaxFlash('success', Yii::t('messages', 'Contacts Export delete failed.'), true);
        }
    }

    public function actionUpdate($id)
    {
        $this->layout = 'dialog';
        $model = BulkExport::find()->where(['id' => $id])->one();
        switch ($model->exportType) {
            case BulkExport::ADDRESS_EXPORT_TYPE:
                $runCommand = BulkExport::CRON_COMMAND_ADDRESS_EXPORT;
                break;
            default:
                $runCommand = BulkExport::CRON_COMMAND;
                break;
        }
        if ($model->processId) {
            BulkExport::runCommand($runCommand, $id, $model->processId); //Process Kill
        }
        yii::$app->db->createCommand("Update BulkExport SET status = " . BulkExport::CANCELED . " WHERE id = {$id}")->execute();
        Yii::$app->session->setFlash('success', Yii::t('messages', 'Contacts export stopped.'));
        return $this->redirect(array('advanced-search/bulk-export-view/'));
    }

    /**
     * delete the bulk edit record when cancelling
     */
    public function actionCancelBulkEdit($id)
    {

        $model = BulkEdit::find()->where(['id' => $id])->one();
        if (!is_null($model)) {
            // $searchModel = SearchCriteria::find()->where(['id' => $model->searchCriteriaId])->one();
            // CustomValueSearch::deleteAll('relatedId=:relatedId', [':relatedId' => $searchModel->id]);
            // $searchModel->delete();
            $model->delete();
        }
        return $this->redirect(['advanced-search/admin']);
    }

    /**
     * Bulk delete a list of records
     */
    public function actionBulkDelete()
    {

        $model = new SearchCriteria();
        $session_criteria_id = Yii::$app->session->get('criteriaId');
        if (!empty($session_criteria_id)) {
            $model = SearchCriteria::find()->where(['id' => $session_criteria_id])->one();
        }
        //if already in progress or pending, should display error message via ajax
        $modelProgress = BulkDelete::find()->where('status=:status', [':status' => BulkDelete::IN_PROGRESS])->one();
        $modelPending = BulkDelete::find()->where('status=:status', [':status' => BulkDelete::PENDING])->one();
        //if already in progress or pending, should display error message via ajax
        if (null !== $modelProgress || null !== $modelPending) {
            $id = !is_null($modelProgress) ? $modelProgress->id : $modelPending->id;
            $link = Html::a('here', ['advanced-search/stop-bulk-delete/' . $id]);
            $message = Yii::t('messages', 'Previous bulk delete is still on progress. Please try again later or click {here} to stop and start fresh.', [
                'here' => $link
            ]);
            echo Json::encode(array('status' => 'error',
                'message' => ToolKit::setAjaxFlash('error', $message, true)));
            exit;
        }

        if (isset($_POST['User'])) {
            $user_data = $_POST['User'];
            $model->attributes = $_POST['User'];
            if ($model->keywordsExclude != null) {
                $model->keywordsExclude = implode(",", $model->keywordsExclude);
            }
            if ($model->keywordsExclude2 != null) {
                $model->keywordsExclude2 = implode(",", $model->keywordsExclude2);
            }
            if ($model->keywords2 != null) {
                $model->keywords2 = implode(",", $model->keywords2);
            }
            if (empty($session_criteria_id)) {
                $model->criteriaName = 'Saved search-' . time();
                $model->critetiaType = SearchCriteria::BULK;
            }
            $model->date = User::convertSystemTime();
            if (!empty($model->keywords)) {
                $model->keywords = implode(',', $model->keywords);
            }
            if (empty($_POST['User']['userType'])) {
                $model->userType = 0;
            }
            if (!empty($model->teams)) {
                $model->teams = implode(',', $model->teams);
            }
            if (!empty($model->network)) {
                $model->network = implode(',', $model->network);
            }
            $attributes = $model->attributes;
            if ($model->save()) {
                $data = ToolKit::post('CustomValue');
                if ($data) {
                    $this->customValueUpdate($data, $model->id);
                }

                $searchModel = new UserSearch();
                $criteria = $searchModel->searchPeople($user_data);
                $userCount = $criteria->getTotalCount();

                // if no records found, show error message
                if (0 == $userCount) {
                    CustomValueSearch::deleteAll('relatedId=:relatedId', [':relatedId' => $model->id]);
                    $model->delete();
                    echo Json::encode(array('status' => 'error',
                        'message' => ToolKit::setAjaxFlash('error', Yii::t('messages', "Search did not match any results. Please try again."), true)));
                    exit;
                }

                $bulkDeleteModel = new BulkDelete();
                $bulkDeleteModel->searchCriteriaId = (!empty($session_criteria_id)) ? $session_criteria_id : $model->id;
                $bulkDeleteModel->status = BulkEdit::PENDING;
                $bulkDeleteModel->createdAt = date('Y-m-d H:i:s');
                $bulkDeleteModel->createdBy = Yii::$app->getUser()->getId();
                $bulkDeleteModel->totalRecords = $userCount;
                $bulkDeleteModel->save(false);

                $customAttributes = CustomField::getCustomSearchFieldValues($model->id);
                Yii::$app->appLog->writeLog("Bulk delete criteria created. Saved data:" . Json::encode(array_merge($attributes, $customAttributes)));

                Yii::$app->appLog->writeLog("bulk delete CRON started: " . BulkDelete::CRON_COMMAND . " bulk id:" . $bulkDeleteModel->id);
                AdvanceBulkInsert::runCommand(BulkDelete::CRON_COMMAND, $bulkDeleteModel->id); //first

                echo Json::encode(array('status' => 'success',
                    'message' => ToolKit::setAjaxFlash('success', Yii::t('messages', "Bulk delete started. You will be informed by email upon the completion."), true)));
                exit;

            } else {
                $errors = current($model->getErrors());
                Yii::$app->appLog->writeLog("Bulk delete criteria save failed. Error:" . Json::encode($errors[0]));
                $errorMessage = Yii::t('messages', "Bulk delete criteria save failed {errors}. ", [
                    'errors' => $errors[0]
                ]);
                echo Json::encode(array('status' => 'error',
                    'message' => ToolKit::setAjaxFlash('error', $errorMessage, true)));
            }

        } else {
            Yii::$app->appLog->writeLog("Bulk delete criteria not found.");
            echo Json::encode(array('status' => 'error',
                'message' => ToolKit::setAjaxFlash('error', Yii::t('messages', "Bulk delete criteria not found."), true)));
        }

    }

    /**
     * stop a bulk delete
     */
    public function actionStopBulkDelete($id)
    {
        $model = BulkDelete::find()->where(['id' => $id])->one();
        if ($model->status == BulkEdit::IN_PROGRESS || $model->status == BulkEdit::PENDING) {
            $model->status = BulkEdit::FINISHED;
            $model->save(false);
            Yii::$app->session->setFlash('success', Yii::t('messages', 'Previous bulk delete stopped successfully'));
            $fromEmail = Yii::$app->params['smtp']['senderEmail'];
            $fromName = Yii::$app->params['smtp']['senderLabel'];
            $subject = Yii::t('messages', "Bulk Edit");
            $message = $this->renderPartial('@app/views/emailTemplates/notificationTemplate', array(
                'content' => Yii::t('messages', 'Your bulk delete process has been stopped'), $model));
            $emails = User::getModeratorEmails();
            if ($emails) {
                foreach ($emails as $record) {
                    if (Yii::$app->toolKit->sendEmail(array($record), $subject, $message, null, null, $fromName, $fromEmail)) {
                        Yii::$app->appLog->writeLog("Bulk delete notification email sent.");
                    }
                }
            }
            return $this->redirect(array('advanced-search/admin'));
        }
    }

    /**
     * Bulk edit a list of records
     */
    public function actionBulkEdit()
    {
        $model = new SearchCriteria();
        $session_criteria_id = Yii::$app->session->get('criteriaId');
        if (!empty($session_criteria_id)) {
            $model = SearchCriteria::find()->where(['id' => $session_criteria_id])->one();
        }
        $modelProgress = BulkEdit::find()->where('status=:status', [':status' => BulkEdit::IN_PROGRESS])->one();
        $modelPending = BulkEdit::find()->where('status=:status', [':status' => BulkEdit::PENDING])->one();

        //if already in progress or pending, should display error message via ajax
        if (null !== $modelProgress || null !== $modelPending) {
            $id = !is_null($modelProgress) ? $modelProgress->id : $modelPending->id;
            $hereLink = Html::a('here', ['advanced-search/stop-bulk-edit/' . $id]);
            $errorMessage = Yii::t('messages', 'Previous bulk edit is still on progress. Please try again later or click {here} to stop and start fresh.', [
                'here' => $hereLink
            ]);

            echo Json::encode(array('status' => 'error',
                'message' => ToolKit::setAjaxFlash('error', $errorMessage, true)));
            exit();
        }

        if (isset($_POST['User'])) {
            $user_data = $_POST['User'];
            $model->attributes = $_POST['User'];
            if ($model->keywordsExclude != null) {
                $model->keywordsExclude = implode(",", $model->keywordsExclude);
            }
            if ($model->keywordsExclude2 != null) {
                $model->keywordsExclude2 = implode(",", $model->keywordsExclude2);
            }
            if ($model->keywords2 != null) {
                $model->keywords2 = implode(",", $model->keywords2);
            }

            if (!empty($model->network)) {
                $model->network = implode(',', $model->network);
            }
            if (empty($session_criteria_id)) {
                $model->criteriaName = 'Saved search-' . time();
                $model->critetiaType = SearchCriteria::BULK;
            }
            $model->date = User::convertSystemTime();
            if (empty($_POST['User']['userType'])) {
                $model->userType = 0;
            }
            if (!empty($model->keywords)) {
                $model->keywords = implode(',', $model->keywords);
            }
            if (!empty($model->teams)) {
                $model->teams = implode(',', $model->teams);
            }
            $attributes = $model->attributes;

            if ($model->save()) {
                $data = ToolKit::post('CustomValue');
                if ($data) {
                    $this->customValueUpdate($data, $model->id);
                }

                $searchModel = new UserSearch();
                $criteria = $searchModel->searchPeople($user_data);
                $userCount = $criteria->getTotalCount();

                // if no records found, show error message
                if (0 == $userCount) {
                    CustomValueSearch::deleteAll('relatedId=:relatedId', array(':relatedId' => $model->id));
                    $model->delete();
                    echo Json::encode(array('status' => 'error',
                        'message' => ToolKit::setAjaxFlash('error', Yii::t('messages', "Search did not match any results. Please try again."), true)));
                    return $this->redirect(['advanced-search/admin']);
                    exit();
                }

                $bulkEditModel = new BulkEdit();
                $bulkEditModel->searchCriteriaId = (!empty($session_criteria_id)) ? $session_criteria_id : $model->id;
                $bulkEditModel->status = BulkEdit::PENDING;
                $bulkEditModel->createdAt = date('Y-m-d H:i:s');
                $bulkEditModel->createdBy = Yii::$app->user->getId();
                $bulkEditModel->totalRecords = $userCount;
                $bulkEditModel->save(false);

                $customAttributes = CustomField::getCustomSearchFieldValues($model->id);
                Yii::$app->appLog->writeLog("Bulk edit criteria created. Saved data:" . Json::encode(array_merge($attributes, $customAttributes)));
                echo Json::encode(array('status' => 'success',
                    'url' => Yii::$app->urlManager->createUrl(['advanced-search/show-edit-dialog/id/' . $bulkEditModel->id])));
            } else {
                $errors = current($model->getErrors());
                Yii::$app->appLog->writeLog("Bulk edit criteria save failed. Error:" . Json::encode($errors[0]));
                $error_message = Yii::t('messages', "Bulk edit criteria save failed {errors}", [
                    'errors' => $errors[0]
                ]);
                echo Json::encode(array('status' => 'error',
                    'message' => ToolKit::setAjaxFlash('error', $error_message, true)));
            }
        } else {
            Yii::$app->appLog->writeLog("Bulk edit criteria not found.");
            echo Json::encode(array('status' => 'error',
                'message' => ToolKit::setAjaxFlash('error', Yii::t('messages', "Bulk edit criteria not found."), true)));
        }
    }

    /**
     * stop a bulk edit
     */
    public function actionStopBulkEdit($id)
    {
        $model = BulkEdit::find()->where(['id' => $id])->one();
        if ($model->status == BulkEdit::IN_PROGRESS || $model->status == BulkEdit::PENDING) {
            $model->status = BulkEdit::FINISHED;
            $model->save(false);
            Yii::$app->session->setFlash('success', Yii::t('messages', 'Previous bulk edit stopped successfully'));
            $fromEmail = Yii::$app->params['smtp']['senderEmail'];
            $fromName = Yii::$app->params['smtp']['senderLabel'];
            $subject = Yii::t('messages', "Bulk Edit");
            $message = $this->renderPartial('@app/views/emailTemplates/notificationTemplate', array(
                'content' => Yii::t('messages', 'Your bulk edit process has been stopped'), $model));
            $emails = User::getModeratorEmails();
            if ($emails) {
                foreach ($emails as $record) {
                    if (Yii::$app->toolKit->sendEmail(array($record), $subject, $message, null, null, $fromName, $fromEmail)) {
                        Yii::$app->appLog->writeLog("bulk edit process stop email sent");
                    } else {
                        Yii::$app->appLog->writeLog("bulk edit process stop sending failed ");
                    }
                }
            }

            $this->redirect(array('advanced-search/admin'));
        }
    }

    /**
     * @edit users from gridview
     * @param $id
     * @return string|Response
     */
    public function actionShowEditDialog($id)
    {
        $bulkModel = BulkEdit::find()->where(['id' => $id])->one();
        if (is_null($bulkModel)) {
            Yii::$app->session->setFlash('error', Yii::t('messages', 'Bulk edit record cannot be found.'));
            return $this->redirect(array('advanced-search/admin'));
        }
        if (!empty($bulkModel->columnMap)) {
            Yii::$app->session->setFlash('error', Yii::t('messages', 'Cannot modify existing bulk edit record.'));
            return $this->redirect(array('advanced-search/admin'));
        }

        //Custom field data
        $customFields = BulkEditCustomField::getCustomData(CustomType::CF_PEOPLE, $id, CustomField::ACTION_EDIT, ToolKit::post('CustomValue'));
        $model = new User();
        if ($model->load(Yii::$app->request->post())) {
            $user = $_POST['User'];
            $model->attributes = $user;
            $model->zip = isset($user['zip']) ? $user['zip'] : '';
            $model->gender = isset($user['gender']) ? $user['gender'] : '';
            $model->city = isset($user['city']) ? $user['city'] : '';
            $model->countryCode = isset($user['countryCode']) ? $user['countryCode'] : '';
            $model->notes = isset($user['notes']) ? $user['notes'] : '';
            $model->userType = isset($user['userType']) ? $user['userType'] : '';
            $model->emailStatus = isset($user['emailStatus']) ? $user['emailStatus'] : '';
            $model->keywords = isset($user['keywords']) ? $user['keywords'] : '';
            if ($model->emailStatus != User::SUBSCRIBE_EMAIL && $model->emailStatus != '') {
                $model->emailStatus = "NULL";
            }
            if (is_array($model->keywords)) {
                $model->keywords = implode(",", $model->keywords);
            }
            if (isset($_POST['CustomValue'])) {
                $mappedColumns = array_merge($model->attributes, $this->getCustomLabelValuesArray($_POST['CustomValue']));
            } else {
                $mappedColumns = $model->attributes;
            }
            $hasValue = false;
            foreach ($mappedColumns as $key => $value) { //checking for at least one value
                if (!empty($value)) {
                    $hasValue = true;
                    break;
                }
            }
            // show a summary of how many records going to be deleted
            if (!$hasValue) {
                Yii::$app->session->setFlash('error', Yii::t('messages', 'At least one attribute is required for bulk edit.'));
            } else {
                $valid = $model->validate();
                if (isset($_POST['CustomValue'])) {
                    $valid = CustomField::validateBulkEditCustomFieldList($customFields) && $valid;
                }
                if ($valid) {
                    $filterArray = array_filter($mappedColumns);
                    // Fix always email status coming to the edit even tho its not changed
                    if ($model->emailStatus != '') {
                        if ($model->emailStatus == User::SUBSCRIBE_EMAIL) {
                            $filterArray['isUnsubEmail'] = '0';
                            $filterArray['emailStatus'] = 'NULL';
                        } else {
                            $filterArray['emailStatus'] = "NULL";
                        }
                    }
                    $bulkModel->columnMap = Json::encode($filterArray);
                    $bulkModel->save(false);
                    Yii::$app->appLog->writeLog("bulk edit CRON started: " . BulkEdit::CRON_COMMAND . " bulk id:" . $bulkModel->id);
                    AdvanceBulkInsert::runCommand(BulkEdit::CRON_COMMAND, $bulkModel->id); //first
                    Yii::$app->session->setFlash('success', Yii::t('messages', 'Bulk edit started. You will be informed by email upon the completion.'));
                    header('Refresh:2;url=' . Yii::$app->urlManager->createAbsoluteUrl('advanced-search/admin'));
                }
            }
        }

        $userCount = $bulkModel->totalRecords;
        $keywords = Keyword::getActiveKeywords();
        unset($keywords[Keyword::KEY_AUTO]);
        $tmpKeywords = array();
        foreach ($keywords as $behaviour => $behaviours) {
            $tmpKeywords[Keyword::getBehaviourOptions($behaviour)] = $behaviours;
        }

        return $this->render('preview', array('model' => $model, 'keywords' => $tmpKeywords, 'customFields' => $customFields, 'id' => $id, 'userCount' => $userCount, 'bulkModel' => $bulkModel));
    }

    /**
     * show all bulk exports status
     */

    public function actionBulkExportView()
    {
        if (Yii::$app->user->isGuest) {
            $this->redirect(array('admin'));
        }
        // Fetch the table BulkExport => statusFile
        $command1 = Yii::$app->db->createCommand("SHOW COLUMNS FROM `BulkExport` LIKE 'statusFile'");
        $count = $command1->queryScalar();
        if (!$count) {
            $command2 = Yii::$app->db->createCommand("ALTER TABLE `BulkExport` ADD `statusFile` VARCHAR(50) NULL AFTER `exportType`");
            $command2->execute();
        }

        $model = new BulkExport();
        $dataProvider = $model->search();
        $model->loadDefaultValues(false);  // clear any default values
        Yii::$app->toolKit->addActivity(Yii::$app->user->getId(), Activity::ACT_ADVAN_BULK_PEOPLE, Yii::$app->session->get('teamId'));
        return $this->render('advanced_bulk_admin', array('model' => $model, 'dataProvider' => $dataProvider));
    }

    /**
     * stop a bulk export
     */
    public function actionStopBulkExport($id)
    {
        $model = BulkExport::find()->where(['id' => $id])->one();
        switch ($model->exportType) {
            case BulkExport::ADDRESS_EXPORT_TYPE:
                $export = Yii::t('messages', 'Address');
                break;
            default:
                $export = Yii::t('messages', 'Bulk');
                break;
        }
        if ($model->status == BulkExport::IN_PROGRESS || $model->status == BulkExport::PENDING) {
            $model->status = BulkExport::CANCELED;
            $model->save(false);
            $exportSuccess = Yii::t('messages', 'Previous {export} export stopped successfully', [
                'export' => $export
            ]);
            Yii::$app->session->setFlash('success', $exportSuccess);
            return $this->redirect(['advanced-search/admin']);
        }

    }

    /**
     * Bulk export a list of records
     */
    public function actionBulkExport($exportType = BulkExport::BULKEXPORT_TYPE)
    {
        switch ($exportType) {
            case BulkExport::ADDRESS_EXPORT_TYPE:
                $export = Yii::t('messages', 'Address');
                $runCommand = BulkExport::CRON_COMMAND_ADDRESS_EXPORT;
                break;
            default:
                $export = Yii::t('messages', 'Bulk');
                $runCommand = BulkExport::CRON_COMMAND;
                break;
        }
        $model = new SearchCriteria();
        $session_criteria_id = Yii::$app->session->get('criteriaId');
        if (!empty($session_criteria_id) and $session_criteria_id != 'NULL') {
            $model = SearchCriteria::find()->where(['id' => $session_criteria_id])->one();
        }
        $modelProgress = BulkExport::find()->where(['status' => BulkExport::IN_PROGRESS, 'exportType' => $exportType])->one();
        $modelPending = BulkExport::find()->where(['status' => BulkExport::PENDING, 'exportType' => $exportType])->one();
        //if already in progress or pending, should display error message via ajax
        if (null !== $modelProgress || null !== $modelPending) {
            $id = !is_null($modelProgress) ? $modelProgress->id : $modelPending->id;
            $hereLinks = Html::a('here', ['advanced-search/stop-bulk-export/' . $id]);
            $errorMessage = Yii::t('messages', "Previous bulk export is still on progress. Please try again later or click {here} to stop and start fresh.", [
                'here' => $hereLinks
            ]);
            echo Json::encode(array('status' => 'error',
                'message' => ToolKit::setAjaxFlash('error', $errorMessage, true)));
            exit();
        }

        if (isset($_POST['User'])) {
            $user_data = $_POST['User'];
            $model->attributes = $_POST['User'];
            $model->id = (!empty($session_criteria_id)) ? $session_criteria_id : '';
            if ($model->keywordsExclude != null) {
                $model->keywordsExclude = implode(",", $model->keywordsExclude);
            }
            // If search criteria selected ,need to prevent create duplicate error
            if ($model->keywordsExclude2 != null) {
                $model->keywordsExclude2 = implode(",", $model->keywordsExclude2);
            }
            if ($model->keywords2 != null) {
                $model->keywords2 = implode(",", $model->keywords2);
            }
            if (empty($session_criteria_id)) {
                $model->criteriaName = 'Saved search-' . time();
                $model->critetiaType = SearchCriteria::BULK;
            }
            $model->date = User::convertSystemTime();
            if (!empty($model->keywords)) {
                $model->keywords = implode(',', $model->keywords);
            }
            if (!empty($model->teams)) {
                $model->teams = implode(',', $model->teams);
            }
            if (!empty($model->network)) {
                $model->network = implode(',', $model->network);
            }
            // allow regional admin to export his zip people
            $isRegional = Yii::$app->user->CheckUserType(WebUser::REGIONAL_ADMIN_NAME) && !Yii::$app->session->get('is_super_admin');
            if ($isRegional) {
                $user = User::find()->where(['id' => Yii::$app->user->getId()])->one();
                $model->zip = $user->zip;
            }
            if (empty($_POST['User']['userType'])) {
                $model->userType = 0;
            }
            $attributes = $model->attributes;
            if ($model->save()) {
                $data = ToolKit::post('CustomValue');
                if ($data) {
                    $this->customValueUpdate($data, $model->id);
                }

                $searchModel = new UserSearch();
                $criteria = $searchModel->searchPeople($user_data);
                $userCount = $criteria->getTotalCount();
                // if no records found, show error message
                if (0 == $userCount) {
                    CustomValueSearch::deleteAll('relatedId=:relatedId', array(':relatedId' => $model->id));
                    $model->delete();
                    echo Json::encode(array('status' => 'error',
                        'message' => ToolKit::setAjaxFlash('error', Yii::t('messages', "Search did not match any results. Please try again."), true)));
                    return $this->redirect(['advanced-search/admin']);
                    exit();
                }

                $connection = Yii::$app->db;
                $criteria_id = (!empty($session_criteria_id)) ? $session_criteria_id : $model->id;
                $connection->createCommand()->insert('BulkExport', [
                    'searchCriteriaId' => $criteria_id,
                    'status' => BulkExport::PENDING,
                    'createdAt' => date('Y-m-d H:i:s'),
                    'createdBy' => Yii::$app->user->getId(),
                    'totalRecords' => $userCount,
                    'exportType' => $exportType,
                ])->execute();
                $exportId = Yii::$app->db->getLastInsertID();
                $bulkExportModel = BulkExport::find()->where(['searchCriteriaId' => $criteria_id, 'id' => $exportId])->one();
                $customAttributes = CustomField::getCustomSearchFieldValues($criteria_id);
                Yii::$app->appLog->writeLog("$export export criteria created. Saved data:" . Json::encode(array_merge($attributes, $customAttributes)));
                $kiil_id = null;

                BulkExport::runCommand($runCommand, $bulkExportModel->id, $kiil_id); //first
                Yii::$app->appLog->writeLog("$export export CRON started: " . $runCommand . " bulk id:" . $bulkExportModel->id);
                echo Json::encode([
                    'status' => 'success',
                    'message' => ToolKit::setAjaxFlash('success', Yii::t('messages', "Export Process started.", ['export' => $export]), true),
                    'url' => Url::to(['advanced-search/bulk-export-view'])
                ]);

            } else {
                $errors = current($model->getErrors());
                Yii::$app->appLog->writeLog("$export export criteria save failed. Error:" . Json::encode($errors[0]));

                echo Json::encode(array('status' => 'error',
                    'message' => ToolKit::setAjaxFlash('error', Yii::t('messages', "{export} export criteria save failed {errors}. ",
                        [
                            'errors' => $errors[0],
                            'export' => $export
                        ]), true)));
                exit;
            }
        } else {
            Yii::$app->appLog->writeLog("$export export criteria not found.");
            echo Json::encode(array('status' => 'error',
                'message' => ToolKit::setAjaxFlash('error', Yii::t('messages', "{export} export criteria not found.",
                    ["export" => $export]
                ), true)));
        }
    }

    public function actionDownload($fullPath)
    {
        $filename = $fullPath;
        $path = Yii::getAlias('@webroot') . $filename;
        if (!empty($path)) {
            header("Content-type:*/*"); //for all file
            header('Content-Disposition: attachment; filename="' . basename($path) . '"');
            header('Content-Length: ' . filesize($path));
            readfile($path);
            Yii::$app->end();
        }

    }

    /**
     * bulk delete,edit,export custom filed serach save
     * @param string $searchCriteriaId criteria id
     * TODO needs to bring into customValueSearch Model
     */
    public function customValueUpdate($data, $searchCriteriaId)
    {
        foreach ($data as $key => $val) {
            $customSearchCriteriaList = CustomValueSearch::find()->where(['relatedId' => $searchCriteriaId])->andWhere(['customFieldId' => $key])->count();
            if ($customSearchCriteriaList == 0) {
                if (is_array($val) && !ToolKit::isEmpty($val['fieldValue'])) {
                    $customSearch = new CustomValueSearch();
                    $customSearch->relatedId = $searchCriteriaId;
                    $customSearch->customFieldId = $key;
                    $customSearch->fieldValue = $val['fieldValue'];
                    $customSearch->save(false);
                }
            }
        }
    }

    /**
     * Download bulk delete status file
     * @param string $statusFile completed file status
     */
    public function actionDownloadStatus()
    {
        if (isset($_GET['status_file'])) {

            $src = Yii::$app->params['fileUpload']['export']['path'] . $_GET['status_file'];
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
     * Update advanced search criteria
     */
    public function actionUpdateSearch()
    {
        if ($_POST['User']) {
            $user_data = $_POST['User'];
            $model = new SearchCriteria();
            if (isset($_POST['savedSearchId'])) {
                $model = SearchCriteria::find()->where(['id' => $_POST['savedSearchId']])->one();
            }
            $model->attributes = $_POST['User'];
            $model->critetiaType = ($model->critetiaType != 0) ? 1 : $model->critetiaType;
            // TODO needs to write in a better way.
            if ($user_data['network']) {
                $model->network = implode(',', $user_data['network']);
            }
            if ($user_data['keywords']) {
                $model->keywords = implode(',', $user_data['keywords']);
            }
            if ($user_data['keywords2']) {
                $model->keywords2 = implode(',', $user_data['keywords2']);
            }
            if ($user_data['keywordsExclude']) {
                $model->keywordsExclude = implode(',', $user_data['keywordsExclude']);
            }
            if ($user_data['keywordsExclude2']) {
                $model->keywordsExclude2 = implode(',', $user_data['keywordsExclude2']);
            }
            if (SearchCriteria::isCriteriaInUse($model->id)) {
                echo Json::encode(array('status' => 'error', 'message' => ToolKit::setAjaxFlash('error', Yii::t('messages', 'Saved search currently use by a campaign'), true)));
            } else {
                if (isset($_POST['validateOnly'])) {
                    $criteraId = null;
                    if ($model->validate()) {
                        $model->updatedBy = Yii::$app->user->identity->getId();
                        $model->updatedAt = date('Y-m-d H:i:s');
                        $model->save();
                        $criteraId = $model->id;
                        $data = ToolKit::post('CustomValue');
                        $customKeyValue = array();
                        if (!empty($data)) {
                            foreach ($data as $key => $val) {
                                if (is_array($val) && !ToolKit::isEmpty($val['fieldValue'])) {
                                    $customKeyValue[$key] = $val['fieldValue'];
                                }
                            }
                        }
                        Yii::$app->session->set('customSearch', $customKeyValue);
                        echo Json::encode(array('status' => 'success', 'criteraId' => $criteraId));
                    } else {
                        $errors = current($model->getErrors());
                        Yii::$app->appLog->writeLog("People advanced search criteria validate failed. Error:" . Json::encode($errors[0]));
                        echo Json::encode(array('status' => 'error', 'message' => ToolKit::setAjaxFlash('error', Yii::t('messages', $errors[0]), true)));
                    }
                }
            }
        } else {
            Yii::$app->appLog->writeLog("People advanced search criteria not found.");
            echo Json::encode(array('status' => 'error', 'message' => ToolKit::setAjaxFlash('error', Yii::t('messages', "People advanced search criteria not found."), true)));
        }
    }

    /**
     * Save advanced search criteria
     */

    public function actionSaveSearch()
    {
        if ($_POST['User']) {
            $user_data = $_POST['User'];
            $model = new SearchCriteria();
            $model->attributes = $_POST['User'];
            $model->critetiaType = 1;
            $model->criteriaName = 'Saved search-' . time();
            if ($user_data['network']) {
                $model->network = implode(',', $user_data['network']);
            }
            if ($user_data['keywords']) {
                $model->keywords = implode(',', $user_data['keywords']);
            }
            if ($user_data['keywords2']) {
                $model->keywords2 = implode(',', $user_data['keywords2']);
            }
            if ($user_data['keywordsExclude']) {
                $model->keywordsExclude = implode(',', $user_data['keywordsExclude']);
            }
            if ($user_data['keywordsExclude2']) {
                $model->keywordsExclude2 = implode(',', $user_data['keywordsExclude2']);
            }
            if (SearchCriteria::isCriteriaInUse($model->id)) {
                echo Json::encode(array('status' => 'error', 'message' => ToolKit::setAjaxFlash('error', Yii::t('messages', 'Saved search currently use by a campaign'), true)));
            } else {
                if (isset($_POST['validateOnly'])) {
                    $criteraId = null;
                    if ($model->validate()) {
                        $model->date = date('Y-m-d H:i:s');
                        $model->createdBy = Yii::$app->user->identity->getId();
                        $model->createdAt = date('Y-m-d H:i:s');
                        $model->updatedBy = 0;
                        $model->updatedAt = date('Y-m-d H:i:s');
                        $model->save();
                        $criteraId = $model->id;
                        Yii::$app->appLog->writeLog("People advanced search criteria validate.");
                        $data = ToolKit::post('CustomValue');
                        $customKeyValue = array();
                        if (!empty($data)) {
                            foreach ($data as $key => $val) {
                                if (is_array($val) && !ToolKit::isEmpty($val['fieldValue'])) {
                                    $customKeyValue[$key] = $val['fieldValue'];
                                }
                            }
                        }
                        Yii::$app->session->set('customSearch', $customKeyValue);
                        echo Json::encode(array('status' => 'success', 'criteraId' => $criteraId));
                    } else {
                        $errors = current($model->getErrors());
                        Yii::$app->appLog->writeLog("People advanced search criteria validate failed. Error:" . Json::encode($errors[0]));
                        echo Json::encode(array('status' => 'error', 'message' => ToolKit::setAjaxFlash('error', Yii::t('messages', $errors[0]), true)));
                    }
                }
            }
        } else {
            Yii::$app->appLog->writeLog("People advanced search criteria not found.");
            echo Json::encode(array('status' => 'error', 'message' => ToolKit::setAjaxFlash('error', Yii::t('messages', "People advanced search criteria not found."), true)));
        }
    }


    /**
     * @return string
     * Creates a new map zone.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */

    public function actionMapZoneCreate()
    {
        $this->layout = 'dialog';
        $model = new MapZone();
        if (isset($_POST['MapZone'])) {
            $model->attributes = $_POST['MapZone'];
            if (isset($_POST['User'])) {
                $model->firstName = $_POST['User']['firstName'];
                $model->lastName = $_POST['User']['lastName'];
                $model->city = $_POST['User']['city'];
                $model->countryCode = $_POST['User']['countryCode'];
                $model->userType = $_POST['User']['userType'];
                $model->zip = $_POST['User']['zip'];
                $model->age = $_POST['User']['age'];
                $model->attributes = $_POST['User']['CustomValue'];
                $model->fullAddress = $_POST['User']['fullAddress'];
                $model->keywords = $_POST['User']['keywords'];
                $model->searchType = $_POST['User']['searchType'];
                $model->keywordsExclude = $_POST['User']['keywordsExclude'];
                if (isset($_POST['User']['active'])) {
                    $model->status = $_POST['User']['active'];
                }
            }
            $model->title = $_POST['MapZone']['title'];
            $model->zoneLongLat = $_POST['MapZone']['teamZoneData'];
            if (!empty($model->keywords)) {
                $model->keywords = implode(',', $model->keywords);
            }
            if (!empty($model->keywordsExclude)) {
                $model->keywordsExclude = implode(',', $model->keywordsExclude);
            }
            if ($model->validate()) {
                $errors = current($model->getErrors());
                try {
                    if ($model->save()) {
                        Yii::$app->appLog->writeLog("Map Zone created.Data:" . Json::encode($model->attributes));
                        echo Json::encode(array('status' => 'success', 'message' => ToolKit::setAjaxFlash('success', Yii::t('messages', "Zone created. You can now use it as a filter."), true)));
                    } else {
                        echo Json::encode(array('status' => 'error', 'message' => ToolKit::setAjaxFlash('error', Yii::t('messages', $errors[0]), true)));
                        Yii::$app->appLog->writeLog("Map Zone create failed.Data:" . Json::encode($model->attributes));
                    }
                } catch (Exception $e) {
                    echo Json::encode(array('status' => 'error', 'message' => ToolKit::setAjaxFlash('error', Yii::t('messages', $errors[0]), true)));
                    Yii::$app->appLog->writeLog("Map Zone create failed.Data:" . Json::encode($model->attributes) . ", Errors:{$e->getMessage()}");
                }
            } else {
                $errors = current($model->getErrors());
                echo Json::encode(array('status' => 'error', 'message' => ToolKit::setAjaxFlash('error', Yii::t('messages', $errors[0]), true)));
                Yii::$app->appLog->writeLog("Map Zone create failed.Validation errors:" . Json::encode($model->errors));
            }
            exit;
        }
        return $this->render('createMapZone', array('model' => $model,));
    }


    /**
     * Creates a popup Save save search.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @param null $id
     * @return string
     */

    public function actionUpdateSearchCriteriaAjax($id = null)
    {
        $model = SearchCriteria::find()->where(['id' => $id])->one();
        $this->layout = 'advancedSearch';
        return $this->render('_popup', array('model' => $model));
    }

    /**
     * @param $array
     * @return array
     * TODO needs to bring into customField model
     */
    public function getCustomLabelValuesArray($array)
    {
        $customDuplicateValues = array();
        foreach ($array as $key => $val) {
            if (isset($val['fieldValue']) && !ToolKit::isEmpty($val['fieldValue'])) {
                $model = CustomField::find()->where(['id' => $key])->one();
                $customDuplicateValues[$model->fieldName] = $val['fieldValue'];
            }
        }
        return $customDuplicateValues;
    }

    /**
     * @param $array
     * @return array
     * TODO needs to bring into custom field model
     */
    public function getCustomValuesArray($array)
    {
        $customDuplicateValues = array();
        foreach ($array as $key => $val) {
            if (isset($val['fieldValue']) && !empty($val['fieldValue'])) {
                $customDuplicateValues[$key] = $val['fieldValue'];
            }
        }
        return $customDuplicateValues;
    }

}
