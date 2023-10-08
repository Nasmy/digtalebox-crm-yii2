<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "lnprofileconnection".
 *
 * @property int $connectionType 1 - connect, 2- unconnect
 * @property string $parentLnUserId LinkedIn profile id of this connection belong
 * @property string $childLnUserId LinkedIn profile id of this connection
 * @property string $joinedDate
 */
class LnProfileConnection extends \yii\db\ActiveRecord
{
    /**
     * LinkedIn profile connection types
     */
    const CON_TYPE_CONNECTED = 1;
    const CON_TYPE_UNCONNECTED = 2;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'LnProfileConnection';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['connectionType', 'parentLnUserId', 'childLnUserId','joinedDate'], 'required'],
            [['connectionType'], 'integer','integerOnly' => true],
            [['joinedDate'], 'safe'],
            [['parentLnUserId', 'childLnUserId'], 'string', 'max' => 15],
            [['connectionType','parentLnUserId','childLnUserId','joinedDate'], 'safe','on' => 'search'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'connectionType' => 'Connection Type',
            'parentLnUserId' => 'Parent Ln User',
            'childLnUserId' => 'Child Ln User',
            'joinedDate' => 'Joined Date',
        ];
    }

    /**
     * {@inheritdoc}
     * @return LnprofileconnectionQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new LnprofileconnectionQuery(get_called_class());
    }
}
