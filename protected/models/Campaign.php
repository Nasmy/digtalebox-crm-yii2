<?php

namespace app\models;

use app\components\ToolKit;
use rmrevin\yii\fontawesome\FA;
use Yii;
use yii\db\Query;

/**
 * This is the model class for table "Campaign".
 *
 * @property int $id
 * @property int $messageTemplateId
 * @property int $searchCriteriaId
 * @property int $status 0 - pending, 1 - inprogress, 2 - finished, 3 - stopeed
 * @property string $startDateTime
 * @property string $endDateTime
 * @property int $campType 1 - email, 2 - twitter, 3 - fb
 * @property int $totalUsers
 * @property int $batchOffset Select query start index
 * @property int $batchOffsetEmail Batch offset for emails
 * @property int $batchOffsetTwitter Batch offset for Twitter messaging
 * @property int $batchOffesetLinkedIn Batch offset for LinkedIn messaging
 * @property int $aBTestId
 * @property int $createdBy
 * @property string $createdAt
 * @property int $updatedBy
 * @property string $updatedAt
 */
class Campaign extends \yii\db\ActiveRecord
{
    // Campaign statuses
    const CAMP_PENDING = 0;
    const CAMP_INPROGRESS = 1;
    const CAMP_FINISH = 2;
    const CAMP_STOP = 3;
    const CAMP_TEST_STOP = 4;
    const CAMP_SCHEDULE_WINNER = 5;

    // Campaign types
    const CAMP_TYPE_EMAIL = 1;
    const CAMP_TYPE_TWITTER = 2;
    const CAMP_TYPE_FACEBOOK = 3;
    const CAMP_TYPE_SMS = 4;
    const CAMP_TYPE_LINKEDIN = 5;
    const CAMP_TYPE_ALL = 6;
    const CAMP_TYPE_AB_TEST_EMAIL = 7;

    public $emailTemplates = array();
    public $endDate;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Campaign';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['messageTemplateId', 'searchCriteriaId', 'status', 'startDateTime', 'endDateTime', 'campType', 'batchOffset', 'createdBy', 'createdAt', 'updatedBy', 'updatedAt'], 'required'],
            [['messageTemplateId', 'searchCriteriaId', 'status', 'campType', 'totalUsers', 'batchOffset', 'batchOffsetEmail', 'batchOffsetTwitter', 'batchOffesetLinkedIn', 'aBTestId', 'createdBy', 'updatedBy'], 'integer'],
            [['startDateTime', 'endDateTime', 'createdAt', 'updatedAt'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'messageTemplateId' => Yii::t('messages', 'Message Template'),
            'searchCriteriaId' => Yii::t('messages', 'Saved Search Name'),
            'status' => Yii::t('messages', 'Status'),
            'startDateTime' => Yii::t('messages', 'Start Date Time'),
            'endDateTime' => Yii::t('messages', 'End Date Time'),
            'campType' => Yii::t('messages', 'Campaign Type'),
            'createdBy' => Yii::t('messages', 'Created By'),
            'totalUsers' => Yii::t('messages', 'Total Users')
        ];
    }


    /**
     * @param $year
     * @return false|string
     */
    function getCampaignCountByCampaignMediaTimeLine($year)
    {
        $query = new Query();
        $results = $campainMedia = $query->select("count(*) AS campaignCount, MONTH(startDateTime) AS createMonth, campType")
            ->where("DATE_FORMAT(startDateTime, '%Y') = '$year'")
            ->groupBy('createMonth, campType')
            ->from(Campaign::tableName())->all();
        $return = array();
        if ($results) {
            $email = $this->getCampaignCountByType($results, self::CAMP_TYPE_EMAIL);
            $sms = $this->getCampaignCountByType($results, self::CAMP_TYPE_SMS);
            $tw = $this->getCampaignCountByType($results, self::CAMP_TYPE_TWITTER);
             $all = $this->getCampaignCountByType($results, self::CAMP_TYPE_ALL);
            $abTest = $this->getCampaignCountByType($results, self::CAMP_TYPE_AB_TEST_EMAIL);
            $total = $this->getCampaignCountByType($results);


            $return[] = ['name' => Yii::t('messages', 'Total'), 'data' => $total];
            $return[] = ['name' => Yii::t('messages', 'Email'), 'data' => $email];
            $return[] = ['name' => Yii::t('messages', 'SMS'), 'data' => $sms];
            $return[] = ['name' => Yii::t('messages', 'Tw'), 'data' => $tw];
            $return[] = ['name' => Yii::t('messages', 'Email + Tw'), 'data' => $all];
            $return[] = ['name' => Yii::t('messages', 'AB Test'), 'data' => $abTest];
        } else {
            $return[] = ['name' => Yii::t('messages', 'Total'), 'data' => 0];
        }

        return json_encode($return);
    }

