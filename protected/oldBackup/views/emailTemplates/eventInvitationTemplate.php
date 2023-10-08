<?php

use app\components\ToolKit;

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
    <!--<title>Digitalebox</title>-->
    <!--<title>Digitalebox</title>-->
    <!-- Styling for Outlook -->
    <!--[if (mso 16)]>
    <style type="text/css">
        a {
            text-decoration: none;
        }

        .event-description .event-description-text table {
            max-width: 580px !important;
        }
    </style>
    <![endif]-->
    <!--[if gte mso 9]>
    <style>sup {
        font-size: 100% !important;
    }

    .event-description .event-description-text table {
        max-width: 580px !important;
    }

</style><![endif]-->
    <!--[if !mso]> -->
    <!--<![endif]-->

    <style>
        .main-frame {
            -webkit-box-shadow: 0px 0px 20px 0px rgba(0, 0, 0, 0.1);
            -moz-box-shadow: 0px 0px 20px 0px rgba(0, 0, 0, 0.1);
            box-shadow: 0px 0px 20px 0px rgba(0, 0, 0, 0.1);

            border-radius: 5px;
        }

        table td div {
            margin-bottom: 10px;
        }

        .event-description img {
            max-width: 100% !important;
            height: 100% !important;;
        }

        .event-description .event-description-text table {
            max-width: 650px !important;
        }

    </style>
</head>

<body style="margin: 0; padding: 40px 0px; background-color: #f3f3f4; font-family: 'Helvetica', Arial, sans-serif;">
<style>
    .main-frame {
        -webkit-box-shadow: 0px 0px 20px 0px rgba(0, 0, 0, 0.1);
        -moz-box-shadow: 0px 0px 20px 0px rgba(0, 0, 0, 0.1);
        box-shadow: 0px 0px 20px 0px rgba(0, 0, 0, 0.1);

        border-radius: 5px;
    }

    table td div {
        margin-bottom: 10px;
    }

    div:empty {
        display: none;
    }

