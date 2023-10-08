<?php


namespace app\components\Validations;


use app\components\TwitterApi;
use yii\validators\Validator;
use Yii;

class ValidateMsgLength extends Validator
{

    public $TwMsgLength;

    /**
     * Validate message length
     */
    public function validateAttribute($model, $attribute)
    {
        switch ($model->network) {
            case TwitterApi::TWITTER:
                if (strlen($model->replyMessage) > $this->TwMsgLength) {
                    $this->addError($model, $attribute, Yii::t('messages', 'Exceeded allowed character limit ({limit}).', array('limit' => $this->TwMsgLength)));
                }

                break;

            /* case FacebookApi::FACEBOOK:
                 if (strlen($this->replyMessage) > self::FB_MSG_LENGTH) {
                     $this->addError('replyMessage', Yii::t('messages','Exceeded allowed character limit ({limit}).', array('{limit}'=>self::FB_MSG_LENGTH)));
                 }
                 break;*/
        }

    }
}