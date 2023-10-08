<?php

namespace app\controllers;

use yii\helpers\Html;
use app\models\Campaign;
use app\models\Configuration;
use app\models\User;
use app\models\MsgBox;
use app\models\CustomValueSearch;
use yii\web\HttpException;
use yii\widgets\ActiveForm;
use Yii;
use app\models\SearchCriteria;
use app\models\SearchCriteriaSearch;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\Activity;
use app\controllers\WebUserController;


/**
 * SearchCriteriaController implements the CRUD actions for SearchCriteria model.
 */
class SearchCriteriaController extends WebUserController
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

                    // ...
                ],
            ],
        ];
    }

    /**
     * Lists all SearchCriteria models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new SearchCriteriaSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single SearchCriteria model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $this->layout = 'dialog';
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new SearchCriteria model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new SearchCriteria();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing SearchCriteria model.
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
     * Manages all models.
     */
    public function actionAdmin()
    {
        $model = new SearchCriteriaSearch();
        $model->scenario = 'search';
        $model->critetiaType = SearchCriteria::ADVANCED;
        $dataProvider = $model->search(Yii::$app->request->queryParams);
        $model->loadDefaultValues(false);

        if (Yii::$app->request->isAjax) {
            $model = new SearchCriteriaSearch();
        }

        if (isset($_GET['SearchCriteriaSearch']))
            $model->attributes = $_GET['SearchCriteriaSearch'];
        return $this->render('admin', [
            'model' => $model,
            'dataProvider' => $dataProvider,
        ]);
    }


    /**
     * Deletes an existing SearchCriteria model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($savedSearchId)
    {
        $model = $this->findModel($savedSearchId);

        $name = $model->name;
        $modelCamp = Campaign::find()->where(['searchCriteriaId' => $savedSearchId])->one();
        $modelMsgBox = MsgBox::find()->where(['criteriaId' => $savedSearchId])->andWhere(['!=', 'status', MsgBox::MSG_STATUS_DELETED])->one();

        if (is_null($modelCamp) && is_null($modelMsgBox)) {
            if ($model->delete()) {
                Yii::$app->toolKit->setAjaxFlash('success', Yii::t('messages', 'Saved search deleted.'));
                Yii::$app->appLog->writeLog("Saved search deleted.Name:{$name}");
            } else {
                Yii::$app->toolKit->setAjaxFlash('error', Yii::t('messages', 'Saved search delete failed.'));
                Yii::$app->appLog->writeLog("Saved search delete failed.Name:{$name}");
            }
        } else {
            if ($modelCamp) {
                Yii::$app->toolKit->setAjaxFlash('error', Yii::t('messages', 'Saved search could not be deleted. Already being assigned for a Campaigns'));
                Yii::$app->appLog->writeLog("saved search using on campaign");
            } else if (!is_null($modelMsgBox)) {
                Yii::$app->toolKit->setAjaxFlash('error', Yii::t('messages', 'Saved search could not be deleted. Already being assigned for a Sending Event or Sending Messages'));
                Yii::$app->appLog->writeLog("saved search using on event");
            } else {
                Yii::$app->toolKit->setAjaxFlash('error', Yii::t('messages', 'Saved search could not be deleted. Already being assigned for a Campaign or sending messages'));
                Yii::$app->appLog->writeLog("Saved search delete failed. Already assigned for a campaign or sending messages.Name:{$name}");
            }
        }
    }

    /**
     * Check whether template is in use before editing.
     * @param integer $id the ID of the model to be updated
     */
    public function actionIsCriteriaInUse($savedSearchId)
    {
        $model = new SearchCriteria();

        $res = array();

        if ($model->isCriteriaInUse($savedSearchId)) {
            return Yii::$app->toolKit->setAjaxFlash('warning', Yii::t('messages', 'Saved search currently use by a campaign'));
            // $res = array('status' => 1, 'message' => $message);
            // $url = Yii::$app->urlManager->createUrl(['search-criteria/admin/']);
            // return $this->redirect($url);
        } else {
            $url = Yii::$app->urlManager->createUrl(['advanced-search/admin/', 'savedSearchId' => $savedSearchId]);
            return $this->redirect($url);
        }
        // echo Json::encode($res);
    }

    /**
     * Finds the SearchCriteria model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return SearchCriteria the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = SearchCriteria::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }


    public function actionAccessDenied()
    {
        return $this->render('accessDenied', []);
    }

    public function actionShowMessagesDialog($id, $type)
    {

        $model = new SearchCriteria();
        $this->layout = 'dialog';
        $modelConfig = Configuration::find()->where(['key' => Configuration::FROM_EMAIL])->one();

        if ('' == $modelConfig->value || Yii::$app->params['smtp']['defaultClientEmail'] == $modelConfig->value) {
            return $this->render('_validateEmail', array('id' => $id, 'type' => $type, 'model' => $model));
        } else {
            return $this->render('_messageSend', array('id' => $id, 'type' => $type, 'model' => $model));
        }
    }

    /**
     * Create new model through Ajax request
     * @return josn encode string
     */
    public function actionAjaxSave()
    {
        $model = new SearchCriteria();
        \Yii::$app->response->format = 'json';
        $options = '';
        if (isset($_POST['searchCriteria'])) {
            $searchCriteria = $_POST['searchCriteria'];
            if ($searchCriteria) {
                parse_str($searchCriteria, $params);
            }
            if ($params['SearchCriteria']['id'] != null) {
                $model = $this->loadModel($params['SearchCriteria']['id']);
            }
            $model->attributes = $params['SearchCriteria'];
            $model->date = User::convertSystemTime();
            $model->createdBy = Yii::$app->user->getId();
            $model->createdAt = User::convertSystemTime();
            $model->critetiaType = 0;
            $attributes = $model->attributes;
            if ($model->validate()) {
                if ($model->save()) {
                    $data = Yii::$app->session->get('customSearch');
                    CustomValueSearch::deleteAll('relatedId = :relatedId', array(':relatedId' => $model->id));
                    if (!empty($data)) {
                        foreach ($data as $key => $val) {
                            $customSearch = new CustomValueSearch();
                            $customSearch->relatedId = $model->id;
                            $customSearch->customFieldId = $key;
                            $customSearch->fieldValue = $val;
                            $customSearch->save(false);

                        }
                    }
                    Yii::$app->session->remove('customSearch');
                    $items = array();
                    $searchCriteriaModel = SearchCriteria::find()->where(['critetiaType' => SearchCriteria::ADVANCED])->asArray()->all();
                    foreach ($searchCriteriaModel as $key => $value) {

                        $items[$value['id']] = $value['criteriaName'];
                    }

                    $searchCriteria = $items;
                    $searchCriteria = array('' => Yii::t('messages', '- Saved Searches -')) + $searchCriteria;

                    foreach($searchCriteria as $value=>$name) {
                        if ($model->id == $value)
                            $options .= Html::tag('option',$name,['value' => $value, 'selected' => 'selected']);
                        else
                            $options .= Html::tag('option',$name,['value' => $value]);
                    }

                    $model->keywords = $model->keywords == null ? '' : explode(",", $model->keywords);
                    $model->teams = $model->teams == null ? '' : explode(",", $model->teams);
                    $model->keywordsExclude =$model->keywordsExclude == null ? '' : explode(",", $model->keywordsExclude);
                    $model->keywords2 =$model->keywords2 == null ? '' : explode(",", $model->keywords2);
                    $model->keywordsExclude2 =$model->keywordsExclude2 == null ? '' : explode(",", $model->keywordsExclude2);


                    Yii::$app->appLog->writeLog("Search criteria created. Criteria data:" . json_encode($attributes));

                    // Add activity
                    $params = array(
                        'title' => $model->criteriaName,
                    );
                    Yii::$app->toolKit->addActivity(Yii::$app->user->id, Activity::ACT_SAVE_ADVAN_SEARCH_PEOPLE, Yii::$app->session->get('teamId'));
                    return json_encode(array(
                        'status' => 'success',
                        'user_id' => $model->id,
                        'options'=>$options,
                        'attributes' => $model->attributes,
                        'name' => $model->criteriaName,
                        'msg' => Yii::t('messages', 'Search criteria saved'),
                    ));
                    // End
                }
            }
            else {
                $errors = current($model->getErrors());
                return json_encode(['status' => 'error','msg' => Yii::t('messages', $errors[0])]);
            }
        } else {

            $errors = current($model->getErrors());

            Yii::$app->appLog->writeLog("Search criteria create failed. Reason:".json_encode($errors[0]));
            return json_encode(['status' => 'error','msg' => Yii::t('messages', $errors[0])]);
            Yii::$app->end();
        }

    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer $id the ID of the model to be loaded
     * @return User the loaded model
     * @throws CHttpException
     */
    public function loadModel($id)
    {
        $model = SearchCriteria::find()->where(['id' => $id])->one();
        if ($model === null)
            throw new HttpException(404, 'The requested page does not exist.');
        return $model;
    }
}
