<?php
namespace app\commands;


use app\components\AppLogger;
use app\components\ToolKit;
use app\models\App;
use app\models\BulkDelete;
use app\models\SearchCriteria;
use app\models\User;
use app\models\CustomField;
use app\models\CustomValue;
use app\models\CustomType;
use app\models\Country;
use app\models\Keyword;
use app\models\Configuration;
use Symfony\Component\Debug\Debug;
use yii\bootstrap\Html;
use yii\console\Controller;
use yii\console\ExitCode;
use Yii;

/**
 * This command sync longitude and latitude for given street address from google map api
 */
class MassBulkDeleteController extends Controller
{
    //public $appModel = null;
    public $bulkModel = null;
    public $lastRecord = 0;
    public $appId = null;

    public function actionIndex($domain, $recordId)
    {
        set_time_limit(0); //no time limitation
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

        Yii::$app->appLog->writeLog("Process started.Domain:{$domain}");

        $appModel = App::find()->where(['domain' => $domain])->one();
        Yii::$app->appLog->appId = $appModel->appId;
        $this->appId = $appModel->appId;
        if ($appModel->packageTypeId == App::FREEMIUM) {
            Yii::$app->appLog->writeLog('Not available for this package');
            exit;
        }

        Yii::$app->appLog->writeLog("Connecting to database.Dbname:{$appModel->dbName}");
        Yii::$app->toolKit->domain = $appModel->domain;
        if (!Yii::$app->toolKit->changeDbConnection($appModel->dbName, $appModel->host, $appModel->username, $appModel->password)) {
            Yii::$app->appLog->writeLog("Database connection change failed.");
            exit;
        }

        $this->bulkModel = BulkDelete::find()->where(['id' => $recordId])->one();
        if (is_null($this->bulkModel)) {
            Yii::$app->appLog->writeLog("bulk delete record not found. recordId:" . $this->bulkModel->id);
            exit;
        }

        Yii::$app->appLog->writeLog('Processing bulk delete record:' . $this->bulkModel->id);
        $this->bulkDelete($this->bulkModel->searchCriteriaId); // first file is uploaded here, if stopped while uploading, next will run
        Yii::$app->appLog->writeLog('Bulk delete processing completed.');
    }

