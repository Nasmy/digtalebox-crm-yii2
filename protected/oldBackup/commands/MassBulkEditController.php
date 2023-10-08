<?php

namespace app\commands;


use app\components\AppLogger;
use app\components\ToolKit;
use app\models\App;
use app\models\BulkEdit;
use app\models\SearchCriteria;
use app\models\User;
use app\models\CustomField;
use app\models\CustomValue;
use app\models\CustomType;
use app\models\Country;
use app\models\Keyword;
use app\models\Configuration;
use Symfony\Component\Debug\Debug;
use yii\console\Controller;
use yii\console\ExitCode;
use Yii;


class MassBulkEditController extends Controller
{
    public $appModel = null;
    public $bulkModel = null;
    public $lastRecord = 0;
    public $appId=null;
    public function actionIndex($domain, $recordId)
    {
        set_time_limit(0); //no time limitation
        ini_set('max_execution_time', 14400);
        ini_set('memory_limit', '-1'); // no memory limitation
        Yii::$app->appLog->isConsole = true;
        Yii::$app->appLog->username = __class__;
        Yii::$app->appLog->logType = 2;

        if (Yii::$app->toolKit->isProcessExists(__class__)) {
            Yii::$app->appLog->writeLog('Previous process in progress.');
            exit;
        }


        if (ToolKit::isEmpty($domain) || ToolKit::isEmpty($recordId)) {
            Yii::$app->appLog->writeLog('Invalid params submitted');
            exit;
        }

        Yii::$app->appLog->writeLog("Process started.Domain:{$domain},recordId:{$recordId}");

        $appModel = App::find()->where(['domain' => $domain])->one();
        Yii::$app->appLog->appId = $appModel->appId;
        $this->appId = $appModel->appId;
        if ($appModel->packageTypeId == App::FREEMIUM) {
            Yii::$app->appLog->writeLog('Not available for this package');
            exit;
        }

        Yii::$app->appLog->writeLog("Connecting to database.Dbname:{$appModel->dbName}");
        Yii::$app->toolKit->domain = $appModel->domain;

        Yii::$app->toolKit->changeDbConnection($appModel->dbName, $appModel->host, $appModel->username, $appModel->password);
        $this->bulkModel = BulkEdit::find()->where(['id'=>$recordId])->one();
        if (is_null($this->bulkModel)) {
            Yii::$app->appLog->writeLog("bulk edit record not found. recordId:" .$this->bulkModel->id);
            exit;
        }

        Yii::$app->appLog->writeLog('Processing bulk export record:' .$this->bulkModel->id);
        $this->bulkEdit($this->bulkModel->searchCriteriaId); // first file is uploaded here, if stopped while uploading, next will run
        Yii::$app->appLog->writeLog('Bulk edit processing completed.');
    }

