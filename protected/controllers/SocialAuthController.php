<?php


namespace app\controllers;

use Yii;
use app\components\Bitly;
use app\components\LinkedInApi;
use app\components\TwitterApi;
use app\models\BitlyProfile;
use app\models\Configuration;
use app\models\LnProfile;
use app\models\McProfile;
use app\models\TwProfile;
use app\models\User;
use Mailchimp\MailChimpApi;
use yii\db\Exception;
use yii\web\Controller;

class SocialAuthController extends Controller
{
    /**
     * Validate social login
     * TODO: need to below all codes under social auth logings.
     */
    public function actionAuthSocialLogin()
    {
        $network = $_REQUEST['network'];
        unset(Yii::$app->session['authType']);

        switch ($network) {

            case TwitterApi::TWITTER;
                $connection = new TwitterApi();
                $twProfInfo = $connection->get(TwitterApi::VERIFY_CREDENTIALS, ['include_email' => 'false']);
                $twProfModel = TwProfile::find()->where(['twUserId' => $twProfInfo->id_str])->one();
                if (null != $twProfModel) {
                    Yii::$app->appLog->writeLog("User has Twitter profile.Twitter id:{$twProfInfo->id_str}");
                    $modelUser = User::find()->where(['id' => $twProfModel->userId])->one();
                    if (null != $modelUser) {
                        Yii::$app->appLog->writeLog("User has user profile.User id:{$modelUser->id}");
                        Yii::$app->toolKit->closePopupWindow(Yii::$app->urlManager->createAbsoluteUrl(['site/bypass-login', 'username' => $modelUser->username, 'password' => $modelUser->password]));
                    } else {
                        Yii::$app->session->setFlash('error', Yii::t('messages', 'You have not sign up with Twitter.'));
                        Yii::$app->appLog->writeLog("User does not has user profile.User id:{$twProfModel->userId}");
                        Yii::$app->toolKit->closePopupWindow(null, true);
                    }
                } else {
                    Yii::$app->session->setFlash('error', Yii::t('messages', 'You have not sign up with Twitter.'));
                    Yii::$app->appLog->writeLog("User does not has Twitter profile.Twitter id:{$twProfInfo->id_str}");
                    Yii::$app->toolKit->closePopupWindow(null, true);
                }

                break;

            /*case FacebookApi::FACEBOOK:
            $userInfo = Yii::$app->facebook->getFacebookUserInfo();
            Yii::$app->facebook->setExtendedAccessToken();

            $modelFbProf = FbProfile::model()->findByPk($userInfo['fbUserId']);

            if (null != $modelFbProf) {
                Yii::$app->appLog->writeLog("User has Facebook profile.Facebook id:{$modelFbProf->fbUserId}");
                $modelUser = User::model()->findByPk($modelFbProf->userId);
                if (null != $modelUser) {
                    $modelFbProf->accessToken = Yii::$app->facebook->getAccessToken();
                    if ($modelFbProf->save(true)) {
                        Yii::$app->appLog->writeLog("Update Facebook profile access token.User id:{$modelUser->id}");
                    }

                    Yii::$app->appLog->writeLog("User has user profile.User id:{$modelUser->id}");
                    Yii::$app->toolKit->closePopupWindow(Yii::$app->createAbsoluteUrl('Site/BypassLogin', array('username' => $modelUser->username, 'password' => $modelUser->password)), false);
                } else {
                    Yii::$app->user->setFlash('error', Yii::t('messages', 'You have not sign up with Facebook.'));
                    Yii::$app->appLog->writeLog("User does not has user profile.User id:{$modelFbProf->userId}");
                    Yii::$app->toolKit->closePopupWindow(null, true);
                }
            } else {
                Yii::$app->user->setFlash('error', Yii::t('messages', 'You have not sign up with Facebook.'));
                Yii::$app->appLog->writeLog("User does not has Facebook profile.Facebook id:" . $userInfo['fbUserId']);
                Yii::$app->toolKit->closePopupWindow(null, true);
            }

            break;*/

            case LinkedInApi::LINKEDIN;

                $li = new LinkedInApi();
                $li->setAccessToken(Yii::$app->session->get('accessToken'));
                $lnProfInfo = $li->getProfileInfo();

                if (isset($lnProfInfo['status']) && ($lnProfInfo['status'] == 403)) {
                    Yii::$app->session->set('authType', 'user');
                    Yii::$app->session->setFlash('error', Yii::t('messages', 'Your LinkedIn api have not this permissions.'));
                    return $this->redirect(array('signup/step2/', 'network' => LinkedInApi::LINKEDIN));
                } else {
                    //get saved profile data from db
                    $lnProfModel = LnProfile::find()->where(['lnUserId' => $lnProfInfo['id']])->one();

                    if (null != $lnProfModel) {
                        Yii::error("User has LinkedIn profile. LinkedIn id:{$lnProfInfo['id']}");
                        $modelUser = User::find()->where(['id' => $lnProfModel->userId])->one();
                        if (null != $modelUser) {
                            Yii::$app->appLog->writeLog("User has user profile.User id:{$modelUser->id}");
                            Yii::$app->toolKit->closePopupWindow(Yii::$app->urlManager->createAbsoluteUrl(['site/bypass-login', 'username' => $modelUser->username, 'password' => $modelUser->password]));
                        } else {
                            Yii::$app->session->setFlash('error', Yii::t('messages', 'You have not sign up with LinkedIn.'));
                            Yii::$app->appLog->writeLog("User does not has user profile.User id:{$lnProfModel->userId}");
                            Yii::$app->toolKit->closePopupWindow(null, true);
                        }
                    } else {
                        Yii::$app->session->setFlash('error', Yii::t('messages', 'You have not sign up with LinkedIn.'));
                        Yii::$app->appLog->writeLog("User does not has LinkedIn profile. LinkedIn id:{$lnProfInfo['id']}");
                        Yii::$app->toolKit->closePopupWindow(null, true);
                    }
                }

                break;
            /*case GooglePlusApi::GOOGLE_PLUS:

                $gpApi = new GooglePlusApi();
                $gpApi->setAccessToken(Yii::$app->session['accessToken']);
                $plus = new Google_Service_Plus($gpApi);
                $gpProfInfo = $plus->people->get('me');
                $gpProfModel = GpProfile::model()->findByPk($gpProfInfo->id);

                if (null != $gpProfModel) {
                    Yii::$app->appLog->writeLog("User has Google+ profile. Google+ id:{$gpProfInfo->id}");
                    $modelUser = User::model()->findByPk($gpProfModel->userId);
                    if (null != $modelUser) {
                        Yii::$app->appLog->writeLog("User has user profile.User id:{$modelUser->id}");
                        Yii::$app->toolKit->closePopupWindow(Yii::$app->createAbsoluteUrl('Site/BypassLogin', array('username' => $modelUser->username, 'password' => $modelUser->password)), false);
                    } else {
                        Yii::$app->user->setFlash('error', Yii::t('messages', 'You have not sign up with Google+.'));
                        Yii::$app->appLog->writeLog("User does not has user profile.User id:{$gpProfModel->userId}");
                        Yii::$app->toolKit->closePopupFWindow(null, true);
                    }
                } else {
                    Yii::$app->user->setFlash('error', Yii::t('messages', 'You have not sign up with Google+.'));
                    Yii::$app->appLog->writeLog("User does not has Google+ profile. Google+ id:{$gpProfInfo->id}");
                    Yii::$app->toolKit->closePopupWindow(null, true);
                }

                break;*/
        }
    }