</style>
<table align="center" cellpadding="0" cellspacing="0"
       style="width: 650px; border-collapse: collapse; min-height: 100%; background-color: #ffffff">
    <tr>
        <td>

            <table cellpadding="0" cellspacing="0" style="border-collapse: collapse;" width="100%">
                <tr class="main-frame">
                    <td style="font-family: 'Helvetica', Arial, sans-serif;">
                        <table width="100%" cellpadding="0" cellspacing="0"
                               style="border:solid 1px #fff;border-top-left-radius:5px;border-top-right-radius:5px;border-bottom:none;">
                            <!--                            <tr>
                                                            <td style="width:600px; padding:3px;background-color:#009994;border-top-left-radius:5px;border-top-right-radius:5px;"></td>
                                                        </tr>-->
                        </table>
                        <!--Header-->

                        <!--[if mso]>
                                    <table width="650" style="max-width:650px;">
                                        <tr>
                                            <td>
                                                <img src=<?php echo $imgUrl; ?> width="650" />
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div style="font-size: 14px; padding: 10px; text-align: center; color: #232323;">
                                                    <a href="" style="text-decoration: none;"><?php echo $model->location ?></a>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                        <![endif]-->
                        <table width="650px" cellpadding="0" cellspacing="0"
                               style="border: solid 1px #fff; border-top-left-radius: 5px; border-top-right-radius: 5px; border-bottom: none; mso-hide:all;">
                            <tr>
                                <td style="width:650px">
                                    <div style="width: 100%; text-align: center; margin-top: 30px; margin-bottom: 30px;">

                                    </div>
                                </td>
                            </tr>
                            <?php if (!ToolKit::isEmpty($model->imageName)) { ?>
                                <tr>
                                    <td style="text-align: center; width: 100%; max-width: 100%;">
                                        <img style="width: 630px; margin:0 auto;" src=<?php echo $imgUrl; ?>>
                                    </td>
                                </tr>
                            <?php } ?>
                            <tr>
                                <td>
                                    <div style="font-size: 14px; padding: 10px; text-align: center; color: #232323;">
                                        <a href="" style="text-decoration: none;"><?php echo $model->location ?></a>
                                    </div>
                                </td>
                            </tr>
                        </table>

                        <table class="event-description" width="650px"
                               style="background-color: #fff; border-left: solid 1px #fff; border-right: solid 1px #fff;"
                               cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="color: #555555; font-family: 'Helvetica', Arial, sans-serif; text-align: center; width: 650px;">
                                    <!--Location button for Outlook -->
                                    <!--[if mso]>
                                    <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml"
                                                 xmlns:w="urn:schemas-microsoft-com:office:word"
                                                 href="http://maps.google.com/?q=<?php echo $model->location; ?>"
                                                 style="margin-top: 20px;height:36px;v-text-anchor:middle;width:230px;"
                                                 arcsize="3%" strokecolor="#A9A9A9" fillcolor="#A9A9A9">
                                        <w:anchorlock/>
                                        <center style="color:#ffffff;"><?php echo Yii::t('messages', 'Event Location'); ?></center>
                                    </v:roundrect>
                                    <![endif]-->
                                    <a style="margin-top: 20px;background-color: darkgray; color: #fff; padding:5px 20px; text-decoration: none; margin-right: 5px; text-align: center;mso-hide:all;"
                                       href="http://maps.google.com/?q=<?php echo $model->location; ?>">
                                        <?php echo Yii::t('messages', 'Event Location'); ?> </a>
                                </td>
                            </tr>
                        </table>

                        <table width="100%" border="0" cellpadding="2" cellspacing="2"
                               style="text-align: center;margin-top: 30px; border-color: #fff; ">
                            <tr>
                                <td colspan="5" style="text-align: center;margin-top: 30px; border-color: #fff;">
                                    <b><?php echo Yii::t('messages', 'Please Confirm Your Participation'); ?> </b></td>
                            </tr>
                            <tr style="border-color: #fff;">
                                <th width="20%" style="border-color: #fff;"></th>
                                <th width="20%" style="border-color: #fff;">

                                    <!--Accept button for Outlook -->
                                    <!--[if mso]>
                                    <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml"
                                                 xmlns:w="urn:schemas-microsoft-com:office:word"
                                                 href="<?php echo $acceptUri; ?>"
                                                 style="height:36px;v-text-anchor:middle;width:120px;" arcsize="3%"
                                                 strokecolor="#57BDB9" fillcolor="#57BDB9">
                                        <w:anchorlock/>
                                        <center style="color:#ffffff;"><?php echo Yii::t('messages', 'Yes'); ?></center>
                                    </v:roundrect>
                                    <![endif]-->

                                    <a style="background-color: #57BDB9; color: #fff; padding: 5px 20px;
                                                           text-decoration: none; -webkit-border-radius: 2px; -moz-border-radius: 2px;
                                                           border-radius: 2px; margin-right: 5px; height: 20px; display: block;width:80px; float:right; mso-hide:all;"
                                       href="<?php echo $acceptUri; ?>"> <?php echo Yii::t('messages', 'Yes'); ?></a>
                                </th>
                                <th width="20%" style="text-align:-webkit-center; border-color: #fff;">

                                    <!--No button for Outlook -->
                                    <!--[if mso]>
                                    <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml"
                                                 xmlns:w="urn:schemas-microsoft-com:office:word"
                                                 href="<?php echo $rejectUri; ?>"
                                                 style="height:36px;v-text-anchor:middle;width:120px;" arcsize="3%"
                                                 strokecolor="#ED7E7D" fillcolor="#ED7E7D">
                                        <w:anchorlock/>
                                        <center style="color:#ffffff;"><?php echo Yii::t('messages', 'No'); ?></center>
                                    </v:roundrect>
                                    <![endif]-->

                                    <a style="background-color: #ED7E7D; color: #fff; padding: 5px 20px;
                                                                                             text-decoration: none; -webkit-border-radius: 2px; -moz-border-radius: 2px;
                                                                                             border-radius: 2px; margin-right: 5px; height: 20px; display: block;width:80px; mso-hide:all;"
                                       href="<?php echo $rejectUri; ?>"> <?php echo Yii::t('messages', 'No'); ?></a>
                                </th>
                                <th width="20%" style="border-color: #fff;">

                                    <!--Maybe button for Outlook -->
                                    <!--[if mso]>
                                    <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml"
                                                 xmlns:w="urn:schemas-microsoft-com:office:word"
                                                 href="<?php echo $maybeUri; ?>"
                                                 style="height:36px;v-text-anchor:middle;width:120px;" arcsize="3%"
                                                 strokecolor="#FDD695" fillcolor="#FDD695">
                                        <w:anchorlock/>
                                        <center style="color:#ffffff;"><?php echo Yii::t('messages', 'Maybe'); ?></center>
                                    </v:roundrect>
                                    <![endif]-->
                                    <a style="background-color: #FDD695; color: #fff; padding: 5px 20px;
                                                           white-space: nowrap; text-decoration: none; -webkit-border-radius: 2px;
                                                           -moz-border-radius: 2px; border-radius: 2px;  height: 20px; display: block;width:80px; mso-hide:all;"
                                       href="<?php echo $maybeUri; ?>"> <?php echo Yii::t('messages', 'Maybe'); ?></a>
                                </th>
                                <th width="20%" style="border-color: #fff;"></th>
                            </tr>
                        </table>

                        <!--Content-->
                        <table class="event-description" width="650px"
                               style="background-color: #fff; border-left: solid 1px #fff; border-right: solid 1px #fff; border-bottom: solid 1px #fff; border-bottom-left-radius: 5px; border-bottom-right-radius: 5px;"
                               cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="color: #555555; font-family: 'Helvetica', Arial, sans-serif; max-width: 100%;">
                                    <div style="margin: 30px 40px; max-width: 100%;">
                                        <div class="event-description-text" style="font-size: 14px;  max-width: 100%; line-height: 22px;mso-hide:all; color: #737373; font-family: 'Helvetica', Arial, sans-serif;">
                                            <?php echo str_replace("margin: auto;", "margin:none;", $model->description); ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>