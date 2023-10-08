<?php

namespace app\controllers;

use app\components\ToolKit;
use app\models\App;
use app\models\Configuration;
use app\models\CustomField;
use app\models\CustomType;
use app\models\CustomValue;
use app\models\Form;
use app\models\FormMembershipDonation;
use app\models\KeywordUrl;
use app\models\MessageTemplate;
use app\models\User;
use Stripe\Charge;
use Stripe\Customer;
use Stripe\Exception\CardException;
use Stripe\Stripe;
use yii\console\Response;
use yii\db\Exception;
use Yii;
use yii\helpers\Json;

/**
 * Newsletter Controller.
 *
 * This class illustrate the newsletter actions
 *
 * @author : Nasmy Ahamed
 * Date: 7/22/2019
 * @copyright Copyright &copy; Keeneye solutions (PVT) LTD
 */
class NewsletterController extends \yii\base\Controller
{
    public $layout = 'newsletter';

    /**
     * Check whether user is in.
     */
    public function beforeAction($action)
    {
        return parent::beforeAction($action);
    }


    /**
     * @return mixed
     * @throws \Exception
     */
    public function actionIndex()
    {
        $model = new User();
        $model->scenario = 'newsletter';
        $success = false;
        $requestUrl = $_SERVER['HTTP_REFERER']; //callback URL
        if (isset($_POST['Submit'])) {
            $requestUrl = empty($_POST['callBackUrl']) ? $requestUrl : $_POST['callBackUrl']; // if not defined, sends to referer
            $model->attributes = $_POST;
            $model->joinedDate = User::convertSystemTime();
            $model->userType = User::NEWSLETTER;
            if ($model->save()) {
                $success = true;
                Yii::$app->appLog->writeLog("Newsletter subscription saved. Url: " . $_SERVER['HTTP_REFERER'] . " | Callback Url: " . $requestUrl . " Data:" . Json::encode($model->attributes));
            } else {
                $success = false;
                Yii::$app->appLog->writeLog("Newsletter subscription failed. Url: " . $_SERVER['HTTP_REFERER'] . " | Callback Url: " . $requestUrl . " Validation errors:" . Json::encode($model->errors));
            }
        }
        //if success redirect here, else render error page
        return $this->redirect($requestUrl);
    }

    /**
     * @return Response|\yii\web\Response
     */
    public function actionKeywordSubscribe()
    {
        $requestUrl = Yii::$app->request->referrer; //callback URL
        $data = $_GET;
        Yii::$app->appLog->writeLog("KeywordUrl submitted." . json_encode($data) . $requestUrl);

        if (ToolKit::isEmpty($data)) {
            return Yii::$app->response->redirect($requestUrl);
        }
        $userId = $data['id'];
        $code = $data['code'];
        $user = User::findOne($userId);

        if (!$user) {
            Yii::$app->appLog->writeLog("KeywordUrl user does not exist." . $data['id']);
            return Yii::$app->response->redirect($requestUrl);
        }

        $pipeSepData = explode('||', $code);
        $keywordCSV = base64_decode($pipeSepData[0]);
        $oldKeywords = $user->keywords;
        $keywordUrl = KeywordUrl::findOne($pipeSepData[1]);
        // print_r($keywordUrl); die();

        if ($keywordUrl) {
            $newKeywords = '';
            if (empty($oldKeywords)) {
                $newKeywords = $keywordCSV;
            } else {
                $newKeywords = implode(',', array_unique(array_merge(explode(',', $oldKeywords), explode(',', $keywordCSV)), SORT_REGULAR));
            }

            $user->keywords = $newKeywords;
            $user->save(false);
            Yii::$app->appLog->writeLog("KeywordUrl user saved." . json_encode($user->getAttributes()));
            Yii::$app->appLog->writeLog("KeywordUrl redirect to externalUrl." . $keywordUrl['externalUrl']);
            return Yii::$app->response->redirect($keywordUrl['externalUrl']);
        }

        Yii::$app->appLog->writeLog("KeywordUrl table record does not exist." . json_encode($pipeSepData));
        return Yii::$app->response->redirect($requestUrl);
    }

