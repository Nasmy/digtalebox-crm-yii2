<?php

namespace app\models;

use app\components\RActiveRecord;
use Yii;

/**
 * This is the model class for table "TwProfile".
 *
 * @property string $twUserId
 * @property string $userId
 * @property string $name
 * @property string $screenName
 * @property string $authToken
 * @property string $authTokenSecret
 * @property string $location
 * @property string $profileImageUrl
 * @property int $followingCount
 * @property int $followerCount
 * @property int $friendsCount
 * @property string $createdAt
 * @property string $followerIdsCursor
 */
class TwProfile extends RActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'TwProfile';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['twUserId', 'userId'], 'required'],
            [['followingCount', 'followerCount', 'friendsCount'], 'integer'],
            [['authToken', 'authTokenSecret', 'location', 'profileImageUrl'], 'string'],
            //[['userId'], 'integer', 'max' => 25],
            [['name'], 'string', 'max' => 25],
            [['authToken', 'authTokenSecret', 'location', 'profileImageUrl'], 'safe'],
            [['screenName', 'followerIdsCursor'], 'string', 'max' => 30],
            [['twUserId'], 'unique'],
            [['twUserId, userId, name, screenName, authToken, authTokenSecret, location, profileImageUrlHttps, followingCount, followerCount, friendsCount, createdAt'], 'safe', 'on'=>'search'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'twUserId' => Yii::t('app', 'Tw User ID'),
            'userId' => Yii::t('app', 'User ID'),
            'name' => Yii::t('app', 'Name'),
            'screenName' => Yii::t('app', 'Screen Name'),
            'authToken' => Yii::t('app', 'Auth Token'),
            'authTokenSecret' => Yii::t('app', 'Auth Token Secret'),
            'location' => Yii::t('app', 'Location'),
            'profileImageUrl' => Yii::t('app', 'Profile Image Url'),
            'followingCount' => Yii::t('app', 'Following Count'),
            'followerCount' => Yii::t('app', 'Follower Count'),
            'friendsCount' => Yii::t('app', 'Friends Count'),
            'createdAt' => Yii::t('app', 'Created At'),
            'followerIdsCursor' => Yii::t('app', 'Follower Ids Cursor'),
        ];
    }

    /**
     * {@inheritdoc}
     * @return TwProfileQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TwProfileQuery(get_called_class());
    }
}
