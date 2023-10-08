<?php
use yii\helpers\Html;

/* @var $this \yii\web\View view component instance */
/* @var $message \yii\mail\MessageInterface the message being composed */
/* @var $content string main view render result */
?>

<?php $this->beginPage() ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?= Yii::$app->charset ?>" />
    <title>Digitalebox</title>

    <style>
        .main-frame {
            -webkit-box-shadow: 0px 0px 20px 0px rgba(0, 0, 0, 0.1);
            -moz-box-shadow: 0px 0px 20px 0px rgba(0, 0, 0, 0.1);
            box-shadow: 0px 0px 20px 0px rgba(0, 0, 0, 0.1);

            border-radius: 5px;
        }
    </style>
</head>

<body style="margin: 0; padding: 40px 0px; background-color: #f3f3f4; font-family: 'Helvetica', Arial, sans-serif;">
<?php $this->beginBody() ?>
<table align="center" cellpadding="0" cellspacing="0"
       style="width: 600px; border-collapse: collapse; min-height: 100%;">
    <tr>
        <td>

            <table cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
                <tr class="main-frame">
                    <td style="font-family: 'Helvetica', Arial, sans-serif;">
                        <!--Header-->
                        <table width="100%" cellpadding="0" cellspacing="0"
                               style="border: solid 1px #e8e8e9; border-top-left-radius: 5px; border-top-right-radius: 5px; border-bottom: none;">
                            <tr>
                                <td style="padding: 30px 40px; background-color: #f8f8f8; border-top-left-radius: 5px; border-top-right-radius: 5px;">
                                    <img src="<?php echo Yii::$app->toolKit->getWebRootUrl() . 'images/emailLogo.png' ?>"
                                         alt="Digitalebox" style="width: 175px; border-collapse: collapse;"></td>
                            </tr>
                        </table>

                        <!--Content-->
                        <table width="100%"
                               style="background-color: #fff; border-left: solid 1px #e8e8e9; border-right: solid 1px #e8e8e9; border-bottom: solid 1px #e8e8e9; border-bottom-left-radius: 5px; border-bottom-right-radius: 5px;"
                               cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="color: #555555; font-family: 'Helvetica', Arial, sans-serif;">
                                    <div style="margin: 30px 40px; width: 600px;">
                                        <!--  <p style="font-size: 14px; margin-top: 0; color: #009994; font-family: 'Helvetica', Arial, sans-serif;">-->
                                        <!--  --><?php //echo Yii::t('messages', 'Dear User,'); ?>
                                        <!--</p>-->

                                        <div style="font-size: 14px; line-height: 22px; color: #737373; font-family: 'Helvetica', Arial, sans-serif;">
                                            <p>
                                                <?= $content ?>
                                            </p>
                                        </div>

                                        <p style="font-size: 14px; color: #009994; line-height: 18px; font-family: 'Helvetica', Arial, sans-serif; margin-top: 30px;">
                                            <?php echo Yii::t('messages', 'Thank You,'); ?><br>
                                            <strong style="color: #009994;"><?php echo Yii::t('messages', 'The DigitaleBox Team'); ?></strong><br>
                                            <span style="color: #009994;">DigitaleBox SAS</span><br>
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
                    <!--                    <td valign="top" align="right" style="padding: 20px; color: #009994;">-->
                    <!--                        <a style="color: #009994; font-size: 12px;" href="">Unsubscribe</a>  |  <a style="color: #009994; font-size: 12px;" href="">Help</a>-->
                    <!--                    </td>-->
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
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