    public function actionAuthSocial($network)
    {

        Yii::$app->appLog->writeLog("Redirected from social network. Network:{$network}");

        Yii::$app->session->remove('authType');

        switch ($network) {

            case TwitterApi::TWITTER:
                $connection = new TwitterApi();
                $twProfInfo = $connection->get(TwitterApi::VERIFY_CREDENTIALS, ['include_email' => 'false']);

                // Delete if there are more than one account other than currently authenticating.
                $modelTwProfiles = TwProfile::find()->where(['userId' => Yii::$app->user->identity->id])->one();
                if ($modelTwProfiles != null) {
                    Yii::$app->appLog->writeLog("There are more than one TwProfiles. So delete the other account if it not the same.");
                    foreach ($modelTwProfiles as $_modelTwProf) {
                        if ($_modelTwProf != $twProfInfo->id_str) {
                            $modelTwProfiles->delete();
                            Yii::$app->appLog->writeLog("Account deleted.TwUserId:{$_modelTwProf}");
                        }
                    }
                }

                $modelTwProf = TwProfile::find()->where(['twUserId' => $twProfInfo->id_str])->one();
                if ($modelTwProf == null)
                    $modelTwProf = new TwProfile();

                //$modelTwProf->scenario = 'signup';
                $modelTwProf->userId = Yii::$app->user->identity->id;
                $modelTwProf->twUserId = $twProfInfo->id_str;
                $modelTwProf->name = $twProfInfo->name;
                $modelTwProf->screenName = $twProfInfo->screen_name;
                $modelTwProf->authToken = Yii::$app->session['oauth_token'];
                $modelTwProf->authTokenSecret = Yii::$app->session['oauth_token_secret'];
                $modelTwProf->location = $twProfInfo->location;
                $modelTwProf->profileImageUrl = $twProfInfo->profile_image_url;
                $modelTwProf->followerCount = $twProfInfo->followers_count;
                $modelTwProf->friendsCount = $twProfInfo->friends_count;
                $modelTwProf->createdAt = date('Y-m-d H:i:s', strtotime($twProfInfo->created_at));


                try {
                    if ($modelTwProf->save()) {

                        $userModel = User::find()->where(['id' => Yii::$app->user->identity->id])->one();
                        //$userModel = User::model()->findByPk(Yii::$app->user->id);
                        $userModel->signup = 1;
                        $userModel->save(false);

                        // Update Configuration
                        Configuration::updateAll(array('value' => 'true'), '`key`=:key', array(':key' => 'TW_ACCESS_TOKEN_UPDATED'));

                        //FeedSearchKeyword::model()->addTwitterHandler('@' . $twProfInfo->screen_name);
                        Yii::$app->appLog->writeLog("Politician Twitter authentication success");
                        Yii::$app->session->setFlash('success', Yii::t('messages', 'Twitter authentication success.'));
                    } else {
                        Yii::$app->appLog->writeLog("Politician Twitter authentication failed");
                        Yii::$app->session->setFlash('error', Yii::t('messages', 'Twitter authentication failed.'));
                    }
                } catch (Exception $e) {
                    Yii::$app->appLog->writeLog("TPolitician Twitter authentication failed");
                    Yii::$app->session->setFlash('error', Yii::t('messages', 'Twitter authentication failed.'));
                }

                Yii::$app->toolKit->closePopupWindow(null, true);

                break;

            case LinkedInApi::LINKEDIN:

                $li = new LinkedInApi();
                $li->setAccessToken(Yii::$app->session->get('accessToken'));
                $lnProfInfo = $li->getProfileInfo();
                $lnEmailInfo = $li->getEmailAddress();

                // Delete if there are more than one account other than currently authenticating.
                $modelLnProfiles = LnProfile::find()->where(['userId' => Yii::$app->user->identity->id])->one();
                if ($modelLnProfiles != null) {
                    Yii::error("There are more than one LnProfiles. So delete the other account if it not the same.");
                    foreach ($modelLnProfiles as $_modelLnProf) {
                        if ($_modelLnProf != $lnProfInfo['id']) {
                            $modelLnProfiles->delete();
                            Yii::error("Account deleted.LnUserId:{$_modelLnProf}");
                        }
                    }
                }

                $modelLnProf = LnProfile::find()->where(['lnUserId' => $lnProfInfo['id']])->one();
                if (empty($modelLnProf)) {
                    $modelLnProf = new LnProfile();
                }

                $modelLnProf->lnUserId = $lnProfInfo['id'];
                $modelLnProf->userId = Yii::$app->user->identity->id;
                // $modelLnProf->firstName = $lnProfInfo['firstName']['localized']['en_US'];
                // $modelLnProf->lastName = $lnProfInfo['lastName']['localized']['en_US'];

                $modelLnProf->firstName = $lnProfInfo['localizedFirstName'];
                $modelLnProf->lastName = $lnProfInfo['localizedLastName'];
                $modelLnProf->email = $lnEmailInfo['elements']['0']['handle~']['emailAddress'];
                if (isset($lnProfInfo['localizedHeadline'])) {
                    $modelLnProf->headline = $lnProfInfo['localizedHeadline'];
                }
                // $modelLnProf->location = @$lnProfInfo['location']['name'];
                $modelLnProf->location = $lnProfInfo['firstName']['preferredLocale']['country'];
                $modelLnProf->accessToken = Yii::$app->session['accessToken'];
                $modelLnProf->createdAt = date('Y-m-d H:i:s');
                $modelLnProf->tokenUpdatedAt = date('Y-m-d');
                if (isset($lnProfInfo['profilePicture'])) {
                    $modelLnProf->pictureUrl = $lnProfInfo['profilePicture']['displayImage~']['elements'][0]['identifiers'][0]['identifier'];
                }

                try {
                    if ($modelLnProf->save()) {
                        $userModel = User::find()->where(['id' => Yii::$app->user->identity->id])->one();
                        $userModel->signup = 1;
                        $userModel->save(false);

                        // Update Configuration
                        $config = Configuration::updateAll(array('value' => 'true'), '`key`=:key', array(':key' => 'LN_ACCESS_TOKEN_UPDATED'));
                        Yii::$app->appLog->writeLog("Politician LinkedIn authentication success");
                        Yii::$app->session->setFlash('success', Yii::t('messages', 'LinkedIn authentication success.'));
                    } else {
                        Yii::$app->appLog->writeLog("Politician LinkedIn authentication failed.Validation errors:" . json_encode($modelLnProf->errors));
                        Yii::$app->session->setFlash('error', Yii::t('messages', "LinkedIn authentication failed."));
                    }
                } catch (Exception $e) {
                    Yii::$app->appLog->writeLog("Politician LinkedIn authentication failed. Error:{$e->getMessage()}");
                    Yii::$app->session->setFlash('error', Yii::t('messages', 'LinkedIn authentication failed.'));
                }

                Yii::$app->toolKit->closePopupWindow(null, true);

                break;

            case Bitly::BITLY:

                $blyApi = new Yii::$app->Bitly(Yii::$app->params['blyApi']['clientId'], Yii::$app->params['blyApi']['clientSecret'], Yii::$app->session['accessToken']);
                $userInfo = $blyApi->userInfo();
                $modelBlyProf = new BitlyProfile();
                $modelBlyProf->blyUserId = $userInfo['apiKey'];
                $modelBlyProf->userId = Yii::$app->user->id;
                $modelBlyProf->fullName = $userInfo['full_name'];
                $modelBlyProf->login = $userInfo['login'];
                $modelBlyProf->accessToken = Yii::$app->session['accessToken'];

                try {
                    if ($modelBlyProf->save()) {
                        Yii::$app->appLog->writeLog("Politician Bitly authentication success");
                        Yii::$app->session->setFlash('success', Yii::t('messages', 'Bitly authentication success.'));
                    } else {
                        Yii::$app->appLog->writeLog("Politician Bitly authentication failed. Validation errors:" . json_encode($modelBlyProf->errors));
                        Yii::$app->session->setFlash('error', Yii::t('messages', "Bitly authentication failed."));
                    }
                } catch (Exception $e) {
                    Yii::$app->appLog->writeLog("Politician Bitly authentication failed. Error:{$e->getMessage()}");
                    Yii::$app->session->setFlash('error', Yii::t('messages', 'Bitly authentication failed.'));
                }

                Yii::$app->toolKit->closePopupWindow(null, true);

                break;

            case MailChimpApi::MAILCHIMP:

                $mcUserData = Yii::$app->session->get('mcUserData');
                $modelMcProf = new McProfile();
                $modelMcProf->mcUserId = $mcUserData['user_id'];
                $modelMcProf->accessToken = $mcUserData['access_token'];
                $modelMcProf->dc = $mcUserData['dc'];
                $modelMcProf->loginId = strval($mcUserData['login']['login_id']);
                $modelMcProf->loginName = $mcUserData['login']['login_name'];
                $modelMcProf->loginEmail = $mcUserData['login']['login_email'];
                $modelMcProf->createdAt = date('Y-m-d H:i:s');
                $modelMcProf->userId = Yii::$app->user->id;
                $modelMcProf->avatar = $mcUserData['login']['avatar'];
                $modelMcProf->apiEndpoint = $mcUserData['api_endpoint'];

                try {
                    if ($modelMcProf->save(false)) {
                        Yii::$app->appLog->writeLog("Client Mailchimp authentication success");
                        Yii::$app->session->setFlash('success', Yii::t('messages', 'Mailchimp authentication success.'));
                    } else {
                        Yii::$app->appLog->writeLog("Client Mailchimp authentication failed. Validation errors:" . json_encode($modelMcProf->errors));
                        Yii::$app->session->setFlash('error', Yii::t('messages', "Mailchimp authentication failed."));
                    }
                } catch (Exception $e) {
                    Yii::$app->appLog->writeLog("Client Mailchimp authentication failed. Error:{$e->getMessage()}");
                    Yii::$app->session->setFlash('error', Yii::t('messages', 'Mailchimp authentication failed.'));
                }
                Yii::$app->toolKit->closePopupWindow(null, true);

                break;
        }
    }

}