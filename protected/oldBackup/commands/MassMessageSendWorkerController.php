<?php


namespace app\commands;


use app\components\ThresholdChecker;
use app\components\ToolKit;
use app\models\App;
use app\models\Campaign;
use app\models\CampaignSmsExceedUser;
use app\models\CampaignUsers;
use app\models\Configuration;
use app\models\CustomField;
use app\models\CustomType;
use app\models\CustomValue;
use app\models\KeywordUrl;
use app\models\MessageTemplate;
use app\models\SearchCriteria;
use app\models\User;
use Codeception\Coverage\Subscriber\Printer;
use yii\console\Controller;
use Yii;
use yii\db\Exception;

/**
 * Class MassMessageSendWorkerController
 * @package app\commands
 */
class MassMessageSendWorkerController extends Controller
{
    // Batch size for users retrieve from database
    private $dbBatchSize = 100;

    // Configuration array
    private $config = array();

    // Application id
    private $appId = null;
    private $dbName = null;

    // Threshold checker object
    private $tc = null;

    // Whether email campaign is over
    private $isEmailCampOver = false;

    // Whether Twitter campaign is over
    private $isTwitterCampOver = false;

    // Whether LinkedIn campaing is over
    private $isLinkedInCampOver = false;

    // Whether Campaing is expired
    private $isCampExpired = false;

    // Whether Campaign is paused
    private $isCampPaused = false;

    // Counter for maintain social account count when collecting many followers
    private $smsCount = 0;

    /**
     * @param $domain
     * @param $campId
     * @throws Exception
     */
    public function actionIndex($domain, $campId)
    {
        Yii::$app->appLog->isConsole = true;
        Yii::$app->appLog->username = __class__;
        Yii::$app->appLog->logType = 2;

        Yii::$app->appLog->writeLog("Process started.Domain:{$domain},CampId:{$campId}");
        $appModel = App::find()->where(['domain' => $domain])->one();
        Yii::$app->appLog->appId = $appModel->appId;
        $this->appId = $appModel->appId;
        $this->dbName = $appModel->dbName;

        Yii::$app->appLog->writeLog("Connecting to database.Dbname:{$appModel->dbName}");
        Yii::$app->toolKit->domain = $appModel->domain;

        Yii::$app->toolKit->changeDbConnection($appModel->dbName, $appModel->host, $appModel->username, $appModel->password);

        $this->config = Configuration::getConfigurations();

        Yii::$app->language = $this->config['LANGUAGE'];


        $this->tc = new ThresholdChecker($appModel->packageType, $appModel->smsPackageType, true);

        $this->tc->renewDate = $appModel->renewDate;
        $this->tc->renewedDate = $appModel->renewedDate;
        $this->doSendMessages($campId);
    }

    /**
     * This function initialize message sending process depending on message type.
     * Execute saved search criteria and save out put to CampaignUsers table
     *
     * @param integer $campId Campaign id
     * @throws Exception
     */
    private function doSendMessages($campId)
    {
        $modelCamp = Campaign::findOne($campId);
        if (null != $modelCamp) {

            switch ($modelCamp->campType) {
                case Campaign::CAMP_TYPE_EMAIL:
                    $this->sendEmails($modelCamp);
                    break;
                case Campaign::CAMP_TYPE_SMS:
                    $this->sendSmsMessages($modelCamp);
                    break;

                default:
                    Yii::$app->appLog->writeLog("Invalid campaign type");
                    break;
            }

        } else {
            Yii::$app->appLog->writeLog("Campaign not found.Id:{$campId}");
        }
    }