    /**
     * To process payments on Membership and/or Donation
     */
    public function actionPaymentProcess($id = null)
    {

        // Database Changing according to domain

        $domain = Yii::$app->toolKit->domain;
        $appData = Yii::$app->toolKit->getAppData();

        Yii::$app->appLog->writeLog("Process started. :{$domain}");
        Yii::$app->appLog->writeLog("Process started App Data:" . json_encode($appData));
        Yii::$app->appLog->writeLog("Connecting to database.Dbname:{$appData['dbName']}");

        if (!Yii::$app->toolKit->changeDbConnection($appData['dbName'], $appData['host'], $appData['username'], $appData['password'])
        ) {
            Yii::$app->appLog->writeLog("Database connection change failed.");
            exit;
        }


        if (isset($_GET['id']) && !empty($_GET['id'])) {
            $id = $_GET['id'];
        }
        $this->layout = 'signup';
        $subscription = null;

        $formMem = FormMembershipDonation::findOne($id);
        $form = Form::findOne($formMem->formId);
        $userModel = User::findOne($formMem->userId);
        $stripeUrl = Yii::$app->urlManager->createAbsoluteUrl(['newsletter/payment-process/', 'id' => $id]);

        $stripePublicKey = Configuration::findOne(Configuration::STRIPE_CLIENT_ID); //pk
        $stripeClientKey = Configuration::findOne(Configuration::STRIPE_SECRET_ID); //sk
        Stripe::setApiKey($stripeClientKey->value); //sk
        $charge = '';
        $chargeAmount = Form::getPaymentAmount($id);
        $plan = Form::getPaymentPlan($id);

        $msg = Yii::t('messages', 'You are about to make onetime payment of {amount} for {plan}.', array(
            'amount' => "{$chargeAmount} " . Yii::$app->params['stripe']['currencyCode'],
            'plan' => $plan
        ));

        $data = array(
            'chargeAmount' => $chargeAmount,
            'email' => $userModel->email,
            'currency' => Yii::$app->params['stripe']['currencyCode']
        );

        if (isset($_POST['stripeToken'])) {
            Yii::$app->appLog->writeLog("Processing payment for Donation or Membership or both");
            $token = $_POST['stripeToken'];

            try {

                $customer = Customer::create([
                    'email' => $userModel->email,
                    'source' => $token,
                ]);

                $charge = Charge::create([
                    'customer' => $customer->id,
                    'amount' => $data['chargeAmount'] * 100,
                    'currency' => $data['currency']
                ]);

            } catch (\Exception $ex) {
                // Since it's a decline, Stripe_CardError will be caught

                Yii::$app->session->setFlash('error', Yii::t('messages', $ex->getMessage()));
                return $this->render('addCard', array(
                    'stripeUrl' => $stripeUrl,
                    'msg' => $msg,
                    'data' => $data,
                    'stripeSecretKey' => $stripePublicKey->value,
                    'email' => $userModel->email
                ));
                // Something else happened, completely unrelated to Stripe
            }

            $formMem->stripeCustomerId = $customer->id;
            $formMem->payerEmail = $userModel->email; // subscribers email
            try {
                $formMem->save(false);
                $msg = Yii::t('messages', 'Payment processed. Successfully charged {amount}', ['amount' => $data['chargeAmount'] . ' ' . $data['currency']]);
                Yii::$app->appLog->writeLog("Payment processed" . $data['chargeAmount'] . ' ' . $data['currency']);
            } catch (Exception $e) {
                Yii::$app->appLog->writeLog("Error while saving processing payment. Error:{$e->getMessage()}");
                Yii::$app->session->setFlash('error', Yii::t('messages', 'Payment process failed.'));
            }
        }

        if (is_object($charge)) {
            return $this->render('index', array(
                'requestUrl' => $form->redirectAddress,
                'success' => true,
                'msg' => $msg
            ));
        } else {
            return $this->render('addCard', array(
                'stripeUrl' => $stripeUrl,
                'msg' => $msg,
                'data' => $data,
                'stripeSecretKey' => $stripePublicKey->value,
                'email' => $userModel->email
            ));
        }
    }