    /**
     * @param $type
     * @param $tc
     * @return string
     */
    public static function getCampaignMessage($type, $tc) {
        $res = 'Campaign created';
        switch ($type) {
            case self::CAMP_TYPE_TWITTER:
                $res = Yii::t('messages', 'Campaign created. System send messages only to maximum of {dmCount} Twitter followers per day.', ['dmCount' => FeedLimit::MAX_DM_PER_DAY]);
                break;
            case self::CAMP_TYPE_FACEBOOK:
                $res = Yii::t('messages', 'Campaign created. System send messages only to maximum of {fbCount} Facebook friends per day.', ['fbCount' => FeedLimit::MAX_FBM_PER_DAY]);
                break;
            case self::CAMP_TYPE_LINKEDIN:
                $res = Yii::t('messages', 'Campaign created. System send messages only to maximum of {lnCount} LinkedIn connections per day.', ['lnCount' => FeedLimit::MAX_LN_PER_DAY]);
                break;
            case self::CAMP_TYPE_SMS:
                $res = Yii::t('messages', 'Campaign created. Monthly SMS limit is {smsCount}.', ['smsCount' => $tc->packageInfo['monthlySmsLimit']]);
                break;
            case self::CAMP_TYPE_ALL:
                $res = Yii::t('messages', 'Campaign created. System send messages only to maximum of {dmCount} Twitter followers per day.', ['dmCount' => FeedLimit::MAX_DM_PER_DAY]);
        }

        return $res;
    }

    /**
     * @param $modelUser
     * @param $origMessage
     * @param $clientUserProfile
     * @return mixed|string|string[]|null
     */
    public function getMessage($modelUser, $origMessage, $clientUserProfile = null) {
        $find = $this->getFindKeywords();
        $findCustom = $this->getFindCustomFieldKeywords();
        $salutation = User::getSalutation($modelUser['gender']);

        $replacement = array(
            $modelUser['firstName'],
            $modelUser['lastName'],
            is_null($modelUser['mobile']) ? '' : $modelUser['mobile'],
            date('Y/m/d'),
            $salutation
        );

        $customFields = CustomField::findBySql('SELECT id as customFieldId,label From CustomField WHERE enabled=:enabled AND relatedTable=:relatedTable',
            array(':enabled' => 1, ':relatedTable' => CustomType::CF_PEOPLE))->all();

        $customColumns = array();
        foreach ($customFields as $customField) {
            if (isset($customField['label'])) {
                $customColumns[] = $customField['label'];
            }
        }

        $replacementCustom = array();
        // TODO: Need to check properly below code.
        foreach ($customColumns as $column) {
            $customField = CustomField::find()->where(['LOWER(label)' => strtolower($column)])->andWhere(['enabled' => 1])->andWhere(['relatedTable' => CustomType::CF_PEOPLE])->asArray()->one();
            if (!ToolKit::isEmpty($customField)) {
                $customValue = CustomValue::find()->where(['customFieldId' => $customField['id']])->andWhere(['relatedId' => $modelUser['id']])->asArray()->one();
                if (!ToolKit::isEmpty($customValue)) {
                    $replacementCustom[] = $customValue['fieldValue'];
                }
            }
        }

        $message = preg_replace($find, $replacement, $origMessage);
        $message = preg_replace($findCustom, $replacementCustom, $message);
        $suffix = '/' . $modelUser['id'];
        $search = KeywordUrl::SUBSCRIBE_URL;
        $message = MessageTemplate::appendUserToKeywordUrl($search, $suffix, $message);

        return $message;
    }

    /**
     * Format find keywords to replace with dynminc attributes
     * @return Mixed array $find Find keywords array
     */
    public function getFindKeywords()
    {
        $find = array(
            '"{' . MessageTemplate::FIRST_NAME . '}"',
            '"{' . MessageTemplate::LAST_NAME . '}"',
            '"{' . MessageTemplate::PHONE_NUMBER . '}"',
            '"{' . MessageTemplate::CURRENT_DATE . '}"',
            '"{' . MessageTemplate::SALUTATION . '}"'
        );

        return $find;
    }

