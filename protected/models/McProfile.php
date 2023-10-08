<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "McProfile".
 *
 * @property int $mcUserId
 * @property string|null $accessToken
 * @property string $dc
 * @property string $loginId
 * @property string $loginName
 * @property string $loginEmail
 * @property string $createdAt
 * @property int $userId
 * @property string|null $avatar Profile picture URL
 * @property string|null $apiEndpoint
 * @property string|null $lastSynced Last sysnced date & time
 */
class McProfile extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'McProfile';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mcUserId', 'dc', 'loginId', 'loginName', 'loginEmail', 'createdAt', 'userId'], 'required'],
            [['mcUserId', 'userId'], 'integer', 'integerOnly'=>true],
            [['createdAt', 'lastSynced'], 'safe'],
            [['avatar', 'apiEndpoint'], 'string'],
            [['accessToken'], 'string', 'max' => 150],
            [['dc'], 'string', 'max' => 5],
            [['userId'], 'integer', 'max' => 20],
            [['loginId', 'loginName', 'loginEmail'], 'string', 'max' => 64],
            [['mcUserId','accessToken','dc','loginId','loginName','loginEmail','createdAt','userId'], 'safe','on' => 'search'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'mcUserId' => 'Mc User',
            'accessToken' => 'Access Token',
            'dc' => 'Dc',
            'loginId' => 'Login',
            'loginName' => 'Login Name',
            'loginEmail' => 'Login Email',
            'createdAt' => 'Created At',
            'userId' => 'User',
        ];
    }

    /**
     * {@inheritdoc}
     * @return McProfileQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new McProfileQuery(get_called_class());
    }
}
