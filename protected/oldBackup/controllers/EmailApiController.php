<?php


namespace app\controllers;


use app\models\CampaignUsers;
use app\models\User;
use Yii;
use yii\db\Query;
use yii\rest\Controller;

/**
 * Class EmailApiController
 * @package app\controllers
 */
class EmailApiController extends Controller
{
    /**
     * @return false|string
     */
    public function actionEmailEventCallback()
    {
        $eventInfo = json_decode(file_get_contents('php://input'), true);
        Yii::$app->appLog->writeLog("MJ Event called.Event info:" . json_encode($eventInfo));
        // TODO Commented this function for the moment due to trying for the mailjet separate API
        if (!empty($eventInfo)) {
            $event = $eventInfo['event'];
            $emailTransactionId = $eventInfo['MessageID'];
            $url = @$eventInfo['url'];
            $query = new Query();
            $query->select(['EmailEventTracker.appId', 'App.domain', 'App.dbName', 'App.host', 'App.username', 'App.password'])
                ->from('EmailEventTracker')
                ->leftJoin('App', '`App`.`appId` = `EmailEventTracker`.`appId`')
                ->where(['EmailEventTracker.emailTransactionId' => $emailTransactionId]);
            $emailEventTrackInfo = $query->one();

            if (!empty($emailEventTrackInfo)) {

                Yii::$app->toolKit->domain = $emailEventTrackInfo['domain'];
                Yii::$app->toolKit->changeDbConnection($emailEventTrackInfo['dbName'], $emailEventTrackInfo['host'],
                    $emailEventTrackInfo['username'], $emailEventTrackInfo['password']);
                $modelCampUser = CampaignUsers::findOne(['emailTransactionId' => $emailTransactionId]);
                if (null != $modelCampUser) {
                    $eventType = $modelCampUser->getEventTypeByMjEventName($event);
                    $modelCampUser->emailStatus = $eventType;
                    $urls = $modelCampUser->clickedUrls . ',' . $url;
                    $modelCampUser->clickedUrls = trim($urls, ',');
                    try {
                        if ($modelCampUser->save()) {
                            Yii::$app->appLog->writeLog("Email status updated. AppId:{$emailEventTrackInfo['appId']}, EventType:{$eventType}, EmailTransactionId:{$emailTransactionId}");
                        } else {
                            Yii::$app->appLog->writeLog("Email status update failed. AppId:{$emailEventTrackInfo['appId']}, EventType:{$eventType}, EmailTransactionId:{$emailTransactionId}");
                        }
                    } catch (Exception $e) {
                        Yii::$app->appLog->writeLog("Email status updated. AppId:{$emailEventTrackInfo['appId']}, EventType:{$eventType}, EmailTransactionId:{$emailTransactionId}, Error:{$e->getMessage()}");
                    }
                }

                // We need to keep records related to open events since clicked events call later and need this record to track it.
                if ('open' != $event && 'click' != $event) {
                    Yii::$app->dbMaster->createCommand()->delete('EmailEventTracker', 'emailTransactionId=:emailTransactionId', array(':emailTransactionId' => $emailTransactionId));
                }
                // $this->updateEmailStatus($event, $modelCampUser->userId);
                $modelCampUser->updateEmailStatus($event, $modelCampUser->userId);
            }
        } else {
            Yii::$app->appLog->writeLog("Callback event is empty");
        }
        return json_encode($eventInfo);
    }

    /**
     * @param $mjEvent
     * @param $userId
     * TODO Need to remove from this controller if the method is working in campaign user model
     */
    private function updateEmailStatus($mjEvent, $userId)
    {
        if ($mjEvent == 'bounce' || $mjEvent == 'blocked') {
            if ($mjEvent == 'bounce') {
                $emailStatus = User::BOUNCED_EMAIL;
            } else {
                $emailStatus = User::BLOCKED_EMAIL;
            }
            $model = User::findOne($userId);
            if (!empty($model)) {
                try {
                    $model->emailStatus = $emailStatus;
                    if ($model->save(false)) {
                        Yii::$app->appLog->writeLog("Email status updated. User id:{$userId}, Status:{$emailStatus}");
                    } else {
                        Yii::$app->appLog->writeLog("Email status update failed. User id:{$userId}, Status:{$emailStatus}");
                    }
                } catch (Exception $e) {
                    Yii::$app->appLog->writeLog("Email status update failed. User id:{$userId}, Status:{$emailStatus}, Error:{$e->getMessage()}");
                }
            }
        }
    }

}
