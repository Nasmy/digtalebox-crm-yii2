<?php
namespace app\components;

use app\components\ToolKit;
use app\models\BroadcastMessage;
use app\models\Event;
use Yii;
use app\models\Campaign;
use app\models\User;
use yii\base\Component;
use yii\db\Query;

/**
 * ThresholdChecker class which handle user package limit cheking functionalities
 */
class ThresholdChecker extends Component
{
    const EMAIL_CONTACTS = 1;
    const SOCIAL_CONTACTS = 2;
    const MONTH_SMS_LIMIT = 3;
    const BROADCAST_LIMIT = 4;
    const GEO_TAGGING_LIMIT = 5;
    // Package information
    public $packageInfo;

    // Whether console application or web application
    public $isConsole = false;

    // Next renewal date
    public $renewDate;

    // Last renewed date
    public $renewedDate;

    // Constructor
    public function __construct($packageType, $smsPackageType = null, $isConsole = false)
    {
        $this->isConsole = $isConsole;
        $this->loadPackage($packageType, $smsPackageType);
    }

    /**
     * Check whether applicatin is active or not
     * @param integer $packageType Type of the package
     * @param integer $smsPackageType SMS package type
     */
    public function loadPackage($packageType, $smsPackageType)
    {
        if (!$this->isConsole && isset(Yii::$app->session->get['packageInfo'])) {
            $this->packageInfo = Yii::$app->session->get['packageInfo'];
        } else {
            $this->packageInfo = Yii::$app->dbMaster->createCommand("SELECT * FROM Package JOIN PackageType ON Package.PackageTypeId = PackageType.id where type='$packageType'")->queryOne();
            $smsPackageInfo = Yii::$app->dbMaster->createCommand("SELECT * FROM SmsPackage where type='$smsPackageType'")->queryOne();
            if (!empty($smsPackageInfo)) {
                $this->packageInfo['monthlySmsLimit'] = $smsPackageInfo['monthlySmsLimit'];
            } else {
                $this->packageInfo['monthlySmsLimit'] = 0;
            }
            if (!$this->isConsole) {
                Yii::$app->session['packageInfo'] = $this->packageInfo;
            }
        }
    }

    /**
     * Clear session data after user logged to the sysetm
     */
    public static function clearSessionData()
    {
        unset(Yii::$app->session['packageInfo']);
    }

    /**
     * Check whether threshold has exeeded
     * @param integer $packageType Type of the package
     */
    public function isThresholdExceeded($event)
    {
        $isExceeded = false;
        switch ($event) {
            case self::EMAIL_CONTACTS:
                $curCount = $this->getCount(self::EMAIL_CONTACTS);
                if ($this->packageInfo['totalEmailContacts'] <= $curCount) {
                    Yii::$app->appLog->writeLog("Email contacts limit exceeded. Package:{$this->packageInfo['totalEmailContacts']}, Current:{$curCount}");
                    $isExceeded = true;
                }
                break;

            case self::SOCIAL_CONTACTS:
                $curCount = $this->getCount(self::SOCIAL_CONTACTS);
                if ($this->packageInfo['totalSocialContacts'] <= $curCount) {
                    Yii::$app->appLog->writeLog("Social contacts limit exceeded. Package:{$this->packageInfo['totalSocialContacts']}, Current:{$curCount}");
                    $isExceeded = true;
                }
                break;

            case self::MONTH_SMS_LIMIT:
                $curCount = $this->getCount(self::MONTH_SMS_LIMIT);
                if ($this->packageInfo['monthlySmsLimit'] <= $curCount) {
                    Yii::$app->appLog->writeLog("SMS contacts limit exceeded. Package:{$this->packageInfo['monthlySmsLimit']}, Current:{$curCount}");
                    $isExceeded = true;
                }
                break;
        }

        return $isExceeded;
    }

