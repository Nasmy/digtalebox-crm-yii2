<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "FbProfile".
 *
 * @property string $fbUserId
 * @property string $userId
 * @property string $name
 * @property string $firstName
 * @property string $middleName
 * @property string $lastName
 * @property string $gender
 * @property string $username
 * @property string $email
 * @property string $location
 * @property string $relationshipStatus
 * @property string $political
 * @property string $accessToken
 * @property string $profileImageUrl
 * @property int $followingCount
 * @property int $followerCount
 * @property int $friendsCount
 * @property int $delStatus
 * @property string $nextQuery
 * @property string $createdDate
 * @property string $lastUpdateDate
 */
class FbProfile extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'FbProfile';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['fbUserId', 'userId'], 'required'],
            [['fbUserId', 'userId', 'followingCount', 'followerCount', 'friendsCount', 'delStatus'], 'integer'],
            [['accessToken', 'profileImageUrl', 'nextQuery'], 'string'],
            [['createdDate', 'lastUpdateDate'], 'safe'],
            [['name'], 'string', 'max' => 200],
            [['firstName', 'middleName', 'lastName', 'username'], 'string', 'max' => 30],
            [['gender'], 'string', 'max' => 10],
            [['email'], 'string', 'max' => 60],
            [['location', 'political'], 'string', 'max' => 50],
            [['relationshipStatus'], 'string', 'max' => 15],
            [['fbUserId'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'fbUserId' => Yii::t('app', 'Fb User ID'),
            'userId' => Yii::t('app', 'User ID'),
            'name' => Yii::t('app', 'Name'),
            'firstName' => Yii::t('app', 'First Name'),
            'middleName' => Yii::t('app', 'Middle Name'),
            'lastName' => Yii::t('app', 'Last Name'),
            'gender' => Yii::t('app', 'Gender'),
            'username' => Yii::t('app', 'Username'),
            'email' => Yii::t('app', 'Email'),
            'location' => Yii::t('app', 'Location'),
            'relationshipStatus' => Yii::t('app', 'Relationship Status'),
            'political' => Yii::t('app', 'Political'),
            'accessToken' => Yii::t('app', 'Access Token'),
            'profileImageUrl' => Yii::t('app', 'Profile Image Url'),
            'followingCount' => Yii::t('app', 'Following Count'),
            'followerCount' => Yii::t('app', 'Follower Count'),
            'friendsCount' => Yii::t('app', 'Friends Count'),
            'delStatus' => Yii::t('app', 'Del Status'),
            'nextQuery' => Yii::t('app', 'Next Query'),
            'createdDate' => Yii::t('app', 'Created Date'),
            'lastUpdateDate' => Yii::t('app', 'Last Update Date'),
        ];
    }

    /**
     * {@inheritdoc}
     * @return FbProfileQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new FbProfileQuery(get_called_class());
    }
}
