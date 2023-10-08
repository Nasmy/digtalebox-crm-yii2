<?php

namespace app\models;

use app\components\RActiveRecord;
use app\components\ToolKit;
use Yii;
use yii\db\Query;

/**
 * This is the model class for table "App".
 *
 * @property string $domain Domain name.Ex:charles.digitalebox.com
 * @property int $appId Id for application
 * @property string $masterUserId
 * @property string $appUserId
 * @property string $dbName Database name
 * @property string $host Database host
 * @property string $username Database username
 * @property string $password Database password
 * @property string $createdAt
 * @property string $renewDate Renewal date
 * @property string $renewedDate Account renewed date
 * @property int $status 1 - active, 2 - inactive
 * @property int $packageType Type of the package reffering to App type
 * @property int $smsPackageType User selected SMS package type
 * @property int $longTermPackageType
 * @property int $isAppCreated 0-not-created,1-created
 * @property string $ppPayerId Paypal payer id
 * @property string $ppSubScrId Subscription profile id
 * @property string $stripeCustomerId
 * @property int $subscrStatus Subscription status. 1 - Active, 2 - Suspend, 3 - Cancel
 * @property string $reSubTime Resubscribed time
 * @property string $upgradeTime Upgrade time
 * @property int $isDefault Whether default app or not. Charging processes skip these apps
 */
class App extends RActiveRecord
{
    const APP_ACTIVE = 1;
    const APP_INACTIVE = 2;
    const FREEMIUM_PLAN_TYPE = 100;
    const FREE7DAY_PLAN_TYPE = 1;

    // Package Type Ids
    const FREEMIUM = 1; // Freemium
    const CM = 2; // Community Management
    const OC = 3; // Organizing Community

    public $packageTypeId;

    const NOTIFICATION_MESSAGE_COUNT = 6;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'App';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['domain, connectionString, username, password'], 'required'],
            [['username, password'], 'length', 'max' => 45],
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            [['domain, connectionString, username, password'], 'safe', 'on' => 'search'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'domain' => 'Domain',
            'connectionString' => 'Connection String',
            'username' => 'Username',
            'password' => 'Password',
        ];
    }

    public static function getVolunteerCount()
    {
        return User::find()->where("signup = :signup AND userType = :userType", array(
            ":signup" => 1,
            ":userType" => User::SUPPORTER,
        ))->count();

    }

    /**
     * @return array
     */
    public static function getAppModels($stat = false)
    {
        $query = new Query();
        if ($stat) {
            return $query->select('a.*, P.PackageTypeId AS packageTypeId, e.createdAt as eventAt')
                ->innerJoin('Package P', 'a.packageType = P.type')
                ->innerJoin('EmailEventTracker e', 'a.appId = e.appId')
                ->where(['!=', 'domain', Yii::$app->params['masterDomain']])
                ->andWhere('DATE_FORMAT(e.createdAt, \'%Y-%m-%d\') = :today')
                ->andWhere(['status' => self::APP_ACTIVE])
                ->from('App a')
                ->groupBy('e.appId')
                ->addParams([
                    ':today' => date('Y-m-d'),
                ])
                ->all();
        }
        return $query->select('a.*, P.PackageTypeId AS packageTypeId')
            ->innerJoin('Package P', 'a.packageType = P.type')
            ->where(['!=', 'domain', Yii::$app->params['masterDomain']])
            ->andWhere(['status' => self::APP_ACTIVE])
            ->from('App a')
            ->all();
    }

    /**
     * get the total count of new messages
     * @return string the number of rows satisfying
     */
    public static function getNewMessageCount()
    {
        return MsgBox::find()->where("receiverUserId = :receiverUserId AND folder = :folder AND status = :status",
            array(
                ":receiverUserId" => Yii::$app->user->id,
                ":folder" => MsgBox::FOLDER_INBOX,
                ":status" => MsgBox::MSG_STATUS_NEW
            ))->count();
    }

    /**
     * get the summary of recent 5 new messages
     * @return string the number of messages
     */
    public static function getMessageSummary()
    {
        $query = new Query();
        $query->select('*')
            ->from('MsgBox')
            ->where('receiverUserId = :receiverUserId AND folder = :folder AND status = :status', [
                ':receiverUserId' => Yii::$app->user->id,
                ':folder' => MsgBox::FOLDER_INBOX,
                ':status' => MsgBox::MSG_STATUS_NEW
            ]);
        $command = $query->createCommand();
        $results = $command->queryAll();
        return $results;

    }

    /**
     * returns the keywords id string or null.
     * @param $keywords
     * @return string|null|array
     */
    public function saveKeywords($keywords, $userId = null)
    {
        $result = array();
        if (ToolKit::isEmpty($keywords)) {
            return null;
        }

        $userId = !empty($userId) ? $userId : 1;
        if (preg_match('/[\'^£$%&*()}{#?><>|=+¬]/', $keywords)) {
            $result[] = $keywords;
            return implode(",", $result);
        }
        $keywords = explode(",", $keywords); //csv string to array
        Yii::$app->appLog->writeLog("enter keyword");
        foreach ($keywords as $val) {
            if ($this->checkKeywordsExist($val)) { // keyword exist
                $result[] = Keyword::findOne(['name' => $val])->id;
            } else { //new keyword
                $model = new Keyword();
                $model->name = $val;
                $model->behaviour = Keyword::KEY_MANUAL;
                $model->status = Keyword::KEY_ACTIVE;
                $model->createdBy = $userId;
                $model->createdAt = date('Y-m-d H:i:s');
                $model->lastUpdated = date('Y-m-d H:i:s');
                $model->updatedBy = $userId;
                $model->updatedAt = date('Y-m-d H:i:s');
                $model->save(false);
                $result[] = $model->id;
            }
        }
        return implode(",", $result);
    }


    /**
     * check for the keyword existence with keyword name
     * @param $keyword
     * @return bool
     */
    public function checkKeywordsExist($keyword)
    {
        $result = false;
        $model = Keyword::find()->where('name=:name', array(':name' => $keyword))->one();
        if (!is_null($model)) {
            $result = true;
        }
        return $result;

    }

    /**
     * @param $packageId
     * @return bool
     */
    public static function checkIsFreemiumPackage($packageId): bool
    {
        if ($packageId == self::FREEMIUM) {
            return true;
        }
        return false;
    }

    /**
     * {@inheritdoc}
     * @return AppQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new AppQuery(get_called_class());
    }
}
