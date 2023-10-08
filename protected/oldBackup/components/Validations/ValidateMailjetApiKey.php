<?php


namespace app\components\Validations;


use app\components\MailjetApi;
use yii\validators\Validator;
use Yii;

class ValidateMailjetApiKey extends Validator
{
    public function validateAttribute($model, $attribute)
    {
        if ($model->mailjetUsername != '' && '' != $model->mailjetPassword) {
            $mj = new MailjetApi($model->mailjetUsername, $model->mailjetPassword);
            $res = $mj->getApiKeyId();
            $keyInfo = json_decode($res, true);
            Yii::$app->appLog->writeLog("Response for get key info:{$res}");
            $apiKeyId = @$keyInfo['Data'][0]['ID'];
            if ('' == $apiKeyId) {
                $this->addError($model, "mailjetUsername", Yii::t('messages', 'Invalid MailJet Username'));
                $this->addError($model, "mailjetPassword", Yii::t('messages', 'Invalid MailJet Password'));
            }
        }
    }
}