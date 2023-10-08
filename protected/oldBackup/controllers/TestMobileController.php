<?php


namespace app\controllers;

use Yii;
use app\components\ToolKit;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberUtil;
use yii\web\Controller;

class TestMobileController extends Controller
{

    public function actionIndex() {
        $mobile = '4444';
        $countryCode = '';
        $interNational = $this->getInternationalMobileNo($mobile, $countryCode);

        echo str_replace(' ', '', trim($interNational));;

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
}