    /**
     * TODO need to brings into separate module
     * Send mass emails
     * @param Campaign $modelCamp Campaign class instance
     * @throws Exception
     */
    private function sendEmails($modelCamp)
    {
        $modelCriteria = SearchCriteria::findOne($modelCamp->searchCriteriaId);

        $modelMessageTemplate = MessageTemplate::findOne($modelCamp->messageTemplateId);

        if (null != $modelCriteria && null != $modelMessageTemplate) {
            Yii::$app->toolKit->setResourceInfo();

            Yii::$app->toolKit->retrieveEmailCampaignId = true;
            Yii::$app->appLog->writeLog("MassMessage resourcePathAbsolute:" . Yii::$app->toolKit->resourcePathAbsolute);
            $templateContent = file_get_contents(Yii::$app->toolKit->resourcePathAbsolute . $modelCamp->messageTemplateId . '.html');
            $clientProfiles = User::getClientProfile(['ALL'], true);


            $criteria = SearchCriteria::getCriteria($modelCriteria);


            $criteria->andWhere('email != ""');
            $criteria->andWhere('isUnsubEmail = 0');
            $criteria->andWhere('(emailStatus IS NULL OR emailStatus = 0)'); // Ignore bounced/blocked emails
            $userCriteria = $criteria;

            if ($modelCamp->campType != Campaign::CAMP_TYPE_ALL) {
                // When campaign type is all, count calculated separately
                Yii::$app->appLog->writeLog("going to count user" . json_encode($criteria));
                $userCount = $userCriteria->from('User t')->count();
                // TODO if user count 0 need to cancel the campaign
                if ($userCount == 0) {
                    $modelCamp->status = Campaign::CAMP_FINISH;
                    $modelCamp->save(false);
                    exit;
                }
                $this->saveUserCount($modelCamp, $userCount);
                Yii::$app->appLog->writeLog("User count:{$userCount}");
            }

            $startIndex = $modelCamp->batchOffsetEmail;
            $exit = false;
            $isStopped = false;
            $emailSendCount = 0;
            $isLimitedCampaign = false;
            $emailArray = Configuration::getConfigFromEmailOptions();
            if ((count($emailArray) > 2) and ($modelCamp->fromEmail != ''))  // Check is there other from emails options available.
            {
                $fromEmail = $modelCamp->fromEmail;
                $fromName = $modelCamp->fromName;
            } else {
                $fromEmail = $this->config['FROM_EMAIL'];
                $fromName = $this->config['FROM_NAME'];

                if (!Configuration::isClientSmtpSet()) {
                    Yii::$app->appLog->writeLog("Client mailjet api configuration getting failed");
                    $fromEmail = Yii::$app->params['campaign']['campaignEmail'];
                    $fromName = Yii::$app->params['smtp']['senderLabel'];
                    $isLimitedCampaign = true;
                }
            }
            do {
                // Reload campaign model to check whether it has paused
                $modelCamp = Campaign::findOne($modelCamp->id);

                if ($modelCamp->status == Campaign::CAMP_STOP) {
                    Yii::$app->appLog->writeLog("Campaign paused.Id:{$modelCamp->id}");
                    $this->updateCampStop($modelCamp, $startIndex, Campaign::CAMP_TYPE_EMAIL);
                    $this->isCampPaused = true;
                    $isStopped = true;
                    break;
                }


                Yii::$app->appLog->writeLog("Retrieve batch, offset:{$startIndex},limit:{$this->dbBatchSize}");
                $criteria->limit($this->dbBatchSize);
                $criteria->offset($startIndex);
                $modelUsers = $criteria->from('User t')->all();
                if (null != $modelUsers) {
                    foreach ($modelUsers as $modelUser) {
                        if ($isLimitedCampaign && Yii::$app->params['campaign']['limit'] == $emailSendCount) {
                            Yii::$app->appLog->writeLog("Campaign paused. Its is a Limited Campaign Id:{$modelCamp->id}");
                            $this->updateCampStop($modelCamp, ($emailSendCount), Campaign::CAMP_TYPE_EMAIL);
                            $this->changeCampStatus($modelCamp, Campaign::CAMP_STOP);
                            Yii::$app->appLog->writeLog("Batch processing over");
                            $exit = true;
                            $this->isCampPaused = true;
                            $isStopped = true;
                            break;
                        }
                        if ($modelCamp->campType == Campaign::CAMP_TYPE_ALL) {
                            // Sometimes message may have deliverd to a user via other campaign type. So we need to check it before sending it again
                            $modelCampUser = CampaignUsers::findAll(array('campaignId' => $modelCamp->id, 'userId' => $modelUser->id));
                            if (!empty($modelCampUser)) {
                                Yii::$app->appLog->writeLog("Message already being sent to user.User id:{$modelUser->id}");
                                continue;
                            }
                        }

                        $campaignModel = new Campaign();
                        $message = $campaignModel->getMessage($modelUser, $templateContent, $clientProfiles['modelUser']);
                        $status = CampaignUsers::MESSAGE_SENT;
                        $emailStatus = CampaignUsers::EMAIL_SENT;
                        $emailSendStatus = Yii::$app->toolKit->sendEmail(
                            array($modelUser['email']),
                            $modelMessageTemplate->subject,
                            $message,
                            array(
                                'userId' => $modelUser['id'],
                                'domain' => Yii::$app->toolKit->domain,
                                'clientName' => $clientProfiles['modelUser']->firstName
                            ),
                            null,
                            $fromName,
                            $fromEmail
                        );

                        $emailTransactionId = null;
                        if ($emailSendStatus) {
                            $emailTransactionId = Yii::$app->toolKit->emailTransactionId;
                            $this->addEmailTrackRecord($this->appId, $emailTransactionId);
                            Yii::$app->appLog->writeLog("Email sent {$emailSendCount}. Email:{$modelUser['email']},Email from email:{$this->config['FROM_EMAIL']} from name:{$this->config['FROM_NAME']} trcking_id:{$emailTransactionId}");
                            $emailSendCount++;
                        } else {
                            $status = CampaignUsers::MESSAGE_SENT_FAIL;
                            $emailStatus = CampaignUsers::EMAIL_FAILED;
                            Yii::$app->appLog->writeLog("Email sent failed.Email:{$modelUser['email']}");
                        }

                        if ($modelCamp->campType == Campaign::CAMP_TYPE_ALL) {
                            if ($status == CampaignUsers::MESSAGE_SENT) {
                                $this->addCampaignUser($modelCamp->id, $modelUser['id'], $status, $emailTransactionId, $emailStatus);
                            }
                        } else {
                            $this->addCampaignUser($modelCamp->id, $modelUser['id'], $status, $emailTransactionId, $emailStatus);
                        }
                    }
                } else {
                    $this->isEmailCampOver = true;
                    Yii::$app->appLog->writeLog("Batch processing over");
                    $exit = true;
                }

                $startIndex += $this->dbBatchSize;

            } while (!$exit);

        } else {
            Yii::$app->appLog->writeLog("Criteria not found or Email template not found");
        }

        if (!$isStopped && $modelCamp->campType != Campaign::CAMP_TYPE_ALL) {
            $this->changeCampStatus($modelCamp, Campaign::CAMP_FINISH);
        }
    }

