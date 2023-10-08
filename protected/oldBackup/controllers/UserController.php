<?php

namespace app\controllers;

use app\components\WebUser;
use app\models\AuthAssignment;
use app\models\AuthItem;
use borales\extensions\phoneInput\PhoneInputBehavior;
use Yii;
use app\models\User;
use app\models\UserSearch;
use yii\base\ErrorException;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\widgets\ActiveForm;
use app\models\Activity;
use app\controllers\WebUserController;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends WebUserController
{
    public $layout = 'column1';

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

    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    /**
     * @return array
     */
    public function allowed()
    {
        return [
            'User.MyAccountImageSave',
        ];
    }

    /**
     * Lists all User models.
     * @return mixed
     */
    /*public function actionIndex()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }*/

    /**
     * Displays a single User model.
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
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        try {
            $model = new User(['scenario' => 'create']);
            $modeAuthAssignment = new AuthAssignment();
            $exceptRoles = array();

            $roles = Yii::$app->user->getRoles();

            if ($model->load(Yii::$app->request->post())) {
                $model->attributes = $_POST['User'];
                $attributes = $model->attributes;
                $attributes['password'] = '***';
                // $model->scenario = 'create';
                $valid = $model->validate();
                if ($valid) {
                    $model->password = $model->encryptUserPassword($model->password);

                    if (WebUser::POLITICIAN_ROLE_NAME == $_POST['User']['role']) {
                        $model->userType = User::POLITICIAN;
                    }

                    $model->isSysUser = 1;
                    $model->isSignupConfirmed = 1;  // System users no need to validate the email address
                    $model->joinedDate = User::convertSystemTime();
                    $model->signUpDate = User::convertSystemTime();
                    $model->supporterDate = User::convertSystemTime();
                    $model->createdAt = User::convertSystemTime();

                    if ($model->save(false)) {

                        AuthAssignment::assignItem($_POST['User']['role'], $model->id);
                        Yii::$app->session->setFlash('success', Yii::t('messages', 'User created'));
                        Yii::$app->appLog->writeLog("User created.User data:" . json_encode($attributes));
                        return $this->redirect('admin');
                    } else {
                        Yii::$app->appLog->writeLog("User create failed.User data:" . json_encode($attributes));
                        Yii::$app->session->setFlash('error', Yii::t('messages', 'User create failed'));
                    }
                } else {
                    Yii::$app->appLog->writeLog("User create failed.Validation errors:" . json_encode($model->errors));
                    Yii::$app->session->setFlash('error', Yii::t('messages', 'Validation failed'));
                    $model->password = $_POST['User']['password'];
                }
            }


            return $this->render('create', [
                'model' => $model,
                'roles' => $roles
            ]);
        } catch (ErrorException $e) {
            echo 'Caught an Error: ', $e->getMessage(), "\n";
            Yii::$app->appLog->writeLog("Exception : " . $e->getLine());
        }
    }

    public function actionAdmin()
    {
        $model = new UserSearch();
        $dataProvider = $model->search(Yii::$app->request->queryParams);
        $model->isSysUser = 1;
        if (isset($_GET['User'])) {
            $model->attributes = $_GET['User'];
        }

        return $this->render('admin', array(
            'searchModel' => $model,
            'dataProvider' => $dataProvider
        ));
    }

    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        try {
            $model = $this->findModel($id);
            $model->scenario = 'update';

            $exceptRoles = array();
            $currentRole = WebUser::getAssignedItems($model->id, $model->userType); //current user role

            if (Yii::$app->session->get('is_super_admin')) {
                $exceptRoles = array(WebUser::POLITICIAN_ADMIN_ROLE_NAME);
                $politician = User::find()->where(['userType' => 1, 'isSysUser' => 1])->andWhere(['!=', 'id', '-2'])->count();
                if ($politician >= 1) {
                    Yii::$app->appLog->writeLog("prevented Politician Role from super admin");
                    if ($currentRole[0] != WebUser::POLITICIAN_ROLE_NAME) {
                        $exceptRoles = array(WebUser::POLITICIAN_ADMIN_ROLE_NAME, WebUser::POLITICIAN_ROLE_NAME);
                    }
                }

            } else {
                $exceptRoles = array(WebUser::SUPPORTER_ROLE_NAME, WebUser::POLITICIAN_ROLE_NAME, WebUser::TEAM_LEAD_ROLE_NAME, WebUser::SUPPORTER_ROLE_NAME, WebUser::SUPPORTER_WITHOUT_TEAM, WebUser::DELETED_TEAM_MEMBER);
            }

            $roles = Yii::$app->user->getItemOptions(AuthItem::TYPE_ROLE, $exceptRoles);

            if ($model->load(Yii::$app->request->post())) {
                $modelOld = User::find()->where(['=', 'id', $model->id])->one();
                $model->attributes = $_POST['User'];
                $attributes = $model->attributes;
                $attributes['password'] = '***';

                if ($model->validate()) {
                    if ($model->password == "") {
                        $model->password = $modelOld->password;
                    } else {
                        $model->password = $model->encryptUserPassword($model->password);
                    }

                    if (WebUser::POLITICIAN_ROLE_NAME == $_POST['User']['role']) {
                        $model->userType = User::POLITICIAN;
                    }
                    if ($model->save(false)) {
                        AuthAssignment::deleteAssignedItems($model->id);
                        AuthAssignment::assignItem($_POST['User']['role'], $model->id);
                        Yii::$app->session->setFlash('success', Yii::t('messages', 'User update success'));
                        return $this->redirect('/user/admin');
                    } else {
                        Yii::$app->appLog->writeLog('Password change failed.Validation errors:' . Json::encode($model->errors));
                        Yii::$app->session->setFlash('error', Yii::t('messages', 'User update failed'));
                    }

                } else {
                    $model->password = "";
                }
            } else {
                $model->role = WebUser::getAssignedItems($model->id);
                $model->password = "";
            }

            return $this->render('update', [
                'model' => $model,
                'roles' => $roles,
                'currentRole' => $currentRole[0]
            ]);

        } catch (ErrorException $e) {
            echo 'Caught an Error: ', $e->getMessage(), "\n";
            Yii::$app->appLog->writeLog("Exception : " . $e->getLine());
        }
    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        return $this->findModel($id)->delete();

        // return $this->redirect(['index']);
    }

    public function actionChangePassword()
    {
        if (Yii::$app->session->get('isPartner') == 1) {
            $model = $this->loadModel(User::PARTNER_USER_ID);
            Yii::error("Partner views Change Password");

        } else
            $model = $this->loadModel(Yii::$app->user->id);

        $model->scenario = 'changePassword';

        $this->performAjaxValidation($model);

        if (isset($_POST['User'])) {
            $modelOld = clone $model;
            $model->attributes = $_POST['User'];
            $model->oldPasswordDb = $modelOld->password;
            if ($model->validate()) {
                $model->password = $model->encryptUserPassword($model->password);
                if ($model->save(false)) {
                    if ($model->userType == User::POLITICIAN && Yii::$app->session->get('isPartner') == 0) {
                        $model->updateMasterProfile();
                    }
                    $model = new User();
                    Yii::error('Password changed');
                    Yii::$app->session->setFlash('success', Yii::t('messages', 'Password changed.'));
                } else {
                    Yii::error('Password change failed');
                    Yii::$app->session->setFlash('error', Yii::t('messages', 'Password change failed.'));
                }
            } else {
                Yii::error('Password change failed.Validation errors:' . json::encode($model->errors));
            }
        } else {
            $model->password = '';
        }

        return $this->render('change-password', array(
            'model' => $model,
        ));
    }


    /**
     * Change own account settings.
     */

    public function actionMyAccount()
    {
        if (Yii::$app->session->get('isPartner') == 1) {
            $model = $this->loadModel(User::PARTNER_USER_ID);
            Yii::error("Partner views My Account");
        } else {
            $model = $this->loadModel(Yii::$app->user->id);
            $model->scenario = 'myAccount';

            if (isset($_POST['User'])) {
                $model->attributes = $_POST['User'];
                $model->address1 = $_POST['User']['address1'];
                if ($model->validate()) {
                    try {
                        $model->profImage = str_replace('{id}', $model->id, User::PROF_IMG_NAME) . ".png";
                        if ($model->save()) {
                            Yii::$app->toolKit->addActivity(Yii::$app->user->id, Activity::ACT_UPDATE_PROF, Yii::$app->session->get('teamId'));
                            if ($model->userType == User::POLITICIAN && Yii::$app->session->get('isPartner') == 0) {
                                $model->updateMasterProfile();
                            }
                            Yii::$app->session->setFlash('success', Yii::t('messages', 'Account details updated'));
                            Yii::error("My account details updated.User data:" . json_encode($model->attributes));
                            return $this->redirect(array('my-account'));
                        } else {
                            Yii::$app->session->setFlash('error', Yii::t('messages', 'Account details update failed'));
                            Yii::error("My account details update failed.User data:" . json_encode($model->attributes));
                        }
                    } catch (Exception $e) {
                        Yii::$app->session->setFlash('error', Yii::t('messages', 'Account details update failed'));
                        Yii::error("My account details update failed.Error:" . $e->getMessage());
                    }
                } else {
                    Yii::$app->session->setFlash('error', Yii::t('messages', 'Account details update failed'));
                    Yii::error("My account details update failed.Validation errors:" . json_encode($model->errors));
                }
            }
        }

        $profImage = User::getPic($model->profImage, 300, 300);
        $profImageUrl = User::getPicUrl($model->profImage);

        return $this->render('my-account', array(
            'model' => $model,
            'profImage' => $profImage,
            'profImageUrl' => $profImageUrl
        ));
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer the ID of the model to be loaded
     */
    public function loadModel($id)
    {
        $model = User::findOne($id);
        if ($model === null)
            throw new CHttpException(404, 'The requested page does not exist.');
        return $model;
    }


    /**
     * Change own account settings.
     */
    public function actionMyAccountImageSave()
    {
        $model = $this->loadModel(Yii::$app->user->id);
        $model->scenario = 'myAccountImg';
        $this->performAjaxValidation($model);
        $model->profImage = str_replace('{id}', $model->id, User::PROF_IMG_NAME) . '.' . pathinfo($model->profImgFile, PATHINFO_EXTENSION) . 'png';
        if ($model->validate()) {
            try {
                if ($model->save()) {
                    Yii::$app->toolKit->setResourceInfo();
                    $filePath = Yii::$app->toolKit->resourcePathRelative . $model->profImage;
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                    Yii::error("My account details updated.User data:" . json_encode($model->attributes));
                    define('UPLOAD_DIR', Yii::$app->toolKit->resourcePathRelative);
                    $img = $_POST['imgBase64'];
                    $img = str_replace('data:image/png;base64,', '', $img);
                    $img = str_replace(' ', '+', $img);
                    $data = base64_decode($img);
                    $file = UPLOAD_DIR . $model->profImage;
                    $success = file_put_contents($file, $data);
                    print $success ? 1 : 0;
                }
            } catch (Exception $e) {
                Yii::$app->session->setFlash('error', Yii::t('messages', 'Account details update failed'));
                Yii::error("My account details update failed.Error:" . $e->getMessage());
            }
        }
    }


    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */

    /**
     * Performs the AJAX validation.
     * @param CModel the model to be validated
     */
    protected function performAjaxValidation($model)
    {
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'user-form') {
            echo ActiveForm::validate($model);
            Yii::$app->end();
        }
    }

    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
