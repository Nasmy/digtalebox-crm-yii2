<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "UserMatchSub".
 *
 * @property int $mainUserId User id of the UserMatchMain
 * @property int $subUserId user id of the matching profile
 * @property string $networkUserId Id of the particular social network
 * @property int $networkType Social network type. 1-Twitter, 2- Facebook, 3-LinkedIn, 4-G+
 * @property string $profileImageUrl
 * @property int $status 0-pending, 1-accepted, 2-rejected
 * @property string $createdAt
 */
class UserMatchSub extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'UserMatchSub';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mainUserId', 'subUserId', 'networkUserId', 'networkType', 'profileImageUrl', 'status', 'createdAt'], 'required'],
            [['mainUserId', 'subUserId', 'networkType', 'status'], 'integer'],
            [['profileImageUrl'], 'string'],
            [['createdAt'], 'safe'],
            [['networkUserId'], 'string', 'max' => 20],
            [['mainUserId', 'subUserId'], 'unique', 'targetAttribute' => ['mainUserId', 'subUserId']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'mainUserId' => 'Main User ID',
            'subUserId' => 'Sub User ID',
            'networkUserId' => 'Network User ID',
            'networkType' => 'Network Type',
            'profileImageUrl' => 'Profile Image Url',
            'status' => 'Status',
            'createdAt' => 'Created At',
        ];
    }
}
