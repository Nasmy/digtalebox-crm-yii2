<?php

namespace app\models;

use app\components\FileKit;
use app\components\RActiveRecord;
use app\components\Validations\ValidateKeywordsWithType;
use Yii;
use yii\helpers\Html;

/**
 * This is the model class for table "MessageTemplate".
 *
 * @property int $id
 * @property string $name
 * @property string $subject Email subject
 * @property string $twMessage Message sent to twitter accounts
 * @property string $fbMessage Facebook message
 * @property string $smsMessage SMS text
 * @property string $smsMessageTwo
 * @property string $lnMessage LinkedIn message
 * @property string $lnSubject LinkedIn subject
 * @property string $description
 * @property int $type 1- bulk template, 2- single template
 * @property string $dateTime
 * @property int $createdBy
 * @property string $createdAt
 * @property int $updatedBy
 * @property string $updatedAt
 * @property string $dragDropMessageCode
 */
class MessageTemplate extends RActiveRecord
{
    public $content = '';
    // public $smsMessageTwo = '';
    public $savedSearchId = '';
    public $messageType = '';
    public $isInstant = false;
    public $retUrl = '';
    public $emailTemplates = array(
        1 => array('thumbnail' => 'email_thumbs/email-green-single.jpg', 'prvImage' => 'email_previews/green-single.jpg', 'fileName' => 'green-single-col'),
        2 => array('thumbnail' => 'email_thumbs/email-green-two.jpg', 'prvImage' => 'email_previews/green-two.jpg', 'fileName' => 'green-two-col'),
        3 => array('thumbnail' => 'email_thumbs/email-green-three.jpg', 'prvImage' => 'email_previews/green-three.jpg', 'fileName' => 'green-three-col'),

        4 => array('thumbnail' => 'email_thumbs/email-blue-single.jpg', 'prvImage' => 'email_previews/blue-single.jpg', 'fileName' => 'blue-single-col'),
        5 => array('thumbnail' => 'email_thumbs/email-blue-two.jpg', 'prvImage' => 'email_previews/blue-two.jpg', 'fileName' => 'blue-two-col'),
        6 => array('thumbnail' => 'email_thumbs/email-blue-three.jpg', 'prvImage' => 'email_previews/blue-three.jpg', 'fileName' => 'blue-three-col'),

        7 => array('thumbnail' => 'email_thumbs/email-purple-single.jpg', 'prvImage' => 'email_previews/purple-single.jpg', 'fileName' => 'purple-single-col'),
        8 => array('thumbnail' => 'email_thumbs/email-purple-two.jpg', 'prvImage' => 'email_previews/purple-two.jpg', 'fileName' => 'purple-two-col'),
        9 => array('thumbnail' => 'email_thumbs/email-purple-three.jpg', 'prvImage' => 'email_previews/purple-three.jpg', 'fileName' => 'purple-three-col'),

        10 => array('thumbnail' => 'email_thumbs/email-red-single.jpg', 'prvImage' => 'email_previews/red-single.jpg', 'fileName' => 'red-single-col'),
        11 => array('thumbnail' => 'email_thumbs/email-red-two.jpg', 'prvImage' => 'email_previews/red-two.jpg', 'fileName' => 'red-two-col'),
        12 => array('thumbnail' => 'email_thumbs/email-red-three.jpg', 'prvImage' => 'email_previews/red-three.jpg', 'fileName' => 'red-three-col'),
    );

    // Keywords
    const FIRST_NAME = 'firstName';
    const LAST_NAME = 'lastName';
    const PHONE_NUMBER = 'phoneNumber';
    const CURRENT_DATE = 'currentDate';
    const SALUTATION = 'salutation';
    const REG_URL = 'registrationUrl';
    const PROSPECT_NAME = 'prospectName';

    // Template Types
    const MASS_TEMPLATE = 1;
    const SINGLE_TEMPLATE = 2;

    const MSG_LEN_FB = 300;
    const MSG_LEN_TW = 280;
    const MSG_LEN_SMS = 138;
    const MSG_LEN_LN = 140;
    const MSG_LEN_SMS_SPECIAL = 70;