    public function bulkEdit($criteriaId)
    {

        $modelCriteria = SearchCriteria::find()->where(['id'=>$criteriaId])->one();

        $criteria = SearchCriteria::getCriteria($modelCriteria);

        // $bulkModel = BulkEdit::find()->where('searchCriteriaId=:searchCriteriaId', [':searchCriteriaId' => $criteriaId])->one();
        $bulkModel = $this->bulkModel;

        if (is_null($bulkModel)) {
            Yii::$app->appLog->writeLog('Bulk Record not found. criteriaId:' . $criteriaId);
            return;
        }

        if (is_null($bulkModel->columnMap) || empty($bulkModel->columnMap)) {
            Yii::$app->appLog->writeLog('Column Map not found. criteriaId:' . $criteriaId);
            return;
        }
        $newKeyWord=array();
        $bulkModel->startAt = date('Y-m-d H:i:s');
        $bulkModel->status = BulkEdit::IN_PROGRESS;
        $bulkModel->save(false);
        $columnMap = json_decode($bulkModel->columnMap, true);
        if(isset($columnMap['keywords'])){
            $newKeyWord = explode(",", $columnMap['keywords']);
        }

        $users = $criteria->from('User t')->all();

            foreach ($users as $user) {
                $model = User::find()->where(['id'=>$user['id']])->one();
                $attributes = User::formatAttributes($columnMap,'BulkEdit');
                $customFields = CustomValue::getCustomData(CustomType::CF_PEOPLE, $model->id, CustomField::ACTION_CREATE, $attributes);
                $model->scenario = 'people';
                $oldKeyword = $model->keywords;
                $model->attributes = $attributes;
                $model->keywords = ToolKit::isEmpty($oldKeyword) ? implode(",", $newKeyWord) : implode(",", array_unique(array_merge(explode(",", $oldKeyword), $newKeyWord), SORT_REGULAR));
                $model->updatedAt = date('Y-m-d H:i:s');
                $customErrors = array();
                foreach ($attributes as $key => $val) {
                    foreach ($customFields as $k => $customField) {
                        if ($key == $customField->fieldName) {
                            $customField->fieldValue = is_array($val) ? implode(",", $val) : $val;
                            $customField->validate();
                            $customErrors[] = $customField->errors;
                            break;
                        }
                    }
                }

                $attributes = array_merge($model->attributes, $attributes);

                try {
                    $valid = $model->validate();
                    if (!$valid) {
                        Yii::$app->appLog->writeLog("Model validated fail, errors:" . @json_encode($model->getErrors()));
                    }

                    //only set custom fields should go inside this. others removed. bcz of overriding
                    $customFields = $bulkModel->getAvailableCustomFields($customFields, array_keys($columnMap));
                    if ($valid && $model->saveWithCustomDataNoValidation($customFields, null, false)) { // no need to validate again, bcz already validated on preview screen
                        $bulkModel->lastRecord = $model->id;
                        $bulkModel->save(false);
                        Yii::$app->appLog->writeLog("Bulk edit people updated. People data:" . @json_encode($attributes));
                    } else {
                        Yii::$app->appLog->writeLog("Bulk edit people update failed. People data:" . @json_encode($attributes));
                    }
                } catch (Exception $e) {
                    Yii::$app->appLog->writeLog("Bulk edit people update failed. Error:{$e->getMessage()}");
                }
            }

        $bulkModel->status = BulkEdit::FINISHED;
        $bulkModel->finishedAt = date('Y-m-d H:i:s');
        $bulkModel->save(false);

        $data = json_decode($bulkModel->columnMap);
        $emails=User::getModeratorEmails();
        if ($emails) {
            foreach ($emails AS $record) {
                $this->sendEmail($data, trim($record));
            }
        }

    }

    private function sendEmail($data, $email)
    {

        $modelConfig = Configuration::find()->where(['key'=>Configuration::LANGUAGE])->one();
        Yii::$app->language = $modelConfig->value;
        $fromEmail = Yii::$app->params['smtp']['senderEmail'];
        $fromName = Yii::$app->params['smtp']['senderLabel'];
        $subject = Yii::t('messages', "Bulk Edit");

        $message = $this->renderPartial('@app/views/emailTemplates/notificationBulkEditTemplate', array(
            'content' => Yii::t('messages', 'Your bulk edit process has been successfully completed. Changes you made are:'), 'data' => $data));
//        print_r($message);die;
        if (Yii::$app->toolKit->sendEmail(array($email), $subject, $message, null, null, $fromName, $fromEmail)) {

            $logSubject = Yii::$app->toolKit->domain . ' - ' . $subject . ' - ' . $this->bulkModel->id;
            $modelCriteria = SearchCriteria::find()->where(['id'=>$this->bulkModel->searchCriteriaId])->one();
            $logMessage = $message . '<br>' . json_encode($modelCriteria->attributes);
            Yii::$app->toolKit->sendEmail(array(Yii::$app->params['smtp']['logsEmail']), $logSubject, $logMessage, null, null, $fromName, $fromEmail); // log email
            Yii::$app->appLog->writeLog("Bulk edit complete email sent successfully to:{$email}");
        }

    }


}

?>