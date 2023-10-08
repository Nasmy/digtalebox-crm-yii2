<?php

namespace app\models;

use app\models\Team;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * This is the model class for table "Activity".
 *
 * @property int $id
 * @property string $dateTime
 * @property int $teamId
 * @property int $userId
 * @property int $activityMsgId Id of the activity. Messages are defined at model
 * @property string $params Json formatted dynamic attibutes.
 */
class Activity extends \yii\db\ActiveRecord
{

    // Activity sections
    const ACT_SECTION_RESOURCE = 1;
    const ACT_SECTION_EVENT = 2;
    const ACT_SECTION_DONATION = 3;
    const ACT_SECTION_MEMBERSHIP = 4;

    // Activity messages
    const ACT_USER_JOINED_TEAM = 1;
    const ACT_USER_LEAVE_TEAM = 2;

    const ACT_CRT_NEW_TEAM = 3;
    const ACT_UPDATE_TEAM = 4;
    const ACT_VIEW_TEAM = 5;
    const ACT_VIEW_TEAM_MEM = 6;
    const ACT_VIEW_GEO = 7;
    const ACT_VIEW_DASHBOARD = 8;
    const ACT_SEARCH_FEED = 9;
    const ACT_VIEW_STAT = 10;
    const ACT_VIEW_INBOX_MSG = 11;
    const ACT_REPLY_MSG = 12;
    const ACT_SENT_NEW_MSG = 13;
    const ACT_DEL_SENT_MSG = 14;
    const ACT_DEL_RCVD_MSG = 15;
    const ACT_VIEW_SENT_MSG = 16;
    const ACT_UPDATE_PROF = 17;
    const ACT_SENT_TW_MSG = 18;
    const ACT_SENT_FB_MSG = 19;
    const ACT_SENT_EMAIL_MSG = 20;
    const ACT_SEARCH_SOCIAL_FEED = 21;
    const ACT_CRT_NEW_CAMPAIGN = 22;
    const ACT_CRT_RESOURCE = 23;
    const ACT_UPDATE_RESOURCE = 24;
    const ACT_VIEW_RESOURCE = 25;
    const ACT_SEARCH_RESOURCE = 26;
    const ACT_CRT_NEW_EVENT = 30;
    const ACT_UPDATE_EVENT = 31;
    const ACT_VIEW_EVENT = 32;
    const ACT_DEL_EVENT = 33;
    const ACT_CALENDAR_EVENT = 34;
    const ACT_CRT_NEW_PEOPLE = 35;
    const ACT_UPDATE_PEOPLE = 36;
    const ACT_VIEW_PEOPLE = 37;
    const ACT_DEL_PEOPLE = 38;
    const ACT_BULK_PEOPLE = 39;
    const ACT_SEARCH_PEOPLE = 40;
    const ACT_EXPORT_PEOPLE = 41;
    const ACT_ADVAN_SEARCH_PEOPLE = 42;
    const ACT_SAVE_ADVAN_SEARCH_PEOPLE = 65;
    const ACT_ADVAN_SEARCH_USER_MAP = 63;
    const ACT_ADVAN_BULK_PEOPLE = 64;
    const ACT_ADVAN_BULK_PEOPLE_CREATE = 60;
    const ACT_DEL_MATCH = 61;
    const ACT_MERGE_PEOPLE = 62;

    const ACT_CRT_DONATION = 43;
    const ACT_UPDATE_DONATION = 44;
    const ACT_VIEW_DONATION = 45;
    const ACT_DEL_DONATION = 46;
    const ACT_MODERATE_DONATION = 47;
    const ACT_DONATION = 48;

    const ACT_SENT_LN_MSG = 49;

    const ACT_CRT_MEMBERSHIP = 50;
    const ACT_UPDATE_MEMBERSHIP = 51;
    const ACT_VIEW_MEMBERSHIP = 52;
    const ACT_DEL_MEMBERSHIP = 53;
    const ACT_MODERATE_MEMBERSHIP = 54;
    const ACT_MEMBERSHIP = 55;

    // Friend Finder
    const ACT_FRI_FINDER_VIEW = 56;
    const ACT_SIGN_UP_FB_VIEW = 57;
    const ACT_SIGN_UP_TW_VIEW = 58;

    // People
    const ACT_PEOPLE_CREATE = 59;


