<?php

namespace app\commands;

use app\components\MailjetApi;
use app\components\ToolKit;
use app\models\App;
use app\models\Campaign;
use app\models\CampaignUsers;
use app\models\Configuration;
use app\models\User;
use yii\console\Controller;
use Yii;
use yii\console\ExitCode;
use Mailjet\Resources;

/**
 * This command is used to generate bulk export files
 */
class MassMailJetController extends Controller
{
    protected $apiKey;
    protected $apiSecret;
    protected $appModel = null;

    /**
     * @param $domains
     * @param $recordId
     * @param $lang
     */
    public function actionIndex($domains, $recordId, $lang) {
        Yii::$app->appLog->isConsole = true;
        Yii::$app->appLog->username = __class__;
        Yii::$app->appLog->logType = 2;
        Yii::$app->appLog->writeLog('Mailjet Api Call Started');
        if (Yii::$app->toolKit->isProcessExists(__class__)) {
            Yii::$app->appLog->writeLog('Previous process in progress.');
            exit;
        }

        Yii::$app->appLog->writeLog('Process started');
        if (Yii::$app->toolKit->isProcessExists(__class__)) { // check is there any bulk static process running
            Yii::$app->appLog->writeLog('Previous process in progress.');
            exit;
        }

        $domain = $domains;
        Yii::$app->sourceLanguage = 'en_us';
        Yii::$app->language = $lang;

        if (ToolKit::isEmpty($domain) || ToolKit::isEmpty($recordId)) {
            Yii::$app->appLog->writeLog('Invalid params submitted');
            exit;
        }

        Yii::$app->appLog->writeLog("Process started.Domain:{$domain}");
        $appModel = App::find()->where(['domain' => $domain])->one();
        $this->appModel = $appModel;

        Yii::$app->appLog->writeLog("Connecting to database.Dbname:{$appModel->dbName}");
        Yii::$app->toolKit->domain = $appModel->domain;

        if (!Yii::$app->toolKit->changeDbConnection($appModel->dbName, $appModel->host, $appModel->username, $appModel->password)) {
            Yii::$app->appLog->writeLog("Database connection change failed.");
            exit;
        }

        // set configuration for mailjet api and secret
        $getMailjetConfigUsername = Configuration::find()->where(['key' => Configuration::MAILJET_USERNAME])->one();
        $getMailjetConfigPassword = Configuration::find()->where(['key' => Configuration::MAILJET_PASSWORD])->one();
        $this->apiKey = $getMailjetConfigUsername['value'];
        $this->apiSecret = $getMailjetConfigPassword['value'];

        $campaignInfo = Campaign::find()->where(['id' => $recordId])->one();
        $totalRecord = CampaignUsers::find()->where(['campaignId'=>$recordId])->count(); // Get total record count
        $campaignCreatedDate = $campaignInfo->createdAt;
        $dateToProcess = \DateTime::createFromFormat("Y-m-d H:i:s", $campaignCreatedDate)->format("Y-m-d");
        $dateToEnd = strtotime("+1 day", strtotime($dateToProcess)); // set campaign stat process date less than 5 days from current date;
        $dateToProcessEnd = date("Y-m-d", $dateToEnd);
        $this->processStart($dateToProcess, $dateToProcessEnd, $totalRecord);
        Yii::$app->appLog->writeLog('File processing completed.');
    }

    /**
     * @param $fromTs
     * @param $toTs
     * @param $totalRecord
     */
    public function processStart($fromTs, $toTs, $totalRecord) {
        Yii::$app->appLog->writeLog("Mailjet: Total Records $totalRecord");
        $processCount = ($totalRecord > 500) ? ceil($totalRecord / 500) : 1;
        $i = 0;
        $offset = 0;
        $mj = new \Mailjet\Client($this->apiKey, $this->apiSecret);
        do { // get all the records within the time period. mailjet send at once 1000 recodes.
            $filters = [
                'Limit'=>500,  // default is 10, max is 1000
                'Sort'=>'ArrivedAt DESC',
                'Offset'=>$offset,
                'FromTs' => $fromTs . 'T00:00:00',
                'ToTS' => $toTs. 'T23:59:00'
            ];
            $response = $mj->get(Resources::$Message,['filters'=>$filters]); // retrieve mailjet record
            $data = $response->getData();
            $offset = $offset + 500;
            if(!empty($data)) {
                foreach ($data as $mjRecord) {
                    $messageId = $mjRecord['ID'];
                    $messageStatus = $mjRecord['Status'];
                    $this->updateStats($messageId, $messageStatus);
                };
            } else {
                Yii::$app->appLog->writeLog('Mailjet: ready to start next client');
                break;
            }
            $i++;
        } while ($i < $processCount);
    }

    /**
     * @param $messageId
     * @param $messageStatus
     */
    public function updateStats($messageId, $messageStatus) {
        $status = null;
        $userStatus = null;
        $eventTracker = CampaignUsers::find()->where(['emailTransactionId' => $messageId])->one();
        // set db status for mailjet status
        switch ($messageStatus) {
            case "sent":
                $status = 1;
                break;
            case "opened":
                $status = 2;
                break;
            case "clicked":
                $status = 3;
                break;
            case "softbounced":
            case "bounce":
            case "hardbounced":
                $status = 4;
                $userStatus = "bounce";
                break;
            case "spam":
                $status = 5;
                break;
            case "blocked":
                $status = 6;
                $userStatus = "blocked";
                break;
            case "queued":
                $status = 8;
                break;
            default:
                $status = 9;
                break;
        }

        if (!empty($eventTracker)) {
            $campaignModel = CampaignUsers::find()->where(['emailTransactionId' => $eventTracker->emailTransactionId])->one();
            $userId = $campaignModel->userId;
            if ($status === 6 || $status === 4) {
                $campaignModel->updateEmailStatus($userStatus, $userId);
            }

            if ($status === 9) {
                $campaignModel->status = 1;
            }
            $campaignModel->emailStatus = $status;
            $campaignModel->save();

            if($status === 3) { // if the email is status is click then update the url information
              $this->updateClickedUrl($messageId);
            }
        }
    }

    /**
     * @param $messageId
     */
    public function updateClickedUrl($messageId) { // Update if the use clicked a link in the email

        $mj = new \Mailjet\Client($this->apiKey, $this->apiSecret);
        $filters = [
            'MessageID' => $messageId
        ];

        $response = $mj->get(Resources::$Clickstatistics, ['filters'=>$filters]);
        $data = $response->getData();
        if(!empty($data)) {
            $campaignModel = CampaignUsers::find()->where(['emailTransactionId' => $messageId])->one();
            $campaignModel->clickedUrls = $data[0]['Url'];
            $campaignModel->save();
        }
    }
}

?>