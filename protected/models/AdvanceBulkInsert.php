<?php

namespace app\models;

use app\components\ThresholdChecker;
use app\components\ToolKit;
use Keboola\Csv\CsvReader;
use Keboola\Csv\CsvWriter;
use Keboola\Csv\Exception;
use Yii;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * This is the model class for table "advancebulkinsert".
 *
 * @property int $id
 * @property string $source
 * @property string|null $renameSource
 * @property string $countryCode
 * @property int $userType
 * @property string $keywords
 * @property string $size
 * @property string $errors
 * @property string $timeSpent
 * @property string $status 1:Queued, 2:In Progress, 3:Finished, 4:Error, 5:Cancel
 * @property string|null $columnMap
 * @property string $createdAt
 * @property int $createdBy
 * @property string $fileToUpload
 */
class AdvanceBulkInsert extends \yii\db\ActiveRecord
{
    public $progress = null;
    public $applicationModel; // TODO need to remove the variable if no more need

    public $fileToUpload;

    /**
     * status
     */
    const QUEUED = 1;
    const IN_PROGRESS = 2;
    const ERROR = 3;
    const FINISHED = 4;
    const CANCELED = 5;
    const PENDING = 6;

    const MAX_LINE_SIZE = 25000;
    const MAX_QUEUE_SIZE = 3;

    const FORMAT_SIZE = 1;
    const FORMAT_PROGRESS = 2;
    const FORMAT_SECONDS = 3;

    const CRON_COMMAND = 'mass-bulk-insert';

