<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "bitlyprofile".
 *
 * @property string $blyUserId
 * @property float $userId
 * @property string|null $login
 * @property string $fullName
 * @property string|null $accessToken Access token of the authenticated user
 */
class BitlyProfile extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'BitlyProfile';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['blyUserId','userId'], 'required'],
            [['userId'], 'number'],
            [['blyUserId'], 'string', 'max' => 150],
             [['fullName'], 'string', 'max' => 60],
             [['blyUserId','userId','login','fullName'], 'safe','on' => 'search'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'blyUserId' => 'Bly User',
            'userId' => 'User',
            'login' => 'Login',
            'fullName' => 'Full Name',
            'accessToken' => 'Access Token',
        ];
    }

    /**
     * {@inheritdoc}
     * @return BitlyprofileQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new BitlyprofileQuery(get_called_class());
    }
}
