<?php

namespace app\models;

use app\components\MailjetApi;
use app\components\RActiveRecord;
use app\components\ToolKit;
use app\components\Validations\ValidateMailjetAccount;
use app\components\Validations\ValidateMailjetApiKey;
use app\components\Validations\ValidateSMSSenderId;
use Mailjet\Client;
use weluse\mailjet\Mailer;
use Yii;
use yii\db\Exception;
use yii\db\Query;
use app\components\Validations\ValidateDonationType;

/**
 * This is the model class for table "Configuration".
 *
 * @property string $key
 * @property string $value
 */
class Configuration extends RActiveRecord
{
    const TW_SEARCH_SINCE_ID = 'TW_SEARCH_SINCE_ID';
    const LANGUAGE = 'LANGUAGE';
    const TIME_ZONE = 'TIME_ZONE';
    const DEFAULT_TIME_ZONE = '43';
    const FROM_EMAIL = 'FROM_EMAIL';
    const FROM_EMAIL_1 = 'FROM_EMAIL_1';
    const FROM_EMAIL_2 = 'FROM_EMAIL_2';
    const FROM_EMAIL_3 = 'FROM_EMAIL_3';
    const FROM_EMAIL_4 = 'FROM_EMAIL_4';
    const FROM_NAME = 'FROM_NAME';
    const FROM_NAME_1 = 'FROM_NAME_1';
    const FROM_NAME_2 = 'FROM_NAME_2';
    const FROM_NAME_3 = 'FROM_NAME_3';
    const FROM_NAME_4 = 'FROM_NAME_4';

    const FB_PAGE = 'FB_PAGE';
    const PAYPAL_ID = 'PAYPAL_ID';
    const CURRENCY = 'CURRENCY';
    const VALIDATE_VOLUNTEER = 'VALIDATE_VOLUNTEER';
    const LN_PAGE = 'LN_PAGE';
    const EXCLUDE_PERSONAL_FB_CONTACTS = 'EXCLUDE_PERSONAL_FB_CONTACTS';
    const MAILJET_USERNAME = 'MAILJET_USERNAME';
    const MAILJET_PASSWORD = 'MAILJET_PASSWORD';

    const TW_AUTO_FOLLOW = 'TW_AUTO_FOLLOW';
    const TW_AUTO_UNFOLLOW = 'TW_AUTO_UNFOLLOW';
    const TW_ACCOUNTS = 'TW_ACCOUNTS';
    const IG_AUTO_FOLLOW = 'IG_AUTO_FOLLOW';
    const IG_AUTO_UNFOLLOW = 'IG_AUTO_UNFOLLOW';
    const IG_ACCOUNTS = 'IG_ACCOUNTS';
    const CLIENT_FIRST_TIME_SITE_GUIDE = 'CLIENT_FIRST_TIME_SITE_GUIDE';
    const LAST_USER_MATCH_DATE = 'LAST_USER_MATCH_DATE';

    const DONATION_TYPE_PAYPAL = 1;
    //const DONATION_TYPE_STRIPE = 2;

    const CHANGE_ORG_API_KEY = 'CHANGE_ORG_API_KEY';
    const CHANGE_ORG_SECRET_KEY = 'CHANGE_ORG_SECRET_KEY';
    const CHANGE_ORG_EMAIL = 'CHANGE_ORG_EMAIL';

    const CUSTOM_FIELD_LIMIT = 'CUSTOM_FIELD_LIMIT';
    const STRIPE_CLIENT_ID = 'STRIPE_CLIENT_ID';
    const STRIPE_SECRET_ID = 'STRIPE_SECRET_ID';

    const SMS_SENDER_ID = 'SMS_SENDER_ID';
    const SMS_SENDER_COUNTRY = 'SMS_SENDER_COUNTRY';

    public $language = null;
    public $timezone = null;
    public $fromEmail = null;
    public $fromEmail_1 = null;
    public $fromEmail_2 = null;
    public $fromEmail_3 = null;
    public $fromEmail_4 = null;

