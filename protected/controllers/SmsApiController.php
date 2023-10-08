<?php
namespace app\controllers;


use app\models\CampaignUsers;
use Yii;
use yii\db\Query;
use yii\rest\Controller;

/**
 * Class SmsApiController
 * @package app\controllers
 */
class SmsApiController extends Controller
{
    /**
     * @return false|string
     */
    public function actionSmsEventCallback() {
        Yii::$app->appLog->writeLog(print_r($_POST, true));
        $eventInfo = $_POST;
        Yii::$app->appLog->writeLog("SMS event called. Event info:" . json_encode($eventInfo));

        if (!empty($eventInfo)) {
            $status = $eventInfo['SmsStatus'];
            $smsId = $eventInfo['SmsSid'];
            $query = new Query();
            $query->select(['SmsEventTracker.appId', 'App.domain', 'App.dbName', 'App.host', 'App.username', 'App.password'])
                ->from('SmsEventTracker')
                ->leftJoin('App', '`App`.`appId` = `SmsEventTracker`.`appId`')
                ->where(['SmsEventTracker.smsId' => $smsId]);
            $smsEventTrackInfo = $query->one();

            if (!empty($smsEventTrackInfo)) {

                Yii::$app->toolKit->domain = $smsEventTrackInfo['domain'];
                Yii::$app->toolKit->changeDbConnection($smsEventTrackInfo['dbName'], $smsEventTrackInfo['host'],
                    $smsEventTrackInfo['username'], $smsEventTrackInfo['password']);
                $modelCampUser = CampaignUsers::findOne(['smsId' => $smsId]);
                if (null != $modelCampUser) {
                    $smsStatus = $this->checkStatus($status);
                    $modelCampUser->smsStatus = $smsStatus;
                    try {
                        if ($modelCampUser->save()) {
                            Yii::$app->appLog->writeLog("SMS status updated. AppId:{$smsEventTrackInfo['appId']}, SmsStatus:{$status}, SmsId:{$smsId}");
                        } else {
                            Yii::$app->appLog->writeLog("SMS status update failed. AppId:{$smsEventTrackInfo['appId']}, SmsStatus:{$status}, SmsId:{$smsId}");
                        }
                    } catch (Exception $e) {
                        Yii::$app->appLog->writeLog("SMS status updated. AppId:{$smsEventTrackInfo['appId']}, SmsStatus:{$status}, SmsId:{$smsId}, Error:{$e->getMessage()}");
                    }
                } else {
                    Yii::$app->appLog->writeLog("No matching record in CampaignUser table. AppId:{$smsEventTrackInfo['appId']}, SmsStatus:{$status}, SmsId:{$smsId}");
                }
            } else {
                Yii::$app->appLog->writeLog("No matching record for SMS id");
            }
        } else {
            Yii::$app->appLog->writeLog("Callback event is empty");
        }
        return json_encode($eventInfo);
    }

    public  function checkStatus($status) {
        switch ($status) {
            case "delivered":
                return CampaignUsers::SMS_DELIVERED;
            case "failed":
                return CampaignUsers::SMS_FAILED;
                break;
            default:
                return CampaignUsers::SMS_QUEUED;


        }
    }

}
