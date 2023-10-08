<?php

namespace app\controllers;

use app\models\Form;
use app\models\FormBuilder;
use app\models\FormBuilderSearch;
use app\models\FormCustomField;
use app\models\FormField;
use app\models\FormSearch;
use app\models\Keyword;
use borales\extensions\phoneInput\PhoneInputBehavior;
use yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\widgets\ActiveForm;
use \app\controllers\WebUserController;

/**
 * Class FormBuilderController
 * @package app\controllers
 */
class FormBuilderController extends WebUserController
{
    /**
     * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
     * using two-column layout. See 'protected/views/layouts/column2.php'.
     */

    public $layout = 'column1';


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
     * @return string  allowed actions
     */
    public function allowedActions()
    {
        $allowed = array();

        return implode(',', $allowed);
    }

    /**
     * Manages all models.
     */
    public function actionAdmin()
    {
        $model = new FormSearch();
        $params = Yii::$app->request->getQueryParams();
        $dataProvider = $model->search($params);

        //$model->loadDefaultValues(false);  // clear any default values
        if (isset($_GET['Form'])) {
            $model->attributes = $_GET['Form'];
        }

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
        $model = new Form();
        $model->enabled = true; // by default enabled
        if (isset($_POST['Form'])) {

            $model->attributes = $_POST['Form'];

            if ($model->validate()) {
                if (!empty($model->keywords)) {
                    $model->keywords = implode(',', $model->keywords);
                }
                if ($model->save()) {
                    $model->content = $model->generateContent();
                    $model->update(false);
                    Yii::$app->session->setFlash('success', Yii::t('messages', 'Form was successfully saved.'));
                    return $this->redirect(array('update', 'id' => $model->id));
                }
            } else {
                Yii::error("Form save failed.Validation Errors:" . json_encode($model->errors));
            }
        }
        $keywords = Keyword::getActiveKeywords();
        // unset($keywords[Keyword::KEY_AUTO]);
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
     * @param $id
     * @throws \Throwable
     * @throws yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        $model = Form::findOne($id);
        if ($model === null) {
            throw new yii\web\HttpException(500, Yii::t('messages', 'The requested Form does not exist.'));
        }

        if ($model->delete()) {
            FormField::deleteAll('formId=' . $id);
            FormCustomField::deleteAll('formId=' . $id);

            Yii::$app->toolKit->setAjaxFlash('success', Yii::t('messages', 'Form was successfully deleted.'));
        } else
            Yii::$app->toolKit->setAjaxFlash('error', $model->errors());
    }

    /**
     * Updates a model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @throws \Exception
     * @throws \Throwable
     */
    public function actionUpdate($id)
    {

        $model = Form::findOne($id);
        $model->isUpdate = true;
        $oldIsMembership = $model->isMembership;
        $oldIsDonation = $model->isDonation;

        if ($model === null) {
            throw new yii\web\HttpException(500, Yii::t('messages', 'The requested Form does not exist.'));
        }

        $list = array();
        $formFields = FormField::find()->where(['formId' => $id])->all();
        $formCustomFields = FormCustomField::find()->where(['formId' => $id])->all();

        foreach ($formFields as $val) {
            $list[] = $val->fieldId;
        }

        foreach ($formCustomFields as $val) {
            $list[] = $val->customFieldId;
        }
        $model->fieldList = $list;
        $model->preview = str_replace("type='submit'", "type='button'", $model->content); //disabling form submit

        if (isset($_POST['Form'])) {

            $model->attributes = Yii::$app->request->post('Form');
            if (!empty($model->keywords)) {
                $model->keywords = implode(',', $model->keywords);
            }

            if (!$model->enablePayment) {
                $model->isDonation = 0;
                $model->isMembership = 0;
            }

            $isContentChanged = false;
            $isNewForm = false;
            $content = explode(" ", $model->content);
            foreach ($content as $value) {
                if (strpos($value, "name='isConcern'") !== false) {
                    $isNewForm = true;
                    break;
                }
            }
            if (!$isNewForm) {
                $isContentChanged = true;
            }

            if ($model->validate()) {
                if (count($list) == count($model->fieldList)) { //check old fields and new fields are equal
                    $result = array_diff_assoc($list, $model->fieldList); // if equal, then check if index hold the same values
                    if (!empty($result)) {
                        $isContentChanged = true;
                    }
                } else {
                    $isContentChanged = true;
                }

                if ($model->isMembership != $oldIsMembership || $model->isDonation != $oldIsDonation) {
                    $isContentChanged = true;
                }

                if ($model->save(true)) {
                    if ($isContentChanged) {
                        $model->content = $model->generateContent();
                        $model->update(false);
                    }
                    Yii::$app->session->setFlash('success', Yii::t('messages', 'Form was successfully saved.'));
                    return $this->redirect(array('update', 'id' => $model->id));
                }

            } else {
                Yii::error("Form add failed.Validation Errors:" . json_encode($model->errors));
            }

        }

        $model->content = $model->getTranslated($model->content);
        $model->preview = $model->getTranslated($model->preview);

        if ($model->keywords !== null) {
            $model->keywords = explode(",", $model->keywords);
        }

        $params = array(
            'model' => $model,
            'keywords' => Keyword::getTempKeyword(true),
        );

        return $this->render('update', $params);
    }

    /**
     * Performs the AJAX validation.
     * @param model the model to be validated
     * @throws yii\base\ExitException
     */
    protected function performAjaxValidation($model)
    {
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'activity-form') {
            echo ActiveForm::validate($model);
            Yii::$app->end();
        }
    }


}
