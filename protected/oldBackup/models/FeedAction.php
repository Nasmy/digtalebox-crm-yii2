<?php

namespace app\models;

use app\components\TwitterApi;
use app\components\Validations\ValidateMsgLength;
use Yii;

/**
 * This is the model class for table "feedaction".
 *
 * @property int $id
 * @property string $feedId Id from Feed table
 * @property int $actionType 1-reply,2-like/favourite,3-retweet
 * @property string $replyMessage
 * @property int $createdBy
 * @property string $createdAt
 */
class FeedAction extends \yii\db\ActiveRecord
{
    const REPLY = 1;
    const LIKE = 2;
    const SHARE = 3;
    const FOLLOW = 4;

    const FB_MSG_LENGTH = 500;
    const TW_MSG_LENGTH = 140;

    public $network;
    public $name;
    public $profImage;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'FeedAction';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['feedId', 'actionType', 'createdBy', 'createdBy', 'createdAt'], 'required','on'=>'reply,like,follow'],
            [['replyMessage'], 'required','on'=>'reply'],
            [['actionType'], 'number','integerOnly' => true,'on'=>'reply,like,follow'],
            [['feedId'], 'string', 'max'=>50, 'on'=>'reply,like,follow'],
            [['createdBy'], 'string', 'max' => 20,'on'=>'reply,like,follow'],
            [['replyMessage'], ValidateMsgLength::className(),'TwMsgLength'=>self::TW_MSG_LENGTH, 'on'=>'reply'],
            [['id','feedId','actionType','replyMessage','createdBy','createdAt'], 'safe', 'on'=>'search'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'feedId' => 'Feed',
            'actionType' => 'Action Type',
            'replyMessage' => Yii::t('messages','Message'),
            'createdBy' => 'Created By',
            'createdAt' => 'Created At',

        ];
    }
    /**
     * Validate message length
     */
    public function validateMsgLength()
    {
        switch ($this->network) {
            case TwitterApi::TWITTER:
                if (strlen($this->replyMessage) > self::TW_MSG_LENGTH) {
                    $this->addError('replyMessage', Yii::t('messages','Exceeded allowed character limit ({limit}).', ['limit'=>self::TW_MSG_LENGTH]));
                }

                break;

           /* case FacebookApi::FACEBOOK:
                if (strlen($this->replyMessage) > self::FB_MSG_LENGTH) {
                    $this->addError('replyMessage', Yii::t('messages','Exceeded allowed character limit ({limit}).', array('{limit}'=>self::FB_MSG_LENGTH)));
                }
                break;*/
        }
    }


    /**
     * {@inheritdoc}
     * @return FeedactionQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new FeedactionQuery(get_called_class());
    }
}