    public $actMessages = array(
        self::ACT_USER_JOINED_TEAM => '{name} joined to the team "{teamName}"',
        self::ACT_USER_LEAVE_TEAM => '{name} left from the team "{teamName}"',
        self::ACT_CRT_NEW_TEAM => '{name} created new team "{teamName}"',
        self::ACT_UPDATE_TEAM => '{name} modified the team "{teamName}"',
        self::ACT_VIEW_TEAM => '{name} viewed team(s)',
        self::ACT_VIEW_TEAM_MEM => '{name} viewed members of the team "{teamName}"',
        self::ACT_VIEW_GEO => '{name} viewed Geographical map of the team "{teamName}"',
        self::ACT_VIEW_DASHBOARD => '{name} viewed dashboard',
        self::ACT_SEARCH_FEED => '{name} searched social feeds',
        self::ACT_VIEW_STAT => '{name} viewed statistics',
        self::ACT_VIEW_INBOX_MSG => '{name} viewed Inbox message',
        self::ACT_REPLY_MSG => '{name} sent reply for a message',
        self::ACT_SENT_NEW_MSG => '{name} sent new message',
        self::ACT_DEL_SENT_MSG => '{name} deleted a sent message',
        self::ACT_DEL_RCVD_MSG => '{name} deleted a received message',
        self::ACT_VIEW_SENT_MSG => '{name} viewed sent messages',
        self::ACT_UPDATE_PROF => '{name} updated the profile',
        self::ACT_SENT_TW_MSG => '{name} sent Twitter message',
        self::ACT_SENT_FB_MSG => '{name} sent Facebook message',
        self::ACT_SENT_EMAIL_MSG => '{name} sent an Email',
        self::ACT_SEARCH_SOCIAL_FEED => '{name} search social feed',
        self::ACT_CRT_NEW_CAMPAIGN => '{name} created new campaign',
        self::ACT_CRT_RESOURCE => '{name} added new resource {title}',
        self::ACT_UPDATE_RESOURCE => '{name} updated resource {title}',
        self::ACT_VIEW_RESOURCE => '{name} viewed resource {title}',
        self::ACT_SEARCH_RESOURCE => '{name} searched resources',
        self::ACT_CRT_NEW_EVENT => '{name} created new event {eventName}',
        self::ACT_UPDATE_EVENT => '{name} updated event {eventName}',
        self::ACT_VIEW_EVENT => '{name} viewed event {eventName}',
        self::ACT_DEL_EVENT => '{name} deleted event {eventName}',
        self::ACT_CALENDAR_EVENT => '{name} viewed event calendar',
        self::ACT_CRT_NEW_PEOPLE => '{name} created new contact {person}',
        self::ACT_UPDATE_PEOPLE => '{name} updated contact {person}',
        self::ACT_VIEW_PEOPLE => '{name} viewed contact {person}',
        self::ACT_DEL_PEOPLE => '{name} deleted a contact {person}',
        self::ACT_DEL_MATCH => '{name} deleted a contact {person}',
        self::ACT_MERGE_PEOPLE => '{name} merged a contact {person}',
        self::ACT_BULK_PEOPLE => '{name} upload people from file {fileName}',
        self::ACT_SEARCH_PEOPLE => '{name} viewed search people',
        self::ACT_EXPORT_PEOPLE => '{name} export advanced search people',
        self::ACT_ADVAN_SEARCH_PEOPLE => '{name} viewed advanced search people',
        self::ACT_SAVE_ADVAN_SEARCH_PEOPLE => '{name} saved advanced search people {title}',
        self::ACT_CRT_DONATION => '{name} a created a new donation page {title}',
        self::ACT_UPDATE_DONATION => '{name} updated donation page {title}',
        self::ACT_VIEW_DONATION => '{name} viewed donation page {title}',
        self::ACT_DEL_DONATION => '{name} deleted donation page {title}',
        self::ACT_MODERATE_DONATION => '{name} published donation page {title}',
        self::ACT_DONATION => '{name} viewed donation list',
        self::ACT_SENT_LN_MSG => '{name} sent LinkedIn message',
        self::ACT_ADVAN_SEARCH_USER_MAP => '{name} viewed advanced search user map',
        self::ACT_ADVAN_BULK_PEOPLE => '{name} viewed advanced bulk insert',
        self::ACT_ADVAN_BULK_PEOPLE_CREATE => '{name} created advanced bulk insert',
        self::ACT_CRT_MEMBERSHIP => '{name} a created a new membership page {title}',
        self::ACT_UPDATE_MEMBERSHIP => '{name} updated membership page {title}',
        self::ACT_VIEW_MEMBERSHIP => '{name} viewed membership page {title}',
        self::ACT_DEL_MEMBERSHIP => '{name} deleted membership page {title}',
        self::ACT_MODERATE_MEMBERSHIP => '{name} published membership page {title}',
        self::ACT_MEMBERSHIP => '{name} viewed membership list',
        self::ACT_FRI_FINDER_VIEW => '{name} viewed friend finder',
        self::ACT_SIGN_UP_FB_VIEW => '{name} viewed facebook invitation',
        self::ACT_SIGN_UP_TW_VIEW => '{name} viewed twitter invitation',
        self::ACT_PEOPLE_CREATE => '{name} Create People'
    );

