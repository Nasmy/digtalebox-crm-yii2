<?php

use app\models\Country;
use yii\helpers\Html;
use app\models\Keyword;
use app\models\User;

$user = new User();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
    <title>Digitalebox</title>

    <style>
        .main-frame{
            -webkit-box-shadow: 0px 0px 20px 0px rgba(0, 0, 0, 0.1);
            -moz-box-shadow:    0px 0px 20px 0px rgba(0, 0, 0, 0.1);
            box-shadow:         0px 0px 20px 0px rgba(0, 0, 0, 0.1);

            border-radius: 5px;
        }
    </style>
</head>

<body style="margin: 0; padding: 40px 0px; background-color: #f3f3f4; font-family: 'Helvetica', Arial, sans-serif;">

<table align="center" cellpadding="0" cellspacing="0" style="width: 600px; border-collapse: collapse; min-height: 100%;">
    <tr>
        <td style="font-family: 'Helvetica', Arial, sans-serif;">

            <table cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
                <tr class="main-frame">
                    <td style="font-family: 'Helvetica', Arial, sans-serif;">
                        <!--Header-->
                        <table width="100%" cellpadding="0" cellspacing="0" style="border: solid 1px #e8e8e9; border-top-left-radius: 5px; border-top-right-radius: 5px; border-bottom: none;">
                            <tr>
                                <td style="padding: 30px 40px; background-color: #f8f8f8; border-top-left-radius: 5px; border-top-right-radius: 5px;"><img src="<?php echo Yii::$app->toolKit->getWebRootUrl() . 'images/emailLogo.png' ?>" alt="Digitalebox" style="width: 175px; border-collapse: collapse;"></td>
                            </tr>
                        </table>

                        <!--Content-->
                        <table width="100%" style="background-color: #fff; border-left: solid 1px #e8e8e9; border-right: solid 1px #e8e8e9; border-bottom: solid 1px #e8e8e9; border-bottom-left-radius: 5px; border-bottom-right-radius: 5px;" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="color: #555555; font-family: 'Helvetica', Arial, sans-serif;">
                                    <div style="margin: 30px 40px; width: 600px;">
                                        <p style="font-size: 14px; margin-top: 0; color: #009994;"><?php echo Yii::t('messages', 'Dear User,'); ?></p>

                                        <div style="font-size: 14px; line-height: 22px; color: #737373;">
                                            <p><?php echo $content ?></p>

                                            <table style="background-color: #fbfbfb; border: none; font-family: 'Helvetica', Arial, sans-serif;">
                                                <thead>
                                                <tr>
                                                    <td style="padding: 5px 10px;">Field</td>
                                                    <td> &nbsp; </td>
                                                    <td style="padding: 5px 10px;">Content</td>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php
                                                $count = 0;
                                                $data = json_decode(json_encode($data), true);        //Convert stdClass to array format
                                                if(array_key_exists("isUnsubEmail", $data)){            //Search in array there is any subscription related data
                                                    unset($data["emailStatus"]);
                                                }
                                                $dropdownAttrs = array('gender', 'countryCode', 'userType', 'emailStatus', 'isUnsubEmail', 'keywords');
                                                foreach ($data as $key => $value) {
                                                    if (in_array($key, $dropdownAttrs)) {
                                                        switch ($key) {
                                                            case 'gender':
                                                                $value = $user->getGenderLabel($value);
                                                                break;
                                                            case 'countryCode':
                                                                $value = Country::getContryByCode($value);
                                                                break;
                                                            case 'userType':
                                                                $value = $user->getUserTypes($value);
                                                                break;
                                                            // Fix email template
                                                            // output
                                                            case 'emailStatus':
                                                                $value = $user->getDbEmailStatus($value);
                                                                break;
                                                            case 'isUnsubEmail':
                                                                $key = 'Email Status';
                                                                $value = $user->getDbEmailStatus($value);
                                                                break;
                                                            case 'keywords':
                                                                $value = explode(",", $value);
                                                                $keywordArr = array();
                                                                foreach ($value as $keyword) {
                                                                    $KeywordOne = Keyword::find()->where(['id'=>$keyword])->one();
                                                                    if(isset($KeywordOne->name)) {
                                                                        $keywordArr[] = $KeywordOne->name;
                                                                    }
                                                                }
                                                                $value = implode(",", $keywordArr);
                                                                break;
                                                        }
                                                    }
                                                    //$key = User::getAttributeLabel($key);
                                                    $value = is_array($value) ? implode(',', $value) : $value;

                                                    echo "<tr><td style=\"padding: 5px 10px;\">$key</td><td> - </td><td style=\"padding: 5px 10px;\"><strong>$value</strong></td></tr>";
                                                    $count++;
                                                }
                                                ?>
                                                </tbody>

                                            </table>
                                        </div>

                                        <p style="font-size: 14px; color: #009994; line-height: 18px; margin-top: 30px; font-family: 'Helvetica', Arial, sans-serif;">
                                            <?php echo Yii::t('messages', 'Thank You,'); ?><br>
                                            <strong style="color: #009994;"><?php echo Yii::t('messages', 'The DigitaleBox Team'); ?></strong><br>
                                        <div style="color: #009994;">DigitaleBox SAS</div><br>
                                        <a style="color: #009994;" href="http://www.digitalebox.fr">www.digitalebox.fr</a>
                                        </p>

                                    </div>
                                </td>
                            </tr>

                        </table>
                    </td>
                </tr>
            </table>

            <!--Footer-->
            <table width="100%" style="color: #737373; font-family: 'Helvetica', Arial, sans-serif;">

                <tr>
                    <td valign="top" style="font-family: 'Helvetica', Arial, sans-serif; font-size: 12px; padding: 20px;">
                        <div style="font-weight: bold; color: #737373; margin-bottom: 5px;">
                            <?php echo Yii::t('messages', 'DigitaleBox | Organizing community software'); ?>
                        </div>
                        <div style="color: #737373; margin-bottom: 8px;">
                            Tel: +33 (0)1 71 02 01 17<br>
                            Email: <a style="color: #737373;" href="mailto:support@digitalebox.fr">support@digitalebox.fr</a>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td colspan="2">
                        <div style="font-family: 'Helvetica', Arial, sans-serif; font-size: 12px; text-align: center; color: #737373; margin-bottom: 20px; border-top: solid 1px #d2d2d2; border-bottom: solid 1px #d2d2d2; padding: 10px;">
                            <?php echo Yii::t('messages', 'Copyright © 2018 DigitaleBox. All rights reserved'); ?>
                        </div>
                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>