    public $fromName = null;
    public $fromName_1 = null;
    public $fromName_2 = null;
    public $fromName_3 = null;
    public $fromName_4 = null;

    public $fbPage = null;
    public $lnPage = null;
    public $paypalId = null;
    public $fbPageId = null;
    public $lnPageId = null;
    public $currency = null;
    public $validateVolunteer = null;
    public $excludeFbPersonalContacts = null;
    public $twAutoFollow = null;
    public $twAutoUnfollow = null;
    public $twAccounts = null;
    public $igAutoFollow = null;
    public $igAutoUnfollow = null;
    public $igAccounts = null;
    public $mailjetUsername = null;
    public $mailjetPassword = null;
    public $donationType = array();
    //public $stripeId = null;
    public $newsletterEmbed;
    public $changeOrgApiKey = null;
    public $changeOrgSecretKey = null;
    public $changeOrgEmail = null;
    public $smsSenderId = null;
    public $smsSenderCountry = null;
    public $stripeClientId = null;
    public $stripeSecretId = null;
    public $curMjUsername = '';
    public $curMjPassword = '';

    private $_donationTypeList = array(
        self::DONATION_TYPE_PAYPAL => 'Paypal',
        //self::DONATION_TYPE_STRIPE => 'Stripe',
    );

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Configuration';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['key', 'value'], 'required', 'on' => 'update'],
            [['key'], 'string', 'max' => 64, 'on' => 'update'],
            [['value'], 'string', 'max' => 60, 'on' => 'update'],
            [['language', 'fromEmail', 'fromName'], 'required', 'on' => 'configForm'],
            [['fromEmail', 'fromName', 'fbPage', 'paypalId', 'lnPageId', 'lnPage'], 'string', 'max' => 60, 'on' => 'configForm'],
            [['fromEmail_1'], 'email', 'on' => 'configForm'],
            [['fromEmail_2'], 'email', 'on' => 'configForm'],
            [['fromEmail_3'], 'email', 'on' => 'configForm'],
            [['fromEmail_4'], 'email', 'on' => 'configForm'],
            [['fromEmail', 'fromEmail_1', 'fromEmail_2', 'fromEmail_3', 'fromEmail_4', 'fromName', 'fromName_1', 'fromName_2', 'fromName_3', 'fromName_4', 'fbPage', 'paypalId', 'lnPageId', 'lnPage'], 'string', 'max' => 60, 'on' => 'configForm'],
            [['paypalId'], ValidateDonationType::className()],
            [['mailjetUsername', 'mailjetPassword'], ValidateMailjetAccount::className(), 'on' => 'configForm'],
            [['mailjetUsername'], ValidateMailjetApiKey::className(), 'on' => 'configForm'],
            [['smsSenderId'], 'string', 'min' => 4, 'on' => 'configForm'],
            [['smsSenderId'], ValidateSMSSenderId::className(), 'on' => 'configForm'],
            [['smsSenderCountry'], 'safe'],
            [['timezone', 'language', 'currency', 'validateVolunteer', 'excludeFbPersonalContacts', 'twAutoFollow', 'twAutoUnfollow', 'twAccounts', 'igAutoFollow', 'igAutoUnfollow', 'igAccounts', 'mailjetUsername', 'mailjetPassword', 'donationType', 'newsletterEmbed', 'changeOrgApiKey', 'changeOrgSecretKey', 'changeOrgEmail', 'smsSenderId', 'stripeClientId', 'stripeSecretId'], 'safe', 'on' => 'configForm'],
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            [['key', 'value'], 'safe', 'on' => 'search'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'key' => 'Key',
            'value' => 'Value',
            'fromEmail' => Yii::t('messages', 'From Email'),
            'fromEmail_1' => Yii::t('messages', 'From Email 1'),
            'fromEmail_2' => Yii::t('messages', 'From Email 2'),
            'fromEmail_3' => Yii::t('messages', 'From Email 3'),
            'fromEmail_4' => Yii::t('messages', 'From Email 4'),
            'fromName' => Yii::t('messages', 'From Name'),
            'fromName_1' => Yii::t('messages', 'From Name 1'),
            'fromName_2' => Yii::t('messages', 'From Name 2'),
            'fromName_3' => Yii::t('messages', 'From Name 3'),
            'fromName_4' => Yii::t('messages', 'From Name 4'),
            'fbPage' => Yii::t('messages', 'Facebook Page'),
            'lnPageId' => Yii::t('messages', 'LinkedIn Page'),
            'paypalId' => Yii::t('messages', 'Paypal Id'),
            'currency' => Yii::t('messages', 'Currency'),
            'lnPage' => Yii::t('messages', 'LinkedIn Page'),
            'validateVolunteer' => Yii::t('messages', 'Validate Volunteer'),
            'excludeFbPersonalContacts' => Yii::t('messages', 'Exclude Facebook Personal Contacts'),
            'twAutoFollow' => Yii::t('messages', 'Twitter Auto Follow'),
            'twAutoUnfollow' => Yii::t('messages', 'Twitter Auto Unfollow'),
            'twAccounts' => Yii::t('messages', 'Twitter Account'),
            'igAutoFollow' => Yii::t('messages', 'Instagram Auto Follow'),
            'igAutoUnfollow' => Yii::t('messages', 'Instagram Auto Unfollow'),
            'igAccounts' => Yii::t('messages', 'Instagram Account'),
            'mailjetUsername' => Yii::t('messages', 'MailJet Username'),
            'mailjetPassword' => Yii::t('messages', 'MailJet Password'),
            'donationType' => Yii::t('messages', 'Donation Type'),
            //'stripeId' => Yii::t('messages', 'Stripe Id'),
            'newsletterEmbed' => Yii::t('messages', 'Embed Newsletter'),
            'changeOrgApiKey' => Yii::t('messages', 'Change.org Api Key'),
            'changeOrgSecretKey' => Yii::t('messages', 'Change.org Secret Key'),
            'changeOrgEmail' => Yii::t('messages', 'Change.org Email'),
            'stripeClientId' => Yii::t('messages', 'Stripe Client Id'),
            'stripeSecretId' => Yii::t('messages', 'Stripe Secret Id'),
            'smsSenderId' => Yii::t('messages', 'SMS Sender Id'),
            'smsSenderCountry' => Yii::t('messages', 'SMS Sender Country'),
            'language' => Yii::t('messages', 'Languages'),
            'timezone' => Yii::t('messages', 'Time Zone'),
        ];
    }

    /**
     * @return array
     */
    public function getDonationTypeList()
    {
        return $this->_donationTypeList;
    }

    /**
     * @return bool
     */
    public static function isClientSmtpSet()
    {
        $return = true;
        $config = Configuration::getConfigurations();
        if (ToolKit::isEmpty($config[Configuration::MAILJET_USERNAME])
            || ToolKit::isEmpty($config[Configuration::MAILJET_PASSWORD])) {
            $return = false;
        }
        return $return;
    }

    /**
     * @return bool
     */
    public static function isClientSmsSet()
    {
        $return = true;
        $config = Configuration::getConfigurations();
        if (!array_key_exists(Configuration::SMS_SENDER_COUNTRY, $config) || ToolKit::isEmpty($config[Configuration::SMS_SENDER_COUNTRY])) {
            $return = false;
        }
        return $return;
    }

    /**
     * @return array
     * @description get user configuration data.
     */
    public static function getConfigurations()
    {
        $configurations = array();
        $configModels = Configuration::find()->all();

        foreach ($configModels as $configModel) {
            $configurations[$configModel->key] = $configModel->value;
        }

        return $configurations;
    }

    /**
     * Retrive time zone selection dropdown options
     * @return mixed array $options Time Zone array
     */
    public function getTimeZoneOptions($key = null)
    {
        $options = array(0 => "America/Noronha",
            0 => "- Select Time Zone -",
            1 => "America/Nassau",
            2 => "Asia/Thimphu",
            3 => "Africa/Gaborone",
            4 => "Europe/Minsk",
            5 => "America/Belize",
            6 => "America/St_Johns",
            7 => "Indian/Cocos",
            8 => "Africa/Kinshasa",
            9 => "Africa/Bangui",
            10 => "Africa/Brazzaville",
            11 => "Europe/Zurich",
            12 => "Africa/Abidjan",
            13 => "Pacific/Rarotonga",
            14 => "America/Santiago",
            15 => "Africa/Douala",
            16 => "Asia/Shanghai",
            17 => "America/Bogota",
            18 => "America/Costa_Rica",
            19 => "America/Havana",
            20 => "Atlantic/Cape_Verde",
            21 => "America/Curacao",
            22 => "Indian/Christmas",
            23 => "Asia/Nicosia",
            24 => "Europe/Prague",
            25 => "Europe/Berlin",
            26 => "Africa/Djibouti",
            27 => "Europe/Copenhagen",
            28 => "America/Dominica",
            29 => "America/Santo_Domingo",
            30 => "Africa/Algiers",
            31 => "America/Guayaquil",
            32 => "Europe/Tallinn",
            33 => "Africa/Cairo",
            34 => "Africa/El_Aaiun",
            35 => "Africa/Asmara",
            36 => "Europe/Madrid",
            37 => "Africa/Addis_Ababa",
            38 => "Europe/Helsinki",
            39 => "Pacific/Fiji",
            40 => "Atlantic/Stanley",
            41 => "Pacific/Chuuk",
            42 => "Atlantic/Faroe",
            43 => "Europe/Paris",
            44 => "Africa/Libreville",
            45 => "Europe/London",
            46 => "America/Grenada",
            47 => "Asia/Tbilisi",
            48 => "America/Cayenne",
            49 => "Europe/Guernsey",
            50 => "Africa/Accra",
            51 => "Europe/Gibraltar",
            52 => "America/Godthab",
            53 => "Africa/Banjul",
            54 => "Africa/Conakry",
            55 => "America/Guadeloupe",
            56 => "Africa/Malabo",
            57 => "Europe/Athens",
            58 => "Atlantic/South_Georgia",
            59 => "America/Guatemala",
            60 => "Pacific/Guam",
            61 => "Africa/Bissau",
            62 => "America/Guyana",
            63 => "Asia/Hong_Kong",
            64 => "America/Tegucigalpa",
            65 => "Europe/Zagreb",
            66 => "America/Port-au-Prince",
            67 => "Europe/Budapest",
            68 => "Asia/Jakarta",
            69 => "Europe/Dublin",
            70 => "Asia/Jerusalem",
            71 => "Europe/Isle_of_Man",
            72 => "Asia/Kolkata",
            73 => "Indian/Chagos",
            74 => "Asia/Baghdad",
            75 => "Asia/Tehran",
            76 => "Atlantic/Reykjavik",
            77 => "Europe/Rome",
            78 => "Europe/Jersey",
            79 => "America/Jamaica",
            80 => "Asia/Amman",
            81 => "Asia/Tokyo",
            82 => "Africa/Nairobi",
            83 => "Asia/Bishkek",
            84 => "Asia/Phnom_Penh",
            85 => "Pacific/Tarawa",
            86 => "Indian/Comoro",
            87 => "America/St_Kitts",
            88 => "Asia/Pyongyang",
            89 => "Asia/Seoul",
            90 => "Asia/Kuwait",
            100 => "America/Cayman",
            101 => "Asia/Almaty",
            102 => "Asia/Vientiane",
            103 => "Asia/Beirut",
            104 => "America/St_Lucia",
            105 => "Europe/Vaduz",
            106 => "Asia/Colombo",
            107 => "Africa/Monrovia",
            108 => "Africa/Maseru",
            110 => "Europe/Vilnius",
            111 => "Europe/Luxembourg",
            112 => "Europe/Riga",
            113 => "Africa/Tripoli",
            114 => "Africa/Casablanca",
            115 => "Europe/Monaco",
            116 => "Europe/Chisinau",
            117 => "Europe/Podgorica",
            118 => "America/Marigot",
            119 => "Indian/Antananarivo",
            120 => "Pacific/Majuro",
            121 => "Europe/Skopje",
            122 => "Africa/Bamako",
            123 => "Asia/Rangoon",
            124 => "Asia/Ulaanbaatar",
            125 => "Asia/Macau",
            126 => "Pacific/Saipan",
            127 => "America/Martinique",
            128 => "Africa/Nouakchott",
            129 => "America/Montserrat",
            130 => "Europe/Malta",
            131 => "Indian/Mauritius",
            132 => "Indian/Maldives",
            133 => "Africa/Blantyre",
            134 => "America/Mexico_City",
            135 => "Asia/Kuala_Lumpur",
            136 => "Africa/Maputo",
            137 => "Africa/Windhoek",
            138 => "Pacific/Noumea",
            139 => "Africa/Niamey",
            140 => "Pacific/Norfolk",
            141 => "Africa/Lagos",
            142 => "America/Managua",
            143 => "Europe/Amsterdam",
            144 => "Europe/Oslo",
            145 => "Asia/Kathmandu",
            146 => "Pacific/Nauru",
            147 => "Pacific/Niue",
            148 => "Pacific/Auckland",
            149 => "Asia/Muscat",
            150 => "America/Panama",
            151 => "America/Lima",
            152 => "Pacific/Tahiti",
            153 => "Pacific/Port_Moresby",
            154 => "Asia/Manila",
            155 => "Asia/Karachi",
            156 => "Europe/Warsaw",
            157 => "America/Miquelon",
            158 => "Pacific/Pitcairn",
            159 => "America/Puerto_Rico",
            160 => "Asia/Gaza",
            161 => "Europe/Lisbon",
            162 => "Pacific/Palau",
            163 => "America/Asuncion",
            164 => "Asia/Qatar",
            165 => "Indian/Reunion",
            166 => "Europe/Bucharest",
            167 => "Europe/Belgrade",
            168 => "Europe/Kaliningrad",
            169 => "Africa/Kigali",
            170 => "Asia/Riyadh",
            171 => "Pacific/Guadalcanal",
            172 => "Indian/Mahe",
            173 => "Africa/Khartoum",
            174 => "Europe/Stockholm",
            175 => "Asia/Singapore",
            176 => "Asia/Singapore",
            177 => "Europe/Ljubljana",
            178 => "Arctic/Longyearbyen",
            179 => "Europe/Bratislava",
            180 => "Africa/Freetown",
            181 => "Europe/San_Marino",
            182 => "Africa/Dakar",
            183 => "Africa/Mogadishu",
            184 => "America/Paramaribo",
            185 => "Africa/Juba",
            186 => "Africa/Sao_Tome",
            187 => "America/El_Salvador",
            188 => "America/Lower_Princes",
            189 => "Asia/Damascus",
            190 => "Africa/Mbabane",
            191 => "America/Grand_Turk",
            192 => "Africa/Ndjamena",
            193 => "Indian/Kerguelen",
            194 => "Africa/Lome",
            195 => "Asia/Bangkok",
            196 => "Asia/Dushanbe",
            197 => "Pacific/Fakaofo",
            198 => "Asia/Dili",
            199 => "Asia/Ashgabat",
            200 => "Africa/Tunis",
            201 => "Pacific/Tongatapu",
            202 => "Europe/Istanbul",
            203 => "America/Port_of_Spain",
            204 => "Pacific/Funafuti",
            205 => "Asia/Taipei",
            206 => "Africa/Dar_es_Salaam",
            207 => "Europe/Kiev",
            208 => "Africa/Kampala",
            209 => "Pacific/Johnston",
            210 => "America/New_York",
            211 => "America/Montevideo",
            212 => "Asia/Samarkand",
            213 => "Europe/Vatican",
            214 => "America/St_Vincent",
            215 => "America/Caracas",
            216 => "America/Tortola",
            217 => "America/St_Thomas",
            218 => "Asia/Ho_Chi_Minh",
            219 => "Pacific/Efate",
            220 => "Pacific/Wallis",
            221 => "Pacific/Apia",
            222 => "Asia/Aden",
            223 => "Indian/Mayotte",
            224 => "Africa/Johannesburg",
            225 => "Africa/Lusaka",
            226 => "Africa/Harare");

        asort($options);

        if (null != $key) {
            return isset($options[$key]) ? $options[$key] : '';
        }

        return $options;
    }

    /**
     * Retrive time zone if the system
     * @return string $timezone Time Zone string
     */
    public function getTimeZone()
    {
        $timeZoneModel = Configuration::findOne(Configuration::TIME_ZONE);

        if ($timeZoneModel->value != 0) {
            $timeZone = $timeZoneModel->value;
        } else {
            $timeZone = Configuration::DEFAULT_TIME_ZONE;
        }

        $timeZone = Configuration::getTimeZoneOptions($timeZone);

        return $timeZone;
    }

    /**
     * @throws Exception
     */
    public function addNewKeysForFromEmail()
    {
        $sql = '';
        for ($i = 1; $i <= 4; $i++) {
            $sql .= "INSERT INTO `Configuration` (`key`, `value`) VALUES ('FROM_EMAIL_$i', ''); ";
            $sql .= "INSERT INTO `Configuration` (`key`, `value`) VALUES ('FROM_NAME_$i', ''); ";
        }
        $sql .= "ALTER TABLE `Campaign` ADD `fromName` VARCHAR(50) NOT NULL AFTER `status`, ADD `fromEmail` VARCHAR(50) NOT NULL AFTER `fromName`;";
        $command = Yii::$app->db->createCommand($sql);
        $command->execute();
    }

    /**
     * Set MailJet event callback URL after configuring user`s own MailJet account
     */
    public function setEventCallbackUrl()
    {
        $config = Configuration::getConfigurations();
        $username = $config[Configuration::MAILJET_USERNAME];
        $password = $config[Configuration::MAILJET_PASSWORD];

        if ('' != $username && '' != $password) {
            $mj = new MailjetApi($username, $password);
            $res = $mj->getApiKeyId();
            $keyInfo = json_decode($res, true);
            $apiKeyId = @$keyInfo['Data'][0]['ID'];
            $eventTypes = array('open', 'click', 'bounce', 'spam', 'blocked');
            if ('' != $apiKeyId) {
                foreach ($eventTypes as $event) {
                    $params = array(
                        'APIKeyID' => $apiKeyId,
                        'EventType' => $event,
                        'IsBackup' => false,
                        'Url' => Yii::$app->params['smtp']['eventCallbackUrl']
                    );

                    $res = $mj->setCallbackUrl($params);
                }
            }
        }
    }

    /**
     * Remove existing callback URLs if user removed or chnaged API keys
     * @param string $username Current MailJet account username
     * @param string $password Current MailJet account password
     */
    public function removeEventCallbackUrl($username, $password)
    {
        $mj = new MailjetApi($username, $password);
        $eventTypes = array('open', 'click', 'bounce', 'spam', 'blocked');

        foreach ($eventTypes as $event) {
            $res = $mj->removeCallbackUrlByEvent($event);
            Yii::$app->appLog->writeLog("Response for removing callback URL:{$res}");
        }
    }

    /**
     * Retrive language selection dropdown options
     * @return mixed array $options Language array
     */
    public function getLanguageOptions()
    {
        $options = array();

        foreach (Yii::$app->params['lang'] as $key => $langInfo) {
            $options[$langInfo['identifier']] = $langInfo['name'];
        }

        return $options;
    }

    /**
     * Retrieve configaration from email options
     * @return array $arrOptions Available from email options
     * @return integer $criteriaType Criteria type. self::ADVANCED, self::BASIC, 3 - all
     * @throws \yii\db\Exception
     */
    public static function getEmailNameById($id)
    {
        $arrOptions = array();
        $rows_name = Yii::$app->db->createCommand('select * from Configuration where Configuration.key like \'FROM_NAME%\' AND Configuration.key!=\'FROM_NAME_NOTIFICATION\' AND Configuration.value!=\'\'')->queryAll();
        if ($rows_name) {
            foreach ($rows_name as $key => $row) {
                $arrOptions[0] = '';
                $arrOptions[$key + 1] = $row['value'];
            }
        }
        return $arrOptions[$id];
    }

    /**
     * @description Get Email Sender Information
     * @return array
     * @throws Exception
     */
    public static function getConfigFromEmailOptions()
    {

        $arrOptions = array();
        $rows_name = Yii::$app->db->createCommand('select * from Configuration where Configuration.key like \'FROM_NAME%\' AND Configuration.key!=\'FROM_NAME_NOTIFICATION\' AND Configuration.value!=\'\'')->queryAll();

        if ($rows_name) {
            foreach ($rows_name as $key => $row) {
                $arrOptions[0] = Yii::t('messages', '- Select From Email -');
                $arrOptions[$key + 1] = $row['value'];
            }
        }
        return $arrOptions;
    }


    /**
     * @description Get SMS Api info by Selected Sender Country
     * @param false $allInfo
     * @return mixed|string
     * @throws Exception
     */
    public static function getConfigFromSmsOption($allInfo = false)
    {
        $arrOptions = array();
        $rows_name = Yii::$app->db->createCommand('select * from Configuration where Configuration.key = "SMS_SENDER_COUNTRY" AND Configuration.value!=\'\'')->queryOne();
        if (!empty($rows_name)) {
            $smsApiList = Yii::$app->params['smsApi'];
            $key = $rows_name['value'];
            if ($allInfo) {
                return $smsApiList[$key];
            }
            return $smsApiList[$key]['mobile'];
        }

        return '';

    }

    /**
     * @return array
     * @description Return the array of SMS Sender
     */
    public static function getSmsSenderCountry()
    {
        $smsApiList = Yii::$app->params['smsApi'];
        $smsSenderCountry = ['' => Yii::t('messages', '-- select your sms sender --')];

        foreach ($smsApiList as $key => $value) {
            $selectKey = $key;
            $selectValue = $smsApiList[$key]['countryName'] . ' : ' . $smsApiList[$key]['mobile'];
            $smsSenderCountry[$selectKey] = $selectValue;

        }

        return $smsSenderCountry;
    }

    /**
     * @param $id
     * @return mixed|string
     * @throws Exception
     */
    public static function getEmailById($id)
    {
        $arrOptions = array();
        $rows_email = Yii::$app->db->createCommand('select * from Configuration where Configuration.key like \'FROM_EMAIL%\' AND Configuration.key!=\'FROM_EMAIL_NOTIFICATION\' AND Configuration.value!=\'\'')->queryAll();
        foreach ($rows_email as $key => $row) {
            $arrOptions[0] = '';
            $arrOptions[$key + 1] = $row['value'];
        }
        return $arrOptions[$id];
    }

    /**
     * @param $models
     * @return array
     */
    public function setConfigurationAttributes($models)
    {
        $configFormAttribute = [];
        foreach ($models as $model) {
            $key = $this->getConfigurationAttByKey($model->key);
            $value = $model->value;
            $configFormAttribute[$key] = $value;
        }
        return $configFormAttribute;
    }

    /**
     * @param $postData
     * @return bool
     */
    public function updateConfigurationData($postData)
    {
        $dataFailed = false;
        $updateMjEventUrl = false;
        $curMjUsername = '';
        $curMjPassword = '';
        foreach ($postData as $key => $value) {
            $postDbKey = $this->getConfigurationKeyByAttr($key);
            if (empty($postDbKey)) {
                continue;
            }
            $data = Configuration::find()->where(['key' => $postDbKey])->one();
            if ($postDbKey == self::MAILJET_USERNAME) {
                $curMjUsername = $data->value;
                $updateMjEventUrl = ($value != $data->value) ? true : false;
            }

            if ($postDbKey == self::MAILJET_PASSWORD) {
                $curMjPassword = $data->value;
            }

            try {
                if (!empty($data)) {
                    $data->value = $value;
                    $data->save(false);
                    Yii::$app->appLog->writeLog("Configuration updated.Key:{$key},Value:{$value}");
                } else {
                    Yii::$app->db->createCommand()->insert('Configuration', [
                        'key' => $postDbKey,
                        'value' => $value
                    ])->execute();
                }
            } catch (\Exception $e) {
                $dataFailed = true;
                Yii::$app->appLog->writeLog("Configuration update failed.Key:{$key},Value:{$value},error:{$e->getMessage()}");
            }

            if ($updateMjEventUrl) {
                if ('' != $curMjUsername && '' != $curMjPassword) {
                    $this->removeEventCallbackUrl($curMjUsername, $curMjPassword); // TODO need to implement this method
                }
                $this->setEventCallbackUrl();
            }

        }
        return $dataFailed;
    }

    /**
     * @return string[]
     */
    public function getConfAttr()
    {
        return [
            'language' => self::LANGUAGE,
            'timezone' => self::TIME_ZONE,
            'fromEmail' => self::FROM_EMAIL,
            'fromName' => self::FROM_NAME,
            'fromEmail_1' => self::FROM_EMAIL_1,
            'fromName_1' => self::FROM_NAME_1,
            'fromEmail_2' => self::FROM_EMAIL_2,
            'fromName_2' => self::FROM_NAME_2,
            'fromEmail_3' => self::FROM_EMAIL_3,
            'fromName_3' => self::FROM_NAME_3,
            'fromEmail_4' => self::FROM_EMAIL_4,
            'fromName_4' => self::FROM_NAME_4,
            'fbPage' => self::FB_PAGE,
            'paypalId' => self::PAYPAL_ID,
            'currency' => self::CURRENCY,
            'validateVolunteer' => self::VALIDATE_VOLUNTEER,
            'lnPage' => self::LN_PAGE,
            'excludeFbPersonalContacts' => self::EXCLUDE_PERSONAL_FB_CONTACTS,
            'smsSenderId' => self::SMS_SENDER_ID,
            'smsSenderCountry' => self::SMS_SENDER_COUNTRY,
            'stripeClientId' => self::STRIPE_CLIENT_ID,
            'stripeSecretId' => self::STRIPE_SECRET_ID,
            'mailjetUsername' => self::MAILJET_USERNAME,
            'mailjetPassword' => self::MAILJET_PASSWORD,
        ];
    }

    /**
     * @description set OPT Message for SMS
     * @return string
     * @throws Exception
     */
    public static function getSmsOpt()
    {
        return "STOP SMS" . " " . self::getConfigFromSmsOption();
    }

    /**
     * @param null $key
     * @return false|int|string
     */
    public function getConfigurationAttByKey($key = null)
    {
        $attr = '';
        $attrList = $this->getConfAttr();
        if ($key != null) {
            $attr = array_search($key, $attrList);
        }
        return $attr;
    }

    /**
     * @param null $attr
     * @return string
     */
    public function getConfigurationKeyByAttr($attr = null)
    {
        $value = '';
        $attrList = $this->getConfAttr();
        if ($attr != null && isset($attrList[$attr])) {
            $value = $attrList[$attr];
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     * @return ConfigurationQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ConfigurationQuery(get_called_class());
    }
}
