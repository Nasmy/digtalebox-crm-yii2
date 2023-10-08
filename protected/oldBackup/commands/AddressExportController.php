<?php


namespace app\commands;

use app\components\AppLogger;
use app\components\ToolKit;
use app\models\App;
use app\models\BulkExport;
use app\models\SearchCriteria;
use app\models\User;
use app\models\Configuration;
use Symfony\Component\Debug\Debug;
use yii\console\Controller;
use Yii;

class AddressExportController extends Controller
{
//    public $appModel = null;
    public $bulkModel = null;
    public $lastRecord = 0;
    public $appId;
    public $config;

    // Batch size for users retrieve from database
    private $dbBatchSize = 100;

    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     * @return int Exit code
     */
    public function actionIndex($domain, $recordId)
    {
        set_time_limit(0); //no time limitation
        ini_set('memory_limit', '-1'); // no memory limitation
        Yii::$app->appLog->isConsole = true;
        Yii::$app->appLog->username = __class__;
        Yii::$app->appLog->logType = 2;


        if (Yii::$app->toolKit->isProcessExists(__class__)) {
            Yii::$app->appLog->writeLog('Previous process in progress.', DEBUG);
            exit;
        }

        if (ToolKit::isEmpty($domain) || ToolKit::isEmpty($recordId)) {
            Yii::$app->appLog->writeLog('Invalid params submitted');
            exit;
        }

        Yii::$app->appLog->writeLog("Process started.Domain:{$domain}");

        $appModel = App::find()->where(['domain' => $domain])->one();
        Yii::$app->appLog->appId = $appModel['appId'];
        $this->appId = $appModel['appId'];
        if ($appModel['packageTypeId'] == App::FREEMIUM) {
            Yii::$app->appLog->writeLog('Not available for this package');
            exit;
        }
        Yii::$app->appLog->writeLog("Connecting to database.Dbname:{$appModel['dbName']}");
        Yii::$app->toolKit->domain = $appModel['domain'];
        Yii::$app->toolKit->changeDbConnection($appModel['dbName'], $appModel['host'], $appModel['username'], $appModel['password']);
        $this->config = Configuration::getConfigurations();
        Yii::$app->language = $this->config['LANGUAGE'];
        $exportRecord = Yii::$app->getDb()->createCommand("SELECT * FROM BulkExport WHERE id = {$recordId}")->queryOne();
        if (is_null($exportRecord)) {
            Yii::$app->appLog->writeLog("address export record not found. recordId:" . $exportRecord['id']);
            exit;
        }
        Yii::$app->appLog->writeLog('Processing address export record:' . $exportRecord['id']);
        $this->bulkExport($exportRecord['searchCriteriaId'], $exportRecord['id']); // first file is uploaded here, if stopped while uploading, next will run
        Yii::$app->appLog->writeLog('Address export processing completed.');
    }

    public function bulkExport($criteriaId, $id)
    {
        $statusFile = mt_rand() . Yii::$app->params['fileUpload']['export']['address'];
        $startIndex = 0;
        $modelCriteria = Yii::$app->getDb()->createCommand("SELECT * FROM SearchCriteria WHERE id = {$criteriaId}")->queryOne();
        $modelCriteria = (object)$modelCriteria;
        $criteria = SearchCriteria::getCriteria($modelCriteria);
        $content = null;
        $rowHeader = null;
        Yii::$app->getDb()->createCommand("Update BulkExport SET startAt = '" . date('Y-m-d H:i:s') . "', status = " . BulkExport::IN_PROGRESS . " WHERE searchCriteriaId = {$criteriaId} and id = {$id}")->execute();

        do {
            $criteria->limit = $this->dbBatchSize;
            $criteria->offset = $startIndex;
            $users = $criteria->from('User t')->all();

            // Write a header of csv file
            $headersFixed = array(
                'salutation',
                'firstName',
                'lastName',
                'address1',
                'zip',
                'city',
                'country'
            );
            $country = array();
            $count = 0;
            foreach ($users as $model) {
                if (!empty($model['countryCode'])) {
                    $country = Yii::$app->getDb()->createCommand("SELECT * FROM Country WHERE countryCode = '{$model['countryCode']}'")->queryOne();
                }
                $attributes = $model;
                //displaying only selected columns in csv
                $filterAttr = array();
                foreach ($attributes as $key => $val) {
                    if (in_array($key, $headersFixed)) {
                        $filterAttr[$key] = $val;
                    }
                }
                $filterAttr['salutation'] = (User::FEMALE == $model['gender']) ? Yii::t('messages',
                    'Madam') : ((User::MALE == $model['gender']) ? Yii::t('messages', 'Mr') : "");

                $filterAttr['country'] = '';
                if ($country) {
                    $filterAttr['country'] = $country['countryName'];
                }
                if (!ToolKit::isEmpty($filterAttr['country']) && !ToolKit::isEmpty($filterAttr['address1'])
                    && !ToolKit::isEmpty($filterAttr['city']) && !ToolKit::isEmpty($filterAttr['zip'])) {
                    $fullAddress = $filterAttr['address1'] . ', ' . $filterAttr['zip'] . ' ' . $filterAttr['city'] . ', ' . $filterAttr['country'];

                    if (preg_match('/^([0-9a-zA-Z\/]+, )?[^-\s][^,]+,( [a-zA-z ]+,)? [0-9]+,? [^,]+,( [a-zA-Z]+)*$/'
                        , $fullAddress)) {
                        $address = array_map("utf8_decode", [
                            '"' . $filterAttr['salutation'] . '"',
                            '"' . $filterAttr['firstName'] . '"',
                            '"' . $filterAttr['lastName'] . '"',
                            '"' . $filterAttr['address1'] . '"',
                            '"' . $filterAttr['zip'] . '"',
                            '"' . $filterAttr['city'] . '"',
                            '"' . $filterAttr['country'] . '"'
                        ]);

                        $id = $model['id'];
                        Yii::$app->getDb()->createCommand("Update BulkExport SET lastRecord = " . $id . " WHERE searchCriteriaId = {$criteriaId}")->execute();
                        $row = implode(",", $address);
                        $content .= "{$row}\n";

                        $count++;
                    }
                }
            }
            $startIndex += $this->dbBatchSize;
        } while (!empty($users));
        $headers = array_map("utf8_decode", array(
            Yii::t('messages', 'Salutation'),
            Yii::t('messages', 'First Name'),
            Yii::t('messages', 'Last Name'),
            Yii::t('messages', 'Street Address'),
            Yii::t('messages', 'Zip'),
            Yii::t('messages', 'City'),
            Yii::t('messages', 'Country')
        ));
        $rowHeader = implode(",", $headers);
        @file_put_contents(Yii::$app->params['fileUpload']['export']['path'] . $statusFile, "{$rowHeader}\n");
        @file_put_contents(Yii::$app->params['fileUpload']['export']['path'] . $statusFile, "{$content}\n", FILE_APPEND);
        $statusFileQuery = "";
        if ($statusFile) {
            $statusFileQuery = ",statusFile ='" . $statusFile . "'";
        }
        Yii::$app->getDb()->createCommand("Update BulkExport SET status = " . BulkExport::FINISHED . ",finishedAt = '" . date('Y-m-d H:i:s') . "' " . $statusFileQuery . " WHERE searchCriteriaId = {$criteriaId}")->execute();


    }
}