    public function bulkDelete($criteriaId)
    {
        $statusFile = random_int(0, 10) . Yii::$app->params['fileUpload']['status']['name'];
        $modelCriteria = SearchCriteria::find()->where(['id' => $criteriaId])->one();
        $criteria = SearchCriteria::getCriteria($modelCriteria);
        $users = $criteria->from('User t')->all();
        // $bulkModel = BulkDelete::find()->where(['searchCriteriaId' => $criteriaId])->one();
        $bulkModel = $this->bulkModel;
        $bulkModel->status = BulkDelete::IN_PROGRESS;
        $bulkModel->startAt = date('Y-m-d H:i:s');
        $bulkModel->save(false);

        // Write a header of csv file
        $headersFixed = array('firstName', 'lastName', 'email', 'dateOfBirth', 'gender', 'zip', 'joinedDate', 'userType', 'city', 'fullAddress');

        $customFields = CustomField::findBySql('SELECT id as customFieldId,label From CustomField WHERE enabled=:enabled AND relatedTable=:relatedTable', array(':enabled' => 1, ':relatedTable' => CustomType::CF_PEOPLE))->all();
        $customColumns = array();
        foreach ($customFields as $customField) {
            $customColumns[] = $customField['label'];
        }

        $count = 0;
        foreach ($users as $attributes) {
            $model = User::find()->where(['id' => $attributes['id']])->one();
            Yii::$app->appLog->writeLog("Data reader row: " . json_encode($attributes));
            //displaying only selected columns in csv

            $filterAttr = array();
            foreach ($attributes as $key => $val) {
                if (in_array($key, $headersFixed)) {
                    $filterAttr[$key] = $val;
                }
            }


            $valuesOrder = array();
            foreach ($customColumns as $column) {
                $customField = CustomField::find()->where('LOWER(label)=:label AND enabled=:enabled AND	relatedTable=:relatedTable', [':label' => strtolower($column), ':enabled' => 1, ':relatedTable' => CustomType::CF_PEOPLE])->one();
                if (!ToolKit::isEmpty($customField)) {
                    $customValue = CustomValue::find()->where('customFieldId=:customFieldId AND relatedId=:relatedId', [':customFieldId' => $customField->id, ':relatedId' => $model->id])->one();
                    if (!ToolKit::isEmpty($customValue)) {
                        $valuesOrder[] = $customValue->fieldValue;
                    }
                }
            }

            $count++;

            $id = $model->id;
            if ($model->deleteWithCustomData()) {
                $bulkModel->lastRecord = $id;
                Yii::$app->appLog->writeLog("People deleted. People data:" . json_encode($attributes));
                
                if ($count == 1) {
                    $headers = array_merge(array_keys($filterAttr), $customColumns);
                    $row = array();
                    $User= new User();
                    foreach ($headers as $header) {
                        $row[] = $User->getAttributeLabel($header);
                    }
                    $rowHeader = implode(",", $row);

                    @file_put_contents(Yii::$app->params['fileUpload']['status']['path'] . $statusFile, "{$rowHeader}\n");

                }
                $content = '';
                $row = implode(",", array_merge($filterAttr, $valuesOrder));
                $content .= "{$row}\n";
                @file_put_contents(Yii::$app->params['fileUpload']['status']['path'] . $statusFile, "{$content}\n", FILE_APPEND);


            } else {
                Yii::$app->appLog->writeLog("People delete failed. People data:" . json_encode($attributes));
            }

        }

        $bulkModel->status = BulkDelete::FINISHED;
        $bulkModel->finishedAt = date('Y-m-d H:i:s');
        $bulkModel->save(false);
        $link = Html::a(Yii::t('messages', 'here'), Yii::$app->toolKit->getWebRootUrl() . 'index.php/advanced-search/download-status?status_file='. $statusFile, array('class' => ''));
        $data = array('count' => $bulkModel->totalRecords, 'here' => $link);
        $emails = User::getModeratorEmails();
        if ($emails){
            foreach ($emails AS $record) {
                $this->sendEmail($record, $data, $criteriaId);
            }
        }
    }


    public function sendEmail($email, $data, $criteriaId)
    {
        $modelConfig = Configuration::find()->where(['key' => Configuration::LANGUAGE])->one();
        Yii::$app->language = $modelConfig->value;
        $fromEmail = Yii::$app->params['smtp']['senderEmail'];
        $fromName = Yii::$app->params['smtp']['senderLabel'];
        $subject = Yii::t('messages', "Bulk Delete");
        $content = Yii::t('messages', 'Your bulk delete process has been successfully completed. You deleted {count} records. Click {here} to download the deleted records.');
        foreach ($data as $key => $value) {
            $content = str_replace("{" . $key . "}", $value, $content);
        }
        $message = $this->renderPartial('@app/views/emailTemplates/notificationTemplate', array('content' => $content));
        if (Yii::$app->toolKit->sendEmail(array($email), $subject, $message, null, null, $fromName, $fromEmail)) {
            $logSubject = Yii::$app->toolKit->domain . ' - ' . $subject . ' - ' . $criteriaId;
            $modelCriteria = SearchCriteria::find()->where(['id' => $criteriaId])->one();
            $logMessage = $message . '<br>' . json_encode($modelCriteria->attributes);
            Yii::$app->toolKit->sendEmail(array(Yii::$app->params['smtp']['logsEmail']), $logSubject, $logMessage, null, null, $fromName, $fromEmail); // log email
            Yii::$app->appLog->writeLog("Bulk delete complete email sent successfully to:{$email}");
        }

    }

}

?>
