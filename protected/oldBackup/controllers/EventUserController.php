<?php

namespace app\controllers;

use app\models\EventUser;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use \app\controllers\WebUserController;

class EventUserController extends WebUserController
{
    /**
     * {@inheritdoc}
     */
     public function behaviors()
     {
         return [
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

/*    public function beforeAction($action)
    {
        $actionList = ['modify'];
        if (in_array($this->action->id, $actionList)) {
            Yii::$app->controller->enableCsrfValidation = false;
            echo header('Access-Control-Allow-Origin: *');
        } else {
            Yii::$app->controller->enableCsrfValidation = true;
        }
        return parent::beforeAction($action);

    }*/


    public function actionModify($user=null)
    {

         if (Yii::$app->request->isPost) {
             $eventId = Yii::$app->request->post('eventId');
             $rsvpStatus = Yii::$app->request->post('rsvpStatus');
             $userId = Yii::$app->request->post('user');
             $q = Yii::$app->request->post('q');

            $model = EventUser::find()->where(array('userId' => $userId, 'eventId' => $eventId))->one();

            if (!is_null($model)) {
                 $model->isParticipate = $q;
                if ($model->save(false)) {
                    $str = $model->confirmed == EventUser::PRESENT ? '' : 'NOT';
                    Yii::$app->appLog->writeLog("Event user {$str} participated.Event:{$eventId} User:{$userId}");
                } else {
                    Yii::$app->appLog->writeLog("Event user participated update failed.Event:{$eventId} User:{$userId}");
                }
            } else {
                Yii::$app->appLog->writeLog("Event user participated update failed.Event:{$eventId} User:{$userId}");
            }
        }
    }

}
