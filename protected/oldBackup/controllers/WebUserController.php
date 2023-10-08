<?php


namespace app\controllers;


use app\components\ThresholdChecker;
use app\components\WebUser;
use app\models\CandidateInfo;
use app\models\Configuration;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Response;
use app\components\ToolKit;

class WebUserController extends Controller
{

    function init()
    {
        parent::init();
        $configuration = new Configuration();
        // check the configuration language is available or not.
        if (isset(Yii::$app->session['lang'])) {
            Yii::$app->language = Yii::$app->session['lang'];
        } else {
            $config = $configuration->getConfigurations();
            $lang = $config['LANGUAGE'];
            switch ($config['LANGUAGE']){
                case 'en':
                    $lang  =  "en-US";
                    break;
                case 'fr':
                    $lang  =  "fr-FR";
                    break;
                case 'ru':
                    $lang  =  "ru-RU";
                    break;
                case 'pt':
                    $lang  =  "pt-PT";
                    break;
                case 'it':
                    $lang  =  "it-IT";
                    break;
            }
            Yii::$app->language = $lang;
        }

        $headerText = '';
        if (isset(Yii::$app->session['headerText'])) {
            $headerText = Yii::$app->session['headerText'];
        } else {
            $modelCandidateInfo = CandidateInfo::find()->one();
            $headerText = $modelCandidateInfo->headerText;
            Yii::$app->session->set('headerText', $headerText);
        }

        date_default_timezone_set($configuration->getTimeZone());
    }


    /**
     * Check whether service is active. If not just redirect to error page
     * @param string $action Controller action
     * @return bool|Response
     * @throws BadRequestHttpException
     */
    public function beforeAction($action)
    {
        // If app is freemium redirect to deactivate page
        if (Yii::$app->toolKit->isAppFreemium() && Yii::$app->controller->id != 'deactivate') {
            return $this->redirect(array('site/deactivate/'));
        }

        // If app is inactive redirect to inactive page
        if (!Yii::$app->toolKit->isAppActive() && Yii::$app->controller->id != 'inactive') {
            return $this->redirect(array('site/inactive/'));
        }

        $curAction = Yii::$app->controller->id . '.' . Yii::$app->controller->action->id;

        // Check whether user has access for particular operation in his package
        if (!WebUser::checkPackageAccess($curAction)) {
            return $this->redirect(array('site/access-denied/'));
        }

        // For some actions we check threshold(package limitations) before redirecting to particular action.
        if (in_array(strtolower($curAction), array_map('strtolower', Yii::$app->params['thresholdActions']))) {
            $tc = new ThresholdChecker(Yii::$app->session->get('packageType'), Yii::$app->session->get('smsPackageType'));
            $isThresholdExceeded = false;

            $thresholdType = ThresholdChecker::EMAIL_CONTACTS;
       
            switch ($curAction) {
                case 'people.create':
                case 'advanced-bulk-insert.create':
                    $isThresholdExceeded = $tc->isThresholdExceeded(ThresholdChecker::EMAIL_CONTACTS);
                    break;
            }

            if ($isThresholdExceeded) {
                return $this->redirect(array('site/upgrade/', 'thresholdType' => $thresholdType));
            }
        }

        return parent::beforeAction($action); // TODO: Change the autogenerated stub
    }

}