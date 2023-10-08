<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "GpProfile".
 *
 * @property string $gpUserId
 * @property int $userId
 * @property string $familyName
 * @property string $givenName
 * @property string $email
 * @property string $imageUrl
 * @property string $accessToken
 * @property string $createdAt
 * @property string $gender
 */
class GpProfile extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'GpProfile';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['gpUserId', 'userId', 'familyName', 'givenName', 'email', 'imageUrl', 'accessToken', 'createdAt', 'gender'], 'required'],
            [['userId'], 'integer'],
            [['imageUrl', 'accessToken'], 'string'],
            [['createdAt'], 'safe'],
            [['gpUserId'], 'string', 'max' => 30],
            [['familyName', 'givenName'], 'string', 'max' => 20],
            [['email'], 'string', 'max' => 64],
            [['gender'], 'string', 'max' => 6],
            [['gpUserId'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'gpUserId' => 'Gp User ID',
            'userId' => 'User ID',
            'familyName' => 'Family Name',
            'givenName' => 'Given Name',
            'email' => 'Email',
            'imageUrl' => 'Image Url',
            'accessToken' => 'Access Token',
            'createdAt' => 'Created At',
            'gender' => 'Gender',
        ];
    }
}
