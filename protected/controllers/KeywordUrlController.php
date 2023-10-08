<?php

namespace app\controllers;

use app\models\KeywordUrlSearch;
use yii\bootstrap\ActiveForm;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\Keyword;
use app\models\KeywordUrl;
use app\models\User;
use yii;
use yii\web\Controller;
use \app\controllers\WebUserController;

/**
 * Keyword Url Controller.
 *
 * This class illustrate Manage url for keyword
 *
 * @author : Nasmy Ahamed
 * Date: 7/22/2019
 * @copyright Copyright &copy; Keeneye solutions (PVT) LTD
 */
class KeywordUrlController extends WebUserController
{
    /**
     * @var string the default layout for the views. Defaults to '/column1', meaning
     * using two-column layout. See 'protected/views/layouts/column2.php'.
     */
    public $layout = 'column1';


    /**
     * Returns a list of behaviors that this component should behave as.
     *
     * Child classes may override this method to specify the behaviors they want to behave as.
     *
     * The return value of this method should be an array of behavior objects or configurations
     * indexed by behavior names. A behavior configuration can be either a string specifying
     * the behavior class or an array of the following structure:
     *
     * ```php
     * 'behaviorName' => [
     *     'class' => 'BehaviorClass',
     *     'property1' => 'value1',
     *     'property2' => 'value2',
     * ]
     * ```
     *
     * Note that a behavior class must extend from [[Behavior]]. Behaviors can be attached using a name or anonymously.
     * When a name is used as the array key, using this name, the behavior can later be retrieved using [[getBehavior()]]
     * or be detached using [[detachBehavior()]]. Anonymous behaviors can not be retrieved or detached.
     *
     * Behaviors declared in this method will be attached to the component automatically (on demand).
     *
     * @return array the behavior configurations.
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
     * @return string
     */
    public function actionAdmin()
    {

        $model = new KeywordUrlSearch();
        $model->loadDefaultValues(false);  // clear any default values
        if (isset($_GET['KeywordUrl'])) {
            $model->attributes = $_GET['KeywordUrl'];
        }
        $dataProvider = $model->search(Yii::$app->request->queryParams);

        return $this->render('admin', [
            'model' => $model,
            'dataProvider' => $dataProvider
        ]);
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @throws \Exception
     */
    public function actionCreate()
    {
        $model = new KeywordUrl();
        $model->scenario = 'create';
         $this->performAjaxValidation($model);
        if (isset($_POST['KeywordUrl'])) {
            $model->attributes = $_POST['KeywordUrl'];
            $model->keywords = $_POST['KeywordUrl']['keywords'];
            if ($model->validate()) {
                if (!empty($model->keywords)) {
                    $model->keywords = implode(',', $model->keywords);
                }
                $model->url = KeywordUrl::generateUrl($model->keywords);
                $model->createdAt = User::convertSystemTime();

                if ($model->save()) {
                    KeywordUrl::appendIdUrl($model->id);
                    Yii::$app->session->setFlash('success', Yii::t('messages', 'Keyword Url was successfully saved.'));
                    return $this->redirect(array('update', 'id' => $model->id));
                }
            } else {
                Yii::$app->appLog->writeLog("Keyword Url save failed.Validation Errors:" . json_encode($model->errors));
            }
        }

        $keywords = Keyword::getActiveKeywords();
        unset($keywords[Keyword::KEY_AUTO]);
        $tmpKeywords = array();
        foreach ($keywords as $behaviour => $behaviours) {
            $tmpKeywords[Keyword::getBehaviourOptions($behaviour)] = $behaviours;
        }

        $params = array(
            'model' => $model,
            'keywords' => $tmpKeywords,

        );

        return $this->render('create', $params);
    }


    /**
     * Updates a model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param $id
     * @return string|yii\console\Response|yii\web\Response
     * @throws yii\web\HttpException
     * @throws \Exception
     */
    public function actionUpdate($id)
    {
        $model = $this->loadModel($id);
        if ($model === null) {
            throw new yii\web\HttpException(500, Yii::t('messages', 'The requested Keyword Url does not exist.'));
        }
        $model->scenario = 'create';
        $this->performAjaxValidation($model);
        if ($model->load(Yii::$app->request->post())) {
            $model->attributes = $_POST['KeywordUrl'];
            $model->keywords = $_POST['KeywordUrl']['keywords'];
            if (!empty($model->keywords)) {
                $model->keywords = implode(",", $model->keywords);
            }
            $model->url = KeywordUrl::generateUrl($model->keywords);
            $model->updatedAt = User::convertSystemTime();
            //$model->IsNewRecord = false; // if needed in feature
            if ($model->validate()) {
                if ($model->save(false)) {
                    KeywordUrl::appendIdUrl($model->id);
                    Yii::$app->session->setFlash('success', Yii::t('messages', 'Keyword Url was successfully saved.'));
                    return Yii::$app->response->redirect(['keyword-url/update', 'id' => $model->id], 302);

                }
            } else {
                Yii::$app->appLog->writeLog("Keyword Url add failed.Validation Errors:" . json_encode($model->errors));
            }
        }

        if ($model->keywords !== null) {
            $model->keywords = explode(",", $model->keywords);
        }

        $params = array(
            'model' => $model,
            'keywords' => Keyword::getTempKeyword(),
        );

        return $this->render('update', $params);
    }


    /**
     * @param $id
     * @throws \Throwable
     * @throws yii\db\StaleObjectException
     * @throws yii\web\HttpException
     */
    public function actionDelete($id)
    {
        $model = $this->loadModel($id);
        if ($model === null) {
            throw new yii\web\HttpException(500, Yii::t('messages', 'The requested Keyword Url does not exist.'));
        }

        if ($model->delete()) {
            Yii::$app->session->setFlash('success', Yii::t('messages', 'Keyword Url was successfully deleted.'));
        } else
            Yii::$app->session->setFlash('error', Yii::t('messages', 'Keyword Url delete failed.'));
        return;
    }


    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer the ID of the model to be loaded
     * @return KeywordUrl|null
     * @throws yii\web\HttpException
     */
    public function loadModel($id)
    {
        $model = KeywordUrl::findOne($id);
        if ($model === null)
            throw new yii\web\HttpException(404, 'The requested page does not exist.');
        return $model;
    }


    /**
     * @param $model
     * @return array
     * @throws yii\base\ExitException
     */
    protected function performAjaxValidation($model)
    {
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'keyword-url-form') {
            Yii::$app->response->format = yii\web\Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
    }


}