    /**
     * Format find keywords to replace with custom field attributes
     * @return Mixed array $find Find keywords array
     */
    public function getFindCustomFieldKeywords()
    {
        $prefix = MessageTemplate::CUSTOM_PREFIX;
        $customFields = CustomField::find()
            ->select('id as customFieldId, label')
            ->where(['enabled'=>1, 'relatedTable' => CustomType::CF_PEOPLE])
            ->asArray()
            ->all();

        $find = array();
        foreach ($customFields as $customField) {
            if (isset($customField['label'])) {
                $find[] = '"{' . $prefix . $customField['label'] . '}"';
            }
        }

        return $find;
    }

    /**
     * Retrieve campaign status label
     * @param integer $status Status
     * @return string $label Status label
     */
    public static function getStatusLabel($status, $startDateTime)
    {
        $badgeCss = '';
        switch ($status) {
            case self::CAMP_PENDING:
                $badgeCss = "badge bg-bounced";
                if (strtotime($startDateTime) > time()) {
                    $label = Yii::t('messages', 'Rescheduled');
                } else {
                    $label = Yii::t('messages', 'Pending');
                }
                break;

            case self::CAMP_INPROGRESS:
                $badgeCss = "badge bg-clicked";
                $label = Yii::t('messages', 'Inprogress');
                break;

            case self::CAMP_FINISH:
                $badgeCss = "badge-info";
                 $label = Yii::t('messages', 'Finished');
                break;

            case self::CAMP_STOP:
                $badgeCss = "badge bg-bounced";
                $label = Yii::t('messages', 'Stopped');
                break;

            case self::CAMP_TEST_STOP:
                $badgeCss = "badge-info";
                $label = 'AB Test Stopped';
                break;

            case self::CAMP_SCHEDULE_WINNER:
                $badgeCss = "badge bg-bounced";
                $label = 'Schedule Winner';
                break;
        }

        return "<span class='badge {$badgeCss}'>{$label}</span>";
    }

    /**
     * @param $id
     * @param $status
     * @return bool
     */
    public static function displayABStop($id, $status)
    {
        $return = false;
        $query = new Query();
        $results = $query->select('a.fromRemain, c.status, a.createdBy, c.campType')
            ->join('INNER JOIN', 'Campaign c', 'a.id = c.aBTestId')
            ->where(['c.id' => $id]);
        $model = $results->from('ABTestingCampaign a')->all();
        if (null != $model) {
            if ($model->fromRemain == '') { //If this this not set mean the test campaign is running.
                if ($status == Campaign::CAMP_INPROGRESS || $status == Campaign::CAMP_PENDING) {
                    if (Yii::$app->user->checkAccess('Campaign.StopCampaign') && Yii::$app->user->id == $model->createdBy) {
                        $return = true;
                    }
                }

            }
        }
        return $return;
    }

    /**
     * Retrieve campaign status options or label.
     * @param integer $val Campaign status value.
     * @return mixed campaing status array or label when $val given
     */
    public static function getCampaignStatusOptions($val = null)
    {
        $arrOptions = [
            self::CAMP_PENDING => Yii::t('messages', 'Pending'),
            self::CAMP_INPROGRESS => Yii::t('messages', 'Inprogress'),
            self::CAMP_FINISH => Yii::t('messages', 'Finished'),
            self::CAMP_STOP => Yii::t('messages', 'Stopped'),
            self::CAMP_TEST_STOP => Yii::t('messages', 'AB Test Stopped'),
            self::CAMP_SCHEDULE_WINNER => Yii::t('messages', 'Schedule Winner'),
        ];

        if (null != $val) {
            return $arrOptions[$val];
        }

        return $arrOptions;
    }

    /**
     * Retrieve campaign type label when identifier given
     * @param integer $type Campaign type facebook,twitter or email
     * @return string $label Type label
     */
    public function getCampaignTypeLabel($type)
    {
        $icon = '';
        switch ($type) {
            case self::CAMP_TYPE_EMAIL:
                // $icon = Yii::$app->fa->getIcon('envelope');
                $icon = FA::icon('envelope');
                break;

            case self::CAMP_TYPE_TWITTER:
                $icon = FA::icon('twitter');
                break;

            case self::CAMP_TYPE_FACEBOOK:
                $icon = FA::icon('facebook-square');
                break;

            case self::CAMP_TYPE_SMS:
                $icon = FA::icon('tablet');
                break;

            case self::CAMP_TYPE_LINKEDIN:
                $icon = FA::icon('linkedin-square');
                break;

            case self::CAMP_TYPE_ALL:
                $icon = FA::icon('star');
                break;
            case self::CAMP_TYPE_AB_TEST_EMAIL:
                $icon = "<div style='color: green'> <b> AB</b></div>";
                break;
        }

        return $icon;
    }


