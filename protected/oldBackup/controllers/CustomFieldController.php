<?php

namespace app\controllers;

use app\models\Configuration;
use app\models\CustomType;
use app\models\CustomValue;
use app\models\CustomValueSearch;
use borales\extensions\phoneInput\PhoneInputBehavior;
use Yii;
use app\models\CustomField;
use app\models\CustomFieldSearch;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use \app\controllers\WebUserController;

/**
 * CustomFieldController implements the CRUD actions for CustomField model.
 */
class CustomFieldController extends WebUserController
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
     * Action used to load custom field type based on selected area.
     */

    public function actionLoadCustomType()
    {
        $output = null;
        $model = $_POST['model'];
        $model = new CustomField();

        $model = new $model();

        $emptyOption = $_POST['emptyOption'];
        $areaId = $_POST['areaId'];
        $parentAttribute = $_POST['parentAttribute'];
        $childAttribute1 = $_POST['childAttribute1'];
        $customTypeList = CustomType::getListByArea($emptyOption, $areaId);

        $output .= '<div>';
        $output .= Html::activeDropDownList($model, $childAttribute1, $customTypeList, [
            'id' => $childAttribute1
        ]);
        $output .= '</div>';
        echo $output;
        Yii::$app->end();
    }

    /**
     * Lists all CustomField models.
     * @return mixed
     */
    public function actionAdmin()
    {
        $model = new CustomField();
        // $model->scenario = 'search';
        // $model->loadDefaultValues(false);  // clear any default values
        $dataProvider = $model->search(Yii::$app->request->queryParams);
        return $this->render('admin', array(
            'model' => $model,
            'dataProvider' => $dataProvider,
        ));
    }

    /**
     * Displays a single CustomField model.
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
     * Creates a new CustomField model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        try {
            // TODO : Need to switch log into AppLog component.
            $model = new CustomField();
            $customType = new CustomType();

            $customField = CustomField::find()->all();
            $modelConfig = Configuration::findOne(Configuration::CUSTOM_FIELD_LIMIT); // default 15

            if (count($customField) >= $modelConfig->value) {
                Yii::$app->session->setFlash('error', Yii::t('messages', 'Maximum allowed custom fields count reached.'));
                return $this->redirect(array('admin'));
            }

            if ($model->load(Yii::$app->request->post())) {
                $model->attributes = $_POST['CustomField'];
                if ($model->sortOrder == null) {
                    $model->sortOrder = 0;
                }
                $model->label = $_POST['CustomField']['label'];
                if ($model->validate()) {
                    if ($model->save()) {
                        Yii::$app->session->setFlash('success', Yii::t('messages', 'Custom Field was successfully saved.'));
                        return $this->redirect(array('admin'));
                    }
                } else {
                    Yii::error("Custom Field add failed.Validation Errors:" . json_encode($model->errors));
                }
            }

            $params = [
                'model' => $model,
                'customTypesCodes' => ArrayHelper::map($customType->find()->select(['id', 'typeName'])->all(), 'id', 'typeName'),
                'action' => 'create'
            ];
            return $this->render('create', $params);
        } catch (Exception $e) {
            echo 'Caught an Error: ', $e->getMessage(), "\n";
            Yii::$app->appLog->writeLog("Exception : " . $e->getLine());
        }
    }

    /**
     * Updates an existing CustomField model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        try {
            $model = $this->findModel($id);
            if ($model === null) {
                throw new yii\web\HttpException(500, Yii::t('messages', 'The requested Custom Field does not exist.'));
            }
            $customType = new CustomType();
            if ($model->load(Yii::$app->request->post())) {
                $model->attributes = $_POST['CustomField'];
                if ($model->validate()) {
                    if ($model->save(false)) {
                        Yii::$app->session->setFlash('success', Yii::t('messages', 'Custom Field was successfully saved.'));
                        return $this->redirect(['admin']);
                    }
                } else {
                    Yii::error("Custom Field add failed.Validation Errors:" . json_encode($model->errors));
                }
            }

            $params = array(
                'customTypesCodes' => ArrayHelper::map($customType->find()->select(['id', 'typeName'])->all(), 'id', 'typeName'),
                'model' => $model,
                'action' => 'update'
            );

            return $this->render('update', $params);

        } catch (Exception $e) {
            echo 'Caught an Error: ', $e->getMessage(), "\n";
            Yii::$app->appLog->writeLog("Exception : " . $e->getLine());
        }
    }

    /**
     * Deletes an existing CustomField model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model === null) {
            Yii::$app->session->setFlash('error', Yii::t('messages', 'The requested Custom Field does not exist.'));
        }

        if ($model->delete()) {
            // Check whether the custom value and custom value search is related with deleted custom field.
            (new Query())
                ->createCommand()
                ->delete('CustomValue', ['customFieldId' => $id])
                ->execute();
            (new Query())
                ->createCommand()
                ->delete('CustomValueSearch', ['customFieldId' => $id])
                ->execute();

            Yii::$app->session->setFlash('success', Yii::t('messages', 'Custom Field was successfully deleted.'));
        } else {
            Yii::$app->session->setFlash('error', Yii::t('messages', 'Custom Field Deleted Failed'));
        }

        return $this->redirect(['admin']);
    }

    /**
     * Finds the CustomField model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return CustomField the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = CustomField::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
