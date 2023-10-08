<?php

namespace app\commands;

use app\components\ThresholdChecker;
use app\components\ToolKit;
use app\models\AdvanceBulkInsert;
use app\models\AdvanceBulkInsertStatus;
use app\models\App;
use app\models\CustomField;
use app\models\CustomType;
use app\models\CustomValue;
use app\models\User;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberUtil;
use yii\console\Controller;
use Yii;


/**
 * Mass Bulk Insert Controller.
 *
 * This class illustrate Import contact from csv file
 */
class MassBulkInsertController extends Controller
{
    public $isEmailContactLimitExceed = false;
    public $bulkSuccessCount = 0;
    public $bulkErrorCount = 0;
    public $fileErrors = null;
    public $tmpStatusFile = null;
    public $lineCount = 0;
    public $timeStart = null;
    public $timeEnd = null;
    public $appModel = null;
    public $formModel = null;
    public $currentRecord = 0;


    /**
     * This command echoes what you have entered as the message.
     * @param $domains
     * @param $recordId
     * @param $lang
     * @return void Exit code
     * @throws \Throwable
     */
    public function actionIndex($domains, $recordId, $lang)
    {
        ini_set('auto_detect_line_endings', true);
        set_time_limit(0); //no time limitation
        ini_set('memory_limit', '-1'); // no memory limitation
        Yii::$app->appLog->logType = 2;
        Yii::$app->appLog->isConsole = true;
        Yii::$app->appLog->username = __class__;

        if (Yii::$app->toolKit->isProcessExists(__class__)) { // check is there any bulk insert process running
            Yii::$app->appLog->writeLog('Previous process in progress.');
            exit;
        }

        $domain = $domains;
        Yii::$app->sourceLanguage = 'en_us';
        Yii::$app->language = $lang;

        if (ToolKit::isEmpty($domain) || ToolKit::isEmpty($recordId)) {
            Yii::$app->appLog->writeLog('Invalid params submitted');
            exit;
        }

        Yii::$app->appLog->writeLog("Process started.Domain:{$domain}");
        $appModel = App::find()->where(['domain' => $domain])->one();
        $this->appModel = $appModel;

        if (App::checkIsFreemiumPackage($appModel->packageTypeId)) {
            Yii::$app->appLog->writeLog('Not available for this package');
            exit;
        }

        Yii::$app->appLog->writeLog("Connecting to database.Dbname:{$appModel->dbName}");
        Yii::$app->toolKit->domain = $appModel->domain;

        if (!Yii::$app->toolKit->changeDbConnection($appModel->dbName, $appModel->host, $appModel->username, $appModel->password)) {
            Yii::$app->appLog->writeLog("Database connection change failed.");
            exit;
        }

        $this->formModel = AdvanceBulkInsert::findOne($recordId);

        if (is_null($this->formModel) || ToolKit::isEmpty($this->formModel->renameSource)) {
            Yii::$app->appLog->writeLog("Advanced bulk insert record not found. recordId:" . $recordId);
            exit;
        }
        $filePath = Yii::$app->params['fileUpload']['people']['path'] . $this->formModel->renameSource;
        if (!file_exists($filePath)) {
            Yii::$app->appLog->writeLog("The file {$this->formModel->renameSource} not found.");
            exit;
        }

        Yii::$app->appLog->writeLog('Processing file:' . $this->formModel->renameSource);
        $this->insertFromFile($this->formModel->renameSource);
        Yii::$app->appLog->writeLog('File processing completed.');
    }

