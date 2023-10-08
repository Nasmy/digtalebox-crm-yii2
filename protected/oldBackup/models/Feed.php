<?php

namespace app\models;

use app\components\LinkedInApi;
use app\components\TwitterApi;
use Yii;
use yii\helpers\Html;

/**
 * This is the model class for table "Feed".
 *
 * @property string $id
 * @property int $keywordId Feed search keyword id
 * @property int $type Feed type whether like, post etc..
 * @property int $network 1 - twitter 2 - facebook
 * @property string $name
 * @property string $twScreenName Twitter screen name
 * @property string $networkUserId
 * @property string $text
 * @property string $dateTime
 * @property string $location
 * @property string $msgDateTime
 * @property string $profImageUrl
 * @property int $userType Type of user. 1 - Politician 2 - Supporter 3 - Prospects 4 - Non support
 */
class Feed extends \yii\db\ActiveRecord
{
    const TW_RETWEET = 1;
    const TW_TWEET = 2;
    const TW_REPLY = 3;

    const FB_PAGE_POST_LIKE = 4;
    const FB_PAGE_POST_COMMENT = 5;

    const LN_PAGE_POST_LIKE = 7;
    const LN_PAGE_POST_COMMENT = 8;

    const GPLUS_POST = 9;

    public $fromDate = null;
    public $toDate = null;
    public $message = null;
    public $emailSubject = null;
    public $emailMessage = null;
    public $fbMessage = null;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Feed';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return array(
            [['id','network','networkUserId','text','dateTime','msgDateTime','profImageUrl'], 'required'],
            [['network'],'number','integerOnly' => true],
            [['id'],'number', 'max' => 50],
            [['networkUserId'], 'string', 'max' => 30],
            [['location'], 'string', 'max' => 100],
            [['message','fbMessage'], 'safe'],

            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            [['id, network, networkUserId, text, dateTime, location, msgDateTime, profImageUrl, userType, fromDate, toDate, keywordId, type'], 'safe']
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'network' => 'Network',
            'networkUserId' => 'Network User',
            'text' => 'Text',
            'dateTime' => 'Date Time',
            'location' => 'Location',
            'msgDateTime' => 'Msg Date Time',
            'profImageUrl' => 'Prof Image Url',
            'fromDate' => Yii::t('messages', 'From Date'),
            'toDate' => Yii::t('messages', 'To Date'),
            'message' => Yii::t('messages', 'Message - Maximum of 140 Characters'),
            'emailSubject' => Yii::t('messages', 'Subject'),
            'emailMessage' => Yii::t('messages', 'Message Body'),
            'fbMessage' => Yii::t('messages', 'Message'),
            'keywordId' => Yii::t('messages', 'Keyword'),
        );
    }

    public function getTimeElapsed($dateTime)
    {
        $modeluser = new User();
        // print_r($modeluser->convertDBTime($dateTime)); die();
        $timediff = (time() - strtotime($modeluser->convertDBTime($dateTime)));
        $message = '';

        if ($timediff < 60) {
            $message = $timediff . ' ' . Yii::t('messages', "Seconds ago");
        } else if ($timediff > 60 && $timediff < 3600) {
            $message = ceil($timediff / 60) . ' ' . Yii::t('messages', "Minutes ago");
        } else if ($timediff > 3600 && $timediff < 86400) {
            $message = ceil($timediff / 3600) . ' ' . Yii::t('messages', "Hours ago");
        } else {
            $message = ceil($timediff / 86400) . ' ' . Yii::t('messages', "Days ago");
        }

        return $message;
    }

    /**
     * Prepare usertype dropdown options
     * @return array $options Dropdown options
     */
    public function getUserTypeOptionsForSearch()
    {
        $options = array(
            '' => Yii::t('messages', "- User Category -"),
            User::SUPPORTER => Yii::t('messages', "Supporter"),
            User::PROSPECT => Yii::t('messages', "Prospect"),
            User::NON_SUPPORTER => Yii::t('messages', "Non Supporter")
        );

        return $options;
    }

    /**
     * Prepare feed type dropdown options
     * @params boolean $emptyOption Whether to include empty option
     * @return array $options Dropdown options
     */
    public static function getTypeOptions($emptyOption = true)
    {
        $options = array(
            '' => Yii::t('messages', "- Feed Type -"),
            self::TW_RETWEET => Yii::t('messages', "Retweet"),
            self::TW_TWEET => Yii::t('messages', "Tweet"),
            self::TW_REPLY => Yii::t('messages', "Reply tweet"),
            self::FB_PAGE_POST_LIKE => Yii::t('messages', "Facebook page post liker"),
            self::FB_PAGE_POST_COMMENT => Yii::t('messages', "Facebook page post commentor"),
            self::LN_PAGE_POST_LIKE => Yii::t('messages', "LinkedIn page post liker"),
            self::LN_PAGE_POST_COMMENT => Yii::t('messages', "LinkedIn page post commentor"),
            self::GPLUS_POST => Yii::t('messages', "Google Plus post"),
        );

        if ($emptyOption) {
            unset($options['']);
        }

        return $options;
    }

    /**
     * Format feed message and return
     * @param Feed $model Feed model instance
     * @return string $text Formatted message
     */
    public function getFormattedFeedText($model)
    {

          $keywords = FeedSearchKeyword::find()->all();
        $replyCount = FeedAction::find()->where(['feedId' =>$model['id']])->andWhere(['actionType'=>FeedAction::REPLY]);
        $likeCount = FeedAction::find()->where(['feedId' =>$model['id']])->andWhere(['actionType'=>FeedAction::LIKE]);
        $retweetCount = FeedAction::find()->where(['feedId' =>$model['id']])->andWhere(['actionType'=>FeedAction::SHARE]);

        $replyLabel = null;
        $likeLabel = null;
        $shareLabel = null;
        $followLabel = null;
        $followLabelGplus = null;
        $connectLabelLn = null;

        $feedMessage = $model['text'];
        $socialIcon = null;
        switch ($model['network']) {
            case TwitterApi::TWITTER:
                //Keyword highlight for twitter messages
                $feedMessage = $this->highLightKeyword($model['text'], $keywords);
                $socialIcon = '<i class="fa fa-twitter"></i>';
                $replyLabel = Yii::t('messages', 'Reply');
                $likeLabel = Yii::t('messages', 'Favourite');
                $shareLabel = Yii::t('messages', 'Retweet');
                $followLabel = Yii::t('messages', 'Follow');

                $feedMessage = Yii::$app->toolKit->convertTextUrlsToLinks($feedMessage);
                $feedMessage = Yii::$app->toolKit->convertHashtagsToLinks($feedMessage, 'TW');
                $feedMessage = Yii::$app->toolKit->convertMentionsToLinks($feedMessage, 'TW');
                break;

          /*  case FacebookApi::FACEBOOK:

                $socialIcon = Yii::app()->fa->getIcon('facebook-square', null, null, 1, 'fb-icon');
                $replyLabel = ($model->type == self::FB_PAGE_POST_LIKE) ? null : Yii::t('messages', 'Comment');
                //$replyLabel = Yii::t('messages', 'Comment');
                $likeLabel = null;
                $retweetCount = null;

                $feedMessage = Yii::app()->toolKit->convertTextUrlsToLinks($feedMessage);
                $feedMessage = Yii::app()->toolKit->convertHashtagsToLinks($feedMessage, 'FB');
                break;*/

            case LinkedInApi::LINKEDIN:
                $socialIcon = '<i class="fa fa-linkedin-square"></i>';
                $connectLabelLn = Yii::t('messages', 'View LinkedIn Profile');

                $feedMessage = Yii::$app->toolKit->convertTextUrlsToLinks($feedMessage);
                break;

      /*      case GooglePlusApi::GOOGLE_PLUS:
                $socialIcon = Yii::app()->fa->getIcon('google-plus', null, null, 1, 'gp-icon');
                $followLabelGplus = Yii::t('messages', 'View G+ Profile');

                $feedMessage = Yii::app()->toolKit->convertHashtagsToLinks($feedMessage, 'GP');
                break;*/
        }

        $text = '<div style="word-wrap:break-word;width:450px">';
        $text .= $socialIcon;
        $text .= '<span class="feed-text">' . $feedMessage . '</span></div>';
        $text .= "<strong>@{$model['name']}</strong>";
        $text .= '<br/>';
//        $text .= '<span class="time-elapsed" title="' . $model['dateTime'] . '">' . $model->getTimeElapsed($model['dateTime']) . '</span>';
        $text .= '<br/>';

        $actions = '';

        if (null != $replyLabel) {
            // Reply/Comment
            $actions .= Html::a($replyLabel . ' ', Yii::$app->urlManager->createUrl('/feed-action/reply', array('feedId' => $model->id, 'network' => $model->network)), array('id' => 'reply', 'class' => 'reply'));
            $actions .= Html::a("{$replyCount}", Yii::$app->urlManager->createUrl('/feed-action/list-actions', array('feedId' => $model->id, 'actionType' => FeedAction::REPLY)), array('id' => 'replies', 'class' => 'replies'));
        }

        if (null != $likeLabel) {
            // Like/Favourite
            $actions .= " / " . Html::a("{$likeLabel}" . ' ', Yii::$app->urlManager->createUrl('/feed-action/like', array('feedId' => $model->id, 'network' => $model->network)), array('id' => 'like', 'class' => 'like'));
            $actions .= Html::a("{$likeCount}", Yii::$app->urlManager->createUrl('/feed-action/list-actions', array('feedId' => $model->id, 'actionType' => FeedAction::LIKE)), array('id' => 'likes', 'class' => 'likes'));
        }

        if (null != $shareLabel) {
            // Share/Retweet
            $actions .= " / " . Html::a("{$shareLabel}" . ' ', Yii::$app->urlManager->createUrl('/FeedAction/Share', array('feedId' => $model->id, 'network' => $model->network)), array('id' => 'share', 'class' => 'share'));
            if (null != $retweetCount) {
                $actions .= Html::a("{$retweetCount}", Yii::$app->urlManager->createUrl('/feed-action/list-actions', array('feedId' => $model->id, 'actionType' => FeedAction::SHARE)), array('id' => 'shares', 'class' => 'shares'));
            }
        }

        if (null != $followLabel) {
            // Twitter follow button
            $faModel = FeedAction::findAll(array('feedId' => $model->id, 'createdBy' => Yii::$app->user->id, 'actionType' => FeedAction::FOLLOW));
            if (empty($faModel)) {
                $actions .= " / " . Html::a("{$followLabel}" . ' ', Yii::$app->urlManager->createUrl('/feed-action/follow', array('feedId' => $model->id, 'network' => $model->network)), array('id' => 'follow', 'class' => 'follow'));
            } else {
                $actions .= " / followed";
            }
        }

        if (null != $followLabelGplus) {
            // Googl+ Profile url
            $gPlusProfUrl = "https://plus.google.com/{$model->networkUserId}/about";
            $actions .= " / " . Html::a("{$followLabelGplus}", $gPlusProfUrl, array('target' => '_blank', 'id' => 'gplus_link'));
        }

        if (null != $connectLabelLn) {
            // LinkedIn Profile url
            $lnProfUrl = "https://www.linkedin.com/profile/view?id={$model->networkUserId}";
            $actions .= " / " . Html::a("{$connectLabelLn}", $lnProfUrl, array('target' => '_blank', 'id' => 'linkedin_link'));
        }

        $actions = trim($actions, " / ");

        return $text . $actions;
    }

    /**
     * Prepare user type display label
     * @param integer $userType User types
     * @return string $label User type display label
     */
    public function getUserTypeLabel($userType)
    {
        switch ($userType) {
            case User::SUPPORTER:
                $labelClass = "badge bg-clicked";
                $label = Yii::t('messages', "Supporter");
                break;

            case User::PROSPECT:
                $labelClass = "badge badge-info";
                $label = Yii::t('messages', "Prospect");
                break;

            case User::NON_SUPPORTER:
                $labelClass = "badge bg-bounced";
                $label = Yii::t('messages', "Non Supporter");
                break;

            case User::POLITICIAN:
                $labelClass = "badge bg-bounced";
                $label = Yii::t('messages', "Client");
                break;

            default :
                $labelClass = "badge bg-bounced";
                $label = Yii::t('messages', "Unknown");
                break;
        }

        $displayLabel = '<span class="' . $labelClass . '">' . $label . '</span>';

        return $displayLabel;
    }

    /**
     * Just update usertype status of all the feeds
     * @param string $networkUserId Id which is used in the network
     * @return integer $userType Usertype supporter,prospect,non supporter
     */
    public function updateFeedUserType($networkUserId, $userType)
    {
        Feed::updateAll(array('userType' => $userType), "networkUserId = '{$networkUserId}' ");
    }

    /**
     * {@inheritdoc}
     * @return FeedQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new FeedQuery(get_called_class());
    }
}