    /**
     * TODO Need to bring into separate class
     * Send mass SMS messages
     * @param Campaign $modelCamp Campaign class instance
     * @throws Exception
     */
    public function sendSmsMessages($modelCamp)
    {
        $this->smsCount = $this->tc->getCount(ThresholdChecker::MONTH_SMS_LIMIT);
        $modelCriteria = SearchCriteria::findOne($modelCamp->searchCriteriaId);
        $modelMessageTemplate = MessageTemplate::findOne($modelCamp->messageTemplateId);

        if (null != $modelCriteria && null != $modelMessageTemplate) {
            $clientProfiles = User::getClientProfile();
            $criteria = SearchCriteria::getCriteria($modelCriteria);
            $criteria = $criteria->andWhere('mobile IS NOT NULL AND mobile != \'\'');
            $userCount = $criteria->from('User t')->count();
            $this->saveUserCount($modelCamp, $userCount);
            $startIndex = $modelCamp->batchOffset;
            $rescheduleIndex = $startIndex;
            $exit = false;
            $isStopped = false;
            $isLimitExceeded = false;

            do {
                // Reload campaign model to check whether it has paused
                $modelCamp = Campaign::findOne($modelCamp->id);

                if ($modelCamp->status == Campaign::CAMP_STOP) {
                    Yii::$app->appLog->writeLog("Campaign paused.Id:{$modelCamp->id}");
                    $this->updateCampStop($modelCamp, $startIndex);
                    $isStopped = true;
                    break;
                }

                Yii::$app->appLog->writeLog("Retrieve batch, offset:{$startIndex},limit:{$this->dbBatchSize}");
                $criteria->limit($this->dbBatchSize);
                $criteria->offset($startIndex);
                $modelUsers = $criteria->from('User t')->all();
                if (null != $modelUsers) {
                    foreach ($modelUsers as $modelUser) {
                        if ($this->smsCount >= $this->tc->packageInfo['monthlySmsLimit']) {
                            Yii::$app->appLog->writeLog("Monthly SMS message limit exceeded. Re schedule to tomorrow.");
                            $this->changeCampStatus($modelCamp, Campaign::CAMP_PENDING, date('Y-m-d H:i:s', time() + 86400), $rescheduleIndex);
                            $isLimitExceeded = true;
                            break 2;
                        }

                        $rescheduleIndex++;
                        $campaignModel = new Campaign();
                        $messageList = Campaign::getSmsMessageList($modelMessageTemplate);
                        $stopSms = Configuration::getSmsOpt();
                        $countSms = count($messageList);
                        $i = 1;
                        foreach ($messageList as $message) {
                            $message = $campaignModel->getMessage($modelUser, $message, $clientProfiles['modelUser']);
                            if ($i === $countSms) {
                                // if (strlen($message) <= MessageTemplate::MSG_LEN_SMS) { // this is checked for older sms not updated
                                    $message = $message . "\n" . $stopSms;
                               // }
                            }

                            $status = CampaignUsers::MESSAGE_SENT;
                            $smsStatus = CampaignUsers::SMS_PENDING;

                            $smsId = Yii::$app->toolKit->sendSMS($modelUser['mobile'], $message);
                            Yii::$app->appLog->writeLog("Sms Id: {$smsId}");
                            if ($smsId != false) {
                                $this->smsCount++;
                                $this->addSmsTrackRecord($this->appId, $smsId);
                                $this->addCampaignUser($modelCamp->id, $modelUser['id'], $status, 0, null, $smsId, $smsStatus, $i);
                            } else {
                                $status = CampaignUsers::MESSAGE_SENT_FAIL;
                                $smsStatus = CampaignUsers::SMS_FAILED;
                            }
                            $i++;
                        }
                    }
                } else {
                    Yii::$app->appLog->writeLog("Batch processing over");
                    $exit = true;
                }
                $startIndex += $this->dbBatchSize;
            } while (!$exit);
        } else {
            Yii::$app->appLog->writeLog("Criteria not found or Sms template not found");
        }

        if (!$isStopped && !$isLimitExceeded) {
            $this->changeCampStatus($modelCamp, Campaign::CAMP_FINISH);
        }
    }