    /**
     * @param $fileName
     * @throws \Throwable
     */
    public function insertFromFile($fileName)
    {
        $this->fileErrors = null;
        $this->bulkSuccessCount = 0;
        $this->timeStart = date('Y-m-d H:i:s');
        $filePath = Yii::$app->params['fileUpload']['people']['path'] . $fileName;
        $this->lineCount = count(file($filePath)) - 1; // total records in the csv file
        $isCancelled = false;
        $appModel = new App();
        $bulkModel = AdvanceBulkInsert::findOne(['renameSource' => $fileName]);

        if (is_null($bulkModel)) {
            Yii::$app->appLog->writeLog('Record not found:' . $fileName);
            return;
        }

        $customColumns = [];


        if (is_null($bulkModel->columnMap)) {
            Yii::$app->appLog->writeLog('Column Map not found:' . $fileName);
            return;
        }

        $columnMap = json_decode($bulkModel->columnMap);

        foreach ($columnMap as $key => $val) {
            $customColumns[] = $key;
        }

        $fieldsCount = count($customColumns);

        $tc = new ThresholdChecker($this->appModel->packageType, $this->appModel->smsPackageType, true);
        $emailContactsCount = $tc->getCount(ThresholdChecker::EMAIL_CONTACTS);
        $count = 0;

        // set the header flag and header array

        $headerFlag = true;
        $mappedPeopleDataHeader = [];
        $mappedPeopleData = [];

        if (($handle = fopen($filePath, "r")) !== FALSE) {
            while (($peopleData = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if ($count == 0) { // check the header of csv
                    foreach ($columnMap as $key => $val) {
                        $mappedPeopleDataHeader[] = $peopleData[$val];
                    }
                    $count++;
                    continue; //skipping first record
                }

                $count++;

                $mappedPeopleData = [];

                $i = 0;
                foreach ($columnMap as $key => $val) {
                    $mappedPeopleData[$i] = $peopleData[$val];
                    $i++;
                }

                $attributes = array_combine($customColumns, $mappedPeopleData);

                Yii::$app->appLog->writeLog("Test Map data:" . @json_encode($mappedPeopleData));
                Yii::$app->appLog->writeLog("Test attribute data:" . @json_encode($attributes));

                if (count($mappedPeopleData) == $fieldsCount) {
                    $attributes = User::formatAttributes($attributes);
                    $model = $this->retrieveData($attributes); // set data to model;

                    // check the limit of contacts available in the package before insert.
                    // TODO needs to take into common place.
                    if (!empty($attributes['email']) && $tc->packageInfo['totalEmailContacts'] < $emailContactsCount) {
                        $this->isEmailContactLimitExceed = true;
                        Yii::$app->appLog->writeLog("Email contacts limit exceed.");
                        break;
                    }

                    $findInsertById = AdvanceBulkInsert::find()->where(['id' => $bulkModel->id])->one();
                    if ($findInsertById->status == AdvanceBulkInsert::CANCELED) {
                        Yii::$app->appLog->writeLog("The file {$bulkModel->renameSource} cancelled by user.");
                        $isCancelled = true;
                        break;
                    }

                    Yii::$app->appLog->writeLog("Check Modal:" . json_encode($model));

                    if (null == $model) {
                        $oldNote = null;
                        $oldMobile = null;
                        $oldEmail = null;
                        $oldAddress = null;
                        $oldZip = null;
                        $oldGender = 0;
                        $oldCity = null;
                        $oldCountry = null;
                        $oldUserType = null;
                        $model = new User;
                        $model->joinedDate = date('Y-m-d H:i:s');
                        $oldDateOfBirth = null;
                        $customFields = CustomValue::getCustomData(CustomType::CF_PEOPLE, 0, CustomField::ACTION_CREATE, $attributes, CustomType::CF_SUB_PEOPLE_BULK_INSERT);
                    } else {
                        $oldNote = $model->notes;
                        $oldMobile = $model->mobile;
                        $oldEmail = $model->email;
                        $oldGender = $model->gender;
                        $oldAddress = $model->address1;
                        $oldZip = $model->zip;
                        $oldCity = $model->city;
                        $oldCountry = $model->countryCode;
                        $oldUserType = $model->userType;
                        $oldDateOfBirth = $model->dateOfBirth;
                        $customFields = CustomValue::getCustomData(CustomType::CF_PEOPLE, $model->id, CustomField::ACTION_CREATE, $attributes, CustomType::CF_SUB_PEOPLE_BULK_INSERT);
                    }

                    $this->currentRecord++;
                    Yii::$app->appLog->writeLog('Current Record:' . $this->currentRecord);
                    $model->scenario = 'bulkPeople';
                    $oldKeywords = $model->keywords;
                    $model->attributes = $attributes;
                    $model->notes = $oldNote;
                    $model->countryCode = ToolKit::isEmpty($model->countryCode) ? $oldCountry : $model->countryCode;

                    $model->mobile = $this->retriveMobileNumber($oldMobile, $model->mobile, $model->countryCode);
                    $model->mobile = str_replace(' ', '', trim($model->mobile));

                    $model->email = ToolKit::isEmpty($model->email) ? $oldEmail : $model->email;
                    $model->gender = ToolKit::isEmpty($model->gender) ? $oldGender : $model->gender;
                    $model->address1 = ToolKit::isEmpty($model->address1) ? $oldAddress : $model->address1;
                    $model->zip = ToolKit::isEmpty($model->zip) ? $oldZip : $model->zip;
                    $model->city = ToolKit::isEmpty($model->city) ? $oldCity : $model->city;
                    $model->dateOfBirth = ToolKit::isEmpty($model->dateOfBirth) ? $oldDateOfBirth : $model->dateOfBirth;
                    $model->userType = $this->checkUserType($oldUserType, $model->userType);
                    $model->isManual = 1;

                    // check user type null. Else set non-supporter
                    if (is_null($model->userType)) {
                        $model->userType = User::NON_SUPPORTER;
                    }

                    // Concatenate Note. Latest note is be first.
                    if (!is_null($oldNote)) {
                        $model->notes = ($attributes['notes'] === $model->notes) ? $attributes['notes'] : $attributes['notes'] . ". " . $model->notes;
                    } else {
                        $model->notes = $attributes['notes'];
                    }

                    $savedKeywords = $appModel->saveKeywords($model->keywords, $findInsertById->createdBy);

                    $model->keywords = ToolKit::isEmpty($oldKeywords) ? $savedKeywords : implode(",", array_unique(array_merge(explode(",", $oldKeywords), explode(",", $savedKeywords)), SORT_REGULAR));

                    $model->delStatus = User::NOTDELETE;
                    $model->supporterDate = date('Y-m-d H:i:s');

                    $errorMsg = '';
                    foreach ($attributes as $key => $val) {
                        foreach ($customFields as $k => $customField) {
                            if ($key == $customField->fieldName) {
                                $customField->fieldValue = $val;
                                $customField->validate();
                                if (!empty($customField->errors)) {
                                    $errorKey = array_keys(current(array_filter([$customField->errors])));
                                    $firstElement = current(array_filter([$customField->errors]));
                                    $errorMsg = $key . " : " . $firstElement[$errorKey[0]][0];
                                }
                                break;
                            }
                        }
                    }

                    $attributes = array_merge($model->attributes, $attributes);

                    try {
                        $valid = $model->validate();
                        $valid = CustomField::validateCustomFieldList($customFields) && $valid;
                        if ($valid && $model->saveWithCustomData($customFields, null, false, 'bulkInsert')) {
                            Yii::$app->appLog->writeLog("People created. People data:" . @json_encode($attributes));
                            $this->bulkSuccessCount++;
                            $this->updateStatus($bulkModel->id);

                            if (!empty($model->email)) {
                                $emailContactsCount++;
                            }

                        } else {
                            $this->updateStatus($bulkModel->id);
                            $errorKey = array_keys($model->errors);

                            if (!empty($model->errors)) {
                                $errorMsg = $model->errors[$errorKey[0]][0];
                            }

                            Yii::$app->appLog->writeLog("People create failed. People data:" . @json_encode($attributes));
                            Yii::$app->appLog->writeLog(Yii::t('messages', 'Failed,') . $errorMsg . "," . implode(",", $mappedPeopleData));
                            if ($headerFlag) {
                                //write the header and set the flag off
                                $this->fileErrors[] = Yii::t('messages', 'Status;') . 'Error Message' . ";" . implode(";", $mappedPeopleDataHeader);
                                $headerFlag = false;
                            }
                            $this->fileErrors[] = Yii::t('messages', 'Failed;') . $errorMsg . ";" . implode(";", $mappedPeopleData);
                            $this->bulkErrorCount++;
                        }
                    } catch (\Exception $e) {
                        Yii::$app->appLog->writeLog("People create failed. Error:{$e->getMessage()}");
                    }

                } else {
                    Yii::$app->appLog->writeLog("People create failed. Data line:" . @json_encode($mappedPeopleData));
                    $this->fileErrors[] = Yii::t('messages', 'Failed; Number of data fields not match.') . ";" . implode(";", $mappedPeopleData);
                }
            }
            Yii::$app->appLog->writeLog("End of file. handle is closing.");
            fclose($handle);
        } else {
            Yii::$app->appLog->writeLog("File open failed.");
        }

        Yii::$app->appLog->writeLog("Total Count:" . $count); // Upper code line number 117 is getting wrong value
        $this->lineCount = $count - 1;
        $this->updateStatus($bulkModel->id, 'total-record');

        if (empty($this->fileErrors)) {
            $this->timeEnd = microtime(true);
            try {
                $bulkModel->status = $isCancelled ? AdvanceBulkInsert::CANCELED : AdvanceBulkInsert::FINISHED;
                if ($bulkModel->status == AdvanceBulkInsert::FINISHED) {
                    $fileName = $bulkModel->renameSource;
                    unlink(Yii::$app->params['fileUpload']['people']['path'] . $fileName); // csv file
                    unlink(Yii::$app->params['fileUpload']['people']['path'] . substr($fileName, 0, -4)); // zip file
                }
                $bulkModel->save(false);
                $this->updateNextFile($bulkModel->id);
            } catch (\Exception $e) {
                Yii::$app->appLog->writeLog($e->getMessage());
            }
        } else {
            Yii::$app->appLog->writeLog("People create failed. Data line:" . @json_encode($this->fileErrors));
            $this->writeTempBulkUploadFile();
            $fileName = $bulkModel->renameSource;
            unlink(Yii::$app->params['fileUpload']['people']['path'] . $fileName); // csv file
            unlink(Yii::$app->params['fileUpload']['people']['path'] . substr($fileName, 0, -4)); // zip file
            $this->timeEnd = microtime(true);
            $bulkModel->status = AdvanceBulkInsert::ERROR;
            $bulkModel->errors = $this->tmpStatusFile;
            $bulkModel->save(false);
            $this->updateNextFile($bulkModel->id);
        }
    }

    /**
     * @param $oldMobile
     * @param $newMobile
     * @param $countryCode
     * @rule1 if oldMobile is empty update with new mobile and
     * @rule2 if old mobile not empty and new mobile empty take old mobile
     * @rule3 if old mobile not empty and new mobile not empty take new mobile
     * @description this is according to client scenario
     * @return PhoneNumber|string
     */
    public function retriveMobileNumber($oldMobile, $newMobile, $countryCode)
    {
        $mobile = null;
        if (!ToolKit::isEmpty($newMobile)) {
            $mobile = $newMobile;
        } else {
            $mobile = $oldMobile;
        }

        if (ToolKit::isEmpty($countryCode)) {
            Yii::$app->appLog->writeLog("country code:" . $mobile);
            return $mobile;
        }

        return $this->getInternationalMobileNo($mobile, $countryCode);

    }

    /**
     * @param $mobile
     * @param $countryCode
     * @return PhoneNumber|string
     */
    public function getInternationalMobileNo($mobile, $countryCode)
    {
        $phoneNumber = '';
        Yii::setAlias('@libphonenumber', '@app/vendor/borales/yii2-phone-input');
        $phoneUtil = PhoneNumberUtil::getInstance();
        try {
            $phoneNumber = $phoneUtil->parse($mobile, $countryCode);
            $phoneNumber = $phoneUtil->format($phoneNumber, \libphonenumber\PhoneNumberFormat::INTERNATIONAL);
        } catch (\Exception $ex) {
            Yii::$app->appLog->writeLog("Mobile is invalid:" . $ex->getMessage());
            return $mobile;
        }
        /*if (!ToolKit::isEmpty($mobile) && !ToolKit::isEmpty($countryCode)) {
            Yii::setAlias('@libphonenumber', '@app/vendor/borales/yii2-phone-input');

            $phoneUtil = PhoneNumberUtil::getInstance();
            try {
                $phoneNumber = $phoneUtil->parse($mobile, $countryCode);
                if ($phoneUtil->isValidNumber($phoneNumber)) {
                    $phoneNumber = $phoneUtil->format($phoneNumber, \libphonenumber\PhoneNumberFormat::INTERNATIONAL);
                } else {
                    $phoneNumber = $mobile;
                }
            } catch (\Exception $ex) {
                $phoneNumber = $mobile;
                Yii::$app->appLog->writeLog("Mobile is invalid:" . $ex->getMessage());
                return $phoneNumber;
            }
        }*/
        return $phoneNumber;
    }

    /**
     * @description Retrieve data occurs according to three scenarios
     * @1- Email
     * @2- Mobile
     * @3- FirstName/LastName/DOB are equal
     * @param $attributes
     * @return User|array|null |null
     * @TODO need to remove once the function is working.
     */
    public function retrieveData($attributes)
    {
        $model = null;

        if (is_null($attributes)) {
            return $model;
        }

        // check the email and if not null retrieve data from database
        if (null != $attributes['email']) {
            $attributes['email'] = str_replace(' ', '', trim($attributes['email']));
            $model = User::find()->where(['email' => $attributes['email']])->andWhere('userType NOT IN ("' . User::POLITICIAN . '")')->one();
        }

        // check model is null or empty model and mobile not null. Then retrieve
        if ((empty($model) || null == $model) && null != $attributes['mobile']) {
            $attributes['mobile'] = str_replace(' ', '', trim($attributes['mobile']));
            $model = User::find()->where(['mobile' => $attributes['mobile']])->andWhere('userType NOT IN ("' . User::POLITICIAN . '")')->one();
        }

        // check if email and mobile not empty it will check firstName / lastName / DOB
        if ((empty($model) || null == $model) && null != $attributes['firstName'] && null != $attributes['lastName'] && null != $attributes['dateOfBirth']) {
            $model = User::find()->where(['firstName' => $attributes['firstName'], 'lastName' => $attributes['lastName'], 'dateOfBirth' => $attributes['dateOfBirth']])->andWhere('userType NOT IN ("' . User::POLITICIAN . '")')->one();
        }

        return $model;
    }


    /**
     * @param $oldUserType
     * @param $newUserType
     * @return mixed
     */
    public function checkUserType($oldUserType, $newUserType)
    {
        if (ToolKit::isEmpty($newUserType)) {
            $userType = $oldUserType;
        } else {
            $userType = $newUserType;
        }

        switch ($userType) {
            case User::UNKNOWN:
            case User::SUPPORTER:
            case User::PROSPECT:
            case User::NON_SUPPORTER:
                $result = $userType;
                break;
            default:
                $result = User::UNKNOWN;
                break;
        }

        return $result;
    }

    /**
     * @param $id
     * @throws \Throwable
     */
    private function updateNextFile($id)
    {
        $nextId = AdvanceBulkInsert::getNextId();
        if (!is_null($nextId)) {
            $bulkModelNext = AdvanceBulkInsert::findOne($nextId);
            $bulkModelNext->status = AdvanceBulkInsert::IN_PROGRESS; //next queued item will be in progress
            $bulkModelNext->save(false);
            $this->insertFromFile($bulkModelNext->renameSource); // start the next queued file
        }
    }

    /**
     * @param $id
     * @param null $type
     */
    private function updateStatus($id, $type = null)
    {
        $model = AdvanceBulkInsertStatus::findOne(['bulkId' => $id]);
        if ($type === "total-record") {
            $model->totalRecords = $this->lineCount;
            $model->save();
        } else {
            if (!is_null($model)) { //has a record
                $model->successRecords = $this->bulkSuccessCount;
                $model->invalidRecords = $this->bulkErrorCount;
                $model->currentRecord = $this->currentRecord;
                $model->lastUpdated = date('Y-m-d H:i:s');
                $model->save(false);
            } else { //new record, comes here only ones for each upload
                $model = new AdvanceBulkInsertStatus();
                $model->bulkId = $id;
                $model->startedAt = $this->timeStart;
                $model->totalRecords = $this->lineCount;
                $model->successRecords = $this->bulkSuccessCount;
                $model->invalidRecords = $this->bulkErrorCount;
                $model->currentRecord = $this->currentRecord;
                $model->lastUpdated = date('Y-m-d H:i:s');
                $model->save(false);
            }
        }
    }

    /**
     * @param null $message
     * @return bool
     */
    public function writeTempBulkUploadFile($message = null)
    {
        $this->tmpStatusFile = rand() . Yii::$app->params['fileUpload']['error']['name'];
        if ($message === null) {
            $this->tmpStatusFile = rand() . Yii::$app->params['fileUpload']['error']['name'];
            @file_put_contents(Yii::$app->params['fileUpload']['error']['path'] . $this->tmpStatusFile, false);

            $handle = fopen(Yii::$app->params['fileUpload']['error']['path'] . $this->tmpStatusFile, 'w');
            $rowHeader = $this->fileErrors[0];
            fputcsv($handle, explode(';', $rowHeader));
            // use keys as column title
            foreach ($this->fileErrors as $key => $value) {
                if ($key != 0) {
                    fputcsv($handle, explode(';', $value), ',');
                }
            }
            fclose($handle);
        } else {
            @file_put_contents(Yii::$app->params['fileUpload']['error']['path'] . $this->tmpStatusFile, "{$message}\n", FILE_APPEND);
        }
    }


    /**
     * @return array
     */
    private function getSystemMemInfo()
    {
        $data = explode("\n", file_get_contents("/proc/meminfo"));
        $meminfo = array();
        foreach ($data as $line) {
            list($key, $val) = explode(":", $line);
            $meminfo[$key] = trim($val);
        }
        return $meminfo;
    }


}