    /**
     * @param $results
     * @param null $type
     * @return array
     */
    function getCampaignCountByType($results, $type = null)
    {
        $campaigns = $return = array();
        for ($m = 1; $m <= date('m', time()); $m++) {
            $campaigns[$m] = 0;
        }
        for ($m = 1; $m <= date('m', time()); $m++) {
            foreach ($results as $result) {
                if ($type == $result['campType']) {
                    if ($m == $result['createMonth']) {
                        $campaigns[$m] = (int)$result['campaignCount'];
                    } else {
                        $campaigns[$m] = (ToolKit::isEmpty($campaigns[$m])) ? 0 : $campaigns[$m];
                    }
                } elseif (null == $type) {
                    if ($m == $result['createMonth']) {
                        $campaigns[$m] = $result['campaignCount'] + $campaigns[$m];
                    } else {
                        $campaigns[$m] = (ToolKit::isEmpty($campaigns[$m])) ? 0 : $campaigns[$m];
                    }
                }
            }
        }
        foreach ($campaigns as $campaign) {
            $return[] = $campaign;
        }
        unset($campaigns);
        return $return;
    }

    /**
     * @param $postData
     * @return mixed|string
     */
    public static function getTemplateIdByPost($postData) {
        $templateId = '';
        if(isset($postData['templateEmailId']) && "" != $postData['templateEmailId']) {
            $templateId = $_POST['templateEmailId'];
        }

        if(isset($postData['templateSmsId']) && "" != $postData['templateSmsId']) {
            $templateId = $_POST['templateSmsId'];
        }

        return $templateId;
    }

    /**
     * @param $messageTemplate
     * @return array
     */
    public static function getSmsMessageList($messageTemplate) {
        $messageList = [$messageTemplate->smsMessage];
        if (!empty($messageTemplate->smsMessageTwo)) {
            array_push($messageList, $messageTemplate->smsMessageTwo);
        }
        return $messageList;
    }

    /**
     *
     * @param string $field
     * @return mixed array
     */
    public function getStatusList()
    {
        $list = array(
            self::CAMP_PENDING => Yii::t('messages', 'Pending'),
            self::CAMP_INPROGRESS => Yii::t('messages', 'In Progress'),
            self::CAMP_FINISH => Yii::t('messages', 'Finished'),
            self::CAMP_STOP => Yii::t('messages', 'Stopped'),
            self::CAMP_TEST_STOP => Yii::t('messages', 'AB Test Stopped'),
        );

        return $list;
    }

    /**
     * @return array
     */
    public function getCampaignTypeList()
    {
        $list = array(
            self::CAMP_TYPE_EMAIL => Yii::t('messages', 'Email'),
            self::CAMP_TYPE_TWITTER => Yii::t('messages', 'Twitter'),
            self::CAMP_TYPE_FACEBOOK => Yii::t('messages', 'Facebook'),
            self::CAMP_TYPE_SMS => Yii::t('messages', 'SMS'),
            self::CAMP_TYPE_LINKEDIN => Yii::t('messages', 'LinkedIn'),
            self::CAMP_TYPE_AB_TEST_EMAIL => Yii::t('messages', 'A/B Testing'),
            self::CAMP_TYPE_ALL => Yii::t('messages', 'All'),
        );

        return $list;

    }

    /**
     * @param $fromName
     * @param $fromEmail
     * @param $campId
     * @throws \yii\db\Exception
     */
    public function updateFromEmailById($fromName, $fromEmail, $campId)
    {
        $sql = "UPDATE `Campaign` SET `fromName` = '$fromName', `fromEmail` = '$fromEmail' WHERE `Campaign`.`id` = $campId;";
        $connection = Yii::$app->getDb();
        $command = $connection->createCommand($sql);
        $command->execute();
    }

    /**
     * {@inheritdoc}
     * @return CampaignQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CampaignQuery(get_called_class());
    }
}