    /**
     * Save user count for matching criteria
     * @param Campaign $modelCamp Current campaign model
     * @param integer $count User count.
     */
    private function saveUserCount($modelCamp, $count)
    {
        try {
            $modelCamp->totalUsers = $count;
            if ($modelCamp->save(false)) {
                Yii::$app->appLog->writeLog("User count saved.Id:{$modelCamp->id},user count:{$count}");
            } else {
                Yii::$app->appLog->writeLog("User count save failed.Id:{$modelCamp->id},user count:{$count}");
            }
        } catch (Exception $e) {
            Yii::$app->appLog->writeLog("User count save failed.Id:{$modelCamp->id},user count:{$count},error:{$e->getMessage()}");
        }
    }


    /**
     * When system detects campaign has stoped. We update last batch index.
     */
    private function updateCampStop($modelCamp, $batchIndex, $campType = null)
    {
        try {
            $modelCampTemp = Campaign::findOne($modelCamp->id);
            if ($campType == Campaign::CAMP_TYPE_EMAIL) {
                $modelCampTemp->batchOffsetEmail = $batchIndex;
            } else if ($campType == Campaign::CAMP_TYPE_TWITTER) {
                $modelCampTemp->batchOffsetTwitter = $batchIndex;
            } else if ($campType == Campaign::CAMP_TYPE_LINKEDIN) {
                $modelCampTemp->batchOffesetLinkedIn = $batchIndex;
            } else if ($campType == Campaign::CAMP_TYPE_AB_TEST_EMAIL) {
                $modelCampTemp->batchOffsetEmail = $batchIndex;
            } else {
                $modelCampTemp->batchOffset = $batchIndex;
            }

            if ($modelCampTemp->save(false)) {
                Yii::$app->appLog->writeLog("Batch offset updated.Id:{$modelCampTemp->id},offset:{$batchIndex}");
            } else {
                Yii::$app->appLog->writeLog("Batch offset update failed.Id:{$modelCampTemp->id},offset:{$batchIndex}");
            }
        } catch (Exception $e) {
            Yii::$app->appLog->writeLog("Batch offset update failed.Id:{$modelCampTemp->id},offset:{$batchIndex},error:{$e->getMessage()}");
        }
    }


    // TODO: need to refactor the email and sms track record into one method

