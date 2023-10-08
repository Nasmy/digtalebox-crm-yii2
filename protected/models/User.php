<?php

namespace app\models;

use app\components\LinkedInApi;
use app\components\RActiveRecord;
use app\components\ThresholdChecker;
use app\components\ToolKit;
use app\components\Alpha;
use app\components\TwitterApi;
use app\components\Validations\LocationEscapeSpecialCharacters;
use app\components\Validations\ValidateImageDimesions;
use app\components\Validations\ValidateMobileNumber;
use app\components\WebUser;
use Exception;
use Faker\Provider\DateTime;
use kartik\password\StrengthValidator;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;
use Yii;
use yii\base\ErrorException;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\helpers\Html;
use borales\extensions\phoneInput\PhoneInputValidator;
use yii\helpers\Url;
use app\models\Form;
use yii\console\Application;

/**
 * This is the model class for table "User".
 *
 * @property string $id
 * @property string $address1
 * @property string $mobile
 * @property string $name
 * @property string $firstName
 * @property string $lastName
 * @property string $username
 * @property string $password
 * @property string $email
 * @property int $gender 1 - female, 2-male, 0 - unknown
 * @property string $zip
 * @property string $countryCode
 * @property string $joinedDate
 * @property string $signUpDate User signed up date
 * @property string $supporterDate Date which user get supporter
 * @property int $userType Type of user. 1 - Politician 2 - Supporter 3 - Prospects 4 - Non support 5 - Unknown 6 - Newsletter 7 - Petitioner
 * @property int $signup whether user followed signup process 1 - signup, 0 - not signup
 * @property int $isSysUser 1 - system user, 0 - not a system user
 * @property string $dateOfBirth User date of birth
 * @property int $reqruiteCount User count that he has reqruited
 * @property string $keywords Kewords
 * @property int $delStatus 0 - active, 1 - deleted
 * @property string $city
 * @property int $isUnsubEmail 0 - ok, 1 - Unsubscribed form email
 * @property int $isManual 0 - ok, 1 - Manualy add
 * @property string $longLat Longitude and Latitude
 * @property int $isSignupConfirmed Whether user has confirmed signup
 * @property string $profImage Profile image name or URL
 * @property double $totalDonations Total donations made by the user
 * @property int $isMcContact 1 - Mailchimp contact
 * @property int $emailStatus 1-bounced, 2-blocked
 * @property string $notes note tmp field
 * @property string $network
 * @property int $formId Id if the form if the user created by using a form
 * @property string $addressInvalidatedAt
 * @property string $pwResetToken
 * @property string $resetPasswordTime
 * @property string $createdAt
 * @property string $updatedAt
 */
class User extends RActiveRecord
{
    /**
     * Maximum width and height and size for image in pixels
     */
    const MAX_IMG_WIDTH = 240;
    const MAX_IMG_HEIGHT = 240;
    const MAX_SIZE = 512000;
    const SEARCH_NORMAL = 1;
    const SEARCH_STRICT = 2;
    const SEARCH_EXCLUDE = 3;
    const PARTNER_USER_ID = -2;

    /**
     * User gender
     */
    const ASEXUAL = 0;
    const FEMALE = 1;
    const MALE = 2;
    const BOOL_YES = 1;
    const BOOL_NO = 0;

    /**
     * User types
     */
    const SUPER_ADMIN = -1;
    const POLITICIAN = 1;
    const SUPPORTER = 2;
    const PROSPECT = 3;
    const NON_SUPPORTER = 4;
    const UNKNOWN = 5;
    const NEWSLETTER = 6;
    const PETITIONER = 7;
    const NOT_APPLICABLE = 9;
    const REGIONAL_ADMIN = 8;
    const DELETE = 1;
    const NOTDELETE = 0;
    const BATCH_SIZE = 5000;
    const MAX_LINE_SIZE = 2000;
    const PROF_IMG_NAME = '{id}_profImg';
    const SIGNUP = 1;

    /**
     * Email statuses
     */
    const UNSUBSCRIBE_EMAIL = 0;
    const UNSUBSCRIBED_EMAILS = 1;
    const BOUNCED_EMAIL = 1;
    const BLOCKED_EMAIL = 2;
    const SUBSCRIBE_EMAIL = 3;
    const UN_BOUNCE_MAIL = 4;
    const UN_BLOCK_MAIL = 5;
    const DB_SUBSCRIBE = 0;

    /*
     * default value for date of birth
     */
    const DEFAULT_DATE_OF_BIRTH = '0000-00-00';

    /*
     *  const for multiselect networks
     */
    const MOBILE = 5;
    const EMAIL = 6;

    /**
     * const for system user
     */
    const NOT_IS_MANUAL = 0;
    const IS_MANUAL = 1;

    /**
     * const for system user
     */
    const NOT_SYSTEM_USER = 0;
    const SYSTEM_USER = 1;

    /**
     * User role
     */
    public $role;
    /**
     * Mobile
     */
    // public $mobile;

    /**
     * New password
     */
    public $newPassword;

    /**
     * Confirm password
     */
    public $confPassword;

    /**
     * Old password
     */
    public $oldPassword;

    /**
     * Old password db
     */
    public $oldPasswordDb;

    /**
     * age
     */
    public $age;

    /**
     * mapZone
     */
    public $mapZone;

    /**
     * bulkFile
     */
    public $bulkFile;

    /**
     * mailTemplate
     */
    public $mailTemplate;

    /**
     * mailTemplate
     */
    public $fileErrors = null;

    /**
     * tmpStatusFile
     */
    public $tmpStatusFile = null;

    /**
     * connections
     */
    public $connections = 0;

    /**
     * extra parameters
     */
    public $extraParams = null;

    /**
     * loginitude and latitude of the user location
     */
    public $longLat = null;

    /**
     * Profile image file
     */
    public $profImgFile = null;

    /**
     * Facebook profile id
     */
    public $fbUserId = null;

    /**
     * Team id
     */
    public $teams = null;

    /**
     * Verify email address
     */
    public $verifyEmail = null;

    /**
     * Email verification code
     */
    public $verifyCode = null;

    /**
     * Flag for whether email verification done or not
     */
    public $isVerified = null;

    /**
     * Team id
     */
    public $teamId = 0;
// public $teamId = null;

    /**
     * Team name
     */
    public $teamName = null;

    /**
     * User network Facebook/Twitter
     */
    public $network = null;
    public $lnUserId;
    public $twUserId;

    /**
     * Whether email contact limit exceded
     */
    public $isEmailContactLimitExceed = false;
    public $bulkSuccessCount = 0;

    /**
     * Whether to include or exclude personal facebook contacts
     */
    public $excludeFbPersonalContacts = '';
    public $isDisplayKeywords2 = '';
    // Full Address of the user
    public $fullAddress;
    public $active = null;
    public $customFieldData = array();
    public $longitude = null;
    public $latitude = null;
    public $searchType = null;
    private $searchTypeList = array(
        self::SEARCH_NORMAL => 'Normal',
        self::SEARCH_STRICT => 'Strict',
        self::SEARCH_EXCLUDE => 'Exclude',
    );
    public $keywordsExclude;
    public $keywordsExclude2;
    public $searchType2 = null;
    public $keywords2 = null;
    public $dupCount;
    public $dupcount2;
    public $parentId;
    /* commented due to value is not saving  on user formID
     * public $formId;
     * */
    public $isDonation;
    public $isMembership;


    /**
     * Field list for signup process. Mention exact db field name here.
     */
    private $signupFields = array(
        //'name',
        'firstName',
        'lastName',
        'email',
        'username',
        'city',
        'mobile',
        'address1',
        'password',
        'countryCode',
        'gender',
        'dateOfBirth',
        'confPassword',
        'zip',
    );

    /**
     * Field list for file upload process. Mention exact db field name here.
     */
    private $fileUploadFields = array(
        //'name',
        'firstName',
        'lastName',
        'email',
        'mobile',
        'address1',
        'zip',
        'city',
        //'countryCode',
        'gender',
        'dateOfBirth',
        'notes',
    );
    private $advancedFileUploadFields = array(
        //'name',
        'firstName',
        'lastName',
        'email',
        'mobile',
        'address1',
        'zip',
        'city',
        //'countryCode',
        'gender',
        'dateOfBirth',
        'notes',
        'keywords',
    );
    // Variables for UserMatch queries
    public $twUid;
    public $twName;
    public $twProfImgUrl;
    public $twId;
    public $fbUid;
    public $fbFname;
    public $fbProfImgUrl;
    public $fbId;
    public $fbLname;
    public $lnUid;
    public $lnFname;
    public $lnLname;
    public $lnProfImgUrl;
    public $lnId;
    public $gpUid;
    public $gpFname;
    public $gpLname;
    public $gpProfImgUrl;
    public $gpId;
    // public $emailStatus;
    public $subEmail;
    public $checkEmail;
    public $concern;
    public $isConcern;
    public $isMobileValide = true;

    const SENARIO_CREATE = 'create';
    const SENARIO_UPDATE = 'update';
    const SENARIO_BULK = 'bulk';
    const SENARIO_ADVANCE_BULK = 'advancedBulk';
    const SENARIO_UPDATE_PEOPLE = 'updatePeople';

