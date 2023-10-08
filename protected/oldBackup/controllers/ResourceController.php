<?php

namespace app\controllers;

use app\models\Activity;
use app\models\User;
use borales\extensions\phoneInput\PhoneInputBehavior;
use Exception;
use Yii;
use app\models\Resource;
use app\models\ResourceSearch;
use yii\base\ErrorException;
use yii\base\ExitException;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;
use yii\widgets\ActiveForm;
use app\controllers\WebUserController;

/**
 * ResourceController implements the CRUD actions for Resource model.
 */
class ResourceController extends WebUserController
{
    public $layout = 'column1';

    /**
     * {@inheritdoc}
     */

    public function behaviors()
    {
        return [
            [
                'class' => PhoneInputBehavior::className(),
                'countryCodeAttribute' => 'countryCode',
            ],
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
     * @return string  allowed actions
     */
    public function allowedActions()
    {
        $allowed = array();

        return implode(',', $allowed);
    }

    /**
     * Lists all Resource models.
     * @return mixed
     */
    public function actionAdmin()
    {
        $searchModel = new ResourceSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        Yii::$app->toolKit->setResourceInfo();


        // Add activity
        $params = array(
            'section' => Activity::ACT_SECTION_RESOURCE
        );
        Yii::$app->toolKit->addActivity(
            Yii::$app->user->id,
            Activity::ACT_SEARCH_RESOURCE,
            Yii::$app->session->get('teamId')
        );
        return $this->render('admin', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'attributeLabels' => $searchModel->attributeLabels(),
        ]);
    }

    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     * @return string
     * @throws HttpException
     * @throws HttpException
     */
    public function actionView($id, $dialog = true)
    {

        $model = $this->loadModel($id);

        $showBreadCrumb = true;

        if ($dialog) {
            $showBreadCrumb = false;
            $this->layout = 'dialog';
        } else {
            if (Resource::APPROVED != $model->status) {
                Yii::$app->session->setFlash('warning', 'Sorry! still content is not approved.');
                $this->redirect(array('admin'));
            }
        }

        Yii::$app->toolKit->setResourceInfo();
        $resourceUrl = Yii::$app->urlManager->baseUrl . '/' . Yii::$app->toolKit->resourcePathRelative;

        $url = '';
        if ($model->type == Resource::VIDEO) {
            $url = Yii::$app->toolKit->getVideoEmbedUrl($model->fileName);
        }

        $shareUrl = Url::to('resource/view-shared', array('id' => $id));

        // Add activity
        $params = array(
            'title' => $model->title,
            'link' => $this->getResourceViewUrl($id),
            'section' => Activity::ACT_SECTION_RESOURCE
        );

        Yii::$app->toolKit->addActivity(
            Yii::$app->user->id,
            Activity::ACT_VIEW_RESOURCE,
            Yii::$app->session->get('teamId'),
            json_encode($params)
        );
        // End

        return $this->render('view', array(
            'model' => $model,
            'resourceUrl' => $resourceUrl,
            'url' => $url,
            'shareUrl' => $shareUrl,
            'showBreadCrumb' => $showBreadCrumb
        ));
    }


    /**
     * Creates a new Resource model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     * @throws Exception
     */
    public function actionCreate()
    {
        $model = new Resource();
        $model->scenario = 'create';

        if ($model->load(Yii::$app->request->post())) {
            $Resource = $_POST['Resource'];
            $model->attributes = $Resource;

            $model->file = UploadedFile::getInstance($model, 'file');

            if (($Resource['type'] == Resource::DOCUMENT || $model['type'] == Resource::IMAGE) && null != $model->file) {
                $model->size = $model->file->size;
                $model->fileName = $model->file->name;
            } else if ($Resource['type'] == Resource::VIDEO) {
                $model->size = 0;
                $model->fileName = $Resource['url'];
            }

            $model->status = Resource::PENDING_APPROVAL;
            $model->createdAt = User::convertSystemTime();
            $model->createdBy = Yii::$app->user->id;
            $model->updatedBy = 0; // Null
            $model->updatedAt = date("Y-m-d H:i:s"); // Null

            if ($model->validate()) {
                try {
                    $attributes = json_encode($model->attributes);
                    if ($model->save(false)) {

                        if (null != $model->file) {
                            Yii::$app->toolKit->setResourceInfo();
                            $model->file->saveAs(Yii::$app->toolKit->resourcePathRelative . $model->fileName);
                        }

                        Yii::$app->appLog->writeLog("Resource created. Data:{$attributes}");
                        Yii::$app->session->setFlash('success', Yii::t('messages', 'Resource created. Please wait, your resource will be modereated shortly.'));
                        $getResourceViewUrl = Yii::$app->urlManager->createUrl(['resource/view', 'id' => $model->id, 'dialog' => "false"]);
                        // Add activity
                        $params = array(
                            'title' => $model->title,
                            'link' => $this->getResourceViewUrl($model->id),
                            'section' => Activity::ACT_SECTION_RESOURCE
                        );
                        Yii::$app->toolKit->addActivity(
                            Yii::$app->user->id,
                            Activity::ACT_CRT_RESOURCE,
                            Yii::$app->session->get('teamId'),
                            json_encode($params)
                        );

                        // End
                        return $this->redirect('admin');
                    } else {
                        Yii::$app->appLog->writeLog("Resource create failed. Data:{$attributes}");
                        Yii::$app->session->setFlash('success', Yii::t('messages', 'Resource create failed.'));
                    }
                } catch (Exception $e) {
                    Yii::$app->appLog->writeLog("Resource create failed. Data:{$attributes}, Errors:{$e->getMessage()}");
                    Yii::$app->session->setFlash('success', Yii::t('messages', "{$model->errors}"));
                }
            } else {
                Yii::$app->appLog->writeLog("Resource create failed. Validation errors:" . json_encode($model->errors));
            }

        }
        return $this->render('create', [
            'model' => $model,
        ]);

    }


    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     * @return string
     * @throws HttpException
     * @throws HttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->loadModel($id);
        $model->scenario = 'update';
        $this->layout = 'dialog';

        $model->prvResType = $model->type;

        if (isset($_POST['Resource'])) {
            $model->attributes = $_POST['Resource'];

            $model->file = UploadedFile::getInstance($model, 'file');

            if (($model->type == Resource::DOCUMENT || $model->type == Resource::IMAGE) && null != $model->file) {
                $model->size = $model->file->size;
                $model->fileName = $model->file->name;
            } else if ($model->type == Resource::VIDEO) {
                $model->size = 0;
                $model->fileName = $model->url;
            }

            $model->status = Resource::PENDING_APPROVAL;
            $model->updatedAt = User::convertSystemTime();
            $model->updatedBy = strval(Yii::$app->user->id);

            if ($model->validate()) {
                try {
                    $attributes = json_encode($model->attributes);
                    if ($model->save(false)) {
                        if (!empty($model->file)) {
                            Yii::$app->toolKit->setResourceInfo();
                            $model->file->saveAs(Yii::$app->toolKit->resourcePathRelative . $model->fileName);
                        }
                        Yii::error("Resource updated. Data:{$attributes}");
                        Yii::$app->session->setFlash('success', Yii::t('messages', 'Resource updated. Please wait, your resource will be modereated shortly.'));

                        // Add activity
                        $params = array(
                            'title' => $model->title,
                            'link' => $this->getResourceViewUrl($id),
                            'section' => Activity::ACT_SECTION_RESOURCE
                        );
                        Yii::$app->toolKit->addActivity(
                            Yii::$app->user->id,
                            Activity::ACT_UPDATE_RESOURCE,
                            Yii::$app->session->get('teamId'),
                            json_encode($params)
                        );
                        // End
                    } else {
                        Yii::error("Resource update failed. Data:{$attributes}");
                        Yii::$app->session->setFlash('success', Yii::t('messages', 'Resource update failed.'));
                    }
                } catch (Exception $e) {
                    Yii::$app->appLog->writeLog("Resource update failed. Data:{$attributes}, Errors:{$e->getMessage()}");
                    Yii::$app->session->setFlash('error', Yii::t('messages', 'Resource update failed.'));
                }
            } else {
                Yii::$app->appLog->writeLog("Resource create failed. Validation errors:" . json_encode($model->errors));
            }
        }

        if ($model->type == Resource::VIDEO) {
            $model->url = $model->fileName;
        }

        return $this->render('update', array(
            'model' => $model,
        ));
    }


