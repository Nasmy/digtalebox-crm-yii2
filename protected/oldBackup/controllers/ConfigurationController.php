<?php

namespace app\controllers;

use app\components\Bitly;
use app\components\LinkedInApi;
use app\components\TwitterApi;
use app\models\BitlyProfile;
use app\models\LnPageInfo;
use app\models\LnProfile;
use app\models\McProfile;
use app\models\SearchCriteria;
use app\models\TwProfile;
use app\models\User;
use borales\extensions\phoneInput\PhoneInputBehavior;
use Mailchimp\MailChimpApi;
use Yii;
use app\models\Configuration;
use app\models\ConfigurationSearch;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use \app\controllers\WebUserController;

/**
 * ConfigurationController implements the CRUD actions for Configuration model.
 */
class ConfigurationController extends WebUserController
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
     * Lists all Configuration models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ConfigurationSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Configuration model.
     * @param string $id
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
     * Creates a new Configuration model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Configuration();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->key]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Configuration model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @return string
     * @throws \yii\db\Exception
     */
    public function actionUpdate()
    {
        $configFormModel = new Configuration();
        $configFormModel->scenario = 'configForm';
        $models = Configuration::find()->all();
        $highlightFields = json_encode(array());
        if (isset($_GET['hlt'])) {
            $hlt = base64_decode($_GET['hlt']);
            $highlightFields = json_encode(explode(',', $hlt));
        }
        $tableCheck = SearchCriteria::getConfigFromEmailValueCheck();
        if (!$tableCheck) {
            $configFormModel->addNewKeysForFromEmail();
        }

        $configFormModel->attributes = $configFormModel->setConfigurationAttributes($models); // Set default values before post

        if (isset($_POST['Configuration'])) {
            $anyFailed = false;
            $configFormModel->attributes = $_POST['Configuration'];
            if ($_POST['Configuration']['smsSenderCountry'] != 'FR') {
                $_POST['Configuration']['smsSenderId'] = '';
            }
            if ($configFormModel->validate()) {
                $anyFailed = $configFormModel->updateConfigurationData($_POST['Configuration']); // Update the values into db after post and validation
            } else {
                $anyFailed = true;
            }

            if ($anyFailed) {
                Yii::$app->session->setFlash('error', Yii::t('messages', 'Configurations update failed'));
            } else {
                Yii::$app->session->setFlash('success', Yii::t('messages', 'Configurations updated'));
            }
        }

        if (!empty($configFormModel->paypalId)) {
            $configFormModel->donationType[] = Configuration::DONATION_TYPE_PAYPAL;
        }

        $configFormModel->newsletterEmbed = $this->render('newsletter', array(), true);

        return $this->render('update', [
            'configFormModel' => $configFormModel,
            'highlightFields' => $highlightFields
        ]);
    }

    /**
     * Deletes an existing Configuration model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * @param integer $id the ID of the model to be updated
     */
    public function actionResetSocialMedia($id)
    {
        $modelClient = User::find()->where(['userType' => User::POLITICIAN])->andWhere(['!=', 'id', User::PARTNER_USER_ID])->andWhere(['!=', 'isManual', User::IS_MANUAL])->one();

        if ($modelClient == null) {
            Yii::error("Client profile not found to reset social media");
            return $this->redirect(array('configuration/update'));
        }
        switch ($id) {
            case TwitterApi::TWITTER:
                $profile = TwProfile::find()->where(['=', 'userId', $modelClient->id])->one();
                if ($profile) {
                    $profile->deleteAll();
                    Yii::$app->appLog->writeLog("Twitter profile deleted");
                    Yii::$app->session->setFlash('success', Yii::t('messages', 'Twitter reset success'));
                } else {
                    Yii::$app->session->setFlash('success', Yii::t('messages', 'Twitter already reset'));
                }
                break;
            case LinkedInApi::LINKEDIN: //both profile and page
                $profile = LnProfile::find()->where(['=', 'userId', $modelClient['id']])->one();
                if ($profile) {
                    $profile->delete();
                    $modelConfig = Configuration::find()->where(['key' => Configuration::LN_PAGE])->one();
                    if (!empty($modelConfig->value)) {
                        $modelConfig->value = '';
                        $modelConfig->save(false);
                    }
                    LnPageInfo::deleteAll(); // delete the linkedIn Page
                    Yii::$app->appLog->writeLog("LinkedIn profile deleted");
                    Yii::$app->session->setFlash('success', Yii::t('messages', 'LinkedIn reset success'));

                } else {
                    Yii::$app->session->setFlash('success', Yii::t('messages', 'LinkedIn already reset'));
                }
                break;
            case MailChimpApi::MAILCHIMP:
                $profile = McProfile::findOne(array('userId' => $modelClient->id));
                if ($profile) {
                    $profile->delete();
                    Yii::$app->session->setFlash('success', Yii::t('messages', 'Mailchimp reset success'));
                    Yii::error("Mailchimp profile deleted");
                }
                break;
            case Bitly::BITLY:
                $profile = BitlyProfile::findOne(array('userId' => $modelClient->id));
                if ($profile) {
                    $profile->delete();
                    Yii::error("Bitly profile deleted");
                    Yii::$app->session->setFlash('success', Yii::t('messages', 'Bitly reset success'));
                }
                break;

        }
        return $this->redirect(array('configuration/update'));

    }

    /**
     * Finds the Configuration model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Configuration the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Configuration::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