    // Name of the user
    public $name;

    // First name of the user
    public $firstName;

    // User profile image
    public $profImage;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Activity';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return [
            [['dateTime', 'teamId', 'activityMsgId', 'params', 'section'], 'required'],
            [['teamId'], 'numerical', 'integerOnly' => true],
            [['userId'], 'length', 'max' => 20],
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            [['id', 'dateTime', 'teamId', 'userId', 'activityMsgId', 'params'], 'safe', 'on' => 'search'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'dateTime' => 'Date Time',
            'teamId' => 'Team ID',
            'userId' => 'User ID',
            'activityMsgId' => 'Activity Msg ID',
            'params' => 'Params',
        ];
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * Typical usecase:
     * - Initialize the model fields with values from filter form.
     * - Execute this method to get CActiveDataProvider instance which will filter
     * models according to data in model fields.
     * - Pass data provider to GridView, CListView or any similar widget.
     *
     * @return dataProvider the data provider that can return the models
     * based on the search/filter conditions.
     */
    public function search()
    {
        // @todo Please modify the following code to remove attributes that should not be searched.

        $query = Activity::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 10],
            'sort' => ['defaultOrder' => ['dateTime' => SORT_DESC]],

        ]);

        $query->filterWhere([
            'id' => $this->id,
            'dateTime' => $this->dateTime,
            'teamId' => $this->teamId,
            'userId' => $this->userId,
            'activityMsgId' => $this->activityMsgId,
            'params' => $this->params,
        ]);

        return $dataProvider;

    }

    /**
     * Prepare activity message.
     * @param Activity $activityModel Activity model instance
     * @return string $message Activity message
     */
    public function getActivityMessage($activityModel)
    {

        // Preapare team name
        $teamModel = Team::findOne($activityModel->teamId);

        $teamName = is_null($teamModel) != true ? $teamModel->name : '';
        $userInfo = User::find()->where(['id' => $activityModel->userId])->one();
        $userName = "-";

        if (!is_null($userInfo)) {
            $userName = $userInfo->firstName . " " . $userInfo->lastName;
        }

        // End

        // Prepare each section related messages
        $params = json_decode($activityModel->params);

        $section = isset($params->section) ? $params->section : '';

        $person = '';
        $resTitle = '';
        $eventName = '';
        if (!empty($params->person)) {
            $person = $params->person;
        }

        switch ($section) {
            case Activity::ACT_SECTION_RESOURCE:
                $resTitle = Html::a($params->title, Url::to($params->link), ['title' => Yii::t('messages', 'View Resource')]);
                break;

            case Activity::ACT_SECTION_EVENT:
                $eventName = Html::a($params->eventName, Url::to($params->link), ['title' => Yii::t('messages', 'View Event')]);
                break;

            case Activity::ACT_SECTION_DONATION:
                $resTitle = Html::a($params->title, Url::to($params->link), ['title' => Yii::t('messages', 'View Donation')]);
                break;

            case Activity::ACT_SECTION_MEMBERSHIP:
                $resTitle = Html::a($params->title, Url::to($params->link), ['title' => Yii::t('messages', 'View Membership')]);
                break;
            default:
                break;
        }

        // End
        $message = Yii::t('activityMessages', $this->actMessages[$activityModel->activityMsgId], array(
            'name' => '<strong>' . $userName . '</strong>',
            'teamName' => $teamName,
            'eventName' => $eventName,
            'title' => $resTitle,
            'person' => $person
        ));

        return $message;
    }


    /**
     * {@inheritdoc}
     * @return ActivityQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ActivityQuery(get_called_class());
    }
}