    const SCENARIO_FILE_TRANSFORM = 'file-transform';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'AdvanceBulkInsert';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['source', 'progress', 'countryCode', 'userType', 'size', 'errors', 'timeSpent', 'status', 'createdAt', 'createdBy'], 'required'],
            [['createdAt'], 'number', 'integerOnly' => true],
            [['source',], 'string', 'max' => 256],
            [['size', 'timeSpent', 'progress'], 'string', 'max' => 128],
            [['errors'], 'string', 'max' => 512],
            [['status'], 'string', 'max' => 1],
            [['countryCode', 'keywords'], 'default', 'value' => ''],
            [['fileToUpload'], 'file', 'extensions' => 'csv', 'checkExtensionByMimeType' => false, 'maxSize' => 7000000, 'skipOnEmpty' => false, 'on' => [self::SCENARIO_FILE_TRANSFORM]],
            // [['userType'], 'default', 'value' => Yii::$app->user->userType],

            [['id', 'source', 'size', 'progress', 'errors', 'columnMap', 'timeSpent', 'status', 'createdAt', 'createdBy', 'renameSource'], 'safe'],
            [['id', 'source', 'size', 'progress', 'errors', 'columnMap', 'timeSpent', 'status', 'createdAt', 'createdBy'], 'safe', 'on' => 'search']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('messages', 'ID'),
            'source' => Yii::t('messages', 'Source'),
            'size' => Yii::t('messages', 'Size'),
            'progress' => Yii::t('messages', 'Progress'),
            'errors' => Yii::t('messages', 'Errors'),
            'timeSpent' => Yii::t('messages', 'Time Spent'),
            'status' => Yii::t('messages', 'Status'),
            'createdAt' => Yii::t('messages', 'Created At'),
            'createdBy' => Yii::t('messages', 'Created By'),
        ];
    }

    /**
     * format the size text in to a better reading form
     * @param $type
     * @param $value
     * @return Int status
     */
    public static function getFormattedText($type, $value)
    {
        $formattedText = '';
        switch ($type) {
            case self::FORMAT_SIZE:
                $formattedText = number_format((float)($value / 1000), 2, '.', '') . ' KB';
                break;
            case self::FORMAT_PROGRESS || self::FORMAT_SECONDS:
                $formattedText = $value / 1000 . ' KB';;
                break;
            default:
                break;
        }

        return $formattedText;
    }


    /**
     * executes the command given based on the running OS.
     * @param $command
     * @param $recordId
     * TODO need to implement in toolkit
     */
    public function runCommand($command, $recordId)
    {
        $domain = Yii::$app->toolKit->domain;
        $lang = Yii::$app->language;
        $status = 0;
        if (Yii::$app->toolKit->osType == ToolKit::OS_WINDOWS) {
            $cmd = "php " . Yii::$app->params['consolePath'] . "console.php {$command} {$domain} {$recordId} {$lang}";
            Yii::$app->appLog->writeLog("Windows: " . $cmd);
            Yii::$app->appLog->writeLog("RecordId: " . $recordId);
            $handle = popen("start /B " . $cmd, 'r'); // For windows
            $read = fread($handle, 2096);
        } else {
            $cmd = 'php ' . Yii::$app->params['consolePath'] . "console.php {$command} {$domain} {$recordId} {$lang}> /dev/null 2>&1 & echo $!";
            Yii::$app->appLog->writeLog("Linux: " . $cmd);
            exec($cmd, $output, $status); // For Linux
        }

        if ($status != 0) {
            Yii::$app->appLog->writeLog("Command {$command} failed to run.");
        }

    }

    /**
     * Generate error file
     * @param $errorFile
     * @return string
     */
    public static function getErrorFile($errorFile): string
    {
        $link = '';
        if (!ToolKit::isEmpty($errorFile)) {
            $url = Url::to(['advanced-bulk-insert/download-status', 'status_file' => $errorFile]); // your url code to retrieve the profile view

            $link = \yii\bootstrap\Html::a('<i class="fa fa-download  fa-lg"></i>', $url, [
                'onClick' => "
                      var url = $(this).attr('href');
                      window.location.href ='$url';                          
                      return false;
                      ",
                'id'=>'export_download'
            ]);
        }
        return $link;
    }

    /**
     * @param $csvFile
     * @return string
     */
    public static function getReWrightCsvFile($csvFile): string
    {
        $link = '';
        if (!ToolKit::isEmpty($csvFile)) {
            $url = Url::to(['advanced-bulk-insert/download-status', 'reWrite_csv_file' => $csvFile]); // your url code to retrieve the profile view

            $link = \yii\bootstrap\Html::a(Yii::t('messages', 'Download Your File'), $url, ['target' => '_blank', 'class' => 'btn btn-secondary']);
        }

        return $link;
    }

    /**
     * @param $status
     * @return String status labels
     */
    public static function getStatusLabel($status): string
    {
        $cssClass = '';
        $text = '';
        switch ($status) {
            case self::QUEUED:
                $cssClass = 'badge badge-pill bg-bounced';
                $text = Yii::t('messages', 'Queued');
                break;

            case self::IN_PROGRESS:
                $cssClass = 'badge badge-pill badge-info';
                $text = Yii::t('messages', 'In Progress');
                break;

            case self::ERROR:
                $cssClass = 'badge badge-pill badge-danger';
                $text = Yii::t('messages', 'Error');
                break;

            case self::FINISHED:
                $cssClass = 'badge badge-pill badge-success';
                $text = Yii::t('messages', 'Finished');
                break;

            case self::CANCELED:
                $cssClass = 'badge badge-pill bg-bounced';
                $text = Yii::t('messages', 'Cancelled');
                break;

            case self::PENDING:
                $cssClass = 'badge badge-pill bg-blocked';
                $text = Yii::t('messages', 'Pending');
                break;
        }
        return "<div class='{$cssClass}'>{$text}</div>";
    }


    /**
     * format the progress text in to a better reading form
     * @param $id
     * @return string
     */
    public static function getFormattedSpentTime($id): string
    {
        $string = '';
        $differenceInSeconds = '';
        $icon = '';
        $status = AdvanceBulkInsertStatus::find()->where('bulkId=:bulkId', array(':bulkId' => $id))->one();
        if ($status) {
            $string = ' sec';
            $timeFirst = strtotime($status->startedAt);
            $timeSecond = strtotime($status->lastUpdated);
            $differenceInSeconds = $timeSecond - $timeFirst;
            $icon = '<i class="fa fa-clock-o fa-lg"></i>';
        }
        return $icon . ' ' . $differenceInSeconds . $string;
    }

    /**
     * to retrieve next record.
     */
    public static function getNextId()
    {
        $records = AdvanceBulkInsert::find()->where(['status' => AdvanceBulkInsert::QUEUED])->orderBy(['id' => SORT_ASC])->all();
        return !empty($records) ? $records[0]->id : null;
    }

    /**
     * format the progress text in to a better reading form
     * @param $id
     * @return string
     */
    public static function getFormattedProgress($id): string
    {
        $result = '0';
        $style = "width:0%;";
        $progress = 0;
        $data = AdvanceBulkInsertStatus::findOne(['bulkId' => $id]);
        // TODO Duplicate code. Need to refactor.
        if (!is_null($data)) {
            $progressVal = ceil(($data->successRecords / $data->totalRecords) * 100);
            $string = Yii::t('messages', '% completed');
            $result = ToolKit::isEmpty($progressVal) ? $progress : $progressVal;
            $style = "width:" . $result . "%;";
        }
        return '<div class="progress themed-progress"><div class="progress-bar" role="progressbar" style="' . $style . '" aria-valuenow="' . $progress . '" aria-valuemin="0" aria-valuemax="100">' . $result . "%" . '</div></div>';
    }


    /**
     * find the available status
     * @return Int status
     */
    public static function getAvailableStatus()
    {
        $status = self::IN_PROGRESS;

        $data = AdvanceBulkInsert::find()->where(['status' => AdvanceBulkInsert::IN_PROGRESS])->all();
        // if already on progress, should be queued
        if (!empty($data)) {
            $status = AdvanceBulkInsert::QUEUED;
        }
        return $status;

    }

    /**
     * get sample format
     * @throws \yii\db\Exception
     */

    public static function getSampleFormat()
    {
        $hint = array();
        $customType = new CustomType();
        $customFields = CustomValue::getCustomData(CustomType::CF_PEOPLE, 0, CustomField::ACTION_CREATE, null, CustomType::CF_SUB_PEOPLE_BULK_INSERT);

        $hint1 = "<strong>" . Yii::t('messages', 'A CSV file should be zipped and uploaded with any of the below attributes.') . "<br></strong>" . Html::encode(Yii::t('messages', '<FIRST NAME>,<LAST NAME>,<EMAIL>,<MOBILE>,<STREET>,<ZIP>,<CITY>,<GENDER>,<DOB>,<NOTE>,<KEYWORD>,<Category>,<Country>'));
        $hint2 = "<br/><strong>" . Yii::t('messages', 'GENDER') . ":</strong>" . Yii::t('messages', 'Unknown') . '-0, ' . Yii::t('messages', 'Female') . '-1, ' . Yii::t('messages', 'Male') . '-2. ';
        $hint2 .= "<br/><strong>" . Yii::t('messages', 'MOBILE') . ":</strong>" . Yii::t('messages', 'Should be in international format');
        $hint2 .= "<br/><strong>" . Yii::t('messages', 'BIRTHDAY') . ":</strong>" . 'YYYY-MM-DD';
        $hint2 .= "<br/><strong>" . Yii::t('messages', 'ZIP') . ":</strong>" . Yii::t('messages', '14390');
        $hint2 .= "<br/><strong>" . Yii::t('messages', 'STREET') . ":</strong>" . Yii::t('messages', '27 Avenue Pasteur');
        $hint2 .= "<br/><strong>" . Yii::t('messages', 'CITY') . ":</strong>" . Yii::t('messages', 'Cabourg');
        $hint2 .= "<br/><strong>" . Yii::t('messages', 'COUNTRY') . ":</strong>" . Yii::t('messages', 'France -FR');
        $hint2 .= "<br/><strong>" . Yii::t('messages', 'NOTE') . ":</strong> Text";
        $hint2 .= "<br/><strong>" . Yii::t('messages', 'CATEGORY') . ":</strong>" . Yii::t('messages', 'N/A') . '-9, ' . Yii::t('messages', 'Supporter') . '-2, ' . Yii::t('messages', 'Prospect') . '-3. ' . Yii::t('messages', 'Non-Supporter') . '-4, ' . Yii::t('messages', 'Unknown') . '-5. ';
        $hint2 .= "<br/><strong>" . Yii::t('messages', 'KEYWORD') . ":</strong> " . Yii::t('messages', 'Should be in CSV format. Ex: post,article,android,samsung');

        foreach ($customFields as $k => $customField) {
            $hint1 .= Html::encode(',<' . Yii::t('messages', strtoupper($customField->fieldLabel)) . '>');
            $hint2 .= "<br/><strong>" . Yii::t('messages', strtoupper($customField->fieldLabel)) . ":</strong>" . Yii::t('messages', $customType->getTypeHints(CustomField::getCustomType($customField->fieldName, CustomType::CF_PEOPLE)));
        }
        $hint2 .= "<br/><strong>" . Yii::t('messages', 'Unknown Fileds') . ":</strong>" . Yii::t('messages', 'Enter N/A if you don`t know exact value of it. Ex: Daniel,Vetori,aniel@yahoo.com,N/A,N/A,N/A,2,N/A');
        $hint2 .= "<br/><strong>" . Yii::t('messages', 'Maximum Number of Rows') . ":</strong>" . AdvanceBulkInsert::MAX_LINE_SIZE;

        $hint['hint1'] = $hint1;
        $hint['hint2'] = $hint2;

        return $hint;
    }


    /**
     * @param $fileName
     * @param $targetDir
     * @return string
     * @throws Exception
     */
    public function reWriteExcel($fileName, $targetDir)
    {
        $filePath = $targetDir . $fileName;
        $newFilePath = '';
        $csvFile = new CsvReader($filePath);
        $newHeader = [];
        $newRow = [];
        $count = 0;

        // Extract data from csv file
        foreach ($csvFile as $row) {
            $countRowElement = count($row);
            if ($countRowElement > 1) {
                $newRow[$count] = $row;
                $count++;
            }

        }

        if($count > 25000) {
            return false;
        }

        $header = $newRow[0]; // assign header
        unset($newRow[0]); // remove header

        if(in_array('Error Message', $header)) { // Todo this line need to be improve according to difference scenario
            // Yii::$app->toolkit->setFlash('error', Yii::t('messages','Your file is invalid'));
            return 'error';
        }

        $newHeader[] = $this->fixCsvHeader(array_values($header));
        $newContent = $newRow;
        $repairCsv = $this->fixCsvContent(array_values($newContent), $newHeader);
        $newRow = array_merge($newHeader, $repairCsv); // prepare new array for write csv
        $filePath = $targetDir . 'converted' . $fileName;
        $fp = fopen($filePath, 'w');
        foreach ($newRow as $row) {
            fputcsv($fp, $row);
        }
        fclose($fp);
        /*$csvFile = new CsvWriter($targetDir . 'converted' . $fileName); // Assign new file for write
        foreach ($newRow as $row) {
            $csvFile->writeRow($row); // Writing new file
        }*/

        return $filePath;
    }


    /**
     * @param $header
     * @return mixed
     */
    public function fixCsvHeader($header)
    {
        $headerFormat = ['FIRST NAME', 'LAST NAME', 'EMAIL', 'MOBILE', 'STREET', 'ADDRESS', 'ZIP', 'CITY', 'GENDER', 'DOB', 'DATE', 'NOTE', 'KEYWORD', 'CATEGORY', 'COUNTRY', 'Prénom', 'Prenom', 'PRENOM', 'Nom', 'Email', 'Cellulaire', 'Adresse', 'Code postal', 'Ville', 'Sexe', 'Dob', 'Date', 'Notes', 'Mots clefs', 'Catégorie', 'Pays'
        ];
        $i = 0;
        foreach ($header as $head) {
            if (in_array(strtoupper($head), $headerFormat)) {
                $header[$i] = (strtoupper($head) == 'DOB') ? 'DATE' : strtoupper($head);
            } elseif (in_array(ucwords($head), $headerFormat)) {
                $header[$i] = ucwords($head);
            } else {
                $header[$i] = $head;
            }
            $i++;
        }
        return $header;
    }

    /**
     * @param $csvData
     * @param $header
     * @return array
     */
    public function fixCsvContent($csvData, $header): array
    {
        $repair_data = array();
        $headerRow = $header[0];
        $repair_data = array();
        $r = 0; // Row count in csv
        foreach ($csvData as $data) {
            $totalRowCount = count($data);

            for ($c = 0; $c < $totalRowCount; $c++) { // $c is column in a row of csv
                $value = $data[$c];
                $repair_data[$r][$c] = $value;
                if (ToolKit::isEmpty($value) && ($headerRow[$c] == 'Sexy' || $headerRow[$c] == 'GENDER')) {
                    $repair_data[$r][$c] = 0;
                } elseif (ToolKit::isEmpty($value)) {
                    $repair_data[$r][$c] = 'N/A';
                } else {

                   switch ($headerRow[$c]) {
                        case 'EMAIL':
                        case 'Email':
                            $repair_data[$r][$c] = $this->handleEmail($value);
                            break;
                        case 'MOBILE':
                        case 'Cellulaire':
                            $repair_data[$r][$c] = $this->handleMobile($value);
                            break;
                        case 'STREET':
                        case 'ADDRESS':
                        case 'Adresse':
                            $repair_data[$r][$c] = str_replace(',', ' ', $value);
                            break;
                        case 'GENDER':
                        case 'Sexe':
                            $repair_data[$r][$c] = $this->handleGender($value);
                            break;
                        case 'DATE':
                        case 'Date':
                            $repair_data[$r][$c] = $this->handleDateOfBirth($value);
                            break;
                        case 'KEYWORD':
                        case 'Mots clefs':
                            $repair_data[$r][$c] = $this->handleKeyword($value);
                            break;
                        case 'COUNTRY':
                        case 'Pays':
                            $repair_data[$r][$c] = str_replace('France', 'FR', $value);;
                            break;
                        default:
                            $repair_data[$r][$c] = $value;

                    }
                }

            }
            $r++;
        }

        return $repair_data;
    }

    /**
     * @param $value
     * @return int
     */
    public function handleGender($value) {
          $genderMale = ['monsieur','Monsieur','MONSIEUR','Mr', 'MR','M.','M','mr','m'];
          $genderFemale = ['madame','Madame','MADAME','Ms','MS','Mme','MME','Mlle','MLLE','Mme','F','mme'];
          $gender = 0;
          if(in_array($value, $genderMale)) {
              $gender = 2;
          } elseif (in_array($value, $genderFemale)) {
              $gender = 1;
          } else {
              $gender = $value;
          }
          return $gender;

    }

    /**
     * @param $value
     * @return false|string
     */
    public function handleDateOfBirth($value) {

        $correctFormat = preg_replace("/[\/]/", "-", $value);

        $excelDate = strtotime($correctFormat);

        return date('Y-m-d', $excelDate);;
    }

    /**
     * @param $value
     * @return string|string[]
     */
    public function handleKeyword($value) {
       $removeFirstComma = ltrim($value, ',');
       $removeLastComma = rtrim($removeFirstComma, ',');
       $replaceCollen = str_replace(';', ',', $removeLastComma);
       return str_replace(' , ', ',', $replaceCollen);
    }

    /**
     * @param $value
     * @return string|string[]
     */
    public function handleMobile($value) {
        $mobile_text = $value;
        $mobile_text = str_replace(' ', '', $mobile_text);
        $mobile_text = str_replace('.', '', $mobile_text);
        $mobile_text = str_replace('-', '', $mobile_text);
        $mobile_text = str_replace('_', '', $mobile_text);
        $mobile_text = str_replace('(', '', $mobile_text);
        $mobile_text = str_replace(')', '', $mobile_text);

        return $mobile_text;
    }

    /**
     * @param $value
     * @return string|string[]
     */
    public function handleEmail($value)
    {
        $email = $value;
        $email = str_replace(' ', '', $email);
        $email = str_replace(';', '.', $email);
        $email = str_replace(',', '.', $email);
        return $email;
    }


    /**
     * @description the current server memory info.
     * @return array
     */
    private function getSystemMemInfo() // TODO need to remove if no need
    {
        $data = explode("\n", file_get_contents("/proc/meminfo"));
        $memInfo = array();
        foreach ($data as $line) {
            list($key, $val) = explode(":", $line);
            $memInfo[$key] = trim($val);
        }
        return $memInfo;
    }

    /**
     * @param $targetDir
     * @return bool
     */
    public function uploadCsv($targetDir) // TODO need to remove if no need
    {
        if ($this->validate()) {
            $date = date('m-d-YHis', time());
            $this->fileToUpload->saveAs('excel/' . $this->fileToUpload->baseName . '.' . $this->fileToUpload->extension);
            return true;
        } else {
            return false;
        }
    }

    /**
     * find the count of current queue
     * @return Int status
     */
    public static function getQueueCount(): int
    {
        // $data = self::findAll('status=:status1 OR status=:status2', array(':status1' => self::QUEUED, ':status2' => self::PENDING));
        $data = self::find()->where(['status' => self::QUEUED])->orWhere(['status' => self::PENDING])->all();
        return count($data);
    }


    /**
     * {@inheritdoc}
     * @return AdvanceBulkInsertQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new AdvanceBulkInsertQuery(get_called_class());
    }
}