    /**
     * Retrive current cout for specific event type
     * @param string $event Eventy type
     * @return integer $count Db record count
     */
    public function getCount($event)
    {
        $count = 0;
        switch ($event) {
            case self::EMAIL_CONTACTS:

                $count = User::find()->where(["not", ["email" => null]])->andWhere(["!=", "email", ""])->andWhere(["!=", "userType", User::SUPER_ADMIN])->andWhere(["!=", "userType", User::POLITICIAN])->andWhere(["!=", "userType", 0])->andWhere(['isSysUser' => 0])->count();

                break;

            case self::SOCIAL_CONTACTS:
                $query = new Query();
                // $query->select()
                $results = $query->select("u.userType,COUNT(u.id) AS userCount")
                    ->join('INNER JOIN', 'User u', 'u.id = t.userId')
                    ->where('u.userType != :userType1 AND u.userType != :userType2 AND u.isSysUser = :isSysUser')
                    ->params([':userType1' => User::SUPER_ADMIN, ':userType2' => User::POLITICIAN, ':isSysUser' => 0]);
                $fbCount = $results->from('FbProfile t')->count();
                $lnCount = $results->from('LnProfile t')->count();
                $twCount = $results->from('TwProfile t')->count();
                // $gpCount = GpProfile::model()->count($criteria);
                $count = $fbCount + $lnCount + $twCount; // $gpCount;
                break;

            case self::MONTH_SMS_LIMIT:
                $query = new Query();
                $renewDate = (!ToolKit::isEmpty($this->renewDate) && (Event::DEFAULT_DATE != $this->renewDate)) ? $this->renewDate : date('Y-m-d', strtotime('last day of this month'));
                $renewedDate = (!ToolKit::isEmpty($this->renewedDate) && (Event::DEFAULT_DATE != $this->renewedDate)) ? $this->renewedDate : date('Y-m-01');

                $results = $query->join('INNER JOIN', 'Campaign C', 't.campaignId = C.id')
                    ->where('C.campType = :campType')
                    ->andWhere('DATE_FORMAT(t.createdAt, \'%Y-%m-%d\') <= :renewDate AND DATE_FORMAT(t.createdAt, \'%Y-%m-%d\') >= :renewedDate')
                    ->addParams([
                        ':campType' => Campaign::CAMP_TYPE_SMS,
                        ':renewDate' => $renewDate,
                        ':renewedDate' => $renewedDate
                    ]);

                $count = $results->from('CampaignUsers t')->count();
                $smsExceedCount = $results->from('CampaignSmsExceedUser t')->count();

                $count = $count + $smsExceedCount;

                break;

            case self::BROADCAST_LIMIT:
                $count = BroadcastMessage::find()->where("recordStatus = :recordStatus OR recordStatus = :recordStatus1")->addParams([
                    ":recordStatus" => BroadcastMessage::REC_STATUS_PENDING,
                    ":recordStatus1" => BroadcastMessage::REC_STATUS_DRAFT
                ])->count();
                break;
        }

        return $count;
    }

    /**
     * Retrive remaining email or socila contacts count
     * @param string $event Eventy type
     * @return integer $count Remaingng record count
     */
    public function getRemainingCount($event)
    {
        $remainingCount = 0;
        switch ($event) {
            case self::EMAIL_CONTACTS:
                $remainingCount = ($this->packageInfo['totalEmailContacts'] - $this->getCount(self::EMAIL_CONTACTS));
                break;

            case self::SOCIAL_CONTACTS:
                $remainingCount = ($this->packageInfo['totalSocialContacts'] - $this->getCount(self::SOCIAL_CONTACTS));
                break;

            case self::BROADCAST_LIMIT:
                $configModel = Configuration::model()->findByPk('IS_JOINED_VIA_INVITATION');
                $count = 0;
                if ($configModel->value) {
                    $count = 1;
                }
                $acceptedInvitationCount = Invitation::model()->count('isJoined=:isJoined', array(':isJoined' => 1));
                $remainingCount = (($this->packageInfo['maxBroadcastMsgs'] + $acceptedInvitationCount + $count) - $this->getCount(self::BROADCAST_LIMIT));
                break;
            case self:: GEO_TAGGING_LIMIT:
                if (isset($this->packageInfo['geotaggingLimit'])) {
                    $remainingCount = $this->packageInfo['geotaggingLimit'];
                }
                break;
        }

        return $remainingCount;
    }
}

?>
