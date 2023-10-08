<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "UserMatchMain".
 *
 * @property int $userId
 * @property string $createdAt
 * @property int $status 0-pending,1-matched,2-rejected
 */
class UserMatchMain extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'UserMatchMain';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['userId', 'createdAt', 'status'], 'required'],
            [['userId', 'status'], 'integer'],
            [['createdAt'], 'safe'],
            [['userId'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'userId' => 'User ID',
            'createdAt' => 'Created At',
            'status' => 'Status',
        ];
    }
}
