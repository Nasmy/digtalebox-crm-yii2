<?php

namespace app\controllers;

use app\components\Bitly;
use app\components\LinkedInApi;
use Mailchimp\MailChimpApi;
use yii\helpers\Url;
use Yii;
use \app\controllers\WebUserController;

class AuthCallBackController extends WebUserController
{
    /**
     * Linked in callback
     */
    public function actionLinkedInCallback()
    {
        $state = $_REQUEST['state'];
        list($uniqid, $domain, $isSecure) = explode('$$', $state);

        $urlParts = parse_url(Url::base(true));
        if (isset($_REQUEST['code'])) {
            $li = new LinkedInApi();
            $accessToken = $li->getAccessToken($_REQUEST['code']);
            $redirectUri = "{$urlParts['scheme']}://{$domain}/index.php/signup/callback/" . LinkedInApi::LINKEDIN . "?accessToken={$accessToken}";

        } else {
            Yii::$app->appLog->writeLog("User has cancelled LinkedIn authentication");
            $redirectUri = "{$urlParts['scheme']}://{$domain}/index.php/signup/callback/" . LinkedInApi::LINKEDIN;
        }

        if ($isSecure)
            $redirectUri = str_replace('http://', 'https://', $redirectUri);
        else
            $redirectUri = str_replace('https://', 'http://', $redirectUri);

        Yii::$app->appLog->writeLog("LinkedIn Callback redirectUri" . $redirectUri);

        return $this->redirect($redirectUri);
    }

    /**
     * Bitly callback
     */
    public function actionBitlyCallback()
    {
        $state = $_REQUEST['state'];
        list($uniqid, $domain, $isSecure) = explode('|', $state);
        $accessToken = '';

        if (isset($_REQUEST['code'])) {
            $blyApi = new Bitly(Yii::$app->params['blyApi']['clientId'], Yii::$app->params['blyApi']['clientSecret']);
            try {
                $url = Yii::$app->params['blyApi']['callbackUrl'];
                $isStaging = Yii::$app->toolKit->isStaging();
                if (!$isStaging) {
                    $url = str_replace('http://', 'https://', $url);
                }

                $accessToken = $blyApi->getAccessToken($_REQUEST['code'], $url);

            } catch (Exception $e) {
                Yii::$app->appLog->writeLog("Error while authenticating with Bitly. Error:{$e->getMessage()}");
            }
        } else {
            Yii::$app->appLog->writeLog("User has cancelled LinkedIn authentication");
        }

        $urlParts = parse_url(Url::base(true));

        $redirectUri = "{$urlParts['scheme']}://{$domain}/index.php/signup/callback?network=" . Bitly::BITLY . "&accessToken={$accessToken}";

        if ($isSecure)
            $redirectUri = str_replace('http://', 'https://', $redirectUri);
        else
            $redirectUri = str_replace('https://', 'http://', $redirectUri);

        return $this->redirect($redirectUri);
    }

    /**
     * Mailchimp callback
     */
    public function actionMailchimpCallBack()
    {
        $isSecure = Yii::$app->request->isSecureConnection;
        $domain = $_SERVER['SERVER_NAME'];

        $urlParts = parse_url(Url::base(true));
        $isStaging = Yii::$app->toolKit->isStaging();

        $state = $_REQUEST['state'];
        $code = $_REQUEST['code'];

        Yii::$app->appLog->writeLog("Auth call back domain:" . $domain);
        $redirectUri = "{$urlParts['scheme']}://{$domain}/index.php/signup/callback/" . MailChimpApi::MAILCHIMP . "?code={$code}&state={$state}";

        Yii::$app->appLog->writeLog("Auth call back before:" . $redirectUri);
        if ($isSecure) {
            $redirectUri = str_replace('http://', 'https://', $redirectUri);
        } else {
            $redirectUri = str_replace('https://', 'http://', $redirectUri);
        }
        Yii::$app->appLog->writeLog("Auth call back after:" . $redirectUri);

        return $this->redirect($redirectUri);
    }
}
