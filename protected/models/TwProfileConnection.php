<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "twprofileconnection".
 *
 * @property int $connectionType 1 - friend, 2- follower, 3-following, 4 - Unfollower
 * @property int $parentTwUserId Twitter profile id of this connection belong
 * @property int $childTwUserId Twitter profile id of this connection
 * @property string|null $createdAt
 */
class TwProfileConnection extends \yii\db\ActiveRecord
{
    /**
     * Twitter profile connection types
     */
    const CON_TYPE_FRIEND = 1;
    const CON_TYPE_FOLLOWER = 2;
    const CON_TYPE_FOLLOWING = 3;
    const CON_TYPE_UNFOLLOWER = 4;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'TwProfileConnection';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['connectionType', 'parentTwUserId', 'childTwUserId'], 'required'],
            [['connectionType'], 'number','integerOnly' => true],
            [['parentTwUserId', 'childTwUserId'], 'number','max' => 30],
            [['connectionType', 'parentTwUserId', 'childTwUserId'],'safe','on' => 'search'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'connectionType' => 'Connection Type',
            'parentTwUserId' => 'Parent Tw User',
            'childTwUserId' => 'Child Tw User',
        ];
    }

    /**
     * {@inheritdoc}
     * @return TwprofileconnectionQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TwprofileconnectionQuery(get_called_class());
    }
}
