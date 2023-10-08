<?php

namespace app\controllers;

use app\models\Autokeywordcondition;
use app\models\Keyword;
use app\models\User;
use borales\extensions\phoneInput\PhoneInputBehavior;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use \app\controllers\WebUserController;

class KeywordController extends WebUserController

{
    /**
     * @var string the default layout for the views. Defaults to '/column1', meaning
     * using two-column layout. See 'protected/views/layouts/column2.php'.
     */
    public $layout = '/column1';


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
     * Manages all models.
     */
    public function actionAdmin()
    {
        $model = new Keyword();
        $model->scenario = 'search';
        $dataProvider = $model->searchKeyword(Yii::$app->request->queryParams);
        $model->loadDefaultValues(false);  // clear any default values
        if (isset($_GET['Keyword']))
            $model->attributes = $_GET['Keyword'];

        return $this->render('admin', array(
            'model' => $model,
            'dataProvider' => $dataProvider,
        ));
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate()
    {
        try {
            $model = new Keyword();
            $model->scenario = 'create';
            if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                $reqPost = Yii::$app->request->post('Keyword');
                $model->lastUpdated = User::convertSystemTime();
                if ($model->load(Yii::$app->request->post()) && $model->validate()) {

                    try {
                        $model->setAttribute('behaviour', $reqPost['behaviour']);
                        $model->createdBy = Yii::$app->user->id;
                        $model->createdAt = User::convertSystemTime();
                        $model->setAttribute('updatedBy', User::convertSystemTime());
                        $model->setAttribute('updatedAt', User::convertSystemTime());
                        $model->conditions = $reqPost['conditions'];

                        if ($model->save()) {

                            if (null != $model->conditions) {
                                foreach ($model->conditions as $conditionId) {
                                    $modelCond = new AutoKeywordCondition();
                                    $modelCond->keywordId = $model->id;
                                    $modelCond->ruleId = $conditionId;
                                    $modelCond->setAttribute('lastUpdate', User::convertSystemTime());

                                    if ($modelCond->ruleId == AutoKeywordCondition::APPLY_TEAMS) {
                                        $params = (!empty($model->team)) ? array('teamIds' => implode(',', $model->team)) : [];
                                        $modelCond->params = json_encode($params);
                                    }

                                    try {
                                        if (!$modelCond->save()) {
                                            Yii::$app->appLog->writeLog('Auto keyword rule add failed');
                                        }
                                    } catch (Exception $e) {
                                    }
                                }
                            }
                            Yii::$app->session->setFlash('success', Yii::t('messages', "Keyword added"));
                            return $this->redirect(array('admin'));
                        } else {
                            Yii::$app->session->setFlash('error', Yii::t('messages', "Keyword add failed"));
                            Yii::$app->appLog->writeLog("Keyword add failed.Attributes:" . json_encode($model->attributes));
                        }
                    } catch (Exception $e) {
                        Yii::$app->appLog->writeLog("Keyword add failed.Attributes:" . json_encode($model->attributes) . ",Error:{$e->getMessage()}");
                        Yii::$app->session->setFlash('error', Yii::t('messages', "Keyword add failed"));
                    }
                } else {
                    Yii::$app->appLog->writeLog("Keyword add failed.Validation Errors:" . json_encode($model->errors));
                }

            }
            return $this->render('create', array(
                'model' => $model,
            ));

        } catch (Exception $e) {
            echo 'Caught an Error: ', $e->getMessage(), "\n";
            Yii::$app->appLog->writeLog("Exception : " . $e->getLine());
        }
    }

    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function actionView($id)
    {
        $this->layout = 'dialog';
        $model = $this->loadModel($id);

        $modelAutoKeyCond = Autokeywordcondition::find()
            ->where(['=', 'keywordid', $id])
            ->andWhere(['=', 'status', AutoKeywordCondition::AUTO_KEY_COND_ACTIVE])
            ->all();

        $ruleStr = '';
        $AutoKeywordCondition = new AutoKeywordCondition();
        if (null != $modelAutoKeyCond) {
            foreach ($modelAutoKeyCond as $modelAuto) {
                $ruleStr .= $AutoKeywordCondition->rules[$modelAuto->ruleId] . '<br/>';
            }
        }

        return $this->render('view', array(
            'model' => $model,
            'ruleStr' => $ruleStr
        ));
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($id)
    {
        try {
            $this->layout = 'peopleDialog';
            $model = $this->loadModel($id);
            $model->scenario = 'update';

            if (isset($_POST['Keyword'])) {
                $model->attributes = $_POST['Keyword'];
                $model->lastUpdated = User::convertSystemTime();
                if ($model->validate()) {
                    try {
                        $model->updatedBy = Yii::$app->user->id;
                        $model->updatedAt = User::convertSystemTime();
                        if ($model->save()) {
                            //AutoKeywordCondition::model()->deleteAllByAttributes(array('keywordId'=>$id));
                            AutoKeywordCondition::updateAll(['status' => AutoKeywordCondition::AUTO_KEY_COND_INACTIVE], "keywordId = '{$id}' ");
                            if (null != $model->conditions) {
                                foreach ($model->conditions as $conditionId) {

                                    $modelCond = AutoKeywordCondition::find()
                                        ->where(['=', 'keywordid', $id])
                                        ->andWhere(['=', 'ruleId', $conditionId])
                                        ->all();

                                    if (null != $modelCond) {
                                        $modelCond->status = AutoKeywordCondition::AUTO_KEY_COND_ACTIVE;
                                    } else {
                                        $modelCond = new AutoKeywordCondition();
                                        $modelCond->keywordId = $model->id;
                                        $modelCond->ruleId = $conditionId;
                                        $modelCond->status = AutoKeywordCondition::AUTO_KEY_COND_ACTIVE;
                                    }

                                    if ($modelCond->ruleId == AutoKeywordCondition::APPLY_TEAMS) {
                                        $params = array('teamIds' => implode(',', $model->team));
                                        $modelCond->params = json_encode($params);
                                    }

                                    try {
                                        if (!$modelCond->save()) {
                                            Yii::$app->appLog->writeLog('Auto keyword rule add failed');
                                        }
                                    } catch (Exception $e) {
                                    }
                                }
                            }
                            Yii::$app->session->setFlash('success', Yii::t('messages', "Keyword updated"));
                            //                        Yii::app()->clientScript->registerScript("updateKeywordGrid", "parent.$.fn.yiiGridView.update('keyword-grid');");
                        } else {
                            Yii::$app->session->setFlash('error', Yii::t('messages', "Keyword update failed"));
                            Yii::$app->appLog->writeLog("Keyword update failed.Attributes:" . Json::encode($model->attributes));
                        }
                    } catch (Exception $e) {
                        Yii::$app->appLog->writeLog("Keyword update failed.Attributes:" . Json::encode($model->attributes) . ",Error:{$e->getMessage()}");
                        Yii::$app->session->setFlash('error', Yii::t('messages', "Keyword update failed"));
                    }
                } else {
                    Yii::$app->appLog->writeLog("Keyword update failed.Validation Errors:" . Json::encode($model->errors));
                }
            } else {
                $modelAutoKeyCond = AutoKeywordCondition::find()
                    ->where(['=', 'keywordid', $id])
                    ->andWhere(['=', 'status', AutoKeywordCondition::AUTO_KEY_COND_ACTIVE])
                    ->all();

                $conditions = array();
                if (null != $modelAutoKeyCond) {
                    foreach ($modelAutoKeyCond as $modelAuto) {
                        $conditions[] = $modelAuto->ruleId;
                        if ($modelAuto->ruleId == AutoKeywordCondition::APPLY_TEAMS) {
                            $params = json_decode($modelAuto->params);
                            $model->team = explode(',', $params->teamIds);
                        }
                    }
                }

                $model->conditions = $conditions;
            }

            return $this->render('update', array(
                'model' => $model,
            ));

        } catch (Exception $e) {
            echo 'Caught an Error: ', $e->getMessage(), "\n";
            Yii::$app->appLog->writeLog("Exception : " . $e->getLine());
        }
    }


    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be deleted
     */
    public function actionDelete($id)
    {
        $model = $this->loadModel($id);

        $model->status = Keyword::KEY_DELETED;

        try {

            if ($model->save(false)) {
                Yii::$app->toolKit->setAjaxFlash('success', 'Keyword deleted');
                Yii::error("Keyword deleted.Attributes:" . json_encode($model->attributes));
            } else {
                Yii::$app->toolKit->setAjaxFlash('error', 'Keyword delete failed');
                Yii::error("Keyword delete failed.Attributes:" . json_encode($model->attributes));
            }

        } catch (Exception $e) {
            Yii::$app->toolKit->setAjaxFlash('error', 'Keyword delete failed');
            Yii::error("Keyword delete failed.Attributes:" . json_encode($model->attributes) . ",Errors:{$e->getMessage()}");
        }
    }


    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer the ID of the model to be loaded
     */
    public function loadModel($id)
    {
        $model = Keyword::findOne($id);
        if ($model === null)
            throw new CHttpException(404, 'The requested page does not exist.');
        return $model;
    }


}
