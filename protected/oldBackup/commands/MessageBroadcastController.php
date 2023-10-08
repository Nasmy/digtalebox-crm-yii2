<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use app\components\LinkedInApi;
use app\components\TwitterApi;
use app\models\App;
use app\models\BroadcastMessage;
use app\models\LnPageInfo;
use app\models\User;
use yii\console\Controller;
use Yii;
use yii\db\Exception;
use yii\console\ExitCode;
use app\components\ToolKit;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class MessageBroadcastController extends Controller
{

    public $fbPageName = null;
    public $lnPageName = null;
    public $fbPostPublishStatus = BroadcastMessage::MSG_STATUS_FAILED;
    public $fbProfPostPublishStatus = BroadcastMessage::MSG_STATUS_FAILED;
    public $twPostPublishStatus = BroadcastMessage::MSG_STATUS_FAILED;
    public $lnPostPublishStatus = BroadcastMessage::MSG_STATUS_FAILED;
    public $lnPagePostPublishStatus = BroadcastMessage::MSG_STATUS_FAILED;
    public $resourceUrl = '';


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

        if (!is_null($appModels)) {
            // Loop through each application(client)
            foreach ($appModels as $appModel) {
                Yii::$app->appLog->appId = $appModel['appId'];
                Yii::$app->appLog->writeLog("Connecting to database.Dbname:{$appModel['dbName']}");
                Yii::$app->toolKit->domain = $appModel['domain'];
                if (!Yii::$app->toolKit->changeDbConnection($appModel['dbName'], $appModel['host'], $appModel['username'], $appModel['password'])) {
                    Yii::$app->appLog->writeLog("Database connection change failed.");
                    continue;
                }
                Yii::$app->toolKit->resourcePathRelative = null;
                Yii::$app->toolKit->setResourceInfo();
                $this->resourceUrl = Yii::$app->toolKit->getWebRootUrl() . Yii::$app->toolKit->resourcePathRelative;
                $this->broadcastMessage();
            }
        } else {
            Yii::$app->appLog->writeLog('Applications not found', CRITICAL);
        }

        Yii::$app->appLog->writeLog('Process over');

        return ExitCode::OK;
    }


    /**
     * Retrieve messages from database and publish to relavant resource
     */
    private function broadcastMessage()
    {

        $bmModels = BroadcastMessage::find()->where(['recordStatus' => BroadcastMessage::REC_STATUS_PENDING])->andWhere(['<', 'publishDate', date('Y-m-d H:i:s')])->all();


        if (!empty($bmModels)) {
            $clientProfiles = User::getClientProfile();
            $clientLnProfile = $clientProfiles['modelLnProfile'];

//          $clientFbProfile = $clientProfiles['modelFbProfile'];
            $clientTwProfile = $clientProfiles['modelTwProfile'];
            foreach ($bmModels as $bmModel) {
                /* if ('' != $bmModel->fbPost) {
                    $this->publishFbPagePost($clientFbProfile, $bmModel);
                }
                if ('' != $bmModel->fbProfPost) {
                    $this->publishFbProfPost($clientFbProfile, $bmModel);
                }
                */

                if ('' != $bmModel->twPost) {
                    $this->publishTwitterPost($clientTwProfile, $bmModel);
                }
                if ('' != $bmModel->lnPost) {
                    $this->publishLinkedInPost($clientLnProfile, $bmModel);
                }
                if ('' != $bmModel->lnPagePost) {
                    $this->publishLinkedInPagePost($clientLnProfile, $bmModel);
                }

                /*   if ('' != $bmModel->lnPagePost) {
                       $this->publishLinkedInPagePost($clientLnProfile, $bmModel);
                   }*/

                try {
//              $bmModel->fbPostStatus = $this->fbPostPublishStatus;
                $bmModel->twPostStatus = $this->twPostPublishStatus;
//              $bmModel->fbProfPostStatus = $this->fbProfPostPublishStatus;
                    $bmModel->lnPagePostStatus = $this->lnPagePostPublishStatus;
                    $bmModel->lnPostStatus = $this->lnPostPublishStatus;
                    $bmModel->recordStatus = BroadcastMessage::REC_STATUS_PROCESSED;
                    $bmModel->save(false);
                } catch (Exception $e) {
                }
            }
        } else {
            Yii::$app->appLog->writeLog('No new messages to be published');
        }
    }


    /**
     * Publish to LinkedIn profile
     * @param LnProfile $clientLnProfile Instance of LnProfile
     * @param BroadcastMessage $bmModel Instance of BroadcastMessage
     */
    private function publishLinkedInPost($clientLnProfile, $bmModel)
    {
        if (!empty($clientLnProfile)) {
            $ln = new LinkedInApi();
            $ln->setAccessToken($clientLnProfile->accessToken);
            if ('' != $bmModel->lnImageName) {
                $imageLink = $this->resourceUrl . $bmModel->lnImageName;
            } else {
                $imageLink = null;
            }
            $title = Yii::t('messages', 'Image');
            $status = $ln->linkedInPost($clientLnProfile->lnUserId, $bmModel->lnPost, $title, $imageLink);
            if ($status) {
                $this->lnPostPublishStatus = BroadcastMessage::MSG_STATUS_PUBLISHED;
                Yii::$app->appLog->writeLog("Message published to LinkedIn profile. Record id:{$bmModel->id}");
            } else {
                Yii::$app->appLog->writeLog("Message could not be published to LinkedIn profile. Record id:{$bmModel->id}");
            }
        } else {
            Yii::$app->appLog->writeLog('Client not authenticated with LinkedIn');
        }
    }

    /**
     * Publish to LinkedIn page
     * @param LnProfile $clientLnProfile Instance of LnProfile
     * @param BroadcastMessage $bmModel Instance of BroadcastMessage
     */
    private function publishLinkedInPagePost($clientLnProfile, $bmModel)
    {

        if (null == $this->lnPageName) {
            $lnPageInfoModel = LnPageInfo::find()->one();
            Yii::$app->appLog->writeLog("Reading LnPageInfo model: " . json_encode($lnPageInfoModel));
            if (!empty($lnPageInfoModel)) {
                $this->lnPageName = $lnPageInfoModel->pageId;
            }
        }


        if (!empty($clientLnProfile) && null != $this->lnPageName) {
            $ln = new LinkedInApi();
            $ln->setAccessToken($clientLnProfile->accessToken);

            if ('' != $bmModel->lnPageImageName) {
                $imageLink = $this->resourceUrl . $bmModel->lnPageImageName;
            } else {
                $imageLink = null;
            }
            $title = Yii::t('messages', 'Image');

            $status = $ln->linkedInPagePost($this->lnPageName, $bmModel->lnPagePost, $title, $imageLink);

            if ($status) {
                $this->lnPagePostPublishStatus = BroadcastMessage::MSG_STATUS_PUBLISHED;
                Yii::$app->appLog->writeLog("Message published to LinkedIn page. Record id:{$bmModel->id}");
            } else {
                Yii::$app->appLog->writeLog("Message could not be published to LinkedIn page. Record id:{$bmModel->id}");
            }
        } else {
            Yii::$app->appLog->writeLog('Client not authenticated with LinkedIn or not configured LinkedIn page');
        }
    }

    /**
     * Retrieve mime type of the file
     * @param string $filePath Absolute path of the file
     * @return string Mime type of the file
     */
    private function getFileMimeType($filePath)
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $type = finfo_file($finfo, $filePath);
        finfo_close($finfo);

        return $type;
    }

    /**
     * Publish to Twitter profile
     * @param TwProfile $clientTwProfile Instance of TwProfile
     * @param BroadcastMessage $bmModel Instance of BroadcastMessage
     */
    private function publishTwitterPost($clientTwProfile, $bmModel)
    {
        if (!empty($clientTwProfile))
        {
            $twConnection = new TwitterApi();
            if ('' != $bmModel->twImageName)
            {
                $imagePath = $this->resourceUrl.$bmModel->twImageName;
                $result = $twConnection->upload(TwitterApi::IMAGE_UPLOAD,['status' => trim($bmModel->twPost),'media_category' =>'tweet_image','media_data' => base64_encode(file_get_contents($imagePath))],$clientTwProfile->authToken,$clientTwProfile->authTokenSecret);
                $mediaId = $result->media_id;
                $params = array(
                    'media_ids' => $mediaId,
                    'status' => trim($bmModel->twPost)
                );
                $response = $twConnection->post(TwitterApi::STATUS_UPDATE,$params,$clientTwProfile->authToken,$clientTwProfile->authTokenSecret);
            } else {
                $response = $twConnection->post(TwitterApi::STATUS_UPDATE, ['status' => trim($bmModel->twPost)],$clientTwProfile->authToken,$clientTwProfile->authTokenSecret);
            }
            Yii::$app->appLog->writeLog("Post:" . trim($bmModel->twPost));
            Yii::$app->appLog->writeLog("Length:" . strlen(trim($bmModel->twPost)));
            if (!isset($response->errors)) {
                $this->twPostPublishStatus = BroadcastMessage::MSG_STATUS_PUBLISHED;
                Yii::$app->appLog->writeLog("Message published to Twitter page. Record id:{$bmModel->id}");
            } else {
                Yii::$app->appLog->writeLog("Message could not published to Twitter page. Record id:{$bmModel->id}, Errors:" . json_encode($response->errors));
            }
        } else {
            Yii::$app->appLog->writeLog('Client not authenticated with Twitter');
        }
    }
}
