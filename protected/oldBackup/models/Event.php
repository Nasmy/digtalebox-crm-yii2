<?php

namespace app\models;

use app\components\Validations\ValidateImageDimesions;
use app\components\Validations\ValidateRecipients;
use app\components\WebUser;
use Yii;

/**
 * This is the model class for table "Event".
 *
 * @property int $id
 * @property int $fbId Facebook event id
 * @property string $name
 * @property string $description
 * @property string $imageName
 * @property string $isFbEvent
 * @property string $fbDescription Facebook message
 * @property string $location
 * @property string $locationMapCordinates
 * @property string $address
 * @property string $type 1 - specific date 2 - date range
 * @property string $privacyType 0 - Public 1 - Friends of Guests2 - Invite Only
 * @property string $startDate
 * @property string $startTime Event start time
 * @property string $endDate
 * @property string $endTime
 * @property string $rsvpStatus 0 - RSVP disable 1 - RSVP enable
 * @property string $status 0 - new not moderated1 - accepted2 - rejected
 * @property string $keywords
 * @property string $advanceKeyword Advance  search keyword
 * @property string $priority 0 - none  1 - urgent  2 - high  3 - medium  4 - low
 * @property string $isFbPageEvent Whether fb page event or not, 1- fb page event, 0- no
 * @property int $fbPageEventId Facebook page event id
 * @property string $comments Add additional infor such as rejected reason
 * @property string $createdAt
 * @property string $updatedAt
 * @property int $createdBy
 * @property int $updatedBy
 */
class Event extends \yii\db\ActiveRecord
{
    /**
     * Maximum width and height and size for image in pixels.
     */
    const MIN_IMG_WIDTH = 630;
    const MIN_IMG_HEIGHT = 320;
    const MAX_SIZE = 512000;

    const NONE = 0;
    /**
     * Event priority levels
     */
    const LOW = 1;
    const MEDIUM = 2;
    const HIGH = 3;
    const URGENT = 4;

    /**
     * Event status
     */
    const PENDING = 0;
    const ACCEPTED = 1;
    const REJECTED = 2;

    const DEFAULT_DATE = '0000-00-00';

    const RSVP_DISABLE = '0';
    const RSVP_ENABLE = '1';
    const FACEBOOK_EVENT = '1';
    const NOT_FACEBOOK_EVENT = '0';

    const FACEBOOK = '2';
    const EMAIL = '1';

    public $startTimeStamp = '';
    public $endTimeStamp = '';

    public $userlist = '';
    public $criteriaId = '';
    public $createrName;
    public $firstName;
    public $lastName;

    public $fbPageId;
    public $list;

    /**
     * @param string Event image file
     */
    public $imageFile = '';

    public $emailTemplate = '';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Event';
    }


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'location', 'startDate', 'startTime', 'endTime'], 'required'],
            [['id'], 'number', 'integerOnly' => true],
            [['name'], 'string', 'max' => 45],
            //            [['isFbEvent','isFbPageEvent','type','privacyType','rsvpStatus','status','priority'],'string'],
            [['createdBy', 'updatedBy'], 'integer', 'max' => 20],
            [['startDate', 'endDate'], 'date', 'format' => 'yyyy-MM-dd'],
            [['keywords'], 'match', 'pattern' => '/^[A-Za-z0-9_!@#$%^&*+=?.,]+$/u', 'message' => Yii::t('messages', 'Spaces or given characters are not allowed')],
//
            [['criteriaId'], 'number', 'integerOnly' => true, 'on' => 'compose'],
            [['userlist'], ValidateRecipients::className(), 'on' => 'compose'],
