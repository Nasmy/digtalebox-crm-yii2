<?php

namespace app\commands;


use app\components\AppLogger;
use app\components\ToolKit;
use app\models\App;
use app\models\BulkExport;
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

/**
 * This command is used to generate bulk export files
 */
class MassBulkExportController extends Controller
{
    private $workerCmdName = 'mass-bulk-export';
    public $appModel = null;
    public $bulkModel = null;
    public $lastRecord = 0;
    private $config = array();
    // Application id
    private $appId = null;
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

        ToolKit::changeDbConnection($appModel->dbName, $appModel->host, $appModel->username, $appModel->password);
        $connection = Yii::$app->getDb();
        $exportRecord = $connection->createCommand("SELECT * FROM BulkExport WHERE id = {$recordId}")->queryOne();
        if (is_null($exportRecord)) {
            Yii::$app->appLog->writeLog("bulk export record not found. recordId:" . $exportRecord['id']);
            exit;
        }
        Yii::$app->appLog->writeLog('Processing bulk export record:' . $exportRecord['id']);
        $this->bulkExport($exportRecord['searchCriteriaId'], $exportRecord['id']); // first file is uploaded here, if stopped while uploading, next will run
        Yii::$app->appLog->writeLog('Bulk export processing completed.');

    }

    public function bulkExport($criteriaId, $id)
    {

        $statusFile = mt_rand() . Yii::$app->params['fileUpload']['export']['name'];
        $startIndex = 0;
        $connection = Yii::$app->getDb();
        $modelCriteria = $connection->createCommand("SELECT * FROM SearchCriteria WHERE id = {$criteriaId}")->queryOne();
        $modelCriteria = (object)$modelCriteria;
        $criteria = SearchCriteria::getCriteria($modelCriteria);

        $content = '';
        $rowHeader = '';
        $row=array();
        // $bulkModel = $connection->createCommand("SELECT * FROM BulkExport WHERE id = {$id}")->queryOne();
        $bulkModel = $connection->createCommand("SELECT * FROM BulkExport WHERE searchCriteriaId = {$criteriaId} and id = {$id}")->queryOne();
        if (is_null($bulkModel)) {
            Yii::$app->appLog->writeLog('Bulk Record not found. criteriaId:' . $criteriaId);
            return;
        }
        // $connection->createCommand("Update BulkExport SET startAt = '" . date('Y-m-d H:i:s') . "', status = " . BulkExport::IN_PROGRESS . " WHERE id = {$id}")->execute();
        $connection->createCommand("Update BulkExport SET startAt = '" . date('Y-m-d H:i:s') . "', status = " . BulkExport::IN_PROGRESS . " WHERE searchCriteriaId = {$criteriaId} and id = {$id}")->execute();

        do {

            $criteria->limit = $this->dbBatchSize;
            $criteria->offset = $startIndex;
            $users = $criteria->from('User t')->all();

            // Write a header of csv file
            $headersFixed = array(
                'firstName',
                'lastName',
                'keywords',
                'email',
                'dateOfBirth',
                'gender',
                'userType',
                'address1',
                'notes'
            );

            $customHeaders = array(
                'fullAddress' => 'Street Address',
                'country' => 'Country'
            );

            $customFields = CustomField::findBySql("SELECT id as customFieldId,label From CustomField WHERE enabled=1 AND relatedTable='" . CustomType::CF_PEOPLE . "'")->all();

            $customColumns = array();
            foreach ($customFields as $customField) {
                $customColumns[] = $customField['label'];
            }

            $count = 0;
            $country = array();

            foreach ($users as $model) {
                if (!empty($model['countryCode'])) {

                    $country = $connection->createCommand("SELECT * FROM Country  WHERE countryCode = '{$model['countryCode']}'")->queryOne();
                }

                $attributes = $model;
                //displaying only selected columns in csv
                $filterAttr = array();
                foreach ($attributes as $key => $val) {
                    if (in_array($key, $headersFixed)) {
                        $filterAttr[$key] = $val;
                    }
                }
                $filterAttr['mobile'] = $attributes['mobile']; // reorder
                $filterAttr['zip'] = $attributes['zip'];
                if (!ToolKit::isEmpty($filterAttr['address1'])) {
                    $filterAttr['fullAddress'] = $filterAttr['address1'];
                    unset($filterAttr['address1']);
                } else {
                    $filterAttr['fullAddress'] = '';
                    unset($filterAttr['address1']);
                }
                $filterAttr['city'] = $attributes['city'];  // reorder
                $filterAttr['country'] = '';
                if ($country) {
                    $filterAttr['country'] = $country['countryName'];
                }
                $filterAttr['joinedDate'] = $attributes['joinedDate']; // reorder


                $valuesOrder = array();
                foreach ($customColumns as $column) {
                    $customField = CustomField::find()->where(['LOWER(label)' => strtolower($column), 'enabled' => 1, 'relatedTable' => CustomType::CF_PEOPLE])->one();
                    if (!ToolKit::isEmpty($customField)) {
                        $customValue = CustomValue::find()->where(['customFieldId' => $customField->id, 'relatedId' => $model['id']])->one();
                        if (!ToolKit::isEmpty($customValue)) {
                            $valuesOrder[] = $customValue->fieldValue;
                        } else {
                            $valuesOrder[] = '';
                        }
                    }
                }

                $count++;

                $id = $model['id'];
                $connection->createCommand("Update BulkExport SET lastRecord = " . $id . " WHERE id = {$id}")->execute();

                if ($count == 1) { // header row
                    $headers = array_merge(array_keys($filterAttr), $customColumns);
                    $rowHeader = array();
                    $User = new User();
                    foreach ($headers as $header) {
                        if (isset($customHeaders[$header]))
                            $rowHeader[] = $customHeaders[$header];
                        else
                            $rowHeader[] = $User->getAttributeLabel($header);
                    }

                }

                $keywordLabels = array();
                if (!empty($filterAttr['keywords'])) {
                    $keywords = explode(",", $filterAttr['keywords']);
                    foreach ($keywords as $keyword) {
                        $keywordLabels[] = Keyword::getLabel($keyword);
                    }
                }

                $filterAttr['keywords'] = implode(',', $keywordLabels);
                $filterAttr['notes'] = $filterAttr['notes'];
                $row[] = array_merge($filterAttr, $valuesOrder);
            }

            $startIndex += $this->dbBatchSize;
        } while (!empty($users));

        $this->array_csv_download($row, $statusFile, $rowHeader);

        $statusFileQuery = "";
        if ($statusFile) {
            $statusFileQuery = ",statusFile ='" . $statusFile . "'";
        }
        $connection->createCommand("Update BulkExport SET status = " . BulkExport::FINISHED . ",finishedAt = '" . date('Y-m-d H:i:s') . "' " . $statusFileQuery . " WHERE searchCriteriaId = {$criteriaId}")->execute();

    }

    /*
* returns csv file out put.
* */
    function array_csv_download($array, $filename = "export.csv", $rowHeader, $delimiter = ",")
    {
        // clean output buffer
        $handle = fopen(Yii::$app->params['fileUpload']['export']['path'] . $filename, 'w');
        // use keys as column titles
        fputcsv($handle, $rowHeader);
        foreach ($array as $value) {
            fputcsv($handle, $value, $delimiter);
        }
        fclose($handle);
        // use exit to get rid of unexpected output afterward
    }


}

?>