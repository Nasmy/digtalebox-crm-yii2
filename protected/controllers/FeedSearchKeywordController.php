<?php

namespace app\controllers;

use app\models\Configuration;
use app\models\FeedSearchKeyword;
use app\models\Keyword;
use borales\extensions\phoneInput\PhoneInputBehavior;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\widgets\ActiveForm;
use \app\controllers\WebUserController;

class FeedSearchKeywordController extends WebUserController
{
    /**
     * @var string the default layout for the views. Defaults to '/column1', meaning
     * using two-column layout. See 'protected/web/views/layouts/column1.php'.
     */
    public $layout = '/column1';
    public $keywordId;
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

    public function beforeAction($action) {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }


    public function actionAdmin()
    {
        $dbConfig = new Configuration();
        $dbConfig = $dbConfig->getConfigurations();
        $model=new FeedSearchKeyword();
        $model->loadDefaultValues();
        if(isset($_GET['FeedSearchKeyword']))
            $model->attributes=$_GET['FeedSearchKeyword'];

        return $this->render('admin',[
                'model'=>$model,
                'dailyFeedLimit' => $dbConfig['DAILY_FEED_SEARCH_LIMIT']
        ]);
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate()
    {
        try{
            $model= new FeedSearchKeyword();
            $Keyword = new Keyword();
            $this->performAjaxValidation($model);
            if(isset($_POST['FeedSearchKeyword'])) {
                $model->attributes=$_POST['FeedSearchKeyword'];
                $model->keywordId = 0;
                $model->collectedCountTwDaily = 0;
                $model->collectedCountFbDaily = 0;
                $model->collectedCountGpDaily = 0;
                $model->dateGp = date("Y-m-d H:i:s");
                $model->date = date("Y-m-d H:i:s");
                $model->dateFb = date("Y-m-d H:i:s");
                try {
                    if($model->save()) {
                        $model->distributeThreshold();

                        Yii::error("Feed search keyword added.Data:" . json_encode($model->attributes));
                        Yii::$app->session->setFlash('success', Yii::t('messages','Keyword added'));

                        // Add feed keyword to keyword table
                          $keywordId = $Keyword->addSystemKeyword($model->keyword, Yii::$app->user->id);

                        if ($keywordId) {
                            FeedSearchKeyword::updateAll(['keywordid' => $keywordId], "id = '{$model->id}' ");
                            Yii::error("Keyword added to Keyword table");
                        } else {
                            Yii::error("Keyword add failed to Keyword table.");
                        }
                      return  $this->redirect(['admin']);
                    } else {
                        Yii::error("Feed search keyword add failed.Data:" . json_encode($model->attributes));
                        Yii::$app->session->setFlash('error', Yii::t('messages','Keyword add failed'));
                    }
                    }catch(Exception $e){
                        Yii::error("Feed search keyword add failed.Error:{$e->getMessage()}");
                        Yii::$app->session->setFlash('error', Yii::t('messages','Keyword add failed'));
                    }
            }

            return  $this->render('create',[
                'model'=>$model,
            ]);
        }
        catch (Exception $e) {
            echo 'Caught an Error: ',  $e->getMessage(), "\n";
            Yii::$app->appLog->writeLog("Exception : ".$e->getLine());
        }
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($id)
    {
        try{
            $model=$this->loadModel($id);

            $this->performAjaxValidation($model);
            if(isset($_POST['FeedSearchKeyword'])) {
                $prvKeyword = $model->keyword;
                $model->attributes=$_POST['FeedSearchKeyword'];
                try {
                    if($model->save()) {
                        $model->distributeThreshold();
                        $modelKeyword = Keyword::find()->where(['name' => $prvKeyword, 'status' => 1])->one();
                         if (!empty($modelKeyword)) {
                            $modelKeyword->name = $model->keyword;
                            try {
                                $modelKeyword->save(false);
                            } catch (Exception $e) {
                            }
                        }
                        Yii::error("Feed search keyword updated.Data:" . json_encode($model->attributes));
                        Yii::$app->session->setFlash('success', Yii::t('messages','Keyword updated'));
                       return $this->redirect(['admin']);
                    } else {
                        Yii::error("Feed search keyword update failed.Data:" . json_encode($model->attributes));
                        Yii::$app->session->setFlash('error', Yii::t('messages','Keyword update failed'));
                    }
                } catch(Exception $e) {
                    Yii::error("Feed search keyword update failed.Error:{$e->getMessage()}");
                    Yii::$app->session->setFlash('error', Yii::t('messages','Keyword update failed'));
                }
            }

           return $this->render('update',[
               'model'=>$model,
           ]);
        }
        catch (Exception $e) {
            echo 'Caught an Error: ',  $e->getMessage(), "\n";
            Yii::$app->appLog->writeLog("Exception : ".$e->getLine());
        }
    }


    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be deleted
     * @return \yii\web\Response
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        if(Yii::$app->request->isPost)
        {
            // we only allow deletion via POST request
            $model = $this->loadModel($id);
            $attributes = $model->attributes;

            if ($model->delete()) {
                $model->distributeThreshold();
                Yii::error("Feed search keyword deleted.Data:" . json_encode($attributes));
                Yii::$app->toolKit->setAjaxFlash('success', Yii::t('messages','Keyword deleted'));
            } else {
                Yii::error("Feed search keyword delete failed.Data:" . json_encode($attributes));
                Yii::$app->toolKit->setAjaxFlash('error', Yii::t('messages','Keyword delete failed'));
            }

            // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
            if(!isset($_GET['ajax']))
                   return $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] :['admin']);
            }
        else
                throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer the ID of the model to be loaded
     */
    public function loadModel($id)
    {
        $model=FeedSearchKeyword::findOne($id);
        if($model===null)
            throw new CHttpException(404,'The requested page does not exist.');
        return $model;
    }

    /**
     * Performs the AJAX validation.
     * @param model the model to be validated
     */
    protected function performAjaxValidation($model)
    {
        if(isset($_POST['ajax']) && $_POST['ajax']==='feed-search-keyword-form')
        {
            echo ActiveForm::validate($model);
            Yii::$app->end();
        }
    }

}