//
//            // Upload event image
            [['imageFile'], 'file', 'skipOnEmpty' => true, 'extensions' => 'jpg,jpeg,gif,png', 'maxSize' => self::MAX_SIZE, 'tooBig' => Yii::t('messages', 'File has to be smaller than 500 Kb')],
            [['imageFile'], ValidateImageDimesions::className(), 'MinImgMinWidth' => self::MIN_IMG_WIDTH, 'MinImgMinHeight' => self::MIN_IMG_HEIGHT],

            [['description', 'locationMapCordinates', 'address', 'endDate', 'startTime', 'endTime', 'keywords', 'advanceKeyword', 'comments', 'createdAt', 'endTimeStamp', 'startTimeStamp', 'fbDescription', 'fbId', 'userlist', 'criteriaId', 'imageName'], 'safe'],
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            [['id', 'name', 'description', 'location', 'locationMapCordinates', 'address', 'type', 'privacyType', 'startDate', 'endDate', 'rsvpStatus', 'status', 'comments', 'createdAt', 'updatedAt', 'createdBy', 'updatedBy', 'imageName'], 'safe', 'on' => 'search'],
            [['emailTemplate'], 'required', 'on' => 'EventReminder'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fbId' => Yii::t('messages', 'Facebook ID'),
            'name' => Yii::t('messages', 'Event Title'),
            'description' => Yii::t('messages', 'Event Description'),
            'isFbEvent' => Yii::t('messages', 'Create on Facebook'),
            'fbDescription' => Yii::t('messages', 'Facebook Message'),
            'location' => Yii::t('messages', 'Location'),
            'locationMapCordinates' => Yii::t('messages', 'Location Map Cordinates'),
            'address' => Yii::t('messages', 'Address'),
            'type' => Yii::t('messages', 'Type'),
            'privacyType' => Yii::t('messages', 'Privacy Type'),
            'startDate' => Yii::t('messages', 'Start Date'),
            'startTime' => Yii::t('messages', 'Start'),
            'endDate' => Yii::t('messages', 'End Date'),
            'endTime' => Yii::t('messages', 'End'),
            'rsvpStatus' => Yii::t('messages', 'RSVP Status'),
            'status' => Yii::t('messages', 'Status'),
            'keywords' => Yii::t('messages', 'Keywords'),
            'advanceKeyword' => Yii::t('messages', 'Advance Keyword'),
            'priority' => Yii::t('messages', 'Priority'),
            'comments' => Yii::t('messages', 'Comments'),
            'date' => Yii::t('messages', 'Date'),
            'time' => Yii::t('messages', 'Time'),
            'createdBy' => Yii::t('messages', 'Event Owner'),
            'criteriaId' => Yii::t('messages', 'Criteria'),
            'userlist' => Yii::t('messages', 'Receivers'),
            'createrName' => Yii::t('messages', 'Created By'),
            'isFbPageEvent' => Yii::t('messages', 'Create on Facebook Page'),
            'imageName' => Yii::t('messages', 'Image Name'),
            'imageFile' => Yii::t('messages', 'Event Image'),
            'list' => Yii::t('messages', 'List'),
            'invited' => Yii::t('messages', 'Invited '),
            'notReplied' => Yii::t('messages', 'Not Replied'),
            'maybe' => Yii::t('messages', 'Maybe').' ',
            'attend' => Yii::t('messages', 'Attend '),
            'notAttend' => Yii::t('messages', 'Not Attend '),
        ];
    }

    public function validateRecipients()
    {
        if ("" == $this->userlist && "" == $this->criteriaId) {
            $this->addError('userlist', Yii::t('messages', 'Please select a Criteria or enter Receipients'));
        }
    }


    /**
     * Validate image width and height
     */
    public function validateImageDimesions()
    {
        if ("" != $this->imageFile) {
            $imagehw = getimagesize($this->imageFile->tempName);
            $imagewidth = $imagehw[0];
            $imageheight = $imagehw[1];

            if ($imagewidth > self::MIN_IMG_WIDTH) {
                $this->addError('imageFile', Yii::t('messages', 'Image width should be {width}', array('width' => self::MIN_IMG_WIDTH . 'px')));
            } else if ($imageheight > self::MIN_IMG_HEIGHT) {
                $this->addError('imageFile', Yii::t('messages', 'Image height should be {height}', array('height' => self::MIN_IMG_HEIGHT . 'px')));
            }
        }
    }

    /**
     *
     * @param string $field
     * @return Ambigous <multitype:, multitype:NULL >
     */
    public function fillDropDown($field)
    {
        $return = array();
        switch ($field) {
            case 'priority':
                $return = array(
                    self::NONE => Yii::t('messages', 'None'),
                    self::LOW => Yii::t('messages', 'Lower'),
                    self::MEDIUM => Yii::t('messages', 'Medium'),
                    self::HIGH => Yii::t('messages', 'Higher'),
                    self::URGENT => Yii::t('messages', 'Urgent'),
                );
                break;

            case 'status':
                $return = array(
                    self::PENDING => Yii::t('messages', 'Pending'),
                    self::ACCEPTED => Yii::t('messages', 'Accepted'),
                    self::REJECTED => Yii::t('messages', 'Rejected'),
                );
                break;

            case 'advanceKeyword':
                $keywords = Keyword::getActiveKeywords();
                $return = isset($keywords[Keyword::KEY_MANUAL]) ? $keywords[Keyword::KEY_MANUAL] : array();
                break;
        }

        return $return;
    }


    /**
     *
     * @return string
     */
    public function eventPriority($priority = null)
    {

        $priority = $priority == null ? $this->priority : $priority;
        $return = '';
        switch ($priority) {
            case self::LOW:
                $return = '#045FB4';
                break;

            case self::MEDIUM:
                $return = '#688A08';
                break;

            case self::HIGH:
                $return = '#B45F04';
                break;

            case self::URGENT:
                $return = '#8A0808';
                break;

            default:
                $return = '#999999';
                break;
        };
        return $return;
    }

    public function saveRecursive()
    {
        $return = false;
        $date = $this->startDate;
        while (strtotime($date) <= strtotime($this->endDate)) {
            $model = new Event;
            $model->attributes = $this->attributes;
            $model->endDate = $date;
            $model->startDate = $date;

            if ($model->save()) {
                //$model->createFacebookEvent();
                $return = true;
                // Add activity
                $params = array(
                    'eventName' => $model->name,
                );
                Yii::$app->toolKit->addActivity(
                    Yii::$app->user->id,
                    Activity::ACT_CRT_NEW_EVENT,
                    Yii::$app->session->getId('teamId'),
                    json_encode($params)
                );
                // End
            }
            $date = date("Y-m-d", strtotime("+1 day", strtotime($date)));
        }
        return $return;
    }


    public function afterFind()
    {
        $this->startTimeStamp = date("Y-m-d H:i", strtotime("{$this->startDate} {$this->startTime}"));
        $this->endTimeStamp = date("Y-m-d H:i", strtotime("{$this->endDate} {$this->endTime}"));

        if ($this->endTime == "00:00:00") {
            $this->endTime = '';
        } else {
            $this->endTime = date("H:i", strtotime($this->endTime));
        }

        if ($this->startTime == "00:00:00") {
            $this->startTime = '';
        } else {
            $this->startTime = date("H:i", strtotime($this->startTime));
        }
    }


    public static function checkEditable($user)
    {
        $isEditable = false;
        if (Yii::$app->user->checkAccess(WebUser::SUPERADMIN_ROLE_NAME)) {
            $isEditable = true;
        } else if (Yii::$app->user->checkAccess(WebUser::POLITICIAN_ROLE_NAME)) {
            $isEditable = true;
        } else if (Yii::$app->user->checkAccess(WebUser::POLITICIAN_ADMIN_ROLE_NAME) && Yii::$app->user->getId() == $user) {
            $isEditable = true;
        } else if (Yii::$app->user->checkAccess(WebUser::TEAM_LEAD_ROLE_NAME) && Yii::$app->user->getId() == $user) {
            $isEditable = true;
        }
        return $isEditable;
    }

    /**
     *
     * @param mixed $returnType
     */
    public function eventDuration($returnType)
    {
        $secondsDiff = strtotime($this->endTimeStamp) - (strtotime($this->startTimeStamp));
        $days = floor($secondsDiff / 3600 / 24) + ($secondsDiff / 3600 / 24 == 0 ? 1 : 0);

        switch ($returnType) {
            case 'allday':
                return $days != 0 ? true : false;
                break;

            case 'eventlist':
                // return $days != 0 ?  Yii::t('messages','Full day') : Yii::t('messages', date("h:iA", strtotime($this->startTimeStamp)));
                return $days != 0 ? Yii::t('messages', 'Full day') : Yii::t('messages', date("H:i", strtotime($this->startTimeStamp)));
                break;

            case 'eventInfo':
                return $days != 0 ?
                    $days < 0 || $days == 1 ?
                        '' :
                        '<div class="event"><small>' . date("Y/m/d ", strtotime($this->startTimeStamp)) . Yii::t('messages', 'until') . date(" Y/m/d", strtotime($this->endTimeStamp)) . '</small></div>' :
                    '<div class="event"><small>' . date("H\hi", strtotime($this->startTimeStamp)) . " " . Yii::t('messages','To') . date(" H\hi", strtotime($this->endTimeStamp)). '</small></div>';
                break;

            case 'eventview':
                /*return $days != 0 ?
                    $days < 0 || $days == 1 ?
                        date("l, F j, Y", strtotime($this->startTimeStamp)) :
                        date("Y/m/d h:i", strtotime($this->startTimeStamp)) . Yii::t('messages', date("A", strtotime($this->startTimeStamp))) . " " . Yii::t('messages', 'until') . date(" Y/m/d h:i", strtotime($this->endTimeStamp)) . Yii::t('messages', date("A", strtotime($this->endTimeStamp))) :
                        date("h:i", strtotime($this->startTimeStamp)) . Yii::t('messages', date("A", strtotime($this->startTimeStamp))) . " " . Yii::t('messages', 'To') . date(" h:i", strtotime($this->endTimeStamp)) . Yii::t('messages', date("A", strtotime($this->endTimeStamp)));*/
                return $days != 0 ?
                    $days < 0 || $days == 1 ?
                        date("l, F j, Y", strtotime($this->startTimeStamp)) :
                        date("Y/m/d h:i", strtotime($this->startTimeStamp)) . Yii::t('messages', date("A", strtotime($this->startTimeStamp))) . " " . Yii::t('messages', 'until') . date(" Y/m/d h:i", strtotime($this->endTimeStamp)) . Yii::t('messages', date("A", strtotime($this->endTimeStamp))) :
                    date("H\hi", strtotime($this->startTime)) . " - ". date(" H\hi", strtotime($this->endTime));
                        // date("h:i", strtotime($this->startTimeStamp)) . Yii::t('messages', date("A", strtotime($this->startTimeStamp))) . " " . Yii::t('messages', 'To') . date(" h:i", strtotime($this->endTimeStamp)) . Yii::t('messages', date("A", strtotime($this->endTimeStamp)));
                break;

            default:
                break;
        }
    }


    /**
     * {@inheritdoc}
     * @return EventQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new EventQuery(get_called_class());
    }
}