    /**
     * Add email track record to a table on master database, In order to
     * find out that the event callback is related with which application.
     * We can configure only one callback url on Mailjet and cannot pass any
     * values to return back.
     * @param integer $appId Application id identifier
     * @param string $emailTransactionId Id returned from Mailjet API
     */
    private function addEmailTrackRecord($appId, $emailTransactionId)
    {
        $command = Yii::$app->dbMaster->createCommand();
        try {
            $command->insert('EmailEventTracker', [
                'appId' => $appId,
                'emailTransactionId' => $emailTransactionId,
                'createdAt' => date('Y-m-d H:i:s')
            ])->execute();
            Yii::$app->appLog->writeLog("Record added to EmailEventTraker table.");
        } catch (Exception $e) {
            Yii::$app->appLog->writeLog("Record add fail to EmailEventTraker table.Error:{$e->getMessage()}");
        }
    }

    /**
     * Add SMS track record to a table on master database, In order to
     * find out that the event callback is related with which application.
     * We can configure only one callback url on Clickatel and cannot pass any
     * values to return back.
     * @param integer $appId Application id identifier
     * @param string $smsId Id returned from Clickatel API
     */
    private function addSmsTrackRecord($appId, $smsId)
    {
        $command = Yii::$app->dbMaster->createCommand();
        try {
            $command->insert('SmsEventTracker', [
                'appId' => $appId,
                'smsId' => $smsId,
                'createdAt' => date('Y-m-d H:i:s')
            ])->execute();
            Yii::$app->appLog->writeLog("Record added to SmsEventTracker table.");
        } catch (Exception $e) {
            Yii::$app->appLog->writeLog("Record add fail to SmsEventTracker table.Error:{$e->getMessage()}");
        }
    }

    /**
     * Add entry to CampaignUsers table
     * @param integer $campaignId Campaign id
     * @param integer $userId User id.
     * @param integer $status Status, CampaignUsers::MESSAGE_SENT or CampaignUsers::MESSAGE_SENT_FAIL
     * @param integer $emailTransactionId Transaction id returned from Mailjet
     * @param integer $emailStatus Status of email. Relate only with email messages.
     * @param integer $smsId SMS Transaction id.
     * @param integer $smsStatus SMS sending status
     */
    private function addCampaignUser($campaignId, $userId, $status, $emailTransactionId = 0, $emailStatus = null, $smsId = null, $smsStatus = null, $count = 1)
    {
        $user = User::findOne($userId);
        $modelCampaignUsers = new CampaignUsers();
        $modelCampaignUsers->campaignId = $campaignId;
        $modelCampaignUsers->userId = $userId;
        $modelCampaignUsers->email = ToolKit::isEmpty($user->email) ? null : $user->email;
        $modelCampaignUsers->mobile = ToolKit::isEmpty($user->mobile) ? null : $user->mobile;
        $modelCampaignUsers->status = $status;
        $modelCampaignUsers->emailStatus = $emailStatus;
        $modelCampaignUsers->emailTransactionId = $emailTransactionId;
        $modelCampaignUsers->smsId = $smsId;
        $modelCampaignUsers->smsStatus = $smsStatus;
        $modelCampaignUsers->createdAt = date('Y-m-d H:i:s');

        try {
            if ($count > 1) {
                $modelCampaignSmsExceed = new CampaignSmsExceedUser();
                $modelCampaignSmsExceed->campaignId = $campaignId;
                $modelCampaignSmsExceed->userId = $userId;
                $modelCampaignSmsExceed->smsId = $smsId;
                $modelCampaignSmsExceed->createdAt = date('Y-m-d H:i:s');
                $modelCampaignSmsExceed->save(false);
            } else {
                $modelCampaignUsers->save(false);
            }
        } catch (Exception $e) {
            Yii::$app->appLog->writeLog("Record add fail to CampaignUsers.Error:{$e->getMessage()}");
        }
    }

    /**
     * Change campaign status
     * @param Campaign $modelCamp Campaign class instance
     * @param integer $status Campaign status.
     */
    private function changeCampStatus($modelCamp, $status, $rescheduleDateTime = null, $rescheduleIndex = null)
    {
        $modelCampTemp = Campaign::findOne($modelCamp->id);

        $modelCampTemp->status = $status;
        if (null != $rescheduleDateTime) {
            $modelCampTemp->startDateTime = $rescheduleDateTime;
        }

        if (null != $rescheduleIndex) {
            $modelCampTemp->batchOffset = $rescheduleIndex;
        }

        try {
            if ($modelCampTemp->save(false)) {
                Yii::$app->appLog->writeLog("Campaign status changed.Status:{$status}");
            }
        } catch (Exception $e) {
            Yii::$app->appLog->writeLog("Campaign status save failed.Error:" . $e->getMessage());
        }
    }


}
