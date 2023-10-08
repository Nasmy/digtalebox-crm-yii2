<?php

namespace app\controllers;

use app\components\ThresholdChecker;
use app\models\App;
use app\models\BroadcastLinkStat;
use app\models\BroadcastMessage;
use app\models\BroadcastMessageSearch;
use app\models\Invitation;
use app\models\User;
use yii\base\ErrorException;
use yii\db\Exception;
use yii\filters\AccessControl;
use yii;
use yii\helpers\Html;
use yii\web\UploadedFile;
use Bitly;
use \app\controllers\WebUserController;

class BroadcastMessageController extends WebUserController
{
    public function behaviors()
    {
        return [
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

    public $layout = 'column1';


    /**
     * Manages all models.
     *
     */
    public function actionAdmin()
    {

        $model = new BroadcastMessage();
        $model->scenario = 'search';
        $model->loadDefaultValues();  // clear any default values

        if (isset($_GET['BroadcastMessage']))
            $model->attributes = $_GET['BroadcastMessage'];
        if (Yii::$app->session->get('packageTypeId') == App::FREEMIUM) {
            $tc = new ThresholdChecker(Yii::$app->session->get('packageType'));
            $usedCount = $tc->getCount(ThresholdChecker::BROADCAST_LIMIT);
            $remainingCount = $tc->getRemainingCount(ThresholdChecker::BROADCAST_LIMIT);
            $total = $usedCount + $remainingCount;

            Yii::$app->session->setFlash('info', "You have {$remainingCount} broadcast messages remaining out of {$total}");
        }

        // To launch the site guide when freemium user first access the site
        $isSiteGuideViewed = true;
        if (isset($_GET['isSiteGuideViewed']) && !$_GET['isSiteGuideViewed']) {
            $isSiteGuideViewed = false;
        }
        $params = Yii::$app->request->getQueryParams();

        return $this->render('admin', array(
            'model' => $model,
            'searchModel' => $model,
            'isSiteGuideViewed' => $isSiteGuideViewed,
            'attributeLabels' => $model->attributeLabels(),
            'dataProvider' => $model->search($params)
        ));
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate()
    {
        try {
            $tc = new ThresholdChecker(Yii::$app->session->get('packageType'));
            if (Yii::$app->session->get('packageTypeId') == App::FREEMIUM && $tc->getRemainingCount(ThresholdChecker::BROADCAST_LIMIT) == 0) {
                Yii::$app->appLog->writeLog("Broadcast message queue is full for freemium user");
                Yii::$app->session->setFlash('warning', Yii::t('messages', 'Sorry! Your broadcast messaging queue is full. Upgrade to premium version for unlimited messaging or {invite} your friends to join DigitaleBox for expand your message queue size.', [
                    'invite' => Html::a(Yii::t('messages', 'INVITE'), ['invitation/admin'], ['style' => "color:#ED523D;font-weight:bold;text-decoration: underline"]),
                ]));
                return $this->redirect(array('admin'));
            }

            $model = new BroadcastMessage();
            $model->scenario = 'create';
            $clientProfiles = User::getClientProfile(array('BLY'));
            if (isset($_POST['BroadcastMessage'])) {
                $socialPicture = [];
                $model->attributes = $_POST['BroadcastMessage'];
                $model->lnPost = $_POST['BroadcastMessage']['lnPost'];
                $model->lnPagePost = $_POST['BroadcastMessage']['lnPagePost'];
                $model->lnImageFile = UploadedFile::getInstance($model, 'lnImageFile');
                $model->lnPageImageFile = UploadedFile::getInstance($model, 'lnPageImageFile');
                $model->twImageFile = UploadedFile::getInstance($model, 'twImageFile');

                /*
                   $model->fbImageFile=UploadedFile::getInstance($model,'fbImageFile');
                   $model->twImageFile=UploadedFile::getInstance($model,'twImageFile');
                   $model->fbProfImageFile=UploadedFile::getInstance($model,'fbProfImageFile');
                  */

                $model->publishDate = User::convertSystemTime($model->publishDate);
                $model->createdBy = strval(Yii::$app->user->id);
                $model->createdAt = User::convertSystemTime();
                $model->updatedAt = User::convertSystemTime();
                $model->updatedBy = strval(Yii::$app->user->id);
                $model->fbProfPostStatus = 0;
                $model->twPostStatus = 0;
                $model->lnPostStatus = 0;
                $model->fbProfPostStatus = 0;
                $model->recordStatus = 0;

                if ($model->validate()) {
                    $attributes = json_encode($model->attributes);
                    try {
                        if ($model->save(false)) {
                            $model->uploadImages($model);
                            echo Yii::$app->session->setFlash('success', Yii::t('messages', 'Broadcast message created'));
                            Yii::$app->appLog->writeLog("Broadcast message created. Data:{$attributes}");
                            $this->addLinks($model);
                            return $this->redirect(array('admin'));
                        } else {
                            echo Yii::$app->session->setFlash('error', Yii::t('messages', 'Broadcast message create failed'));
                            Yii::$app->appLog->writeLog("Broadcast message create failed. Data:{$attributes}");
                        }
                    } catch (Exception $e) {
                        echo Yii::$app->session->setFlash('error', Yii::t('messages', 'Broadcast message create failed'));
                        Yii::$app->appLog->writeLog("Broadcast message create failed. Data:{$attributes}, Error:{$e->getMessage}");
                    }
                } else {
                    Yii::$app->appLog->writeLog("Broadcast messages create failed. Validation errors:" . json_encode($model->errors));
                }
            }
            if (isset($_GET['isInvite'])) {
                $link = Yii::$app->params['salesAppUrl'] . '?sref=' . base64_encode(Invitation::getSocialCode());

                try {
                    $blyApi = new Yii::$app->Bitly(Yii::$app->params['blyApi']['clientId'], Yii::$app->params['blyApi']['clientSecret'], Yii::$app->params['blyApi']['genericAccessToken']);
                    $urlInfo = $blyApi->shorten($link);
                    Yii::$app->appLog->writeLog("Shorten URL info:" . json_encode($urlInfo));
                    if (isset($urlInfo['url'])) {
                        $link = $urlInfo['url'];
                    }
                } catch (Exception $e) {
                }

                $shareMsg = Yii::t('messages', 'Hey, I invite you to use #digitalebox Social Media tool, use {link} for free features #socialmedia #SMM', ['link' => $link]);

                $model->fbPost = $shareMsg;
                $model->lnPost = $shareMsg;
                $model->twPost = $shareMsg;
                $model->lnPagePost = $shareMsg;
                $model->fbProfPost = $shareMsg;
            }

            return $this->render('create', array(
                'model' => $model,
                'attributeLabels' => $model->attributeLabels(),
                'fbPostLength' => BroadcastMessage::FB_POST_LENGTH,
                'twPostLength' => BroadcastMessage::TW_POST_LENGTH,
                'lnPostLength' => BroadcastMessage::LN_POST_LENGTH,
                'modelBlyProfile' => $clientProfiles['modelBlyProfile']
            ));
        } catch (Exception $e) {
            echo 'Caught an Error: ', $e->getMessage(), "\n";
            Yii::$app->appLog->writeLog("Exception : " . $e->getLine());
        }
    }


    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($id)
    {

        $model = BroadcastMessage::findOne($id);
        $model->scenario = 'update';
        $clientProfiles = User::getClientProfile(array('BLY'));

        if (isset($_POST['BroadcastMessage'])) {
            $model->attributes = $_POST['BroadcastMessage'];
            /*   $model->fbImageFile=CUploadedFile::getInstance($model,'fbImageFile');
               $model->twImageFile=CUploadedFile::getInstance($model,'twImageFile');*/
            $model->lnImageFile = UploadedFile::getInstance($model, 'lnImageFile');
            $model->lnPageImageFile = UploadedFile::getInstance($model, 'lnPageImageFile');
            $model->twImageFile = UploadedFile::getInstance($model, 'twImageFile');
            $model->createdBy = strval($model->createdBy);
            $model->updatedBy = strval(Yii::$app->user->id);
            $model->recordStatus = BroadcastMessage::REC_STATUS_PENDING;
            $model->updatedAt = User::convertSystemTime();
            if ($model->validate()) {
                $attributes = json_encode($model->attributes);
                try {
                    if ($model->save(false)) {
                        $model->uploadImages($model);
                        Yii::$app->session->setFlash('success', Yii::t('messages', 'Broadcast messages updated'));
                        Yii::$app->appLog->writeLog("Broadcast messages updated. Data:{$attributes}");
                        $this->addLinks($model);
                        return $this->redirect(array('admin'));
                    } else {
                        Yii::$app->session->setFlash('error', Yii::t('messages', 'Broadcast messages update failed'));
                        Yii::$app->appLog->writeLog("Broadcast messages update failed. Data:{$attributes}");
                    }
                } catch (Exception $e) {
                    Yii::$app->session->setFlash('error', Yii::t('messages', 'Broadcast messages update failed'));
                    Yii::$app->appLog->writeLog("Broadcast messages update failed. Data:{$attributes}, Error:{$e->getMessage}");
                }
            } else {
                Yii::$app->appLog->writeLog("Broadcast messages update failed. Validation errors:" . json_encode($model->errors));
            }
        } else {
            // Set record status as draft to avoid publish while editing.
            $model->recordStatus = BroadcastMessage::REC_STATUS_DRAFT;
            try {
                $model->save();
            } catch (Exception $e) {
            }
        }

        Yii::$app->toolKit->setResourceInfo();

        return $this->render('update', array(
            'model' => $model,
            'attributeLabels' => $model->attributeLabels(),
            'fbPostLength' => BroadcastMessage::FB_POST_LENGTH,
            'twPostLength' => BroadcastMessage::TW_POST_LENGTH,
            'lnPostLength' => BroadcastMessage::LN_POST_LENGTH,
            'modelBlyProfile' => $clientProfiles['modelBlyProfile']
        ));
    }


    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be deleted
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $attributes = json_encode($model->attributes);
        try {
            $model->delete();
            Yii::$app->toolKit->setAjaxFlash('success', Yii::t('messages', 'Messages deleted'));
            Yii::$app->appLog->writeLog("Broadcast messages deleted. Data:{$attributes}");
        } catch (Exception $e) {
            Yii::$app->toolKit->setAjaxFlash('error', Yii::t('messages', 'Message delete failed'));
            Yii::$app->appLog->writeLog("Broadcast messages delete failed. Data:{$attributes}, Error:{$e->getMessage}");
        }
    }


    /**
     * Delete image
     * @param string $type Image category identifier
     * @param integer $id Record id
     */
    public function actionDelImage()
    {

        $type = Yii::$app->request->post('type');
        $id = Yii::$app->request->post('id');
        $model = BroadcastMessage::findOne($id);

        switch ($type) {
            case "fb":
                $model->fbImageName = '';
                break;
            case "lnPage":
                $model->lnPageImageName = '';
                break;
            case "tw":
                $model->twImageName = '';
                break;
            case "ln":
                $model->lnImageName = '';
                break;
            case "fbProf":
                $model->fbProfImageName = '';
                break;
        }

        try {
            $model->save(false);
            Yii::$app->appLog->writeLog("Image removed. Type:{$type}, id:{$id}");

            $res = ['id' => $id, 'type' => $type, 'msg' => 1];
            echo json_encode($res);

        } catch (Exception $e) {

            Yii::$app->appLog->writeLog("Image removal failed. Error:{$e->getMessage()}");

            $res = ['id' => $id, 'type' => $type, 'msg' => 0];
            echo json_encode($res);
        }
    }


    /**
     * Add shorten links
     * @param BroadcastMessage $model
     */
    private function addLinks($model)
    {
        $fbPostLinks = Yii::$app->toolKit->extractUrlsFromText($model->fbPost);
        if (!empty($fbPostLinks)) {
            BroadcastLinkStat::addLinks(BroadcastLinkStat::FACEBOOK, $fbPostLinks, Yii::$app->session['shortenUrls'], $model->id);
        }
        $fbProfileLinks = Yii::$app->toolKit->extractUrlsFromText($model->fbProfPost);
        if (!empty($fbProfileLinks)) {
            BroadcastLinkStat::addLinks(BroadcastLinkStat::FACEBOOK_PROFILE, $fbProfileLinks, Yii::$app->session['shortenUrls'], $model->id);
        }

        $twPostLinks = Yii::$app->toolKit->extractUrlsFromText($model->twPost);
        if (!empty($twPostLinks)) {
            BroadcastLinkStat::addLinks(BroadcastLinkStat::TWITTER, $twPostLinks, Yii::$app->session['shortenUrls'], $model->id);
        }

        $lnPostLinks = Yii::$app->toolKit->extractUrlsFromText($model->lnPost);
        if (!empty($lnPostLinks)) {
            BroadcastLinkStat::model()->addLinks(BroadcastLinkStat::LINKEDIN, $lnPostLinks, Yii::$app->session['shortenUrls'], $model->id);
        }
        $lnPagePostLinks = Yii::$app->toolKit->extractUrlsFromText($model->lnPagePost);
        if (!empty($lnPagePostLinks)) {
            BroadcastLinkStat::addLinks(BroadcastLinkStat::LINKEDIN_PAGE, $lnPagePostLinks, Yii::$app->session['shortenUrls'], $model->id);
        }

        unset(Yii::$app->session['shortenUrls']);
    }


    /**
     * Shorten URL via Bitly
     */
    public function actionShortenUrl()
    {
        $model = new BroadcastMessage();
        $this->layout = 'dialog';
        $shortUrl = '';

        if (isset($_POST['BroadcastMessage'])) {
            $clientProfiles = User::getClientProfile(array('BLY'));
            $accessToken = Yii::$app->params['blyApi']['genericAccessToken'];
            if (!empty($clientProfiles['modelBlyProfile'])) {
                $accessToken = $clientProfiles['modelBlyProfile']->accessToken;
            }
            $model->attributes = $_POST['BroadcastMessage'];

            try {
                $blyApi = new Yii::$app->Bitly(Yii::$app->params['blyApi']['clientId'], Yii::$app->params['blyApi']['clientSecret'], $accessToken);

                if (!filter_var($model->longUrl, FILTER_VALIDATE_URL)) {
                    echo Yii::$app->session->setFlash('error', Yii::t('messages', 'Link shortning failed. Please try again.'));
                    $urlInfo = 'error';
                } else {
                    $urlInfo = $blyApi->shorten($model->longUrl);
                }
                Yii::$app->appLog->writeLog("Shorten URL info:" . json_encode($urlInfo));
                if (isset($urlInfo['url'])) {
                    $shortUrl = $urlInfo['url'];
                    $shortenUrl = Yii::$app->session['shortenUrls'];
                    $shortenUrl[] = $shortUrl;
                    Yii::$app->session['shortenUrls'] = $shortenUrl;
                } else {
                    echo Yii::$app->session->setFlash('error', Yii::t('messages', 'Link shortning failed. Please try again.'));
                    Yii::$app->appLog->writeLog("Link shortning failed.");
                }
            } catch (Exception $e) {
                echo Yii::$app->session->setFlash('error', Yii::t('messages', 'Link shortning failed. Please try again.'));
                Yii::$app->appLog->writeLog("Link shortning failed. Error:{$e->getMessage()}");
            }
        }

        return $this->render('shortenLinks', array(
            'model' => $model,
            'shortUrl' => $shortUrl
        ));
    }

    /**
     * Shorten URLs in text
     */
    public function actionShortenUrlInText()
    {
        if (isset($_POST['text'])) {
            $text = $_POST['text'];
            $clientProfiles = User::getClientProfile(array('BLY'));
            $accessToken = Yii::$app->params['blyApi']['genericAccessToken'];
            if (!empty($clientProfiles['modelBlyProfile'])) {
                $accessToken = $clientProfiles['modelBlyProfile']->accessToken;
            }

            $textWithLinks = $text;
            $reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";

            preg_match_all($reg_exUrl, $text, $matches);
            $urls = @$matches[0];

            if ('' != $urls) {
                foreach ($urls as $url) {
                    $shortUrl = $url;
                    try {
                        $blyApi = new Yii::$app->Bitly(Yii::$app->params['blyApi']['clientId'], Yii::$app->params['blyApi']['clientSecret'], $accessToken);
                        $urlInfo = $blyApi->shorten($url);
                        Yii::$app->appLog->writeLog("Shorten URL info:" . json_encode($urlInfo));
                        if (isset($urlInfo['url'])) {
                            $shortUrl = $urlInfo['url'];
                            $shortenUrl = Yii::$app->session['shortenUrls'];
                            $shortenUrl[] = $shortUrl;
                            Yii::$app->session['shortenUrls'] = $shortenUrl;
                        } else {
                            Yii::$app->appLog->writeLog("Link shortning failed." . $url);
                        }
                    } catch (Exception $e) {
                        Yii::$app->appLog->writeLog("Link shortning failed. Error:{$e->getMessage()} URL: {$url}");
                    }
                    $textWithLinks = str_replace($url, $shortUrl, $textWithLinks);
                }
            }
            echo $textWithLinks;
        }
    }


    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     * @return string
     */
    public function actionView($id)
    {
        $this->layout = 'dialog';
        Yii::$app->toolKit->setResourceInfo();
        $model = $this->findModel($id);

        return $this->render('view', array(
            'model' => $model,
        ));
    }


    public function actionViewPop($id)
    {
        $this->layout = 'dialog';
        Yii::$app->toolKit->setResourceInfo();
        return $this->render('view-pop', [
            'model' => BroadcastMessage::findOne($id),
        ]);

    }

    /**
     * Finds the StatSummary model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return StatSummary the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = BroadcastMessage::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

}