    /**
     * @return string|Response|\yii\web\Response
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionSubscribe()
    {
        $this->layout = 'signup';
        $model = new User();
        $model->scenario = 'formBuilder';

        if (isset($_POST['email'])) {
            if (!ToolKit::isEmpty($_POST['email']) && User::find()->where('email=:email', [':email' => $_POST['email']])->exists()) {
                $model = User::find()->where(['email' => $_POST['email']])->andWhere('userType NOT IN ("' . User::POLITICIAN . '")')->one();
                $oldmodel = User::find()->where(['email' => $_POST['email']])->andWhere('userType NOT IN ("' . User::POLITICIAN . '")')->one();
            }
        } elseif (isset($_POST['mobile'])) {
            if (!ToolKit::isEmpty($_POST['mobile']) && User::find()->where('mobile=:mobile', array(':mobile' => $_POST['mobile']))->exists()) {

                $model = User::findOne(['mobile' => $_POST['mobile']], 'userType NOT IN ("' . User::POLITICIAN . '")');
                $oldmodel = User::findAll(['mobile' => $_POST['mobile']], 'userType NOT IN ("' . User::POLITICIAN . '")');

            }

        } elseif (isset($_POST['dateOfBirth'])) {

            if (!ToolKit::isEmpty($_POST['dateOfBirth']) && User::find()->where('dateOfBirth=:dateOfBirth', [':dateOfBirth' => $_POST['dateOfBirth']]) && User::find()->where('firstName=:firstName', [':firstName' => $_POST['firstName']]) && User::find()->where('lastName=:lastName', [':lastName' => $_POST['lastName']])) {

                $model = User::findOne(['dateOfBirth' => $_POST['dateOfBirth'], 'firstName' => $_POST['firstName'], 'lastName' => $_POST['lastName']], 'userType NOT IN ("' . User::POLITICIAN . '")');
                $oldmodel = User::findOne(['dateOfBirth' => $_POST['dateOfBirth'], 'firstName' => $_POST['firstName'], 'lastName' => $_POST['lastName']], 'userType NOT IN ("' . User::POLITICIAN . '")');
            }

        } else {
            $model = new User();
        }

        if (is_null($model) || empty($model)) {
            $model = new User();
        }

        $success = false;
        $hasCustomFields = false;
        $error = array();
        $customErrors = array();
        if (isset($_SERVER['HTTP_REFERER'])) {
            $requestUrl = $_SERVER['HTTP_REFERER']; //callback URL
        } else {
            $requestUrl = "";
        }

        $msg = '';
        Yii::$app->appLog->writeLog("Form submitted." . json_encode($_POST) . json_encode($_REQUEST));

        //Custom field data
        $customFields = CustomValue::getCustomData(CustomType::CF_PEOPLE, 0, CustomField::ACTION_CREATE, ToolKit::post('CustomValue'), null, true);

        if (isset($_POST['Submit'])) {
            $id = intval(base64_decode($_POST['formId']));

            $form = Form::findOne($id);
            $model->formId = $form->id;
            $model->isMembership = ToolKit::post('isMembership');
            $model->isDonation = ToolKit::post('isDonation');
            $paymentExist = true;
            if (ToolKit::isEmpty($model->isMembership) && ToolKit::isEmpty($model->isDonation)) {
                $paymentExist = false;
            }
            if (is_null($form) || $form->enabled == 0) {
                $success = false;
                $error[] = Yii::t('messages', 'Form not available.');
                Yii::$app->appLog->writeLog("Newsletter subscription failed. Form disabled.");
            } else {
                $requestUrl = empty($_POST['callBackUrl']) ? $requestUrl : $_POST['callBackUrl']; // if not defined, sends to referer
                Yii::$app->appLog->writeLog("Redirect URL." . $requestUrl);
                $oldKeywords = $model->keywords;
                // Remove Captcha hidden values
                unset($_POST['BDC_VCID_jqueryBasicCaptcha']);
                unset($_POST['BDC_BackWorkaround_jqueryBasicCaptcha']);
                unset($_POST['BDC_Hs_jqueryBasicCaptcha']);
                unset($_POST['BDC_SP_jqueryBasicCaptcha']);
                $model->attributes = $_POST;
                $model->formId = $form->id;
                $model->joinedDate = User::convertSystemTime();
                $model->createdAt = User::convertSystemTime();
                $model->userType = User::NEWSLETTER;
                $newKeywords = '';
                if (!empty($form->keywords)) { //if not empty, there is a new keyword
                    if (empty($oldKeywords)) {
                        $newKeywords = $form->keywords;
                        Yii::$app->appLog->writeLog("old keywords empty:" . $newKeywords);
                    } else {
                        $newKeywords = implode(',', array_unique(array_merge(explode(',', $oldKeywords), explode(',', $form->keywords)), SORT_REGULAR));
                        Yii::$app->appLog->writeLog("old keywords not empty:" . $newKeywords);
                    }
                }
                $model->keywords = empty($form->keywords) ? $oldKeywords : $newKeywords;
                $valid = $model->validate();

                $hasCustomFields = false;
                if (isset($_POST['CustomValue'])) { //validate all active custom fields only if custom fields available
                    $hasCustomFields = true;
                    $valid = CustomField::validateCustomFieldList($customFields) && $valid;
                    if (!$valid) {
                        $customErrors = CustomField::returnCustomFieldErrors($customFields);
                    }
                }
                $isBoundToLaw = false;
                if (isset($_POST['isConcern']) && !ToolKit::isEmpty($_POST['isConcern'])) {
                    $isBoundToLaw = true;
                }

                if (isset($_SERVER['HTTP_REFERER'])) {
                    $httpRefErer = $_SERVER['HTTP_REFERER'];
                } else {
                    $httpRefErer = "";
                }
                if ($valid && $isBoundToLaw) {
                    if ($model->isNewRecord) {
                        if ($hasCustomFields) {
                            $attributesArr = array('address1', 'mobile', 'name', 'firstName', 'lastName', 'username', 'password', 'email',
                                'gender', 'zip', 'countryCode', 'joinedDate', 'signUpDate', 'supporterDate', 'userType', 'signup', 'isSysUser', 'dateOfBirth',
                                'reqruiteCount', 'keywords', 'delStatus', 'city', 'isUnsubEmail', 'isManual', 'isSignupConfirmed', 'profImage',
                                'totalDonations', 'isMcContact', 'emailStatus', 'notes', 'longLat', 'formId', 'concern', 'createdAt', 'updatedAt');

                            $model->saveWithCustomData($customFields, $attributesArr, false);
                        } else {
                            $model->save();
                        }
                    } else {
                        $oldMobile = $oldmodel->mobile;
                        $oldEmail = $oldmodel->email;
                        $oldGender = $oldmodel->gender;
                        $oldAddress = $oldmodel->address1;
                        $oldZip = $oldmodel->zip;
                        $oldCity = $oldmodel->city;
                        $oldCountry = $oldmodel->countryCode;
                        $oldDateOfBirth = $oldmodel->dateOfBirth;
                        $oldfirstName = $oldmodel->firstName;
                        $oldlastName = $oldmodel->lastName;
                        $model->firstName = ToolKit::isEmpty($model->firstName) ? $oldfirstName : $model->firstName;
                        $model->lastName = ToolKit::isEmpty($model->lastName) ? $oldlastName : $model->lastName;
                        $model->countryCode = ToolKit::isEmpty($model->countryCode) ? $oldCountry : $model->countryCode;
                        if (!ToolKit::isEmpty($oldmodel->mobile)) {
                            $model->mobile = $oldMobile;
                        }
                        //$model->mobile = ToolKit::isEmpty($oldmodel->mobile) ? $oldMobile : $model->mobile;
                        $model->email = ToolKit::isEmpty($model->email) ? $oldEmail : $model->email;
                        if (!ToolKit::isEmpty($oldmodel->email)) {
                            $model->email = $oldmodel->email;
                        }
                        $model->gender = ToolKit::isEmpty($model->gender) ? $oldGender : $model->gender;
                        $model->address1 = ToolKit::isEmpty($model->address1) ? $oldAddress : $model->address1;
                        $model->zip = ToolKit::isEmpty($model->zip) ? $oldZip : $model->zip;
                        $model->city = ToolKit::isEmpty($model->city) ? $oldCity : $model->city;
                        if (isset($oldmodel->dateOfBirth)) {
                            if (!ToolKit::isEmpty($oldmodel->dateOfBirth)) {
                                $model->dateOfBirth = $oldmodel->dateOfBirth;
                            }
                        }

                        $model->supporterDate = date('Y-m-d H:i:s');
                        $attributes = $_POST;
                        $customFields = CustomValue::getCustomData(CustomType::CF_PEOPLE, $oldmodel->id, CustomField::ACTION_CREATE, ToolKit::post('CustomValue'));

                        if ($hasCustomFields) {
                            foreach ($attributes as $key => $val) {
                                foreach ($customFields as $k => $customField) {
                                    if ($key == $customField->fieldName) {
                                        $customField->fieldValue = $val;
                                        $customField->validate();
                                        break;
                                    }
                                }
                            }
                            $attributesArr = array('address1', 'mobile', 'name', 'firstName', 'lastName', 'username', 'password', 'email',
                                'gender', 'zip', 'countryCode', 'joinedDate', 'signUpDate', 'supporterDate', 'userType', 'signup', 'isSysUser', 'dateOfBirth',
                                'reqruiteCount', 'keywords', 'delStatus', 'city', 'isUnsubEmail', 'isManual', 'isSignupConfirmed', 'profImage',
                                'totalDonations', 'isMcContact', 'emailStatus', 'notes', 'longLat', 'formId', 'concern');
                            $attributes = array_merge($model->attributes, $attributes);
                            //$customFields = CustomValue::model()->getCustomData(CustomType::CF_PEOPLE, $oldmodel->id, CustomField::ACTION_CREATE, $attributes, CustomType::CF_SUB_PEOPLE_BULK_INSERT);
                            $valid = CustomField::validateCustomFieldList($customFields);
                            if ($valid) {
                                $model->saveWithCustomData($customFields, $attributesArr, false);
                            }
                        } else {
                            $model->save(false);
                        }
                    }

                    if ($paymentExist) { //save only if member or donation or both is selected
                        $memberDonation = new FormMembershipDonation();
                        $memberDonation->userId = $model->id;
                        $memberDonation->formId = $form->id;
                        $memberDonation->memberType = ToolKit::isEmpty($model->isMembership) ? null : $model->isMembership;
                        $memberDonation->memberFee = ToolKit::isEmpty($model->isMembership) ? null : Form::getPaymentTypeFee($model->isMembership);
                        $memberDonation->donationFee = ToolKit::isEmpty($model->isDonation) ? null : $model->isDonation;
                        $memberDonation->save(false);
                    }

                    if ($form->notified) {
                        $data = $model->attributes;

                        // send email to politician
                        $userModel = New User();
                        $modelClient = $userModel->getModeratorEmails();

                        // TODO: if the function is work we will remove this line
                        // $modelClient = User::find()->where(['userType' => User::POLITICIAN])->andWhere(['!=','id',User::PARTNER_USER_ID])->one();
                        unset($_POST['formId'], $_POST['callBackUrl'], $_POST['Submit']);

                        if (isset($_POST['CustomValue'])) {
                            $custom = $this->getCustomLabelValuesArray($_POST['CustomValue']);
                            unset($_POST['CustomValue']);
                            $data = array_merge($_POST, $custom);
                        }

                        foreach ($modelClient as $client) {
                            $this->sendEmail($data, $client);
                        }

                    }
                    if (!ToolKit::isEmpty($form->templateId && +$form->templateId > 0)) {
                        Yii::$app->toolKit->setResourceInfo();
                        $templateModel = MessageTemplate::findOne($form->templateId);
                        $templateContent = file_get_contents(Yii::$app->toolKit->resourcePathAbsolute . $templateModel->id . '.html');
                        if (!empty($templateContent)) {
                            $config = Configuration::getConfigurations();
                            $fromEmail = ToolKit::isEmpty($config['FROM_EMAIL']) ? Yii::$app->params['smtp']['senderEmail'] : $config['FROM_EMAIL'];
                            $fromName = ToolKit::isEmpty($config['FROM_NAME']) ? Yii::$app->params['smtp']['senderLabel'] : $config['FROM_NAME'];

                            if (Yii::$app->toolKit->sendEmail(array($model->email), $templateModel->subject, $templateContent, null, null, $fromName, $fromEmail)) {
                                Yii::$app->appLog->writeLog("Template message sent to subscriber." . json_encode($model->attributes));
                            } else {
                                Yii::$app->appLog->writeLog("Template message send failed to subscriber." . json_encode($model->attributes) . json_encode($templateModel->attributes) . "Content:" . $templateContent);
                            }
                        }
                    }

                    $success = true;
                    Yii::$app->appLog->writeLog("Newsletter subscription saved. Url: " . $httpRefErer . " | Callback Url: " . $requestUrl . " Data:" . Json::encode($_POST));

                    if ($paymentExist) {
                        return Yii::$app->response->redirect(['newsletter/payment-process', 'id' => $memberDonation->id]);
                    }
                } else {
                    $success = false;
                    if (!$isBoundToLaw) {
                        $msg = Yii::t('messages', "This form is not obliged with European General Data Protection Regulation, hence the information submitted will not be saved.");
                    }
                    Yii::$app->appLog->writeLog("Newsletter subscription failed. Url: " . $httpRefErer . " | Callback Url: " . $requestUrl . " Validation errors:" . Json::encode($model->errors));
                }
            }
        }
        $params = array(
            'model' => $model,
            'error' => $error,
            'requestUrl' => $requestUrl,
            'hasCustomFields' => $hasCustomFields,
            'customErrors' => $customErrors,
            'success' => $success,
            'msg' => $msg
        );

        // if success redirect here, else render error page
        if ($success) {
            return $this->render('index', $params);

        } else {
            return $this->render('error', $params);
        }

    }

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
     * @param $data
     * @param $email
     */
    private function sendEmail($data, $email)
    {
        $modelConfig = Configuration::findOne(Configuration::LANGUAGE);
        Yii::$app->language = $modelConfig->value;
        $fromEmail = Yii::$app->params['smtp']['senderEmail'];
        $fromName = Yii::$app->params['smtp']['senderLabel'];
        $subject = Yii::t('messages', "Form Submit");
        $message = $message = Yii::$app->controller->renderPartial('@app/views/emailTemplates/notificationBulkEditTemplate', [
                'content' => Yii::t('messages', 'Form has been successfully submitted. Inputs are:'),
                'data' => $data
            ]
        );

        if (Yii::$app->toolKit->sendEmail([$email], $subject, $message, null, null, $fromName, $fromEmail)) {

            // log email for log@digitalebox.fr
            $logSubject = Yii::$app->toolKit->domain . ' - ' . $subject;
            $logMessage = $message;
            Yii::$app->toolKit->sendEmail([Yii::$app->params['smtp']['logsEmail']], $logSubject, $logMessage, null, null, $fromName, $fromEmail); // log email
            Yii::$app->appLog->writeLog("Form submit email sent successfully to:{$email}");
        }
    }

}