    /**
     * Delete resource.
     * @param integer $id Resource id
     * @throws HttpException
     */
    public function actionDelete($id)
    {
        $model = $this->loadModel($id);

        if (null != $model) {
            $model->status = Resource::DELETED;
            try {
                if ($model->save(false)) {
                    Yii::error("Resource deleted. Resource id:{$id},fileName:{$model->fileName}");
                    Yii::$app->toolKit->setAjaxFlash('success', Yii::t('messages', 'Resource deleted'));
                } else {
                    Yii::error("Resource delete failed. Resource id:{$id},fileName:{$model->fileName}");
                    Yii::$app->toolKit->setAjaxFlash('error', Yii::t('messages', 'Resource delete failed'));
                }
            } catch (Exception $e) {
                Yii::error("Resource delete failed. Resource id:{$id},fileName:{$model->fileName},Error:{$e->getMessage()}");
                Yii::$app->toolKit->setAjaxFlash('error', Yii::t('messages', 'Resource delete failed'));
            }
        }
    }

    /**
     * Approve resource.
     * @param integer $id Resource id
     * @throws HttpException
     */
    public function actionApprove($id)
    {
        $model = $this->loadModel($id);

        if (null != $model) {
            $model->status = Resource::APPROVED;
            try {
                if ($model->save(false)) {
                    $params = array('title' => $model->title, 'link' => $this->getResourceViewUrl($id));
                    Yii::$app->toolKit->addActivity(Yii::$app->user->id, Activity::ACT_VIEW_RESOURCE, Yii::$app->session->get('teamId'), json_encode($params));

                    Yii::error("Resource approved. Resource id:{$id},fileName:{$model->fileName}");
                    Yii::$app->toolKit->setAjaxFlash('success', Yii::t('messages', 'Resource approved'));
                } else {
                    Yii::error("Resource approval failed. Resource id:{$id},fileName:{$model->fileName}");
                    Yii::$app->toolKit->setAjaxFlash('error', Yii::t('messages', 'Resource approve failed'));
                }
            } catch (Exception $e) {
                Yii::error("Resource approval failed. Resource id:{$id},fileName:{$model->fileName},Error:{$e->getMessage()}");
                Yii::$app->toolKit->setAjaxFlash('error', Yii::t('messages', 'Resource approve failed'));
            }
        }
    }


