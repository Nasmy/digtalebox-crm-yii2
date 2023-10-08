<?php

namespace app\controllers;

use app\components\CUploadedFile;
use app\models\User;
use Yii;
use app\models\CandidateInfo;
use app\models\CandidateInfoSearch;
use yii\data\ArrayDataProvider;
use yii\db\Expression;
use yii\filters\AccessControl;
use yii\helpers\Html;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\widgets\ActiveForm;
use \app\controllers\WebUserController;

/**
 * CandidateInfoController implements the CRUD actions for CandidateInfo model.
 */
class CandidateInfoController extends WebUserController
{
    public $layout='/column1';
    /**
     * {@inheritdoc}
     */

    public function behaviors()
    {
        return [
            'corsFilter' => [
                'class' => \yii\filters\Cors::className(),
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
     * {@inheritdoc}
     */
    public function beforeAction($action) {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    /**
     * @return array
     */
    public function allowed()
    {
        return [
            'CandidateInfo.CheckCandidateInfo',
            'CandidateInfo.Create',
            'CandidateInfo.EditProfImage',
            'CandidateInfo.Upload',
            'CandidateInfo.CropImage',
            'CandidateInfo.ApplyTheme',
            'CandidateInfo.UpdateImage',
            'CandidateInfo.DeleteImage',
            'CandidateInfo.ImageSave',
        ];
    }
    /**
     * Lists all CandidateInfo models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CandidateInfoSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single CandidateInfo model.
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
     * Creates a new CandidateInfo model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new CandidateInfo();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * @return \yii\web\Response
     */
    public function actionCheckCandidateInfo()
    {
        $model = CandidateInfo::find();

        if (null == $model) {
            return $this->redirect(['candidate-info/create']);
        } else {
            return $this->redirect(['candidate-info/update']);
        }
    }

    /**
     * Updates an existing CandidateInfo model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate()
    {
            $modelCanditeInfoId = CandidateInfo::find()->one();
            $model = $this->findModel($modelCanditeInfoId->id);
            $model->scenario = 'update';

            $model->regUrl = Yii::$app->urlManager->createAbsoluteUrl('signup/step1');
            $this->performAjaxValidation($model);
            Yii::$app->toolKit->setResourceInfo();
            if (isset($_POST['setDefault'])) {
                try {
                    $path = Yii::$app->toolKit->resourcePathAbsolute;
                    $imagePath = "{$path}$model->volunteerBgImageName";
                    $model->volunteerBgImageName = new Expression('null');
                    $model->volunteerBgImageFile = UploadedFile::getInstance($model, 'volunteerBgImageFile');
                    if ($model->save(false)) {
                        if (file_exists($imagePath)) {
                            unlink($imagePath); // when setting default, deleting old image if exists.
                        }
                         Yii::$app->appLog->writeLog("Volunteer background image reset.");
                        Yii::$app->session->setFlash('success', Yii::t('messages', 'Volunteer background image reset.'));
                    } else {
                         Yii::$app->appLog->writeLog("Volunteer background image reset failed.");
                        Yii::$app->session->setFlash('error', Yii::t('messages', 'Volunteer background reset failed.'));
                    }
                } catch (\Exception $e) {
                     Yii::$app->appLog->writeLog("Volunteer background image reset failed. Error:{$e->getMessage()}");
                    Yii::$app->session->setFlash('error', Yii::t('messages', 'Volunteer background image reset failed.'));
                }
            } else if (isset($_POST['CandidateInfo'])) {
                $modelOld = clone $model;
                $model->attributes = $_POST['CandidateInfo'];
                $model->volunteerBgImageFile = UploadedFile::getInstance($model, 'volunteerBgImageFile');
                $model->signupFields = isset($_POST['CandidateInfo']['signupFields']) ? implode(",", $_POST['CandidateInfo']['signupFields']) : null;
                if($model->validate()) {
                    if (null != $model->volunteerBgImageFile) {
                        $model->volunteerBgImageName = $model->volunteerBgImageFile->name;
                    }
                    if ($model->save(false)) {
                        if(null != $model->volunteerBgImageFile) {
                            Yii::$app->toolKit->setResourceInfo();
                            @mkdir(Yii::$app->toolKit->resourcePathRelative);
                            $model->volunteerBgImageFile->saveAs(Yii::$app->toolKit->resourcePathRelative . $model->volunteerBgImageName);
                        }
                        if($model->profImageName != $modelOld->profImageName) {
                            @copy(Yii::$app->params['webTempRelPath'] . $model->profImageName, Yii::$app->toolKit->resourcePathRelative . $model->profImageName);
                        }
                        Yii::$app->session->setFlash('success', Yii::t('messages','Organization information updated'));
                        Yii::$app->appLog->writeLog("Candidate info updated.Data:" . json_encode($model->attributes));
                    } else {
                         Yii::$app->session->setFlash('success', Yii::t('messages','Organization information updated'));
                        Yii::$app->appLog->writeLog("Candidate info updated.Data:" . json_encode($model->attributes));
                    }
                } else {
                    Yii::$app->appLog->writeLog("Candidate info updated failed.Validation errors:" . json_encode($model->errors));
                }
            }

            $model->signupFields = null != $model->signupFields ? explode(",", $model->signupFields) : null;

            if (null == $model->profImageName) {
                $profImageUrl = "http://placehold.it/{$model->imgWidth}x{$model->imgWidth}";
            } else {
                $profImageUrl = Yii::$app->urlManager->baseUrl . '/' . Yii::$app->toolKit->resourcePathRelative . $model->profImageName;
            }

            $altPic = Yii::$app->toolKit->getAltProfPic();
            $profImage = Html::img($profImageUrl, array('width' => 300, 'height' => 300));
            return $this->render('update',array(
                'model'=>$model,
                'customSinupFields' => User::getCustomSignupFields(),
                'profImageUrl' => $profImageUrl,
                'profImage' => $profImage,
            ));
    }

    public function actionImageSave() {
          $modelCanditeInfoId = CandidateInfo::find()->one();
          $model = $this->findModel($modelCanditeInfoId->id);
          $this->performAjaxValidation($model);
          $model->profImageName = Yii::$app->params['clientProfThumbName']. '_' . rand(0,9999) . '.png';
          if($model->validate()) {
              try {
                  if($model->save()) {
                      Yii::$app->toolKit->setResourceInfo();
                      $filePath = Yii::$app->toolKit->resourcePathRelative.$model->profImageName;
                      if(file_exists($filePath)) {
                          unlink($filePath);
                      }
                      define('UPLOAD_DIR', Yii::$app->toolKit->resourcePathRelative);
                      $img = $_POST['imgBase64'];
                      $img = str_replace('data:image/png;base64,', '', $img);
                      $img = str_replace(' ', '+', $img);
                      $data = base64_decode($img);
                      $file = UPLOAD_DIR . $model->profImageName;
                      $success = file_put_contents($file, $data);
                      print $success ? 1 : 0;
                  }
              } catch (Exception $e) {

              }
          }
    }

    /**
     * Edit candidate info profile image
     */
    public function actionEditProfImage()
    {
        $this->layout='dialog';
        $model = new CandidateInfo();

        return $this->render('edit_prof_image',array(
            'model'=>$model,
        ));
    }

    /**
     * Upload new front image
     */
    public function actionUploadImage()
    {
        $model = CandidateInfo::find()->one();
        $model->scenario = 'uploadImage';
        $this->layout = 'dialog';

        $time = time();
        $frontImage = $_POST['imgName'];
        $ext = pathinfo($frontImage, PATHINFO_EXTENSION);
        $frontImage = $time.".".$ext;

        //  $model->frontImages = $frontImage; use if neeeded in future

        Yii::$app->toolKit->setResourceInfo();

        Yii::error("Candidate Info details. data:" . json_encode($model->attributes));
        define('UPLOAD_DIR', Yii::$app->toolKit->resourcePathRelative);
        $img = $_POST['imgBase64'];
        $img = str_replace('data:image/png;base64,', '', $img);
        $img = str_replace(' ', '+', $img);
        $data = base64_decode($img);
        $validate = $model->validateImageDimesions($data);
        if ($model->validate()) { // && validate[0] removed
            $file = UPLOAD_DIR . $frontImage;
            $success = file_put_contents($file, $data);
            if ($success) {
                $images = json_decode($model->frontImages, true);
                 $images[] = array(
                    'id' => $time,
                    'name' => $frontImage,
                    'link' => '',
                    'isDefault' => 0
                );
                 $model->frontImages = json_encode($images);
                try {
                    $model->save(false);
                    Yii::error("Front image uploaded.");
                    Yii::$app->session->setFlash('success', Yii::t('messages', 'Image uploaded'));
                    $closeDialog = ($success) ? 1 : 0;
                } catch (Exception $e) {
                    Yii::error("Front image upload failed. Error:{$e->getMessage()}");
                    Yii::$app->session->setFlash('error', Yii::t('messages', 'Image upload failed'));
                     $closeDialog = 0;
                }
            } else {
                Yii::$app->session->setFlash('error', Yii::t('messages', 'Image upload failed'));
                $closeDialog = 0;
            }
        } else {
            Yii::$app->session->setFlash('error', 'error');
            if (!$validate[0]){
                Yii::$app->session->setFlash('error', $validate[1]);
            } else {
                Yii::error("Front image upload failed. Validation errors:" . json_encode($model->errors));
            }
            $closeDialog = 0;
        }
        print $closeDialog;
    }


    /**
     * Update image
     */
    public function actionUpdateImage()
    {
        $model = CandidateInfo::find()->one();
        $model->scenario = 'uploadImage';
        $this->layout = 'dialog';
        $id = $_POST['imgId'];
        $frontImage = $_POST['imgName'];
        $ext = pathinfo($frontImage, PATHINFO_EXTENSION);
        $frontImage = $id.".".$ext;
        //  $model->frontImages = $frontImage; use if neeeded in future

        Yii::$app->toolKit->setResourceInfo();
        Yii::error("Candidate Info details. data:" . json_encode($model->attributes));
        define('UPLOAD_DIR', Yii::$app->toolKit->resourcePathRelative);
        $img = $_POST['imgBase64'];
        $img = str_replace('data:image/png;base64,', '', $img);
        $img = str_replace(' ', '+', $img);
        $data = base64_decode($img);
        $validate = $model->validateImageDimesions($data);
        if ($model->validate()) { // && validate[0] removed
            $file = UPLOAD_DIR . $frontImage;
            $success = file_put_contents($file, $data);
            $images = json_decode($model->frontImages, true);

            $index = 0;
            foreach ($images as $image) {
                if ($image['id'] == $id) {
                    $images[$index] = array(
                        'id' => $id,
                        'name' => $frontImage,
                        'link' => '',
                        'isDefault' => 0
                    );
                }
                $index++;
            }
            $model->frontImages = json_encode($images);
             try {
                $model->save(false);
                Yii::error("Front image uploaded.");
                Yii::$app->session->setFlash('success', Yii::t('messages', 'Image uploaded'));
                $closeDialog = ($success) ? 1 : 0;
            } catch (Exception $e) {
                Yii::error("Front image upload failed. Error:{$e->getMessage()}");
                Yii::$app->session->setFlash('error', Yii::t('messages', 'Image upload failed'));
                $closeDialog = 0;
            }
        } else {
            if (!$validate[0]) {
                Yii::$app->session->setFlash('error', $validate[1]);
            } else {
                Yii::error("Front image upload failed. Validation errors:" . json_encode($model->errors));
            }
            $closeDialog = 0;
        }

        print $closeDialog;

    }


    /**
     * Deletes an existing CandidateInfo model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Upload image
     */
    public function actionUpload() {

    }

    /**
     * Remove front image
     */
    public function actionDeleteImage($id)
    {
        $model = CandidateInfo::find($id)->one();

        $images = json_decode($model->frontImages, true);
        $newImageSet = array();
        if (!empty($images)) {
            foreach ($images as $image) {
                if ($image['id'] == $id) {
                    Yii::$app->toolKit->setResourceInfo();
                    $filePath = Yii::$app->toolKit->resourcePathRelative.$image['name'];
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                    continue;
                }
                $newImageSet[] = $image;
            }
        }

        try {
            $model->frontImages = json_encode($newImageSet);
            $model->save(false);
            Yii::error("Front image removed.");
            Yii::$app->session->setFlash('success', Yii::t('messages', 'Image removed'));
        } catch (Exception $e) {
            Yii::error("Front image remove failed. Error:{$e->getMessage()}");
            Yii::$app->session->setFlash('error', Yii::t('messages', 'Image remove failed'));
        }
        Yii::$app->toolKit->setResourceInfo();

       return $this->redirect(array('manage-images'));
    }


    /**
     * Finds the CandidateInfo model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return CandidateInfo the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = CandidateInfo::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * Manage front page images
     */
    public function actionManageImages()
    {
        $model = CandidateInfo::find()->one();
        Yii::$app->toolKit->setResourceInfo();
        $frontImages = json_decode($model->frontImages, true);

//        $dataProvider = new ActiveDataProvider([
//            'query' => $frontImages,
//            'pagination' => [
//                'pageSize' => 20,
//            ],
//        ]);

        $dataProvider = new ArrayDataProvider([
            'allModels' => $frontImages,
            'sort' => [
                'attributes' => ['id', 'username', 'email'],
            ],
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

//        $Dataprovider = $provider->getModels();

        return $this->render('imageSettings',array(
            'model'=>$model,
            'dataProvider'=>$dataProvider
        ));
    }

    /**
     * Update portal texts. Such as about us message, portal name etc..
     */
    public function actionUpdateTexts()
    {
        $model = CandidateInfo::find()->one();
        $model->scenario = 'updateText';
        $this->performAjaxValidation($model);
        if (isset($_POST['CandidateInfo'])) {
            $model->attributes = $_POST['CandidateInfo'];
            if ($model->validate()) {
                try {
                    $model->save(false);
                    Yii::$app->session->set('headerText', $model->headerText);
                    Yii::error("Texts updated");
                    Yii::$app->session->setFlash('success', Yii::t('messages', 'Portal texts updated'));
                } catch (Exception $e) {
                    Yii::error("Portal texts update failed. Error:{$e->getMessage()}");
                    Yii::$app->session->setFlash('error', Yii::t('messages', 'Texts updated'));
                }
            } else {
                Yii::error("Portal texts update failed. Validation errors:" . json_encode($model->errors));
            }
        }

       return $this->render('updateText',array(
            'model'=>$model
        ));
    }

    /**
     * Set new theme
     */
    public function actionTheme()
    {
        $model = CandidateInfo::find()->one();

       return $this->render('themeSelect',array(
            'model'=>$model,
        ));
    }

    /**
     * Change theme settings.
     * @param integer $id Theme id as in the config file
     * @return \yii\web\Response
     */

    public function actionApplyTheme($id)
    {
        $model = CandidateInfo::find()->one();
        $model->themeStyle = $id;
        try {
            if ($model->save(false)) {
                unset(Yii::$app->session['themeStyle']);// After unset reload the new theme
                Yii::error("Theme settings saved.");
                Yii::$app->session->setFlash('success', Yii::t('messages', 'Theme applied'));
            } else {
                Yii::error("Theme settings save failed.");
                Yii::$app->session->setFlash('error', Yii::t('messages', 'Theme apply failed'));
            }
        } catch (Exception $e) {
            Yii::error("Theme settings save failed. Error:{$e->getMessage()}");
            Yii::$app->session->setFlash('error', Yii::t('messages', 'Theme apply failed'));
        }

       return $this->redirect(array('theme'));
    }

    /**
     * Change background image
     */
    public function actionChangeBgImage()
    {
        $model = CandidateInfo::find()->one();
        $model->scenario = 'uploadBgImage';
        $closeDialog = 0;
        Yii::$app->toolKit->setResourceInfo();
        $bgImage = (null == $model->bgImage)? null : "/".Yii::$app->toolKit->resourcePathRelative.$model->bgImage."?buster=".uniqid();
        if (isset($_POST['setDefault'])) {
            try {
                $model->bgImage = new \yii\db\Expression('NULL');;
                if ($model->save(false)) {
                    unset(Yii::$app->session['bgImage']);// After unset reload the new background image
                    Yii::error("Background image reset.");
                    Yii::$app->session->setFlash('success', Yii::t('messages', 'Background image reset'));
                } else {
                    Yii::error("Background image reset failed.");
                    Yii::$app->session->setFlash('error', Yii::t('messages', 'Background reset failed'));
                }
            } catch (Exception $e) {
                Yii::error("Background image reset failed. Error:{$e->getMessage()}");
                Yii::$app->session->setFlash('error', Yii::t('messages', 'Background image reset failed'));
            }
            return $this->redirect('change-bg-image');
        } else if (isset($_POST['CandidateInfo'])) {

            $model->bgImageFile = $fileName = $_POST['imgName'];
            Yii::error("Candidate Info details. data:" . json_encode($model->attributes));
            define('UPLOAD_DIR', Yii::$app->toolKit->resourcePathRelative);
            $img = $_POST['imgBase64'];
            $img = str_replace('data:image/png;base64,', '', $img);
            $img = str_replace(' ', '+', $img);
            $data = base64_decode($img);
            $validate = $model->validateBgImageDimesions($data);

            try {
                if ($model->validate() && $validate[0]) {
                    $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                    $model->bgImage = 'bgImg.'.$ext;
                    $file = UPLOAD_DIR . $model->bgImage;
                    $success = file_put_contents($file, $data);

                    if($success){
                        if ($model->save(false)) {
                            unset(Yii::$app->session['bgImage']);// After unset reload the new background image
                            Yii::error("Background image saved.");
                            Yii::$app->session->setFlash('success', Yii::t('messages', 'Background image uploaded'));
                            $closeDialog = 1;
                        } else {
                            Yii::error("Background image save failed.");
                            Yii::$app->session->setFlash('error', Yii::t('messages', 'Background image upload failed'));
                            $closeDialog = 0;
                        }
                    } else {
                        Yii::error("Background image save failed.");
                        Yii::$app->session->setFlash('error', Yii::t('messages', 'Background image upload failed'));
                        $closeDialog = 0;
                    }
                } else {
                    if (!$validate[0]) {
                        Yii::$app->session->setFlash('error', $validate[1]);
                    } else {
                        Yii::error("Background image upload failed. Validation errors:" . json_encode($model->errors));
                    }
                    $closeDialog = 0;
                }
            } catch (Exception $e) {
                Yii::error("Background image apply failed. Error:{$e->getMessage()}");
                Yii::$app->session->setFlash('error', Yii::t('messages', 'Background image apply failed'));
                $closeDialog = 0;
            }
            print $closeDialog;
        } else {
         return  $this->render('changeBgImage',array(
                'model'=>$model,
                'bgImag'=>$bgImage
            ));
        }
    }



    /**
     * Performs the AJAX validation.
     * @param CModel the model to be validated
     */
    protected function performAjaxValidation($model)
    {
        if(isset($_POST['ajax']) && $_POST['ajax']==='candidate-info-form')
        {
            echo ActiveForm::validate($model);
            Yii::$app->end();
        }
    }
}
