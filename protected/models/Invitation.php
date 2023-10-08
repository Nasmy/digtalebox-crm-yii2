<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "invitation".
 *
 * @property int $id
 * @property string $email
 * @property string|null $code Invitation code
 * @property int $isJoined Whether invitee has joined
 * @property string $createdAt
 * @property int $createdById User id of the person who created
 * @property int|null $type Joined via, Email invitaion -1, Social invitation -2
 */
class Invitation extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Invitation';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['email','code','isJoined','createdAt'], 'required'],
            [['isJoined', 'createdById'], 'integer','integerOnly' => true],
            [['email'], 'email'],
            [['email'], 'unique', 'message'=>Yii::t('messages', 'You have already sent an invitation to this email.')],
            [['email'], 'string', 'max' => 64],
            [['code'], 'string', 'max' => 15],
            [['id','email','code','isJoined','createdAt'],'safe', 'on'=>'search'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'email' => 'Email',
            'code' => 'Code',
            'isJoined' => 'Is Joined',
            'createdAt' => 'Created At',
            'createdById' => 'Created By ID',
            'type' => 'Type',
        ];
    }

    /**
     * Returns the code for share on social networks
     * @return string $code Code to be shared on networks.
     */
    public function getSocialCode()
    {
        Yii::$app->toolKit->setResourceInfo();
        $code = Yii::$app->toolKit->appId . '|' . Yii::$app->user->id;
        return $code;
    }

    /**
     * {@inheritdoc}
     * @return InvitationQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new InvitationQuery(get_called_class());
    }
}
