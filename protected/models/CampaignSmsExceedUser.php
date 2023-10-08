<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "CampaignSmsExceedUser".
 *
 * @property int $id
 * @property int $campaignId
 * @property int $userId
 * @property string $smsId
 * @property string $createdAt
 */
class CampaignSmsExceedUser extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'CampaignSmsExceedUser';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['campaignId', 'userId', 'smsId', 'createdAt'], 'required'],
            [['campaignId', 'userId'], 'integer'],
            [['createdAt'], 'safe'],
            [['smsId'], 'string', 'max' => 40],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'campaignId' => 'Campaign ID',
            'userId' => 'User ID',
            'smsId' => 'Sms ID',
            'createdAt' => 'Created At',
        ];
    }
}
