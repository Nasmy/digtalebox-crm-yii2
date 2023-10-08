<?php


namespace app\commands;


use app\components\AppLogger;
use app\components\ToolKit;
use app\models\App;
use app\models\Campaign;
use Symfony\Component\Debug\Debug;
use yii\console\Controller;
use yii\console\ExitCode;
use Yii;

/**
 * Class MassMessageSendSupervisorController
 * @package app\commands
 */
class MassMessageSendSupervisorController extends Controller
{
    private $workerCmdName = 'mass-message-send-worker';

    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     * @return int Exit code
     */
    public function actionIndex()
    {
        Yii::$app->appLog->isConsole = true;
        Yii::$app->appLog->username = __class__;
        Yii::$app->appLog->logType = 2;
        if (Yii::$app->toolKit->isProcessExists(__class__)) {
            Yii::$app->appLog->writeLog('Previous process in progress.');
            exit;
        }

        Yii::$app->appLog->writeLog('Process started');

        Yii::$app->toolKit->domain = Yii::$app->params['masterDomain'];
        $appModels = App::getAppModels();
        $countApp = count($appModels);
        Yii::$app->appLog->writeLog('App Count' . $countApp);
        if (null != $appModels) {
            $i = 0;
            foreach ($appModels as $appModel) {
                if ($appModel['appId'] < 5000) {
                    Yii::$app->appLog->appId = $appModel['appId'];
                    if ($appModel['packageTypeId'] == App::FREEMIUM) {
                        Yii::$app->appLog->writeLog('Not available for this package');
                        continue;
                    }
                    Yii::$app->appLog->writeLog("Connecting to database.Dbname:{$appModel['dbName']}");
                    Yii::$app->toolKit->domain = $appModel['domain'];
                    if (!Yii::$app->toolKit->changeDbConnection($appModel['dbName'], $appModel['host'], $appModel['username'],$appModel['password'])) {
                        Yii::$app->appLog->writeLog("Database connection change failed.");
                        continue;
                    }
                    $this->checkCampaign($appModel);
                }
                Yii::$app->appLog->writeLog('count:' . $i++);
            }
        }
        Yii::$app->appLog->writeLog('Process completed');
        return ExitCode::OK;
    }

    /**
     * Check whether is there any campaign to be run.
     * First check is there any inprogress campaign, if so we do not start new campaign.
     * To start new campaign, we just exec and start worker process
     *
     * @param App $appModel Instace of App class
     */
    private function checkCampaign($appModel)
    {
        $workerCmdName = strtolower($this->workerCmdName);
        $inProgCampCount = Campaign::find()->where(['status' => Campaign::CAMP_INPROGRESS])->count();

        if ($inProgCampCount > 0) {
            Yii::$app->appLog->writeLog('There is an inprogress campaign.');
        } else {

            // TODO need to consider time in feature
            // $campaignModel = Campaign::find()->where(['status' => Campaign::CAMP_PENDING])->andWhere(['<','startDateTime', date('Y-m-d H:i:s')])->one();
            $campaignModel = Campaign::find()->where(['status' => Campaign::CAMP_PENDING])->one();
            if (null == $campaignModel) {
                Yii::$app->appLog->writeLog("No campaigns to run");
            } else {

                Yii::$app->appLog->writeLog("There is a campaign.CampaignId:{$campaignModel->id}");

                // $status = 0;
                $campaignModel->status = Campaign::CAMP_INPROGRESS;
                try {
                    if ($campaignModel->save(false)) {
                        Yii::$app->appLog->writeLog("Campaign status updated as inprogress.");
                        if (Yii::$app->toolKit->osType == ToolKit::OS_WINDOWS) {
                            $cmd = "php " . Yii::$app->params['consolePath'] . "console.php {$workerCmdName} {$appModel['domain']} {$campaignModel->id}";
                            Yii::$app->appLog->writeLog("{$cmd}");
                            pclose(popen("start /B " . $cmd, "r")); // For windows
                        } else {
                            $cmd = "php " . Yii::$app->params['consolePath'] . "console.php {$workerCmdName} {$appModel['domain']} {$campaignModel->id} > /dev/null &";
                            Yii::$app->appLog->writeLog("{$cmd}");
                            exec($cmd, $output, $status); // For Linux
                        }
                    } else {
                        Yii::$app->appLog->writeLog("Campaign status update failed.");
                    }
                } catch (Exception $e) {
                    Yii::$app->appLog->writeLog("Campaign status update failed.Error:" . $e->getMessage());
                }
            }
        }

    }
}