    /* User keyword from popup */
    public $userKeywords;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'User';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['pwResetToken', 'resetPasswordTime', 'emailStatus', 'isUnsubEmail', 'formId'], 'safe'],
            // allow only numbers to zip code
            [['zip'], 'checkZipCode', 'on' => 'create,update'],
            // User create via admin
            [['firstName', 'lastName', 'username', 'email', 'role'], 'required', 'on' => [self::SENARIO_CREATE, self::SENARIO_UPDATE]],
            [['firstName', 'lastName'], 'required', 'on' => ['bulkPeople']],
            [['firstName', 'lastName'], Alpha::className(), 'allAccentedLetters' => true, 'allowSpaces' => true, 'extra' => array('_', '-', "'"), 'on' => [self::SENARIO_UPDATE_PEOPLE, 'people', 'bulkPeople', 'signup', 'newsletter', 'myAccount']],
            // [['zip', 'address1', 'city'], LocationEscapeSpecialCharacters::className(), 'on' => [self::SENARIO_UPDATE_PEOPLE, 'people', 'bulkPeople']],
            [['zip', 'address1', 'city', 'notes', 'keywords'], LocationEscapeSpecialCharacters::className(), 'on' => [self::SENARIO_UPDATE_PEOPLE, 'people', 'bulkPeople']],
            [['password', 'confPassword'], 'required', 'on' => [self::SENARIO_CREATE]],
            [['username'], 'string', 'max' => 20, 'on' => [self::SENARIO_CREATE, self::SENARIO_UPDATE]],
            [['username'], 'unique', 'on' => [self::SENARIO_CREATE, self::SENARIO_UPDATE]],
            [['email'], 'email', 'on' => [self::SENARIO_CREATE, self::SENARIO_UPDATE]],
            [['email'], 'unique', 'on' => [self::SENARIO_CREATE, self::SENARIO_UPDATE]],
            ['password', StrengthValidator::className(), 'min' => 12, 'digit' => 1, 'upper' => 1, 'special' => 1, 'message' => Yii::t('messages', 'New Password is weak. New Password must contain at least 12 characters, at least one letter, at least one number and at least one symbol(-@_#&.).'), 'on' => [self::SENARIO_CREATE, self::SENARIO_UPDATE]],
            ['confPassword', 'compare', 'compareAttribute' => 'password', 'on' => [self::SENARIO_CREATE, self::SENARIO_UPDATE]],
            // [['password'], 'string', 'min' => 5, 'on' => 'create'],
            // [['confPassword'], 'compare', 'compareAttribute' => 'password', 'on' => 'create', 'message'=>"Passwords don't match"],
            [['name', 'password'], 'string', 'max' => 45, 'on' => [self::SENARIO_CREATE, self::SENARIO_UPDATE]],
            [['userType', 'isSysUser', 'notes'], 'safe', 'on' => [self::SENARIO_CREATE, self::SENARIO_UPDATE]],

            // Signup process
            $this->getSignupRequiredFieldsRule(),
            // [['address1','zip','city','countryCode','firstName','lastName','email','username','password','confPassword'],'required', 'on' => 'signup'],
            // [['firstName', 'lastName'], Alpha::class, 'allAccentedLetters' => true, 'allowSpaces' => true, 'extra' => array('_', '-'), 'on' => 'signup'],
            [['firstName', 'lastName', 'email'], 'string', 'max' => 100, 'on' => ['signup', self::SENARIO_UPDATE_PEOPLE]],
            [['username'], 'string', 'max' => 30, 'on' => 'signup'], //['password']
            ['password', 'string', 'min' => 6],
            [['username'], 'unique', 'on' => 'signup'],
            [['email'], 'email', 'on' => ['signup', self::SENARIO_UPDATE_PEOPLE]],
            // [['email'], 'email', 'on' => 'onverify'],
            [['email'], 'unique', 'on' => ['signup', self::SENARIO_UPDATE_PEOPLE]],
            [['mobile'], 'string', 'max' => 32, 'on' => ['signup', self::SENARIO_UPDATE_PEOPLE]],
            [['mobile'], 'unique', 'on' => ['signup', self::SENARIO_UPDATE_PEOPLE]],
            [['mobile'], ValidateMobileNumber::className(), 'on' => ['signup', self::SENARIO_UPDATE_PEOPLE]],
            [['address1'], 'string', 'max' => 255, 'on' => ['signup', self::SENARIO_UPDATE_PEOPLE]],
            [['zip'], 'string', 'max' => 15, 'on' => ['signup', self::SENARIO_UPDATE_PEOPLE]],
            [['city'], 'string', 'max' => 50, 'on' => ['signup', self::SENARIO_UPDATE_PEOPLE]],
            [['password'], 'string', 'min' => 5, 'on' => 'signup'],
            [['confPassword'], 'compare', 'compareAttribute' => 'password', 'skipOnEmpty' => false, 'on' => 'signup', 'message' => "Passwords don't match"],
            [['gender', 'longLat', 'verifyEmail', 'isVerified'], 'safe', 'on' => 'signup'],
            // Newsletter process
            [['firstName', 'lastName', 'email', 'userType'], 'required', 'on' => 'newsletter'],
            [['firstName', 'lastName', 'email'], 'string', 'max' => 100, 'on' => 'newsletter'],
            [['email'], 'email', 'on' => 'newsletter'],
            [['email'], 'unique', 'on' => 'newsletter'],
            // Formbuilder process
            [['email', 'userType', 'firstName', 'lastName'], 'required', 'on' => 'formBuilder'],
            [['email'], 'string', 'max' => 100, 'on' => 'formBuilder'],
            [['email'], 'email', 'on' => 'formBuilder'],
            [['email'], 'unique', 'on' => 'formBuilder'],
            [['mobile'], 'string', 'max' => 32, 'on' => 'formBuilder'],
            [['mobile'], 'unique', 'on' => 'formBuilder'],
            //array('mobile', 'validateMobileNumber', 'on' => 'formBuilder'), //Task #1397
            //array('enablePayment', 'validateEnablePayment', 'on' => 'formBuilder'),
            [['isDonation'], 'number', 'integerOnly' => true, 'min' => 1, 'max' => 1000, 'on' => 'formBuilder'],
            [['address1'], 'string', 'max' => 1000, 'on' => 'formBuilder'],
            [['zip'], 'string', 'max' => 15, 'on' => 'formBuilder'],
            [['city'], 'string', 'max' => 50, 'on' => 'formBuilder'],
            [['gender'], 'in', 'range' => array(self::ASEXUAL, self::FEMALE, self::MALE), 'skipOnEmpty' => false, 'message' => 'Please enter a valid value for {attribute}.', 'on' => 'formBuilder'],
            [['dateOfBirth'], 'date', 'format' => self::getDOBDateFormat(), 'on' => 'formBuilder'],
            [['dateOfBirth', 'mobile', 'gender', 'address1', 'zip', 'countryCode', 'city'], 'safe', 'on' => 'formBuilder'],
            [['concern'], 'required', 'message' => Yii::t('messages', 'Please confirm that you agree to give filled information in the form by clicking the checkbox.'), 'on' => 'formBuilder'],
            [['isConcern'], 'required', 'message' => Yii::t('messages', 'This form is not obliged with European General Data Protection Regulation, hence the information submitted will not be saved.'), 'on' => 'formBuilder'],
            // MyAccount
            // MyAccount
            [['firstName', 'lastName', 'email', 'address1', 'zip', 'countryCode', 'gender', 'city'], 'required', 'on' => 'myAccount'],
            [['firstName', 'lastName', 'email'], 'string', 'max' => 100, 'on' => 'myAccount'],
            [['email'], 'email', 'on' => 'myAccount'],
            [['email'], 'unique', 'on' => 'myAccount'],
            [['mobile'], 'string', 'max' => 15, 'on' => 'myAccount'],
            [['mobile'], ValidateMobileNumber::className(), 'on' => 'myAccount'],
            [['address1'], 'string', 'max' => 255, 'on' => 'myAccount'],
            [['city'], 'string', 'max' => 50, 'on' => 'myAccount'],
            [['zip'], 'string', 'max' => 15, 'on' => 'myAccount'],
            [['profImgFile'], 'file', 'skipOnEmpty' => true, 'extensions' => 'jpg,jpeg,png', 'maxSize' => self::MAX_SIZE, 'on' => 'myAccountImg'],
            [['profImgFile'], ValidateImageDimesions::className()],
            [['gender', 'profImage', 'dateOfBirth'], 'safe', 'on' => 'myAccount'],
            // Check email
            [['email'], 'email', 'on' => 'checkEmail'],
            [['email'], 'required', 'on' => 'checkEmail'],
            // Forgot password
            [['email'], 'required', 'on' => 'resetPassword'],
            [['email'], 'email', 'on' => 'resetPassword'],
            // New password *
            [['password', 'confPassword'], 'required', 'on' => 'typeNewPassword'],
            ['password', StrengthValidator::className(), 'min' => 12, 'digit' => 1, 'upper' => 1, 'special' => 1, 'message' => 'New Password is weak. New Password must contain at least 12 characters, at least one letter, at least one number and at least one symbol(-@_#&.).', 'on' => 'typeNewPassword'],
            ['confPassword', 'compare', 'compareAttribute' => 'password', 'on' => 'typeNewPassword'],
            // Change password
            [['password', 'oldPassword', 'confPassword'], 'required', 'on' => 'changePassword'],
            [['oldPassword'], 'checkOldPassword', 'on' => 'changePassword'],
            [['password'], StrengthValidator::className(), 'min' => 12, 'digit' => 1, 'upper' => 1, 'special' => 1, 'message' => 'New Password is weak. New Password must contain at least 12 characters, at least one letter, at least one number and at least one symbol(-@_#&.).', 'on' => 'changePassword'],
            [['confPassword'], 'compare', 'compareAttribute' => 'password', 'on' => 'changePassword'],
            // People section
            [['firstName', 'lastName'], 'required', 'on' => 'people'],
            [['firstName', 'lastName'], 'string', 'max' => 45, 'on' => ['people', 'bulkPeople']],
            [['email'], 'string', 'max' => 100, 'on' => ['people', 'bulkPeople']],
            [['email'], 'email', 'on' => ['people', 'bulkPeople']],
            [['email'], 'unique', 'on' => ['people', 'bulkPeople']],
            [['mobile'], 'string', 'max' => 32, 'on' => ['people', 'bulkPeople']],
            [['mobile'], ValidateMobileNumber::className(), 'on' => ['people', 'bulkPeople', 'myAccount','formBuilder']],
            [['mobile'], 'unique', 'on' => ['people', 'bulkPeople']],
            [['address1'], 'string', 'max' => 255, 'on' => ['people', 'bulkPeople']],
            [['zip'], 'string', 'max' => 15, 'on' => ['people', 'bulkPeople']],
            [['city'], 'string', 'max' => 50, 'on' => ['people', 'bulkPeople']],
            [['keywords', 'userKeywords'], 'implodeParams', 'on' => ['create', 'update', 'people', 'bulkPeople']], //define custom validator to implode checked values into string
            [['age'], 'match', 'pattern' => '/^(?:100|\d{1,2})(?:\-\d{1,2})?$/', 'on' => ['people', 'bulkPeople']], //'/^([0-9])+$/'
            [['gender'], 'in', 'range' => array(self::ASEXUAL, self::FEMALE, self::MALE), 'skipOnEmpty' => true, 'message' => 'Please enter a valid value for {attribute}.', 'on' => ['people', 'bulkPeople']],
            [['dateOfBirth'], 'date', 'format' => self::getDOBDateFormat(), 'on' => ['people', 'bulkPeople']],
            [['countryCode', 'userType', 'age', 'isManual', 'network'], 'safe', 'on' => ['create', 'update', 'people', 'bulkPeople']], //define custom validator to implode checked values into string
            [['firstName', 'lastName', 'excludeFbPersonalContacts', 'notes', 'active', 'fullAddress', 'zip'], 'safe', 'on' => ['people', 'bulkPeople']],
            [['mailTemplate'], 'required', 'on' => 'peopleMail'],
            // Bulk People section
            [['firstName'], 'checkRequired', 'on' => 'bulkPeople'],
            // Mailchimp import
            [['email', 'joinedDate', 'userType', 'isMcContact', 'keywords'], 'required', 'on' => 'mcImport'],
            [['bulkFile'], 'required', 'on' => [self::SENARIO_BULK, self::SENARIO_ADVANCE_BULK]],
            // [['bulkFile'], 'file', 'max' => 255, 'tooLong' => '{attribute} is too long (max {max} chars).', 'on' => [self::SENARIO_BULK, self::SENARIO_ADVANCE_BULK]],
            [['bulkFile'], 'file', 'extensions' => 'txt,csv', 'maxSize' => 1024 * 1024 * 3, 'on' => self::SENARIO_BULK],
            [['bulkFile'], 'file', 'extensions' => 'zip', 'maxSize' => 1024 * 1024 * 3, 'on' => self::SENARIO_ADVANCE_BULK],
            [['active', 'userKeywords'], 'safe', 'on' => [self::SENARIO_BULK, self::SENARIO_ADVANCE_BULK]],
            // [['zip', 'validateRegionalUserZip'], 'on' => 'create'],
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            [['id', 'username', 'password', 'email', 'firstName', 'lastName', 'excludeFbPersonalContacts', 'active', 'fullAddress', 'searchType'], 'safe', 'on' => 'search'],
            [['teamId', 'active', 'fullAddress'], 'safe', 'on' => 'searchVolunteers'],
            [['searchType', 'keywordsExclude', 'keywordsExclude2', 'keywords2', 'searchType2', 'mobile', 'zip', 'mapZone'], 'safe'],
            [['address1', 'gender', 'city', 'dateOfBirth', 'email', 'mobile', 'zip', 'keywords', 'notes', 'countryCode', 'userType'], 'safe', 'on' => 'potentialMatch'],
            [['pwResetToken', 'newPassword', 'resetPasswordTime'], 'safe', 'on' => 'clearToken']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'address1' => Yii::t('messages', 'Street Address'),
            'mobile' => Yii::t('messages', 'Mobile'),
            'name' => Yii::t('messages', 'Name'),
            'firstName' => Yii::t('messages', 'First Name'),
            'lastName' => Yii::t('messages', 'Last Name'),
            'username' => Yii::t('messages', 'Username'),
            'password' => Yii::t('messages', 'New Password'),
            'email' => Yii::t('messages', 'Email'),
            'gender' => Yii::t('messages', 'Gender'),
            'zip' => Yii::t('messages', 'Zip'),
            'countryCode' => Yii::t('messages', 'Country'),
            'joinedDate' => Yii::t('messages', 'Joined Date'),
            'userType' => Yii::t('messages', 'Category'),
            'signup' => Yii::t('messages', 'Signup'),
            'confPassword' => Yii::t('messages', 'Confirm New Password'),
            'dateOfBirth' => Yii::t('messages', 'Date of Birth'),
            'keywords' => Yii::t('messages', 'Keywords'),
            'delStatus' => Yii::t('messages', 'Delete Status'),
            'age' => Yii::t('messages', 'Age(N or N-N)'),
            'mailTemplate' => Yii::t('messages', 'Mail Template'),
            'bulkFile' => Yii::t('messages', 'File'),
            'role' => Yii::t('messages', 'Role'),
            'oldPassword' => Yii::t('messages', 'Old Password'),
            'city' => Yii::t('messages', 'City'),
            'signUpDate' => Yii::t('messages', 'Joined Date'),
            'createdBy' => Yii::t('messages', 'Created By'),
            'profImgFile' => Yii::t('messages', 'Profile Image'),
            'verifyEmail' => Yii::t('messages', 'Email'),
            'teamName' => Yii::t('messages', 'Team Name'),
            'reqruiteCount' => Yii::t('messages', 'Recruiter Count'),
            'totalDonations' => Yii::t('messages', 'Donations'),
            'excludeFbPersonalContacts' => Yii::t('messages', 'Exclude Personal Facebook Contacts'),
            'isDisplayKeywords2' => Yii::t('messages', 'Enable Keywords 2'),
            'notes' => Yii::t('messages', 'Notes'),
            'formId' => Yii::t('messages', 'Form Id'),
            'fullAddress' => Yii::t('messages', 'Address(27 Avenue Pasteur, 14390 Cabourg, France)'),
            'dupCount' => Yii::t('messages', 'Count'),
            'isMembership' => Yii::t('messages', 'Membership'),
            'isDonation' => Yii::t('messages', 'Donation'),
            'pwResetToken' => Yii::t('messages', 'Password Reset Token'),
            'isUnsubEmail' => Yii::t('messages', 'Subscription'),
            'concern' => Yii::t('messages', 'EU data law Concern'),
            'userKeywords' => Yii::t('messages', 'Keywords'),
        ];
    }

    public static function getDOBDateFormat()
    {
        return 'yyyy-mm-dd'; //array('yyyy-MM-dd', '0000-00-00');
    }

    /**
     * Validate image width and height
     */
    public function validateImageDimesions() // TODO needs to rename the method
    {
        if ("" != $this->profImgFile) {
            $imageSize = getimagesize($this->profImgFile->tempName);
            $imageWidth = $imageSize[0];
            $imageHeight = $imageSize[1];

            if ($imageWidth > self::MAX_IMG_WIDTH) {
                $this->addError('profImgFile', Yii::t('messages', 'Image width is too large.'));
            } else if ($imageHeight > self::MAX_IMG_HEIGHT) {
                $this->addError('profImgFile', Yii::t('messages', 'Image height is too large.'));
            }
        }
    }

    /**
     * Encrypt user password.
     * @param string $password Plain password
     * @return string md5 encrypted password
     */
    public static function encryptUserPassword($password)
    {
        return md5($password);
    }


    /**
     * @return ActiveQuery
     */
    public function getRolePermissions0()
    {
        return $this->hasMany(AuthItemChild::className(), ['updatedById' => 'id']);
    }

    /**
     * Return Facebook or Twitter profile picture URI
     * @param null $profImg Profile image
     * @param integer $width
     * @param integer $height
     * @param string $title
     * @param null $id User id
     * @param null $css
     * @return string Image tag
     */
    public static function getPic($profImg = null, $width = 30, $height = 30, $title = "", $id = null, $css = null)
    {
        Yii::$app->toolKit->setResourceInfo();
        $altPic = Yii::$app->toolKit->getAltProfPic();
        $basePath = Url::base() . '/' . Yii::$app->toolKit->resourcePathRelative;
        $img = null;
        if (is_null($profImg)) {
            if (!is_null($id)) {
                $userModel = User::findOne($id);
                $img = $userModel->profImage;
            }
            if (is_null($id) || is_null($img)) {
                $img = Html::img($altPic, array('width' => $width, 'height' => $height, 'class' => "{$css}", 'title' => $title));
                return $img;
            }
        } else {
            $img = $profImg;
        }

        if (!stristr($img, 'http://') && !stristr($img, 'https://')) {
            if (file_exists(Yii::$app->basePath . '/web' . $basePath . $img)) { // TODO need to get from common place
                $imageUrl = $basePath . $img . "?buster=" . uniqid();
                $pic = Html::img($imageUrl, array('width' => $width, 'height' => $height, 'class' => "{$css}", 'title' => $title));
            } else {
                $pic = Html::img($altPic, array('width' => $width, 'height' => $height, 'class' => "{$css}", 'title' => $title));
            }
        } else {
            $pic = Html::img($altPic, array('width' => $width, 'height' => $height, 'class' => "{$css}", 'title' => $title));
        }
        return $pic;
    }

    /**
     * Return Facebook or Twitter profile picture URI
     * @param string $profImg Profile image
     * @return string
     */
    public static function getPicUrl($profImg = null)
    {
        $pic = Yii::$app->toolKit->getAltProfPic();

        if (null != $profImg) {
            if (!stristr($profImg, 'http://') && !stristr($profImg, 'https://')) {
                Yii::$app->toolKit->setResourceInfo();
                $pic = Url::base() . '/' . Yii::$app->toolKit->resourcePathRelative . $profImg . "?buster=" . uniqid();
            }
        }

        return $pic;
    }


    /**
     * Update user keywords
     * @param integer $id
     * @param string $keyword
     * @return bool
     */
    public static function updateKeyword($id, $keyword)
    {
        $userModel = User::findOne($id);

        if (preg_match("/^.*,{$keyword},.*$/", ",{$userModel->keywords},")) {
            return true;
        } else {
            $userModel->keywords = $userModel->keywords . (empty($userModel->keywords) ? $keyword : ",{$keyword}");
            if ($userModel->save(false)) {
                return true;
            } else {
                return false;
            }
        }
    }


    /**
     * Delete user keywords
     * @param integer $id
     * @param string $keyword
     * @return bool
     */
    public static function deleteKeyword($id, $keyword)
    {
        $userModel = User::findOne($id);

        if (null != $userModel) {
            if (preg_match("/^.*,{$keyword},.*$/", ",{$userModel->keywords},")) {
                $delString = preg_replace("/,{$keyword},/", ',', ",{$userModel->keywords},");
                $userModel->keywords = trim($delString, ',');
                if ($userModel->save(false)) {
                    return true;
                } else {
                    return false;
                }
            }

            return false;
        }
    }

    /*
    * function to get user count by type
    * @return array the user count of different user type
    */
    public static function getUserCountByUserType()
    {
        $supporter = 0;
        $prospect = 0;
        $nonSupporter = 0;
        $others = 0;
        $total = 0;
        $query = new Query();
        $results = $query->select("count(*) AS userCount, userType")
            ->where("userType != :userType1 AND userType != :userType2 AND userType != :userType3 AND
         isSysUser = :isSysUser")
            ->params([':userType1' => User::SUPER_ADMIN, ':userType2' => User::POLITICIAN, ':userType3' => 0, ':isSysUser' => 0])
            ->groupBy('userType')
            ->from(User::tableName())->all();

        if ($results) {
            foreach ($results as $result) {
                if (self::SUPPORTER == $result['userType']) {
                    $supporter = $result['userCount'];
                } elseif (self::PROSPECT == $result['userType']) {
                    $prospect = $result['userCount'];
                } elseif (self::NON_SUPPORTER == $result['userType']) {
                    $nonSupporter = $result['userCount'];
                } else {
                    $others = $result['userCount'] + $others;
                }
            }

            $total = $supporter + $prospect + $nonSupporter + $others;
        }

        return array('supporter' => $supporter, 'prospect' => $prospect, 'nonSupporter' => $nonSupporter, 'others' => $others,
            'total' => $total);

    }

    /*
     * function to get user count over time
     * @param String $year
     * @return json String - the user count of each month of the year, array with 0 values returns if DB result is empty
     */
    public function getUserCountByTimeLine($year)
    {
        $total = $return = array();
        for ($m = 1; $m <= date('m', time()); $m++) {
            $total[] = 0;
        }
        $query = new Query();
        $results = $query->select("count(*) AS userCount, userType, MONTH(createdAt) AS createMonth")
            ->where("userType != :userType1 AND userType != :userType2 AND userType != :userType3 AND
         isSysUser = :isSysUser AND DATE_FORMAT(createdAt, '%Y') = {$year}")
            ->params([':userType1' => User::SUPER_ADMIN, ':userType2' => User::POLITICIAN, ':userType3' => 0, ':isSysUser' => 0])
            ->groupBy('userType, createMonth')
            ->from(User::tableName())->all();
        if ($results) {
            $supporter = $this->getUserCountByType($results, self::SUPPORTER);
            $prospect = $this->getUserCountByType($results, self::PROSPECT);
            $nonSupporter = $this->getUserCountByType($results, self::NON_SUPPORTER);
            $unknown = $this->getUserCountByType($results, self::UNKNOWN);
            $total = $this->getUserCountByType($results);
            $others = $this->getOtherUserCountByType($total, $supporter, $prospect, $nonSupporter, $unknown);

            $return[] = ['name' => Yii::t('messages', 'Total Users'), 'data' => $total];
            $return[] = ['name' => Yii::t('messages', 'Supporters'), 'data' => $supporter];
            $return[] = ['name' => Yii::t('messages', 'Prospects'), 'data' => $prospect];
            $return[] = ['name' => Yii::t('messages', 'Non Supporters'), 'data' => $nonSupporter];
            $return[] = ['name' => Yii::t('messages', 'Unknown'), 'data' => $unknown];
            $return[] = ['name' => Yii::t('messages', 'Others'), 'data' => $others];
        } else {
            $return[] = ['name' => Yii::t('messages', 'Total Users'), 'data' => $total];
        }
        unset($total);
        return json_encode($return);

    }

    /*
     * Function to get the chart result of networks - Pie Chart
     * @return json String - user count based on the networks, json String with 0 value returns if the DB returns empty
     */

    function getUserCountByCampaignMedia()
    {
        $query = new Query();
        $results = $query->select("COUNT(u.id) AS userCount")
            ->join('INNER JOIN', 'User u', 'u.id = t.userId')
            ->where('u.userType != :userType1 AND u.userType != :userType2 AND u.isSysUser = :isSysUser')
            ->params([':userType1' => User::SUPER_ADMIN, ':userType2' => User::POLITICIAN, ':isSysUser' => 0]);
        $fbReturn = $results->from('FbProfile t')->all();
        $lnReturn = $results->from('LnProfile t')->all();
        $twReturn = $results->from('TwProfile t')->all();
        $conditionMobile = ' AND mobile IS NOT NULL AND mobile != ""';
        $mobile = $this->getMobileOrEmailCount($conditionMobile);
        $conditionEmail = ' AND email IS NOT NULL AND email != ""';
        $email = $this->getMobileOrEmailCount($conditionEmail);

        $return = array();
        $total = $fbReturn[0]['userCount'] + $lnReturn[0]['userCount'] + $twReturn[0]['userCount'] +
            $mobile[0]['userCount'] + $email[0]['userCount'];
        if ($total > 0) {
            if (0 != floor(($fbReturn[0]['userCount'] / $total) * 360)) {
                $return[] = [Yii::t('messages', 'FB'), (int)$fbReturn[0]['userCount']];
            }
            if (0 != floor(($lnReturn[0]['userCount'] / $total) * 360)) {
                $return[] = [Yii::t('messages', 'LN'), (int)$lnReturn[0]['userCount']];
            }
            if (0 != floor(($twReturn[0]['userCount'] / $total) * 360)) {
                $return[] = [Yii::t('messages', 'TW'), (int)$twReturn[0]['userCount']];
            }
            if (0 != floor(($mobile[0]['userCount'] / $total) * 360)) {
                $return[] = [Yii::t('messages', 'Mobile'), (int)$mobile[0]['userCount']];
            }
            if (0 != floor(($email[0]['userCount'] / $total) * 360)) {
                $return[] = [Yii::t('messages', 'Email'), (int)$email[0]['userCount']];
            }
        }

        if (ToolKit::isEmpty($return)) {
            $return[] = [Yii::t('messages', 'No Users'), 0];
        }
        return json_encode($return);
    }

    /*
     * function used in getUserCountByCampaignMedia function to apply additional conditions
     * @param $condition - DB Query
     * @return array Query results, empty array if no return on query execution
     */
    function getMobileOrEmailCount($condition)
    {
        $query = new Query();
        $results = $query->select("COUNT(id) AS userCount")
            ->where("userType != :userType1 AND userType != :userType2 AND isSysUser = :isSysUser" . $condition)
            ->params([':userType1' => User::SUPER_ADMIN, ':userType2' => User::POLITICIAN, ':isSysUser' => 0])
            ->from(User::tableName())->all();
        return $results;
    }


    /*
    * function to organise the results
    * @param $result - query result as array, $type - user types
    * @return array - user count for each month, array with 0 value if the $result is empty array
    */
    function getUserCountByType($results, $type = null)
    {
        $users = $return = array();
        for ($m = 1; $m <= date('m', time()); $m++) {
            $users[$m] = 0;
        }
        for ($m = 1; $m <= date('m', time()); $m++) {
            foreach ($results as $result) {
                if ($type == $result['userType']) {
                    if ($m == $result['createMonth']) {
                        $users[$m] = (int)$result['userCount'];
                    } else {
                        $users[$m] = (ToolKit::isEmpty($users[$m])) ? 0 : $users[$m];
                    }
                } elseif (null == $type) {
                    if ($m == $result['createMonth']) {
                        $users[$m] = $result['userCount'] + $users[$m];
                    } else {
                        $users[$m] = (ToolKit::isEmpty($users[$m])) ? 0 : $users[$m];
                    }
                }
            }
        }
        foreach ($users as $user) {
            $return[] = $user;
        }
        unset($users);
        return $return;
    }

    /*
    * function to get the other user counts
    * @param $total, $supporter, $prospect, $nonSupporter, $unknown all as array
    * @return array $return
    */
    function getOtherUserCountByType($total, $supporter, $prospect, $nonSupporter, $unknown)
    {
        for ($m = 0; $m < date('m', time()); $m++) {
            $others[$m] = $total[$m] - ($supporter[$m] + $prospect[$m] + $nonSupporter[$m] + $unknown[$m]);
        }
        foreach ($others as $other) {
            $return[] = $other;
        }
        unset($others);
        return $return;
    }

    /**
     * Function to convert DB time to system time
     * The time show in the views as per the configuration
     */
    public static function convertDBTime($dateTime = null)
    {
        $dateTime = new \DateTime($dateTime, new \DateTimeZone('Europe/Paris'));
        $dateTime->setTimezone(new \DateTimeZone('UTC'));
        return $dateTime->format('Y-m-d H:i:s');
    }

    public static function getClientProfile($profTypes = array('ALL'), $isCampaign = false)
    {
        if (in_array('ALL', $profTypes)) {
            $profTypes = array('LN', 'TW', 'FB', 'GP', 'BLY', 'MC');
        }

        $modelUser = self::find()->where(['userType' => self::POLITICIAN])->andWhere(['!=', 'id', self::PARTNER_USER_ID])->andWhere(['!=', 'isManual', self::IS_MANUAL])->one();

        if (!is_null($modelUser)) {
            if (in_array('TW', $profTypes)) {
                $modelTwProfile = TwProfile::findOne(['userId' => $modelUser->id]);
            }

            if (in_array('FB', $profTypes)) {
                $modelFbProfile = FbProfile::findOne(['userId' => $modelUser->id]);
            }

            if (in_array('LN', $profTypes)) {
                $modelLnProfile = LnProfile::findOne(['userId' => $modelUser->id]);
            }

            if (in_array('BLY', $profTypes)) {
                $modelBlyProfile = BitlyProfile::findOne(['userId' => $modelUser->id]);
            }

        }

        return array(
            'modelUser' => $modelUser,
            'modelTwProfile' => isset($modelTwProfile) ? $modelTwProfile : null,
            'modelFbProfile' => isset($modelFbProfile) ? $modelFbProfile : null,
            'modelLnProfile' => isset($modelLnProfile) ? $modelLnProfile : null,
            'modelBlyProfile' => isset($modelBlyProfile) ? $modelBlyProfile : null,
        );
    }

    /**
     * Just concat first/last name
     * @return string return name
     */
    public function getName()
    {
        return "{$this->firstName} {$this->lastName}";
    }

    /**
     * Custom validator to implode checked values into string
     * @param string $attribute active record attribute name.
     */
    public function implodeParams($attribute)
    {
        if (is_array($this->$attribute)) {
            $this->$attribute = implode(',', $this->$attribute);
        }
    }

    /**
     * Validate for required attribute of name or email
     */
    public function checkRequired()
    {
        if ((!is_null($this->firstName) && !is_null($this->lastName)) || !is_null($this->email)) {

        } else {
            $this->addError("firstName", Yii::t('messages', 'First Name and Last Name or Email cannot be blank.'));
        }
    }


    /**
     * Identify missing fileds with, information we collected from respective social account.
     * @param array $availableFileds Fields collected from social account.
     * @return array $fields Available and not available filed list
     */
    public function getSignupFields($availableFileds = array())
    {
        $fields = array();
        foreach ($this->signupFields as $signupField) {
            $fields[$signupField] = (isset($availableFileds[$signupField]) && "" != $availableFileds[$signupField] ? $availableFileds[$signupField] : null);
        }

        return $fields;
    }

    /**
     * Prepare signup required fields list dynamically according to user selected fields.
     * @return array Rule.
     */
    private function getSignupRequiredFieldsRule()
    {
        return array($this->getSignupRequiredFields(), 'required', 'on' => 'signup');
    }

    /**
     * Retrive required fields for signup. Default+Client defined together
     * @return array Required fileds.
     */
    public function getSignupRequiredFields()
    {
        $clientDefinedFields = array();
        $modelCandidateInfo = CandidateInfo::find()->one();
        $defMandatoryFields = array_keys($this->getCustomSignupFields('m'));
        if (null != $modelCandidateInfo && null != $modelCandidateInfo->signupFields) {
            $clientDefinedFields = explode(",", $modelCandidateInfo->signupFields);
        }

        return array_merge($clientDefinedFields, $defMandatoryFields);
    }

    /**
     * Retrieve custom signup fields by type
     * @param string $type o-optional fields only,m-mandatory fields only,a-all.
     * @return array Fields according to requested type
     */
    public static function getCustomSignupFields($type = 'o')
    {
        $customSignupFields = array(
            'firstName' => array('m', Yii::t('messages', 'First Name')),
            'lastName' => array('m', Yii::t('messages', 'Last Name')),
            'email' => array('m', Yii::t('messages', 'Email')),
            'mobile' => array('o', Yii::t('messages', 'Mobile')),
            'address1' => array('o', Yii::t('messages', 'Street Address')),
            'zip' => array('o', Yii::t('messages', 'Zip')),
            'city' => array('o', Yii::t('messages', 'City')),
            'countryCode' => array('o', Yii::t('messages', 'Country Code')),
            'gender' => array('o', Yii::t('messages', 'Gender')),
            'dateOfBirth' => array('o', Yii::t('messages', 'Date of Birth')),
            'username' => array('m', Yii::t('messages', 'Username')),
            'password' => array('m', Yii::t('messages', 'Password')),
            'confPassword' => array('m', Yii::t('messages', 'Confirm Password')),
        );

        $fields = array();

        foreach ($customSignupFields as $fieldName => $fieldInfo) {
            $fieldType = $fieldInfo[0];
            $filedLabel = $fieldInfo[1];

            if ($fieldType == $type) {
                $fields[$fieldName] = $filedLabel;
            } else if ('a' == $type) {
                // All 'a'
                $fields[$fieldName] = $filedLabel;
            }
        }
        return $fields;
    }

    /**
     * Validate zip code to avoid geo location not generating from google api
     */
    public function checkZipCode()
    {
        $notAllowedList = array(' ');
        foreach ($notAllowedList as $letter) {
            if (strpos($this->zip, $letter) !== false && !isset($this->errors['zip'])) {
                $this->addError('zip', Yii::t('messages', 'Zip contains not allowed white space.'));
            }
        }
    }

    /**
     * Validate strings to avoid ; character
     */
    public function escapeSpecialCharacters()
    {
        $notAllowedList = array(';', '@', '~');
        foreach ($notAllowedList as $letter) {
            if (strpos($this->address1, $letter) !== false && !isset($this->errors['address1'])) {
                $this->addError('address1', Yii::t('messages', 'Street address contains not allowed characters.'));
            }

            if (strpos($this->zip, $letter) !== false && !isset($this->errors['zip'])) {
                $this->addError('zip', Yii::t('messages', 'Zip contains not allowed characters.'));
            }

            if (strpos($this->city, $letter) !== false && !isset($this->errors['city'])) {
                $this->addError('city', Yii::t('messages', 'City contains not allowed characters.'));
            }
        }
    }


    /**
     * Retrieve email addresses of moderators. Client, and client admins
     * @return array Email addresses
     */
    public function getModeratorEmails()
    {

        $query = new Query();
        $query = $query->select("t.email")
            ->from('User t')
            ->innerJoin('AuthAssignment AA', 't.id = AA.userId')
            ->where('AA.itemname=:itemname', [':itemname' => WebUser::POLITICIAN_ROLE_NAME]);

        $userModels = $query->all();
        $emails = array();

        if (!empty($userModels)) {
            foreach ($userModels as $userModel) {
                $emails[] = $userModel['email'];
            }
        }
        return $emails;
    }

    /**
     * Validate old password before password change
     */
    public function checkOldPassword()
    {
        if ($this->oldPasswordDb != $this->encryptUserPassword($this->oldPassword)) {
            $this->addError('oldPassword', Yii::t('messages', 'Invalid old password.'));
        }
    }

    /**
     * Validate mobile number in international format using intl-tel-input extension
     * TODO Needs to remove the functionality if working
     */
    /*public function validatingMobileNumber($attribute_name, $params)
    {
        $attribute_value = $this->$attribute_name;
        $phoneNumberUtil = \libphonenumber\PhoneNumberUtil::getInstance();
        try {
            $phoneNumberObject = $phoneNumberUtil->parse($attribute_value, "FR");
            $isValidNumberForRegion = $phoneNumberUtil->isValidNumberForRegion($phoneNumberObject, null);
            $isValid = $phoneNumberUtil->isValidNumber($phoneNumberObject);
            $countryCode = $phoneNumberObject->getCountryCode();
            if ($isValidNumberForRegion == false && $countryCode == 33) {
                $re = '/^
                    (?:(?:\+|00)33|1|0)     # Dialing code
                    \s*[1-9]              # First number (from 1 to 9)
                    (?:[\s.-]*\d{2}){4}   # End of the phone number
                $/mix';

                preg_match_all($re, $attribute_value, $matches, PREG_SET_ORDER, 0);

                if ($matches) {
                    $isValid = true;
                } else {
                    throw new NumberParseException(1, Yii::t('messages', 'Invalid mobile number'));
                }
            }

            if (!$isValid) { // Country code check
                throw new NumberParseException(1, Yii::t('messages', 'Invalid Mobile number or Country code'));
            }
            return true;
        } catch (\libphonenumber\NumberParseException $e) {
            $this->addError($attribute_name, Yii::t('messages', $e->getMessage()));
        }

        return true;

    }*/

    /**
     * Validate mobile number in international format using intl-tel-input extension
     */
    public function validateMobileNumber()
    {
        if (isset($mobile)) {
            $this->mobile = $mobile;
        }
        if (!ToolKit::isEmpty($this->mobile)) {
            Yii::setAlias('@libphonenumber', '@app/vendor/borales/yii2-phone-input');
            $phoneUtil = PhoneNumberUtil::getInstance();
            try {
                $phoneNumber = $phoneUtil->parse($this->mobile, null);
                if (!$phoneUtil->isValidNumber($phoneNumber)) {
                    Yii::$app->session->setFlash('error', Yii::t('messages', 'Mobile is invalid'));
                }
            } catch (Exception $ex) {
                $this->addError('mobile', Yii::t('messages', 'Mobile is invalid'));
                Yii::error("Mobile is invalid:" . $ex->getMessage());
            }
        }
    }

    /**
     * Generate new password for reset, according to password plicy
     * @return string $password Random password. ex:abcd123.@
     */
    public function getNewPassword()
    {
        $letters = 'bcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ';
        $numbers = '0123456789';
        $symbols = '-@_#&.';

        $password = substr(str_shuffle($letters), 0, 4);
        $password .= substr(str_shuffle($numbers), 0, 2);
        $password .= substr(str_shuffle($symbols), 0, 2);

        return $password;
    }

    /**
     * This method is called at the beginning of inserting or updating a record.
     *
     * The default implementation will trigger an [[EVENT_BEFORE_INSERT]] event when `$insert` is `true`,
     * or an [[EVENT_BEFORE_UPDATE]] event if `$insert` is `false`.
     * When overriding this method, make sure you call the parent implementation like the following:
     *
     * ```php
     * public function beforeSave($insert)
     * {
     *     if (!parent::beforeSave($insert)) {
     *         return false;
     *     }
     *
     *     // ...custom code here...
     *     return true;
     * }
     * ```
     *
     * @param bool $insert whether this method called while inserting a record.
     * If `false`, it means the method is called while updating a record.
     * @return bool whether the insertion or updating should continue.
     * If `false`, the insertion or updating will be cancelled.
     * @throws Exception
     */
    public function beforeSave($insert)
    {

        if (parent::beforeSave($insert)) {

            if ($this->isNewRecord) {

                $this->createdAt = self::convertSystemTime();

            }

            $this->updatedAt = self::convertSystemTime();

            return true;

        }
        return false;

    }

    /**
     * Save record with custom fields.
     * @param null $customFields
     * @param null $attributes
     * @param bool $isValidate
     * @return boolean whether the saving succeeds
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function saveWithCustomData($customFields = null, $attributes = null, $isValidate = true, $type = null)
    {
        $success = true;
        try {
            $success = $this->save($isValidate, $attributes);

            // Save custom fields data
            if ($success && !empty($customFields)) {
                foreach ($customFields as $k => $customField) {
                    $CustomValue = CustomValue::find()->where(['relatedId' => $this->id, 'customFieldId' => $customField->customFieldId])->one();
                    if ($CustomValue) {
                        $CustomValue->fieldValue = $customField->fieldValue;
                        $CustomValue->update();
                    } else {
                        $addNewCustomValues = new CustomValue();
                        $addNewCustomValues->customFieldId = $customField->customFieldId;
                        $addNewCustomValues->relatedId = $this->id;
                        if (is_array($customField->fieldValue)) {
                            $fieldValue = implode(",", $customField->fieldValue);
                        } else {
                            $fieldValue = $customField->fieldValue;
                        }
                        $addNewCustomValues->fieldValue = $fieldValue;
                        $addNewCustomValues->save();

                    }
                }
            }
        } catch (Exception $e) {
            $success = false;

            // Added this condition to check the call is coming from bulk insert or different source.
            if (is_null($type)) {
                Yii::$app->session->setFlash('error', Yii::t('messages', 'People could not be saved.') . ' ' . $e->getMessage());
            }

            Yii::$app->appLog->writeLog("People could not be saved" . $e->getMessage());
        }

        return $success;

    }

    /**
     * Update master database user profile when change it on the application
     */
    public function updateMasterProfile()
    {
        $appData = Yii::$app->toolKit->getAppData();
        $command = Yii::$app->dbMaster->createCommand();
        try {
            $command->update('User', array(
                'address1' => $this->address1,
                'mobile' => $this->mobile,
                'firstName' => $this->firstName,
                'lastName' => $this->lastName,
                'username' => $this->username,
                'password' => $this->password,
                'email' => $this->email
            ), 'id=:id', array(':id' => $appData['masterUserId']));
            Yii::error("Master profile details updated");
        } catch (Exception $e) {
            Yii::error("Master profile details update failed. Error:{$e->getMessage()}");
        }
    }

    /**
     * @return int|string
     */
    public static function getNumberOfMobileContacts()
    {
        $count = User::find()->where("mobile != '' AND mobile IS NOT NULL AND userType != :userType1 AND userType !=
		:userType2 AND isSysUser = :isSysUser")->addParams(array(":userType1" => self::POLITICIAN, ":userType2" =>
            self::SUPER_ADMIN, ":isSysUser" => 0))->count();
        return $count;
    }


    /**
     * @param null $dateTime
     * @return string
     * @throws Exception
     */
    public static function convertSystemTime($dateTime = null)
    {
        $configuration = new Configuration();
        $timeZone = $configuration->getTimeZone();

        try {

            $now = new \DateTime('now', new \DateTimeZone($timeZone));

            if ($dateTime != null) {
                $now = new \DateTime($dateTime, new \DateTimeZone($timeZone));
            }

            return $now->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * Typical usecase:
     * - Initialize the model fields with values from filter form.
     * - Execute this method to get CActiveDataProvider instance which will filter
     * models according to data in model fields.
     * - Pass data provider to CGridView, CListView or any similar widget.
     *
     * @param $params
     * @return ActiveDataProvider the data provider that can return the models
     * based on the search/filter conditions.
     */
    public function searchPeople($params)
    {
        $query = User::find()->all();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 10],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'gender' => $this->gender,
            'joinedDate' => $this->joinedDate,
            'signUpDate' => $this->signUpDate,
            'supporterDate' => $this->supporterDate,
            'userType' => $this->userType,
            'signup' => $this->signup,
            'isSysUser' => $this->isSysUser,
            'dateOfBirth' => $this->dateOfBirth,
            'reqruiteCount' => $this->reqruiteCount,
            'delStatus' => $this->delStatus,
            'isUnsubEmail' => $this->isUnsubEmail,
            'isManual' => $this->isManual,
            'isSignupConfirmed' => $this->isSignupConfirmed,
            'totalDonations' => $this->totalDonations,
            'isMcContact' => $this->isMcContact,
            'emailStatus' => $this->emailStatus,
            'formId' => $this->formId,
            'addressInvalidatedAt' => $this->addressInvalidatedAt,
            'resetPasswordTime' => $this->resetPasswordTime,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ]);

        $query->andFilterWhere(['like', 'address1', $this->address1])
            ->andFilterWhere(['like', 'mobile', $this->mobile])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'firstName', $this->firstName])
            ->andFilterWhere(['like', 'lastName', $this->lastName])
            ->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'password', $this->password])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'zip', $this->zip])
            ->andFilterWhere(['like', 'countryCode', $this->countryCode])
            ->andFilterWhere(['like', 'keywords', $this->keywords])
            ->andFilterWhere(['like', 'city', $this->city])
            ->andFilterWhere(['like', 'longLat', $this->longLat])
            ->andFilterWhere(['like', 'profImage', $this->profImage])
            ->andFilterWhere(['like', 'notes', $this->notes])
            ->andFilterWhere(['like', 'network', $this->network])
            ->andFilterWhere(['like', 'pwResetToken', $this->pwResetToken]);

        return $dataProvider;
    }

    /**
     * Assign team for new user
     * @param string $zip ZIP code
     * @param integer $userId User id of the new user
     * @return array
     */
    public function assignTeam($zip, $userId)
    {
        $teamId = Team::getMatchingTeam($zip);
        $teamMemInfo = array('teamId' => $teamId, 'status' => false, 'moderate' => false);

        if (null != $teamId) {
            $modelConfig = Configuration::findAll(Configuration::VALIDATE_VOLUNTEER);
            $model = new TeamMember();
            $model->teamId = $teamId;
            $model->memberUserId = $userId;
            $model->createdAt = date('Y-m-d H:i:s');

            if ($modelConfig->value) {
                $teamMemInfo['moderate'] = true;
                $model->isApproved = TeamMember::PENDING;

                // Send email alert to moderators
                $modelTeam = Team::findOne($teamId);
                $modelTeamLead = User::findOne($modelTeam->leaderUserId);
                $modelUser = User::findOne($userId);
                $message = Yii::t('messages', 'New member "{name}" joined to the team "{teamName}". Pending for membership approval.', array(
                    '{teamName}' => $modelTeam->name,
                    '{name}' => $modelUser->getName()
                ));

//                Yii::$app->toolKit->sendModeratorEmails($message, array($modelTeamLead->email));
            }

            try {
                if ($model->save(false)) {
                    $teamMemInfo['status'] = true;
                }
            } catch (Exception $e) {
                Yii::error("assignTeam {{$e->getCode()}}");
            }
        }

        return $teamMemInfo;
    }

    /**
     * Returns User types(Category) or label.
     * @param integer $key user type identifier
     * @return array Available user types
     */
    public static function getUserTypes($key = null)
    {
        $types = array(
            //self::POLITICIAN =>'Politician',
            self::SUPPORTER => Yii::t('messages', 'Supporter'),
            self::PROSPECT => Yii::t('messages', 'Prospect'),
            self::NON_SUPPORTER => Yii::t('messages', 'Non-Supporter'),
            self::UNKNOWN => Yii::t('messages', 'Unknown'),
        );

        if (null != $key) {
            return isset($types[$key]) ? $types[$key] : '';
        }

        return $types;
    }


    /**
     * @param null $key
     * @return array|mixed|string
     */
    public static function getUserGender($key = null)
    {
        $gender = [
            '' => Yii::t('messages', '- Gender -'),
            self::MALE => Yii::t('messages', 'Male'),
            self::FEMALE => Yii::t('messages', 'Female'),
            self::ASEXUAL => Yii::t('messages', 'Other'),
        ];
        if (null != $key) {
            return isset($gender[$key]) ? $gender[$key] : '';
        }
        return $gender;
    }

    /**
     * /**
     * Retrieve name of the user by id
     * @param integer $userId Id of the user
     * @return string $name Name of the user
     */
    public static function getNameById($userId)
    {
        $name = '-';
        $model = User::findOne($userId);

        if (null != $model) {
            $name = "{$model->firstName} {$model->lastName}";
        }

        return $name;
    }

    /**
     * Returns email status label.
     * @param integer $key email type identifier
     * @return array Available emails status
     */
    public function getEmailStatus($key = null)
    {
        $types = array(
            '' => Yii::t('messages', '- Email Status -'),
            self::UNSUBSCRIBE_EMAIL => Yii::t('messages', 'Unsubscribed'),
            self::BOUNCED_EMAIL => Yii::t('messages', 'Bounced'),
            self::BLOCKED_EMAIL => Yii::t('messages', 'Blocked'),
        );

        if (null != $key) {
            return isset($types[$key]) ? $types[$key] : '';
        }

        return $types;
    }

    /**
     * Returns social network types or network type label.
     * @param integer $network Network identifier
     * @return mixed Network types or network type label
     */
    public function getMultiSelectNetworkTypes($network = null)
    {
        $networks = array(
            array("id" => User::MOBILE, 'network' => Yii::t('messages', 'Mobile')),
            array("id" => User::EMAIL, 'network' => Yii::t('messages', 'Email')),
        );

        if (null != $network) {
            return $networks[$network];
        }

        return $networks;
    }

    /**
     * Return forms titles
     */
    public function getFormsDetails()
    {
        $options = array();
        $options[''] = Yii::t('messages', '- Form Title -');
        $models = Form::find()->orderBy(['title' => SORT_DESC])->all();
        foreach ($models as $model) {
            $options[$model->id] = Yii::t('messages', $model->title);
        }

        return $options;
    }

    /**
     * {@inheritdoc}
     * @return UserQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new UserQuery(get_called_class());
    }

    /**
     * Prepare country selecting dropdown menu.
     * @return array $options Country options
     */
    public function getSearchType()
    {
        return [
            self::SEARCH_NORMAL => Yii::t('messages', 'Normal'),
            self::SEARCH_STRICT => Yii::t('messages', 'Strict'),
            self::SEARCH_EXCLUDE => Yii::t('messages', 'Exclude'),
        ];
    }

    /**
     * Retrive gender label when gender identifier given
     * @param integer $gender Male or Female
     * @param int $type
     * @return string $genderLabel Gender label
     */
    public static function getGenderLabel($gender, $type = 0)
    {
        $genderLabel = '';
        $defaultLabel = '';
        switch ($gender) {
            case self::MALE:
                $genderLabel[0] = Yii::t('messages', 'Male');
                $defaultLabel = Yii::t('messages', 'Male');
                break;

            case self::FEMALE:
                $genderLabel[0] = Yii::t('messages', 'Female');
                $defaultLabel = Yii::t('messages', 'Female');
                break;

            default:
                $genderLabel[0] = Yii::t('messages', 'Unknown');
                $defaultLabel = Yii::t('messages', 'Unknown');
        }

        if ($type == 4) { // Type 4 is for default. It use only for account merge
            return $defaultLabel;
        }
        return @$genderLabel[$type];
    }

    /**
     * Retrive gender Icon when gender identifier given
     * @param integer $gender Male or Female
     * @param int $type
     * @return string $genderLabel Gender label
     */
    public static function getGenderIcon($gender)
    {
        switch ($gender) {
            case self::MALE:
                $genderLabel = '<i class="fa fa-male fa-lg"></i>';
                break;

            case self::FEMALE:
                $genderLabel = '<i class="fa fa-female fa-lg"></i>';;
                break;

            default:
                $genderLabel = '';
        }

        return $genderLabel;
    }

    /**
     * Get network icons (Facebook or Twitter)
     * @param User Object $data
     * @return array
     */
    public function getNetworkIcons($data)
    {
        $icon = '';
        $count = array();
        $fbModel = FbProfile::find()->where(['userId' => $data->id])->all();
        if ($fbModel != null) {
            $icon .= '<i class="fa fa-facebook fa-lg fb-icon"></i>';
            $count[] = 1;
        }

        $lnModel = LnProfile::find()->where(['userId' => $data->id])->all();
        if ($lnModel != null) {
            $icon .= '<i class="fa fa-linkedin-square fa-lg ln-icon"></i>';
            $count[] = 1;
        }
        return array('network' => $icon, 'count' => count($count));
    }

    /**
     * Get people network icons (Facebook or Twitter)
     * @param User Object $data
     * @return string
     */
    public function getPeopleNetworkIcons($data)
    {
        $icon = '';
        $count = array();
        $fbModel = FbProfile::find()->where(['userId' => $data->id])->all();
        if ($fbModel != null) {
            $icon .= '<i class="fa fa-facebook fa-lg fb-icon"></i>';
            $count[] = 1;
        }

        $twModel = TwProfile::find()->where(['userId' => $data->id])->all();
        if ($twModel != null) {
            $icon .= '<i class="fa fa-twitter fa-lg tweet-icon"></i>';
            $count[] = 1;
        }

        $lnModel = LnProfile::find()->where(['userId' => $data->id])->all();
        if ($lnModel != null) {
            $icon .= '<i class="fa fa-linkedin-square fa-lg ln-icon"></i>';
            $count[] = 1;
        }
        return $icon;
    }

    /**
     * @param $keywords
     * @return string
     * TODO needs to bring into keyword Model
     */
    public static function getUserKeywordNames($keywords)
    {
        $Keyword_model = new Keyword();
        $keywordLabel = array();
        $keyword = '';
        if (strpos($keywords, ',') !== false) {
            $keywords = explode(',', $keywords);
            foreach ($keywords as $keywordId) {

                $keywordLabel[] = $Keyword_model->getLabel($keywordId);
            }
            $keyword = implode(', ', $keywordLabel);
        } else {
            $keyword = $Keyword_model->getLabel($keywords);
        }
        return $keyword;
    }

    /**
     * @param $userType
     * @return array
     */
    public function getUserTypeLabel($userType)
    {
        $type = 'N/A';
        $color = '';
        $types = array(
            //self::POLITICIAN =>'Politician',
            self::SUPPORTER => Yii::t('messages', 'Supporter'),
            self::PROSPECT => Yii::t('messages', 'Prospect'),
            self::NON_SUPPORTER => Yii::t('messages', 'Non-Supporter'),
            self::UNKNOWN => Yii::t('messages', 'Unknown'),
        );

        $colors = array(
            //self::POLITICIAN =>'Politician',
            self::SUPPORTER => 'color: green',
            self::PROSPECT => 'color: orange',
            self::NON_SUPPORTER => 'color: red',
            self::UNKNOWN => 'color: gray',
        );

        if (null != $userType) {
            $type = isset($types[$userType]) ? $types[$userType] : 'N/A';
            $color = isset($colors[$userType]) ? $colors[$userType] : '';
        }
        return array('color' => $color, 'type' => $type);
    }


    /**
     * Delete record with custom fields.
     * @return boolean whether the deleting succeeds
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function deleteWithCustomData()
    {
        $success = true;
        $transaction = $this->db->beginTransaction();

        try {
            $success = $this->delete();
            //Delete custom fields data
            if ($success) {
                FbProfile::deleteAll('userId=:userId', array(':userId' => $this->id));
                TwProfile::deleteAll('userId=:userId', array(':userId' => $this->id));
                GpProfile::deleteAll('userId=:userId', array(':userId' => $this->id));
                LnProfile::deleteAll('userId=:userId', array(':userId' => $this->id));
                CustomValue::deleteAll('relatedId=:relatedId AND customFieldId IN (SELECT id FROM CustomField WHERE relatedTable=:relatedTable)', array(':relatedId' => $this->id, ':relatedTable' => CustomType::CF_PEOPLE));
                UserMatchSub::deleteAll('subUserId=:userId', array(':userId' => $this->id));
                UserMatchSub::deleteAll('mainUserId=:userId', array(':userId' => $this->id));
                UserMatchMain::deleteAll('userId=:userId', array(':userId' => $this->id));
            }
        } catch (Exception $e) {
            $success = false;

            Yii::$app->session->setFlash('error', Yii::t('messages', 'People could not be deleted.') . ' ' . $e->getMessage());
        }

        if ($success)
            $transaction->commit();
        else
            $transaction->rollBack();

        return $success;
    }

    /**
     * Prepare the connection list grid data
     * @param string $networkUserId User id of network
     * @return array|int
     */
    public function getConnections($networkUserId, $countOnly = false)
    {
        $connections = array();
        $id = 0;

        $twProfConModels = TwProfileConnection::findAll(['childTwUserId' => $networkUserId]);

        if (null != $twProfConModels) {
            foreach ($twProfConModels as $twProfConModel) {
                $twProfModel = TwProfile::findOne($twProfConModel->parentTwUserId);
                if (null != $twProfModel) {
                    $userModel = User::findOne($twProfModel->userId);
                    if (null != $userModel && $userModel->userType == User::SUPPORTER && User::NOTDELETE == $userModel->delStatus) {
                        $emailIcon = '';
                        $twitterIcon = '';
                        $facebookIcon = '';
                        $linkedInIcon = '';
                        $conType = TwProfileConnection::CON_TYPE_FOLLOWER == $twProfConModel->connectionType ? FollowUp::CONTYPE_TW_FOLLOWER : FollowUp::CONTYPE_TW_FRIEND;
                        $prosTwProf = TwProfile::findOne($networkUserId);
                        $data = "{$twProfModel->twUserId},{$twProfModel->userId},{$prosTwProf->userId}," . TwitterApi::TWITTER . ",{$conType}," . FollowUp::REQ_TYPE_REQUEST;
                        if ($userModel->signup) {

                            // Check whether supporter has email
                            if (!$userModel->isUnsubEmail) {
                                if (!FollowUp::isNotified($twProfModel->userId, $prosTwProf->userId, FollowUp::NOTITYPE_EMAIL)) {
                                    $emailIcon = '<a href="#" title="' . Yii::t('messages', 'Send email') . '" id="emailIcon" data="' . $data . '">' . Yii::app()->fa->getIcon('envelope') . '</a>&nbsp;';
                                } else {
                                    $emailIcon = '<span class="icon-disabled" title="' . Yii::t('messages', 'Already sent an email') . '">' . Yii::app()->fa->getIcon('envelope') . '</span>&nbsp;';
                                }
                            }

                            // Check whether supporter has facebook profile
                            $fbProfModel = FbProfile::model()->findByAttributes(array('userId' => $userModel->id));
                            if (null != $fbProfModel) {
                                $dataFb = "{$fbProfModel->fbUserId},{$fbProfModel->userId},{$prosTwProf->userId}," . FacebookApi::FACEBOOK . ",{$conType}," . FollowUp::REQ_TYPE_REQUEST;
                                if (!FollowUp::isNotified($fbProfModel->userId, $prosTwProf->userId, FollowUp::NOTITYPE_FB_MESSAGE)) {
                                    $facebookIcon = '<a href="#" title="' . Yii::t('messages', 'Send Facebook message') . '" id="facebookIcon" data="' . $dataFb . '">' . Yii::app()->fa->getIcon('facebook-square') . '</a>';
                                } else {
                                    $facebookIcon = '<span class="icon-disabled" title="' . Yii::t('messages', 'Already sent a Facebook message') . '">' . Yii::app()->fa->getIcon('facebook-square') . '</span>&nbsp;';
                                }
                            }

                            // Check whether supporter has Twitter profile
                            if (!FollowUp::isNotified($twProfModel->userId, $prosTwProf->userId, FollowUp::NOTITYPE_TW_MESSAGE)) {
                                $twitterIcon = '<a href="#" title="' . Yii::t('messages', 'Send Twitter direct message') . '" id="tweetIcon" data="' . $data . '">' . Yii::app()->fa->getIcon('twitter') . '</a>';
                            } else {
                                $twitterIcon = '<span class="icon-disabled" title="' . Yii::t('messages', 'Already sent a Twitter message') . '">' . Yii::app()->fa->getIcon('twitter') . '</span>&nbsp;';
                            }

                            // Check whether supporter has LinkedIn profile
                            $lnProfModel = LnProfile::findAll(array('userId' => $userModel->id));
                            if (null != $lnProfModel) {
                                $dataLn = "{$lnProfModel->lnUserId},{$lnProfModel->userId},{$prosTwProf->userId}," . LinkedInApi::LINKEDIN . ",{$conType}," . FollowUp::REQ_TYPE_REQUEST;
                                if (!FollowUp::isNotified($lnProfModel->userId, $prosTwProf->userId, FollowUp::NOTITYPE_LN_MESSAGE)) {
                                    $linkedInIcon = '<a href="#" title="' . Yii::t('messages', 'Send LinkedIn message') . '" id="linkedInIcon" data="' . $dataLn . '">' . Yii::app()->fa->getIcon('linkedin-square') . '</a>';
                                } else {
                                    $linkedInIcon = '<span class="icon-disabled" title="' . Yii::t('messages', 'Already sent a LinkedIn message') . '">' . Yii::app()->fa->getIcon('linkedin-square') . '</span>&nbsp;';
                                }
                            }
                        }

                        $icons = '';

                        if (Yii::$app->user->checkAccess("SendSingleMessage")) {
                            $icons = "{$emailIcon} {$twitterIcon} {$facebookIcon} {$linkedInIcon}";
                        }

                        $connections[$id] = array(
                            'id' => $id,
                            'userId' => $userModel->id,
                            'networkUserId' => $twProfModel->twUserId,
                            'name' => $userModel->getName(),
                            'network' => TwitterApi::TWITTER,
                            'profileImageUrl' => $twProfModel->profileImageUrl,
                            'networkIcon' => '<span class="badge badge-warning">' . FollowUp::getConTypeOptions($conType) . '</span>', //'<span class="tweet-icon"><i class="fa fa-twitter"></i></span>',
                            'icons' => $icons,
                        );
                        $id++;
                    }
                }
            }
        }

        $lnProfConModels = LnProfileConnection::findAll(array('childLnUserId' => $networkUserId));

        if (null != $lnProfConModels) {
            foreach ($lnProfConModels as $lnProfConModel) {
                $lnProfModel = LnProfile::findOne($lnProfConModel->parentLnUserId);
                if (null != $lnProfModel) {
                    $userModel = User::findOne($lnProfModel->userId);
                    if (null != $userModel && $userModel->userType == User::SUPPORTER && User::NOTDELETE == $userModel->delStatus) {
                        $emailIcon = '';
                        $twitterIcon = '';
                        $facebookIcon = '';
                        $linkedInIcon = '';
                        $conType = FollowUp::CONTYPE_LN_CONNECTION;
                        $prosLnProf = LnProfile::findOne($networkUserId);
                        $data = "{$lnProfModel->lnUserId},{$lnProfModel->userId},{$prosLnProf->userId}," . LinkedInApi::LINKEDIN . ",{$conType}," . FollowUp::REQ_TYPE_REQUEST;
                        if ($userModel->signup) {

                            if (!$userModel->isUnsubEmail) {
                                if (!FollowUp::isNotified($lnProfModel->userId, $prosLnProf->userId, FollowUp::NOTITYPE_EMAIL)) {
                                    $emailIcon = '<a href="#" title="' . Yii::t('messages', 'Send email') . '" id="emailIcon" data="' . $data . '">' . Yii::app()->fa->getIcon('envelope') . '</a>&nbsp;';
                                } else {
                                    $emailIcon = '<span class="icon-disabled" title="' . Yii::t('messages', 'Already sent an email') . '">' . Yii::app()->fa->getIcon('envelope') . '</span>&nbsp;';
                                }
                            }

                            // Check whether supporter has facebook profile
                            $fbProfModel = FbProfile::findAll(array('userId' => $userModel->id));
                            if (null != $fbProfModel) {
                                $dataFb = "{$fbProfModel->fbUserId},{$fbProfModel->userId},{$prosLnProf->userId}," . FacebookApi::FACEBOOK . ",{$conType}," . FollowUp::REQ_TYPE_REQUEST;
                                if (!FollowUp::isNotified($fbProfModel->userId, $prosLnProf->userId, FollowUp::NOTITYPE_FB_MESSAGE)) {
                                    $facebookIcon = '<a href="#" title="' . Yii::t('messages', 'Send Facebook message') . '" id="facebookIcon" data="' . $dataFb . '">' . Yii::app()->fa->getIcon('facebook-square') . '</a>';
                                } else {
                                    $facebookIcon = '<span class="icon-disabled" title="' . Yii::t('messages', 'Already sent a Facebook message') . '">' . Yii::app()->fa->getIcon('facebook-square') . '</span>&nbsp;';
                                }
                            }

                            // Check whether supporter has twitter profile
                            $twProfModel = TwProfile::findAll(array('userId' => $userModel->id));
                            if (null != $twProfModel) {
                                $dataTw = "{$twProfModel->twUserId},{$twProfModel->userId},{$prosLnProf->userId}," . TwitterApi::TWITTER . ",{$conType}," . FollowUp::REQ_TYPE_REQUEST;
                                if (!FollowUp::isNotified($twProfModel->userId, $prosLnProf->userId, FollowUp::NOTITYPE_TW_MESSAGE)) {
                                    $twitterIcon = '<a href="#" title="' . Yii::t('messages', 'Send Twitter direct message') . '" id="tweetIcon" data="' . $dataTw . '">' . Yii::app()->fa->getIcon('twitter') . '</a>';
                                } else {
                                    $twitterIcon = '<span class="icon-disabled" title="' . Yii::t('messages', 'Already sent a Twitter message') . '">' . Yii::app()->fa->getIcon('twitter') . '</span>&nbsp;';
                                }
                            }

                            if (!FollowUp::isNotified($lnProfModel->userId, $prosLnProf->userId, FollowUp::NOTITYPE_LN_MESSAGE)) {
                                $linkedInIcon = '<a href="#" title="' . Yii::t('messages', 'Send LinkedIn message') . '" id="linkedInIcon" data="' . $data . '">' . Yii::app()->fa->getIcon('linkedin-square') . '</a>';
                            } else {
                                $linkedInIcon = '<span class="icon-disabled" title="' . Yii::t('messages', 'Already sent a LinkedIn message') . '">' . Yii::app()->fa->getIcon('linkedin-square') . '</span>&nbsp;';
                            }
                        }

                        $icons = '';

                        if (Yii::$app->user->checkAccess("SendSingleMessage")) {
                            $icons = "{$emailIcon} {$twitterIcon} {$facebookIcon} {$linkedInIcon}";
                        }

                        $connections[$id] = array(
                            'id' => $id,
                            'userId' => $userModel->id,
                            'networkUserId' => $lnProfModel->lnUserId,
                            'name' => $userModel->getName(),
                            'network' => LinkedInApi::LINKEDIN,
                            'profileImageUrl' => $lnProfModel->pictureUrl,
                            'networkIcon' => '<span class="badge badge-warning">' . FollowUp::model()->getConTypeOptions($conType) . '</span>',
                            'icons' => $icons,
                        );
                        $id++;
                    }
                }
            }
        }

        if ($countOnly) {
            return $id;
        } else {
            return $connections;
        }
    }


    /**
     * Retrieve number of supporter connections prospect
     * @param string $userType Usertype whether PROSPECT, SUPPORTER etc..
     * @param integer $networkUserId Facebook or Twitter user id
     * @param string $section To where you need this out put.
     * @return string $conStr Connection count embeded html string
     */
    public function getConnectionCount($userType, $networkUserId, $userId = null, $section = 'dashboard')
    {
        $conStr = '';

        switch ($section) {
            case 'dashboard':
                if (User::PROSPECT == $userType || User::UNKNOWN == $userType) {
                    $count = $this->getConnections($networkUserId, true);
                    if (0 != $count) {
                        $data = "{$networkUserId}";
                        $conStr = '<a href="#" id="linkConnection" data="' . $data . '"><span class="badge badge-warning">' . $count . '</span></a>';
                    } else {
                        $conStr = '<span class="badge badge-warning">' . $count . '</span>';
                    }
                }
                break;
            case 'people':
                if (User::PROSPECT == $userType || User::UNKNOWN == $userType) {
                    $networkUserId = $this->getNetworkUserId($userId);

                    $count = $this->getConnections($networkUserId, true);
                    if (0 != $count) {
                        $data = "{$networkUserId}";
                        $conStr = '<a href="#" class="linkConnection" data="' . $data . '"><span class="badge badge-warning">' . $count . '</span></a>';
                    } else {
                        $conStr = '<span class="badge badge-warning">' . $count . '</span>';
                    }
                }
                break;
            case 'followup':
                if (User::PROSPECT == $userType || User::UNKNOWN == $userType) {
                    $networkUserId = $this->getNetworkUserId($userId);

                    $count = $this->getConnections($networkUserId, true);
                    if (0 != $count) {
                        $data = "{$networkUserId}";
                        $conStr = '<a href="#" class="follConnection" data="' . $data . '"><span class="badge badge-warning">' . $count . '</span></a>';
                    } else {
                        $conStr = '<span class="badge badge-warning">' . $count . '</span>';
                    }
                }
                break;
        }

        return $conStr;
    }

    /**
     * Format bulk values accordingly.
     * @param array $attributes Array of attributes
     * @return array Array of formatted attributes
     * TODO needs to take separete model
     */
    public static function formatAttributes($attributes,$type=null)
    {
        $formattedAttr = array();
        foreach ($attributes as $key => $value)
        {
            if(!empty($type) AND is_array($value))
            {
                $value=implode(",", $value);
            }

            if (strtolower($value) == 'n/a' || trim($value) == '') {
                $formattedAttr[$key] = null;
            } else {
                switch ($key) {
                    case 'firstName':
                    case 'lastName':
                    case 'email':
                    case 'address1':
                    case 'zip':
                    case 'city':
                    case 'notes':
                    case 'countryCode':
                    case 'dateOfBirth':
                    case 'keywords':
                        $formattedAttr[$key] = utf8_encode($value);
                        break;
                    case 'gender':
                    case 'mobile':
                        $formattedAttr[$key] = $value;
                        break;
                    default:
                        $customType = CustomField::getCustomType($key, CustomType::CF_PEOPLE);
                        if (!is_null($customType)) {
                            switch ($customType) {
                                case 'boolean':
                                    if (in_array(strtolower($value), array(self::BOOL_YES, self::BOOL_NO))) {
                                        $formattedAttr[$key] = $value;
                                    } else {
                                        $formattedAttr[$key] = self::BOOL_NO; // 'n/a' or any other wrong inputs
                                    }
                                    break;

                                case 'checkbox':
                                    $formattedAttr[$key] = $value; //array
                                    break;

                                default:
                                    $formattedAttr[$key] = utf8_encode($value);
                                    break;
                            }
                        } else {
                            $formattedAttr[$key] = utf8_encode($value);
                        }
                        break;
                }
            }

        }
        Yii::$app->appLog->writeLog(json_encode($formattedAttr));
        return $formattedAttr;
    }


    /**
     * Returns social network types or network type label.
     * @param integer $network Network identifier
     * @return mixed Network types or network type label
     */
    public static function getNetworkTypes($network = null)
    {
        $networks = array(
            '' => Yii::t('messages', '- Social Network -'),
            TwitterApi::TWITTER => Yii::t('messages', 'Twitter'),
            // FacebookApi::FACEBOOK => Yii::t('messages', 'Facebook'),
            LinkedInApi::LINKEDIN => Yii::t('messages', 'LinkedIn'),
            //GooglePlusApi::GOOGLE_PLUS => Yii::t('messages', 'Google+'),
        );

        if (null != $network) {
            return $networks[$network];
        }

        return $networks;
    }

    public function changeDobToNull()
    {
        $connection = Yii::$app->getDb();
        $command = $connection->createCommand("UPDATE User SET dateOfBirth = null WHERE dateOfBirth = 0000-00-00");
        $command->execute();
    }

    /**
     * Apply custom search criteria
     */
    public function getCustomSearchCriteria($query)
    {
        $con = '';
        $customFieldCount = count($this->customFieldData);
        if ($customFieldCount > 1) {
            $customFieldCount--;

            foreach ($this->customFieldData as $customField) {
                $customType = CustomType::getCustomFieldType($customField->customFieldId);
                if (null != $customType && CustomType::CF_TYPE_TEXT == $customType) {
                    $subcon = '(cv.customFieldId=' . $customField->customFieldId . " AND (cv.fieldValue LIKE '" . "%" . $customField->fieldValue . "%'))";
                } else {
                    $subcon = '(cv.customFieldId=' . $customField->customFieldId . " AND (cv.fieldValue = '" . $customField->fieldValue . "'))";
                }
                $con .= empty($con) ? $subcon : " OR " . $subcon;
            }

            $query->join('INNER JOIN', '(SELECT cv.relatedId FROM CustomValue cv WHERE ' . $con . ' GROUP BY cv.relatedId HAVING
 count(*) > ' . $customFieldCount . ') tblcv', 'tblcv.relatedId = t.id');


        } else {
            $customType = CustomType::getCustomFieldType($this->customFieldData[0]->customFieldId);
            if (null != $customType && CustomType::CF_TYPE_TEXT == $customType) {
                $query->join('INNER JOIN', '(SELECT cv.relatedId FROM CustomValue cv WHERE (cv.customFieldId=' . $this->customFieldData[0]->customFieldId . ' AND (cv.fieldValue LIKE "%" "' . $this->customFieldData[0]->fieldValue . '" "%"))) tblcv', 'tblcv.relatedId = t.id');
            } else {
                $query->join('INNER JOIN', '(SELECT cv.relatedId FROM CustomValue cv WHERE (cv.customFieldId=' . $this->customFieldData[0]->customFieldId . ' AND (cv.fieldValue = "' . $this->customFieldData[0]->fieldValue . '"))) tblcv', 'tblcv.relatedId = t.id');
            }
        }
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * Typical usecase:
     * - Initialize the model fields with values from filter form.
     * - Execute this method to get CActiveDataProvider instance which will filter
     * models according to data in model fields.
     * - Pass data provider to CGridView, CListView or any similar widget.
     *
     * @return ActiveDataProvider
     * based on the search/filter conditions.
     */
    public function searchVolunteers()
    {
        // @todo Please modify the following code to remove attributes that should not be searched.

        $query = new Query();
        $params = array();

        $query->select('u.*, TM.teamId, T.name AS teamName')
            ->from('User u')
            ->where('id', $this->id, true)
            ->andWhere('name', $this->name, true)
            ->andWhere('email', $this->email, true)
            ->leftJoin('TeamMember TM', 'TM.memberUserId = u.id')
            ->leftJoin('Team T', 'TM.teamId = T.id');

        if ('' != $this->teamId) {
            $query->filterWhere(['TM.teamId' => $this->teamId]);
        }
        $query->filterWhere(['signup' => 1, 'userType' => User::SUPPORTER]);

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 10],
        ]);

    }


    /**
     * Apply custom search data
     */
    public function setCustomSearchCriteria($data, $customFields, $isObject = true)
    {
        foreach ($data as $key => $val) {
            if (true == $isObject && is_array($val)) {
                foreach ($customFields as $customField) {
                    if ($key == $customField->customFieldId && !ToolKit::isEmpty($data[$key]['fieldValue'])) {
                        $customField->fieldValue = $data[$customField->customFieldId]['fieldValue'];
                        $this->customFieldData[] = $customField;
                        break;
                    }
                }
            } else if (false == $isObject) {
                foreach ($customFields as $customField) {
                    if ($key == $customField->customFieldId && !ToolKit::isEmpty($val)) {
                        $customField->fieldValue = $val;
                        $this->customFieldData[] = $customField;
                        break;
                    }
                }
            }
        }
        return $this->customFieldData;
    }

    /**
     * Returns email status label.
     * @param integer $key user type identifier
     * @return array Available emails status changes
     */
    public function changeEmailStatus($key = null)
    {
        $types = array(
            //self::POLITICIAN =>'Politician',
            '' => Yii::t('messages', '- Change Email Status -'),
            self::SUBSCRIBE_EMAIL => Yii::t('messages', 'Subscribe'),
            self::UN_BOUNCE_MAIL => Yii::t('messages', 'Un-Bounce'),
            self::UN_BLOCK_MAIL => Yii::t('messages', 'Un-Block'),
        );
        if (null != $key) {
            return isset($types[$key]) ? $types[$key] : '';
        }
        return $types;
    }

    /**
     * Save record with custom fields.
     * @return boolean whether the saving succeeds
     * @throws \yii\db\Exception
     */
    public function saveWithCustomDataNoValidation($customFields = null, $attributes = null, $isValidate = true)
    {
        $transaction = Yii::$app->getDb()->beginTransaction();
        $success = true;
        try {
            $success = $this->save($isValidate, $attributes);
            //Save custom fields data
            if ($success && !empty($customFields)) {
                foreach ($customFields as $k => $customField) {
                    $customField->relatedId = $this->id;
                    $success = $customField->save(false);
                    if (!$success) {
                        Yii::$app->appLog->writeLog("Custom Field save failed.Validation Errors:" . json_encode($customField->errors));
                        break;
                    }
                }
            }
        } catch (Exception $e) {
            $success = false;
            Yii::$app->session->setFlash('error', Yii::t('messages', 'People could not be saved.') . ' ' . $e->getMessage());
        }
        if ($success)
            $transaction->commit();
        else
            $transaction->rollBack();
        return $success;
    }

    /**
     * Returns email status for notifications.
     * @param integer $key user type identifier
     * @return array Available emails status changes
     */
    public function getDbEmailStatus($key = null)
    {
        $types = array(
            'NULL' => Yii::t('messages', 'Un-blocked or un-bounced'),
            self::DB_SUBSCRIBE => Yii::t('messages', 'Subscribed'),
        );
        if (null != $key) {
            return isset($types[$key]) ? $types[$key] : '';
        }
        return $types;
    }

    /**
     * Write Temporary status file
     * @param string $message message need to write.
     */
    private function writeTempBulkUploadFile($message = null)
    {
        $this->tmpStatusFile = Yii::$app->params['fileUpload']['status']['name'];
        if ($message === null)
            $message = implode("\n", $this->fileErrors);

        @file_put_contents(Yii::$app->params['fileUpload']['status']['path'] . $this->tmpStatusFile, "{$message}\n", FILE_APPEND);
    }

    /**
     * @param $mobile
     * @param $countryCode
     * @return \libphonenumber\PhoneNumber|string
     */
    public function getInternationalMobileNo($mobile, $countryCode)
    {
        $phoneNumber = '';
        if (!ToolKit::isEmpty($mobile) && !ToolKit::isEmpty($countryCode)) {
            Yii::setAlias('@libphonenumber', '@app/vendor/borales/yii2-phone-input');

            $phoneUtil = PhoneNumberUtil::getInstance();
            try {
                $phoneNumber = $phoneUtil->parse($mobile, $countryCode);
                if (!$phoneUtil->isValidNumber($phoneNumber)) {
                    Yii::$app->session->setFlash('error', Yii::t('messages', 'Mobile is invalid'));
                }
            } catch (Exception $ex) {
                $phoneNumber = '';
                Yii::$app->appLog->writeLog("Mobile is invalid:" . $ex->getMessage());
                return $phoneNumber;
            }
        }
        return $phoneNumber;
    }

    /*public function getAllLongLates()
    {
        $query = new Query();
        $query->select('id,longLat')
            ->from('User')
            ->where(['not', ['longLat' => null]]);

        return $query->all();
    }*/

    /*public function getLongLatById($id)
    {
        $query = new Query();
        $longLat = $query->select('longLat')
            ->from('User')
            ->Where(['id' => $id])->one();
        return $longLat['longLat'];
    }*/

    /**
     * Get map info window of a User.
     * @return string
     * @throws \yii\db\Exception
     */
    public function getMapInfoWindow()
    {
        $modelUser = $this;
        $info = '';
        $address1 = yii::$app->db->createCommand('SELECT address1 FROM User WHERE id=' . $modelUser->id)->queryOne();
        $fullAddress = rtrim($address1['address1']) . ', ' . rtrim($modelUser->city) . ', ' . Country::find()->where(['countryCode' => $modelUser->countryCode])->one()->countryName . ', ' . rtrim($modelUser->zip);
        //ex: 27 Avenue Pasteur, 14390 Cabourg, France
        $twitter = TwProfile::find()->where(['userId' => $modelUser->id])->one();
        $facebook = FbProfile::find()->where(['userId' => $modelUser->id])->one();
        $googlePlus = GpProfile::find()->where(['userId' => $modelUser->id])->one();
        header('Content-type: text/html; charset=UTF-8');
        $fullAddress = htmlentities($fullAddress, ENT_QUOTES, 'UTF-8');
        $mobile = ToolKit::isEmpty($modelUser->mobile) ? "" : '<br> <span style=\'color: #36c;\'>Phone Number: </span>' . $modelUser->mobile;
        $email = ToolKit::isEmpty($modelUser->email) ? "" : '<br> <span style=\'color: #36c;\'>Email: </span>' . $modelUser->email;
        $twitterUrl = is_null($twitter) ? "" : ' <a style=\'display: inline-block;\' href=' . 'https://twitter.com/intent/user?user_id=' . $twitter->twUserId . '><img border=\"0\" alt=\"Twitter\" src="/images/tw.png"></a>';
        $facebookUrl = is_null($facebook) ? "" : '<a style=\'display: inline-block;\' href=' . 'https://www.facebook.com/profile.php?id=' . $facebook->fbUserId . '><img border=\"0\" alt=\"Facebook\" src="/images/fb.png"></a>';
        $googlePlusUrl = is_null($googlePlus) ? "" : ' <a style="display: inline-block;" href=' . 'https://plus.google.com/' . $googlePlus->gpUserId . '><img border="0" alt="Google Plus" src="/images/gp.png"></a>';
        $info = '<div id = \'inforpop\'><span style=\'color: #36c; font-weight: bold;\'> <a class="updateInfowindow" onclick=loadUpdate("' . Yii::$app->urlManager->createUrl(['people/update']) . '?id=' . $modelUser->id . '?q=' . base64_encode(json_encode(array("reqFrom" => "ADVANCED_SEARCH"))) . '") href="#">' . $modelUser->getName() . '</a></span><br>' .
            $fullAddress . $mobile . $email . '<br>' . $twitterUrl . $facebookUrl . $googlePlusUrl . '</div>';
        return $info;
    }

    /**
     * @param $gender
     * @return string
     */
    public static function getSalutation($gender)
    {
        $msg = '';
        switch ($gender) {
            case self::MALE:
                $msg = Yii::t('messages', 'Mr');
                break;
            case self::FEMALE:
                $msg = Yii::t('messages', 'Mrs');
        }
        return $msg;
    }



    // TODO Need to remove the method If functions are working
    /*public function getPoliticientEmail()
    {
        $emails = yii::$app->db->createCommand('SELECT email FROM User WHERE userType = 1 AND email!="" ORDER By id')->queryAll();
        return $emails;
    }*/

}
