<?php

namespace app\models;

use app\components\RActiveRecord;
use Yii;

/**
 * This is the model class for table "DigitaleBox_24.LnProfile".
 *
 * @property string $lnUserId Linkedin profile id
 * @property string $accessToken
 * @property int $userId
 * @property string $firstName
 * @property string $lastName
 * @property string $email
 * @property string $pictureUrl Profile picture url
 * @property string $headline Designation
 * @property string $location
 * @property string $createdAt
 * @property string $conFetchTime Time that fetched connection at last
 * @property string $tokenUpdatedAt
 */
class LnProfile extends RActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'LnProfile';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [

            [['lnUserId'], 'string', 'max'=>15],
            [['userId'], 'integer'],
            [['firstName','lastName'], 'string', 'max'=>25],
            [['email'], 'string', 'max'=>45],
            [['tokenUpdateAt'], 'safe'],
            [['lnUserId','accessToken','userId','firstName','lastName','createdAt'], 'required', 'on'=>'signup'],
            [['lnUserId', 'accessToken', 'userId', 'firstName', 'lastName', 'email', 'pictureUrl', 'createdAt'], 'safe', 'on'=>'search']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'lnUserId' => 'Ln User ID',
            'accessToken' => 'Access Token',
            'userId' => 'User ID',
            'firstName' => 'First Name',
            'lastName' => 'Last Name',
            'email' => 'Email',
            'pictureUrl' => 'Picture Url',
            'headline' => 'Headline',
            'location' => 'Location',
            'createdAt' => 'Created At',
            'conFetchTime' => 'Con Fetch Time',
            'tokenUpdatedAt' => 'Token Updated At',
        ];
    }

    /**
     * Check whether LinkedIn token is about to expire
     * @param integer $userId User id
     * @return boolean true if expired false if not expired
     */

    public static function isTokenExpired($userId) {
        $model = LnProfile::find()->where(['userId'=>$userId])->one();
        $tokenUpdatedTs = strtotime($model->tokenUpdatedAt);
        $expireTs = $tokenUpdatedTs + (86400 * Yii::$app->params['linkedIn']['tokenRefreshDays']);

        if(time() > $expireTs) {
            return true;
        }

        return false;
    }

    public static function addProfile($lnProfile, $userId) {
        $modelLnProfile = new LnProfile();
        $modelLnProfile->lnUserId = $lnProfile['id'];
        $modelLnProfile->userId = $userId;
        $modelLnProfile->firstName = $lnProfile['firstName'];
        $modelLnProfile->lastName = $lnProfile['lastName'];
        $modelLnProfile->pictureUrl = @$lnProfile['pictureUrl'];
        $modelLnProfile->headline = @$lnProfile['headline'];
        $modelLnProfile->location = @$lnProfile['location']['name'];
        $modelLnProfile->createdAt = date('Y-m-d H:i:s');

        $isSuccess = false;

        try{
            if ($modelLnProfile->save(false)) {
                 Yii::$app->appLog->writeLog("LinkedIn profile info saved.Userid:{$userId}");
                $isSuccess = true;
            } else {
                 Yii::$app->appLog->writeLog("LinkedIn profile info save failed.Userid:{$userId}");
            }
        } catch (Exception $e) {
             Yii::$app->appLog->writeLog("Error on saving LinkedIn profile info.Error message:{$e->getMessage()}");
        }

        return $isSuccess;
    }

    /**
     * {@inheritdoc}
     * @return LnProfileQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new LnProfileQuery(get_called_class());
    }
}