    /**
     * Reject resource.
     * @param integer $id Resource id
     * @throws HttpException
     */
    public function actionReject($id)
    {
        $model = $this->loadModel($id);

        if (null != $model) {
            $model->status = Resource::REJECTED;
            try {
                if ($model->save(false)) {
                    Yii::error("Resource rejected. Resource id:{$id},fileName:{$model->fileName}");
                    Yii::$app->toolKit->setAjaxFlash('success', Yii::t('messages', 'Resource rejected'));
                } else {
                    Yii::error("Resource reject failed. Resource id:{$id},fileName:{$model->fileName}");
                    Yii::$app->toolKit->setAjaxFlash('error', Yii::t('messages', 'Resource reject failed'));
                }
            } catch (Exception $e) {
                Yii::error("Resource reject failed. Resource id:{$id},fileName:{$model->fileName},Error:{$e->getMessage()}");
                Yii::$app->toolKit->setAjaxFlash('error', Yii::t('messages', 'Resource reject failed'));
            }
        }
    }

    /**
     * Create resource viewing url.
     * @param integer $id Resource id
     * @return string
     */
    private function getResourceViewUrl($id)
    {
        return Url::to(["/resource/view/", 'id' => $id, "dialog" => "false"]);
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer the ID of the model to be loaded
     * @return Resource|null
     * @throws HttpException
     */
    public function loadModel($id)
    {
        $model = Resource::findOne(['id' => $id]);
        if ($model === null)
            throw new HttpException(404, 'The requested page does not exist.');
        return $model;
    }

    /**
     * Performs the AJAX validation.
     * @param $model
     * @throws ExitException
     */
    protected function performAjaxValidation($model)
    {
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'resource-form') {
            echo ActiveForm::validate($model);
            Yii::$app->end();
        }
    }

    /**
     * Download file.
     * @param string $filePath Absolute file path
     */
    public function actionDownload($filePath)
    {
        Yii::$app->appLog->writeLog("Resource downloaded. Name:" . pathinfo($filePath, PATHINFO_BASENAME));
        // Must be fresh start
        if (headers_sent())
            die('Headers Sent');

        // Required for some browsers
        if (ini_get('zlib.output_compression'))
            ini_set('zlib.output_compression', 'Off');

        // File Exists?
        if (file_exists($filePath)) {
            // return Yii::$app->response->sendFile($filePath);
            // Parse Info / Get Extension
            $fsize = filesize($filePath);
            $path_parts = pathinfo($filePath);
            $ext = strtolower($path_parts["extension"]);

            // Determine Content Type
            switch ($ext) {
                case "pdf":
                    $ctype = "application/pdf";
                    break;
                case "exe":
                    $ctype = "application/octet-stream";
                    break;
                case "zip":
                    $ctype = "application/zip";
                    break;
                case "doc":
                    $ctype = "application/msword";
                    break;
                case "xls":
                    $ctype = "application/vnd.ms-excel";
                    break;
                case "ppt":
                    $ctype = "application/vnd.ms-powerpoint";
                    break;
                case "gif":
                    $ctype = "image/gif";
                    break;
                case "png":
                    $ctype = "image/png";
                    break;
                case "jpeg":
                case "jpg":
                    $ctype = "image/jpg";
                    break;
                default:
                    $ctype = "application/force-download";
            }

            header("Pragma: public"); // required
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: private", false); // required for certain browsers
            header("Content-Type: {$ctype}");
            header("Content-Disposition: attachment; filename=\"" . basename($filePath) . "\";");
            header("Content-Transfer-Encoding: binary");
            header("Content-Length: " . $fsize);
            ob_clean();
            flush();
            readfile($filePath);
        }
    }


    /**
     * Finds the Resource model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Resource the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Resource::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
