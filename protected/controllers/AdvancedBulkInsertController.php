<?php

/**
 * Advance Bulk Insert Controller.
 *
 * Import contact via csv file.
 *
 * @author : Nasmy Ahamed
 * Date: 7/22/2019
 * @copyright Copyright &copy; Keeneye solutions (PVT) LTD
 */

namespace app\controllers;

use Keboola\Csv\CsvReader;
use Keboola\Csv\Exception;
use Yii;
use app\components\ToolKit;
use app\models\Activity;
use app\models\AdvanceBulkInsertStatus;
use app\models\CustomField;
use app\models\CustomType;
use app\models\FileCustomFieldPreview;
use app\models\FilePreview;
use app\models\Keyword;
use app\models\User;
use app\models\AdvanceBulkInsert;
use app\models\AdvanceBulkInsertSearch;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\web\UploadedFile;
use ZipArchive;


class AdvancedBulkInsertController extends Controller
{

    public $layout = 'column1';

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],

                ],
            ],
        ];
    }

    /**
     * Lists all advanceBulkInsert models.
     * @return mixed
     */
    public function actionAdmin()
    {
        $searchModel = new AdvanceBulkInsertSearch();
        $model = new AdvanceBulkInsert();
        $model->loadDefaultValues();

        Yii::$app->toolKit->addActivity(Yii::$app->user->id, Activity::ACT_ADVAN_BULK_PEOPLE, Yii::$app->session->get('teamId'));

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('advanced_bulk_admin', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new advanceBulkInsert model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     * @throws \Exception
     */
    public function actionCreate()
    {
        $isCsvExist = false;
        Yii::$app->toolKit->addActivity(Yii::$app->user->id, Activity::ACT_ADVAN_BULK_PEOPLE_CREATE, Yii::$app->session->get('teamId'));
        $userModel = new User();
        $userModel->scenario = User::SENARIO_ADVANCE_BULK;
        $hintData = AdvanceBulkInsert::getSampleFormat(); // Get sample format of csv file.

        // check the server max size exceeded to upload the content.
        if (isset($_SERVER["CONTENT_LENGTH"]) && ($_SERVER["CONTENT_LENGTH"] > ((int)ini_get('post_max_size') * 1024 * 1024))) {
            Yii::$app->user->setFlash('error', Yii::t('messages', 'Maximum upload file size exceeded. File should be less than 2MB.'));
            Yii::$app->appLog->writeLog("The maximum content length has been exceeded. CONTENT_LENGTH: " . $_SERVER["CONTENT_LENGTH"]);
        }

        if (Yii::$app->request->post('User')) {
            $userModel->attributes = $_POST['User'];
            $userModel->bulkFile = UploadedFile::getInstance($userModel, 'bulkFile'); // get information of the posted csv file.

            if ($userModel->validate()) {
                $randomString = rand();
                $fileName = $randomString . sprintf(Yii::$app->params['fileUpload']['people']['name'], Yii::$app
                        ->user->id, $userModel->bulkFile->getBaseName());

                $filePath = Yii::$app->params['fileUpload']['people']['path'] . $fileName;
                if ($userModel->bulkFile->saveAs($filePath)) {

                    $renameFile = $randomString . sprintf(Yii::$app->params['fileUpload']['people']['name'], Yii::$app->user->id, $userModel->bulkFile->getBaseName()) . '.csv';
                    $uploadPath = Yii::$app->params['fileUpload']['people']['path'];
                    $fileExtractPath = Yii::$app->params['fileUpload']['people']['path'] . $randomString . DIRECTORY_SEPARATOR;
                    $renameFilePath = $uploadPath . $renameFile;

                    Yii::$app->appLog->writeLog("renameFile:" . $renameFile);
                    Yii::$app->appLog->writeLog("uploadPath:" . $uploadPath);
                    Yii::$app->appLog->writeLog("renameFile uploadPath:" . $renameFilePath);
                    $zip = new ZipArchive();
                    $res = $zip->open($filePath);
                    if ($res === true) {
                        $allowedExtension = 'csv';
                        $zip->extractTo($fileExtractPath);
                        $zip->close();
                        $files = scandir($fileExtractPath); // scan the folder
                        foreach ($files as $file) { // for each file in the folder
                            $ext = pathinfo($file, PATHINFO_EXTENSION);
                            if ($ext === $allowedExtension) {
                                $isCsvExist = true;
                                // rename the first csv and stop
                                rename($fileExtractPath . $file, $renameFilePath);
                                break;
                            }
                        }


                    } else {
                        Yii::$app->session->setFlash('error', Yii::t('messages', 'Zip file cannot be opened. Please try again later.'));
                        return $this->redirect('advanced-bulk-insert/admin/');
                    }

                    if ($isCsvExist) {
                        $lineCount = 0;
                        $handle = fopen($renameFilePath, 'r');
                        while (!feof($handle)) {
                            $line = fgets($handle);
                            $lineCount++;
                        }
                        fclose($handle);
                        if ($lineCount > AdvanceBulkInsert::MAX_LINE_SIZE) {
                            Yii::$app->session->setFlash('error', Yii::t('messages', 'Maximum number of rows exceeded. Rows should be less then ') . AdvanceBulkInsert::MAX_LINE_SIZE . '.');
                        } elseif (AdvanceBulkInsert::getQueueCount() >= AdvanceBulkInsert::MAX_QUEUE_SIZE) {
                            Yii::$app->session->setFlash('error', Yii::t('messages', 'Maximum number of uploads exceeded. Please try again after the current importing process is completed.'));
                            return $this->redirect(['advanced-bulk-insert/admin']);
                        } else {
                            // save the record
                            $modelBulk = new AdvanceBulkInsert();
                            $modelBulk->source = $userModel->bulkFile->getBaseName();
                            $modelBulk->renameSource = $renameFile;
                            $modelBulk->countryCode = isset($userModel->countryCode) ? $userModel->countryCode : '';
                            $modelBulk->userType = isset($userModel->userType) ? $userModel->userType : User::NON_SUPPORTER;
                            $oldKeywords = isset($userModel->keywords) ? $userModel->keywords : '';
                            $modelBulk->keywords = empty($userModel->keywords) ? $oldKeywords : implode(",", $userModel->keywords);
                            $modelBulk->status = AdvanceBulkInsert::PENDING;
                            $modelBulk->size = $userModel->bulkFile->size;
                            $modelBulk->createdAt = User::convertSystemTime();
                            $modelBulk->save(false);
                            Yii::$app->session->setFlash('success', Yii::t('messages', 'Please select the appropriate value from the list for mapping.'));
                            return $this->redirect(array('advanced-bulk-insert/preview/', 'id' => $modelBulk->id));
                        }

                    } else {
                        Yii::$app->session->setFlash('error', Yii::t('messages', 'CSV file cannot be found. Please try uploading with a CSV file inside Zip.'));
                        return $this->redirect(['advanced-bulk-insert/admin']);
                    }
                }

            } else {
                Yii::$app->appLog->writeLog("File upload failed. Error:" . json_encode($userModel->getErrors()));
            }

        }

        $keywords = Keyword::getActiveKeywords();
        unset($keywords[Keyword::KEY_AUTO]);
        $tmpKeywords = array();
        foreach ($keywords as $behaviour => $behaviours) {
            $tmpKeywords[Keyword::getBehaviourOptions($behaviour)] = $behaviours;
        }

        return $this->render('advanced_bulk_add', ['model' => $userModel, 'keywords' => $tmpKeywords, 'hintData' => $hintData]);
    }

    /**
     * @param $array
     * @return array
     */
    public function getDuplicateArray($array)
    {
        $customDuplicateValues = array();
        foreach ($array as $key => $val) {
            if (isset($val['fieldValue']) && !empty($val['fieldValue'])) {
                $customDuplicateValues[$key] = $val['fieldValue'];
            }
        }

        $uniqueArray = array_unique($customDuplicateValues);
        $result = array_diff_assoc($customDuplicateValues, $uniqueArray);
        foreach ($uniqueArray as $key => $value) {
            if (in_array($value, $result)) {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    /**
     * @param $array
     * @return array
     */
    public function getCustomValuesArray($array)
    {
        $customDuplicateValues = [];
        foreach ($array as $key => $val) {
            if (isset($val['fieldValue']) && !empty($val['fieldValue'])) {
                $customDuplicateValues[$key] = $val['fieldValue'];
            }
        }
        return $customDuplicateValues;
    }

    /**
     * @param $array
     * @return array
     */
    public function getCustomLabelValuesArray($array)
    {
        $customDuplicateValues = array();
        foreach ($array as $key => $val) {
            if (isset($val['fieldValue']) && !ToolKit::isEmpty($val['fieldValue'])) {

                $model = CustomField::findOne($key);
                $customDuplicateValues[$model->fieldName] = $val['fieldValue'];
            }
        }
        return $customDuplicateValues;
    }

    /**
     * @param $id
     * @return string|Response
     */
    public function actionPreview($id)
    {
        $bulkModel = AdvanceBulkInsert::findOne($id);
        Yii::$app->appLog->writeLog("preview File:");
        if ($bulkModel->status == AdvanceBulkInsert::PENDING) {
            $file = Yii::$app->params['fileUpload']['people']['path'] . $bulkModel->renameSource;
            Yii::$app->appLog->writeLog("preview File:" . $file);
            $f = fopen($file, 'r');
            $firstLine = fgetcsv($f); //array
            fclose($f);
            $data = array();
            $data[''] = Yii::t('messages', '- Select -');
            foreach ($firstLine as $item) {
                $data[] = strlen($item) > 24 ? substr($item, 0, 24) . " ... " : $item;
            }
            Yii::$app->appLog->writeLog("preview File first line:" . json_encode($data));

            // Custom field data
            $customFields = FileCustomFieldPreview::getCustomData(CustomType::CF_PEOPLE, $id, CustomField::ACTION_EDIT, ToolKit::post('CustomValue'), CustomType::CF_SUB_PEOPLE_BULK_INSERT);
            $model = new FilePreview();
            if (isset($_POST['FilePreview'])) {
                $model->attributes = $_POST['FilePreview'];

                $valid = $model->validate();
                if (isset($_POST['CustomValue'])) {
                    $valid = CustomField::validatePreviewCustomFieldList($customFields, $this->getCustomValuesArray($_POST['CustomValue']), $this->getDuplicateArray($_POST['CustomValue'])) && $valid;

                }
                if ($valid) {
                    if (isset($_POST['CustomValue'])) {
                        $mappedColumns = array_merge($model->attributes, $this->getCustomLabelValuesArray($_POST['CustomValue']));

                    } else {
                        $mappedColumns = $model->attributes;
                    }

                    Yii::$app->appLog->writeLog("FilePreview saved success. People mapped columns:" . Json::encode($mappedColumns));
                    $bulkModel->status = AdvanceBulkInsert::getAvailableStatus();
                    $bulkModel->columnMap = json_encode($mappedColumns);
                    $bulkModel->save(false);
                    $data = AdvanceBulkInsert::find()->where(['status' => AdvanceBulkInsert::QUEUED, 'id' => $bulkModel->id])->one();;
                    Yii::$app->appLog->writeLog("Data " . serialize($data));
                    if (is_null($data)) {
                        Yii::$app->appLog->writeLog("CRON started: " . AdvanceBulkInsert::CRON_COMMAND . " bulk id:" . $bulkModel->id);
                        $bulkModel->runCommand(AdvanceBulkInsert::CRON_COMMAND, $bulkModel->id);
                    }
                    Yii::$app->session->setFlash('success', Yii::t('messages', 'Queued for importing. Refresh to check.'));
                    return $this->redirect(array('advanced-bulk-insert/admin'));
                } else {
                    Yii::$app->session->setFlash('error', Yii::t('messages', 'Please fix the errors to continue'));
                    Yii::$app->appLog->writeLog("FilePreview save failed. Error:" . $model->getLastError());
                }
            }

            return $this->render('preview', array('model' => $model, 'customFields' => $customFields, 'data' => $data));
        } else {
            Yii::$app->session->setFlash('error', Yii::t('messages', 'Contacts importing is not pending.'));
            return $this->redirect(array('advanced-bulk-insert/admin'));
        }
    }


    /**
     * Displays a single advanceBulkInsert model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }


    /**
     * Download bulk delete status file
     */
    public function actionDownloadStatus()
    {
        if (isset($_GET['status_file'])) {
            $path = Yii::$app->params['fileUpload']['error']['path'] . $_GET['status_file'];
            return Yii::$app->response->sendFile($path, $_GET['status_file']);
        }

        if (isset($_GET['reWrite_csv_file'])) {
            return Yii::$app->response->sendFile($_GET['reWrite_csv_file']);
        }
    }

    /**
     * @return string
     * @throws Exception
     */
    public function actionReWrite(): string
    {
        $isCsvExist = false;
        $download = [];
        $advanceBulkModel = new AdvanceBulkInsert();
        $advanceBulkModel->scenario = AdvanceBulkInsert::SCENARIO_FILE_TRANSFORM;
        $fileValidateError = false;

        if (Yii::$app->request->post()) {
            $targetDir = Yii::$app->params['fileTransformerPath'];
            $date = date('m-d-YHis', time());
            $advanceBulkModel->fileToUpload = UploadedFile::getInstance($advanceBulkModel, 'fileToUpload');
            $fileName = $date . $advanceBulkModel->fileToUpload->getBaseName() . '.' . $advanceBulkModel->fileToUpload->extension;
            $filePath = $targetDir . $fileName;
            $fileType = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $fileSize = $advanceBulkModel->fileToUpload->size;

            if ($fileType != "csv" && $fileType != "CSV") {
                Yii::$app->session->setFlash("error", "File not allowed");
                $fileValidateError = true;
            }

            if ($fileSize > 70000) {
                Yii::$app->session->setFlash("error", "File not allowed");
                $fileValidateError = true;
            }
            if (!$fileValidateError) {
                if ($advanceBulkModel->fileToUpload->saveAs($filePath)) {
                    $results = $advanceBulkModel->reWriteExcel($fileName, $targetDir);
                    if ($results === false) {
                        Yii::$app->session->setFlash('error', Yii::t('messages', 'The allowed maximum 25000 row exceeded'));
                    } elseif ($results == 'error') {
                        Yii::$app->session->setFlash('error', Yii::t('messages', 'The file is contains errors'));
                    } else {
                        $download = $results;
                    }
                } else {
                    Yii::$app->session->setFlash('error', Yii::t('messages', 'File Uploaded Failed'));
                }
            }
        }
        return $this->render('advanced_bulk_rewrite', ['model' => $advanceBulkModel, 'download' => $download]);
    }

    /**
     * @param $id
     * @return Response
     */
    public function actionStop($id)
    {
        $model = AdvanceBulkInsert::findOne($id);

        if ($model->status == AdvanceBulkInsert::IN_PROGRESS) {
            $model->status = AdvanceBulkInsert::CANCELED;
            $model->save(false);

            Yii::$app->toolKit->setAjaxFlash('success', Yii::t('messages', 'Contacts importing stopped.'));

            return $this->redirect(['advanced-bulk-insert/admin/']);
        } else {
            Yii::$app->toolKit->setAjaxFlash('error', Yii::t('messages', 'Contacts importing completed, cannot be stopped.'));
            return $this->redirect(['advanced-bulk-insert/admin/']);
        }

    }


    /**
     * Deletes an existing advanceBulkInsert model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws \Throwable
     */

    public function actionDelete($id)
    {
        $model = AdvanceBulkInsert::findOne($id);

        if ($model->status == AdvanceBulkInsert::ERROR) {
            $fileName = $model->errors;
        } else {
            $fileName = $model->renameSource;
        }

        $uploadPath = Yii::$app->params['fileUpload']['people']['path'];

        if ($model->delete()) {
            AdvanceBulkInsertStatus::deleteAll('bulkId =:bulkId', array(':bulkId' => $model->id));
            if (file_exists($uploadPath . substr($fileName, 0, -4))) {
                unlink($uploadPath . $fileName); // csv file
                unlink($uploadPath . substr($fileName, 0, -4)); // zip file
            }
            return Yii::$app->toolKit->setAjaxFlash('success', Yii::t('messages', 'Contacts importing deleted.'));
        } else {
            return Yii::$app->toolKit->setAjaxFlash('success', Yii::t('messages', 'Contacts importing delete failed.'));
        }
    }


    /**
     * Finds the advanceBulkInsert model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return advanceBulkInsert the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = advanceBulkInsert::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
