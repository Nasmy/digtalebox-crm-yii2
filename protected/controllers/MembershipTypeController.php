<?php

namespace app\controllers;

use app\models\FormMembershipType;
use borales\extensions\phoneInput\PhoneInputBehavior;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use \app\controllers\WebUserController;

/**
 * Membership Type Controller.
 *
 * This class illustrate Membership of users
 *
 * @author : Nasmy Ahamed
 * Date: 7/22/2019
 * @copyright Copyright &copy; Keeneye solutions (PVT) LTD
 */
class MembershipTypeController extends WebUserController
{
    /**
     * @var string the default layout for the views. Defaults to '/column1', meaning
     * using two-column layout. See 'protected/web/views/layouts/column1.php'.
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
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $this->render('view', array(
            'model' => $this->findModel($id),
        ));
    }

    /**
     * @return string
     */
    public function actionAdmin()
    {
        $model = new FormMembershipType();
        $model->loadDefaultValues(false);  // clear any default values

        if (isset($_GET['FormMembershipType'])) {
            $model->attributes = $_GET['FormMembershipType'];
        }

        return $this->render('admin', array(
            'model' => $model,
        ));
    }

    /**
     * @return string
     */
    public function actionCreate()
    {
            $model = new FormMembershipType(); //limit max 5 should be there
            if (isset($_POST['FormMembershipType'])) {
                $model->attributes = $_POST['FormMembershipType'];
                try {
                    if ($model->save()) {
                        Yii::$app->appLog->writeLog("Membership Type created.Data:" . json_encode($model->attributes));
                        Yii::$app->session->setFlash('success', Yii::t('messages', 'Membership Type created'));
                        $this->redirect(array('admin'));
                    } else {
                        Yii::$app->session->setFlash('error', Yii::t('messages', 'Membership Type create failed'));
                        Yii::$app->appLog->writeLog("Membership Type create failed.Data:" . json_encode($model->attributes));
                    }
                } catch (Exception $e) {
                    Yii::$app->session->setFlash('error', Yii::t('messages', 'Membership Type create failed'));
                    Yii::$app->appLog->writeLog("Membership Type create failed.Data:" . json_encode($model->attributes) . ", Errors:{$e->getMessage()}");
                }
            }

            return $this->render('create', array(
                'model' => $model,
            ));
    }

    /**
     * @param $id
     * @return \yii\web\Response
     * @throws HttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        if (Yii::$app->request->isPost) {
            // we only allow deletion via POST request
            $model = FormMembershipType::findOne($id);

            if ($model->delete()) {
                Yii::$app->session->setFlash('success', Yii::t('messages', 'Membership Type deleted'));
                Yii::error("User deleted.User id:{$id}");
            } else {
                Yii::$app->session->setFlash('error', Yii::t('messages', 'Membership Type delete failed'));
                Yii::error("User delete failed.User id:{$id}");
            }

            // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
            if (!isset($_GET['ajax'])) {
                return $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
            }
        } else {
            throw new HttpException(400, 'Invalid request. Please do not repeat this request again.');
        }
    }

    /**
     * @param $id
     * @return string|\yii\web\Response
     */
    public function actionUpdate($id)
    {
            $model = FormMembershipType::findOne($id);
            if (isset($_POST['FormMembershipType'])) {
                $model->attributes = $_POST['FormMembershipType'];
                if ($model->validate()) {
                    try {
                        if ($model->save()) {
                            Yii::$app->appLog->writeLog("Membership Type updated.Data:" . json_encode($model->attributes));
                            Yii::$app->session->setFlash('success', Yii::t('messages', 'Membership Type updated'));
                            return $this->redirect(array('admin'));
                        } else {
                            Yii::$app->session->setFlash('error', Yii::t('messages', 'Membership Type update failed'));
                            Yii::$app->appLog->writeLog("Membership Type update failed.Data:" . json_encode($model->attributes));
                        }
                    } catch (Exception $e) {
                        Yii::$app->session->setFlash('error', Yii::t('messages', 'Membership Type update failed'));
                        Yii::$app->appLog->writeLog("Membership Type update failed.Data:" . json_encode($model->attributes) . ", Errors:{$e->getMessage()}");
                    }
                } else {
                    Yii::$app->appLog->writeLog("Membership Type update failed.Validation errors:" . json_encode($model->errors));
                }
            }

            return $this->render('update', array(
                'model' => $model,
            ));
    }

    /**
     * Finds the CandidateInfo model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return FormMembershipType
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = FormMembershipType::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }


}