    const CUSTOM_PREFIX = 'c';

    // Message Template Category
    const MSG_CAT_BOTH = 0;
    const MSG_CAT_EMAIL = 1;
    const MSG_CAT_SMS = 2;
    const MSG_CAT_DUPLICATE = 3;

    // Action Scenarios
    const SCENARIO_CREATE_EMAIL = 'emailCreate';
    const SCENARIO_CREATE_SMS = 'smsCreate';
    const SCENARIO_UPDATE_EMAIL = 'emailUpdate';
    const SCENARIO_UPDATE_SMS = 'smsUpdate';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'MessageTemplate';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return [

            // Common
            [['name'], 'string', 'max' => 45],
            [['description'], 'string', 'max' => 64],

            // Create
            // 'twMessage' required # hidded requested by client
            [['name', 'description', 'content', 'subject', 'type', 'dateTime', 'templateCategory'], 'required', 'on' => self::SCENARIO_CREATE_EMAIL],
            [['name', 'description', 'smsMessage', 'type', 'dateTime', 'templateCategory'], 'required', 'on' => self::SCENARIO_CREATE_SMS],

            [['name'], 'unique', 'on' => [self::SCENARIO_CREATE_EMAIL, self::SCENARIO_CREATE_SMS]],
            [['smsMessage'], 'string', 'max' => self::MSG_LEN_SMS, 'on' => self::SCENARIO_CREATE_SMS],
            [['smsMessageTwo'], 'string', 'max' => self::MSG_LEN_SMS, 'on' => self::SCENARIO_CREATE_SMS],
            [['fbMessage', 'content'], ValidateKeywordsWithType::className(), 'massTemplate' => self::MASS_TEMPLATE, 'on' => 'create'], //'twMessage'

            // Update
            [['name', 'description', 'content', 'subject', 'type', 'dateTime'], 'required', 'on' => self::SCENARIO_UPDATE_EMAIL], //'twMessage',
            [['name', 'description', 'smsMessage', 'type', 'dateTime'], 'required', 'on' => self::SCENARIO_UPDATE_SMS], //'twMessage',
            [['name'], 'unique', 'on' => [self::SCENARIO_UPDATE_EMAIL, self::SCENARIO_UPDATE_SMS]],
            [['twMessage'], 'string', 'max' => self::MSG_LEN_TW, 'on' => self::SCENARIO_UPDATE_SMS],
            //array('fbMessage', 'length', 'max'=>self::MSG_LEN_FB, 'on'=>'create'),
            [['smsMessage'], 'string', 'max' => self::MSG_LEN_SMS, 'on' => self::SCENARIO_UPDATE_SMS],
            [['smsMessageTwo'], 'string', 'max' => self::MSG_LEN_SMS, 'on' => self::SCENARIO_UPDATE_SMS],
            [['lnMessage'], 'string', 'max' => self::MSG_LEN_LN, 'on' => 'update'],
            // [['twMessage'], 'isUrlExists', 'on'=>'update'],
            [['fbMessage', 'content'], ValidateKeywordsWithType::className(), 'massTemplate' => self::MASS_TEMPLATE, 'on' => 'update'], //'twMessage',

            // Create Instant Template
            [['name', 'description', 'content', 'subject', 'smsMessage', 'type', 'dateTime'], 'required', 'on' => 'createInstant'], //'twMessage',
            [['name'], 'unique', 'on' => 'createInstant'],
            // [['twMessage'], 'string', 'max'=>self::MSG_LEN_TW, 'on'=>'createInstant'],
            //array('fbMessage', 'length', 'max'=>self::MSG_LEN_FB, 'on'=>'createInstant'),
            [['smsMessage'], 'string', 'max' => self::MSG_LEN_SMS, 'on' => 'createInstant'],
            [['lnMessage'], 'string', 'max' => self::MSG_LEN_LN, 'on' => 'createInstant'],
            // [['twMessage'], 'isUrlExists', 'on'=>'createInstant'],
            [['savedSearchId', 'messageType', 'twMessage'], 'safe', 'on' => 'createInstant'], //  'twMessage' remove for when enable this
            [['fbMessage', 'content'], ValidateKeywordsWithType::className(), 'massTemplate' => self::MASS_TEMPLATE, 'on' => 'createInstant'], // 'twMessage',

            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            [['id', 'name', 'description'], 'safe', 'on' => 'search'],
            [['dragDropMessageCode'], 'safe', 'on' => 'create, update'],

        ];
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array();
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => Yii::t('messages', 'Name of template'),
            'description' => Yii::t('messages', 'Description'),
            'subject' => Yii::t('messages', 'Email Subject'),
            'content' => Yii::t('messages', 'Email Content'),
            'twMessage' => Yii::t('messages', 'Twitter Message'),
            'fbMessage' => Yii::t('messages', 'Facebook Message'),
            'type' => Yii::t('messages', 'Template Type'),
            'dateTime' => Yii::t('messages', 'Date Time'),
            'createdBy' => Yii::t('messages', 'Created By'),
            'smsMessage' => Yii::t('messages', 'SMS Message'),
            'smsMessageTwo' => Yii::t('messages', 'SMS 2 (Only for long message)'),
            'lnMessage' => Yii::t('messages', 'LinkedIn Message'),
            'lnSubject' => Yii::t('messages', 'LinkedIn Subject'),
            'templateCategory' => Yii::t('messages', 'Template Category')
        ];
    }

    /**
     * Check whether user has entered valid keywords for the selected template type
     * @param string $attribute Attribute name
     * @param mixed $params Additional parameters to be passed to validation rule
     */
    public function validateKeywordsWithType($attribute, $params)
    {
        $content = $this->$attribute;
        $invalidKeywords = $this->type == self::MASS_TEMPLATE ? $this->getSingleMsgKeywords() : $this->getTemplateKeywords();

        foreach ($invalidKeywords as $keyword => $label) {
            if (strstr($content, $keyword)) {
                $this->addError($attribute, Yii::t('messages', 'Invalid keyword(s)'));
                break;
            }
        }
    }

    public function getRenderTemplate($templateCat, $action)
    {
        switch ($templateCat) {
            case self::MSG_CAT_EMAIL:
                return ($action == 'create') ? 'emailCreate' : 'emailUpdate';
            case self::MSG_CAT_SMS:
                return ($action == 'create') ? 'smsCreate' : 'smsUpdate';
            default:
                return 'update';
        }
    }

    public static function isSMSCountLessThanMaxCount($remainCount) {
        $remainCount = (int)$remainCount;
        if($remainCount > self::MAX_CHARACTER_COUNT_ASMS) {
            return true;
        } else {
            return false;
        }
    }

    public static function getRenderIcon($templateCategory, $id, $url, $isDuplicate = false)
    {
        if($isDuplicate) {
            $url = $url . '&catId=' . $templateCategory;
            return Html::a(
                '<img src="/images/duplicate.png" width="16px" hight="16px"/>',
                $url,
                [
                    'title' => Yii::t('messages', 'Template Duplicate'),
                    'data-toggle'=>'tooltip',
                    'style' => 'white-space:pre;'
                ]
            );
        }
        switch ($templateCategory) {
            case self::MSG_CAT_EMAIL:
                $url = $url . '&catId=' . self::MSG_CAT_EMAIL;
                return Html::a(
                    '<img src="/images/email-edit.png" width="16px" hight="16px"/>',
                    $url,
                    [
                        'title' => Yii::t('messages', 'Email Update'),
                        'data-toggle'=>'tooltip'
                    ]
                );

            case self::MSG_CAT_SMS:
                $url = $url . '&catId=' . self::MSG_CAT_SMS;
                return Html::a(
                    '<img src="/images/sms-edit.png" width="16px" hight="16px"/>',
                    $url,
                    [
                        'title' => Yii::t('messages', 'Sms Update'),
                        'data-toggle'=>'tooltip',
                        'style' => 'white-space:pre;'
                    ]
                );
        }
    }

    public function checkEmailTemplate($modelTemplate, $tempType = [])
    {
        if (in_array($modelTemplate, $tempType)) {
            return true;
        } else {
            return false;
        }
    }

    public function checkFileIsExist($id)
    {
        $filePath = $this->getFilePath($id);
        if (empty($filePath)) {
            return 0;
        }

        return 1;
    }


    public function getFilePath($id)
    {
        return FileKit::checkFileExist(Yii::$app->toolKit->resourcePathRelative, $id, '.html');
    }

    public function getTemplateScenario($templateCat, $action)
    {
        switch ($templateCat) {
            case self::MSG_CAT_EMAIL:
                return ($action == 'create') ? self::SCENARIO_CREATE_EMAIL : self::SCENARIO_UPDATE_EMAIL;
            case self::MSG_CAT_SMS:
                return ($action == 'create') ? self::SCENARIO_CREATE_SMS : self::SCENARIO_UPDATE_SMS;
            default:
                return 'update';
        }
    }

    /**
     * Check whether string contains an url.
     * @param string $attribute Attribute name
     * @param mixed $params Additional parameters to be passed to validation rule
     */
    public function isUrlExists($attribute, $params)
    {
        $words = array("http://", "https://", "www");
        foreach ($words as $word) {
            if (stristr($this->$attribute, $word)) {
                $this->addError($attribute, Yii::t('messages', 'Sorry, your message contains URL(s). At the moment Twitter does not allow to send messages with URL(s)'));
                break;
            }
        }
    }

    /**
     * @return array Template keywords for dynamic assignments
     */
    public function getTemplateKeywords()
    {
        $templateKeywords = array(
            'firstName' => Yii::t('messages', 'first name of recipient'),
            'lastName' => Yii::t('messages', 'last name of recipient'),
            'phoneNumber' => Yii::t('messages', 'phone number'),
            'currentDate' => Yii::t('messages', 'current date'),
            'salutation' => Yii::t('messages', '(Mr | Mrs | Miss)'),
        );

        return $templateKeywords;
    }

    /**
     * @return array Template keywords for dynamic assignments
     */
    public function getCustomTemplateKeywords()
    {
        $customFields = CustomField::find()->select(['id as customFieldId', 'label'])->from('CustomField')->where(['enabled' => 1, 'relatedTable' => CustomType::CF_PEOPLE])->all();

        $customColumns = array();
        foreach ($customFields as $customField) {
            $customColumns[] = $customField['label'];
        }

        return $customColumns;
    }

    /**
     * @return array Template keywords for dynamic assignments of single messages
     */
    public function getSingleMsgKeywords()
    {
        $templateKeywords = array(
            'registrationUrl' => Yii::t('messages', 'Registration URL'),
            'prospectName' => Yii::t('messages', 'Prospector name'),
        );

        return $templateKeywords;
    }

    /**
     * Prepare keyword list html string to display on the page
     * @return string $keywordsHtml Html output
     */
    public function getTemplateKeywordHtml($type)
    {
        $keywordsHtml = '';

        $keywords = $type == self::MASS_TEMPLATE ? $this->getTemplateKeywords() : $this->getSingleMsgKeywords();

        foreach ($keywords as $keyword => $description) {
            $keywordsHtml .= "<div><div class='badge badge-primary'>{{$keyword}}</div> - <span class='form-feild-info'>{$description}</span></div>";
        }
        return $keywordsHtml;
    }

    /**
     * Prepare keyword list html string to display on the page
     * @return string $keywordsHtml Html output
     */
    public function getTemplateCustomKeywordHtml()
    {
        $prefix = self::CUSTOM_PREFIX;
        $keywordsHtml = '';

        $keywords = $this->getCustomTemplateKeywords();

        foreach ($keywords as $keyword) {
            $keyword = $prefix . $keyword;
            $keywordsHtml .= "<div><div class='badge badge-primary'>{{$keyword}}</div>&nbsp;</div>";
        }
        return $keywordsHtml;
    }

    /**
     * Retrieve message template options
     * @param int $type
     * @return array $options Available templates
     */
    public static function getTemplateOptions($type = self::MASS_TEMPLATE, $templateCategory = null)
    {
        $type = ($type != null) ? $type : self::MASS_TEMPLATE;
        $arrOptions = array('' => Yii::t('messages', '--- Select Template ---'));
        if($templateCategory == self::MSG_CAT_EMAIL) {
            $models = MessageTemplate::find()->where(['type' => $type])->andWhere(['!=','templateCategory', self::MSG_CAT_SMS])->all();
        } else if($templateCategory == self::MSG_CAT_SMS) {
            $models = MessageTemplate::find()->where(['type' => $type])->andWhere(['!=','templateCategory', self::MSG_CAT_EMAIL])->all();
        } else {
            $models = MessageTemplate::find()->where(['type' => $type])->all();
        }

        foreach ($models as $model) {
            $arrOptions[$model->id] = $model->name;
        }

        return $arrOptions;
    }

    /**
     * Retrieve message template type options or label
     * @param Template $key identifier value
     * @return mixed Template type label name or array of template types
     */
    public function getTemplateTypeOptions($key = null, $exclude = array())
    {
        $arrOptions = array('' => Yii::t('messages', '--- Select Template Type ---'),
            self::MASS_TEMPLATE => Yii::t('messages', 'Mass Message'),
            self::SINGLE_TEMPLATE => Yii::t('messages', 'Single Message')
        );

        if (null != $key) {
            return $arrOptions[$key];
        }

        return $arrOptions;
    }

    /**
     * Retrieve message template type options for instant message creation
     * @return array $arrOptions Template type options
     */
    public function getTemplateTypeOptionsInstant()
    {
        $arrOptions = array('' => Yii::t('messages', '--- Select Template Type ---'),
            self::MASS_TEMPLATE => Yii::t('messages', 'Mass Message'),
        );

        return $arrOptions;
    }

    /**
     * Returns template name when its id is given
     * @param integer $templateId Template id
     * @param Array $templates Array of templates retrun from getTemplateOptions().
     * @return string $label Template name label
     */
    public static function getTemplateLabel($templateId, $templates)
    {
        $label = 'not-set';

        if (isset($templates[$templateId])) {
            $label = $templates[$templateId];
        }

        return $label;
    }

    /**
     * Check whether template is allocated for inprogress campaign
     * @param integer $templateId Template id
     * @return bool
     */
    public function IsTemplateInUse($templateId)
    {
        $campModel = Campaign::find()->where(['messageTemplateId' => $templateId])
            ->andWhere(['!=', 'status', Campaign::CAMP_FINISH])
            ->asArray()->all();

        if (null != $campModel) {
            return true;
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getCleanedContent()
    {
        return str_replace('<!-- [if (gte mso 9) | (IE)]>', '<!--[if (gte mso 9) | (IE)]>', $this->content);
    }

    public function getTemplateTop()
    {

        $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

        <html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
          <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
            <meta name="viewport" content="width=device-width">
            <title>Outlook Images</title>
            
            <style>
                .email_body{
                    min-width: 320px;
                }
            </style>
            <!--[if gte mso 9]><style type="text/css">.main_frm{width: 800px;}</style><![endif]-->
            </head>
        <body>';

        return $html;
    }

    /**
     * @return string
     */
    public function getTemplateBottom()
    {
        $html = '
        </body>
        </html>';

        return $html;
    }


    /**
     * @param $needle
     * @param $suffix
     * @param $haystack
     * @return mixed
     */
    public static function appendUserToKeywordUrl($needle, $suffix, $haystack)
    {
        return str_replace($needle, $needle . $suffix, $haystack);
    }


    /**
     * @param $lang
     * @return string
     */
    public static function getMessageTempateLang($lang)
    {
        switch ($lang) {
            case 'en-US':
                $lang = 'en';
                break;
            case 'fr-FR':
                $lang = 'fr';
                break;
            default:
                $lang = 'en';
        }
        return $lang;
    }

    /**
     * {@inheritdoc}
     * @return MessageTemplateQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new MessageTemplateQuery(get_called_class());
    }
}
