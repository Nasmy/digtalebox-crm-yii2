<?php

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

use yii\helpers\Html;
use yii\web\UrlManager;

$previousUrl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : Yii::$app->urlManager->createUrl('dashboard/dashboard');

?>

<div class="container">
    <div class="row no-gutters">
        <div class="col-md-12">
            <div class="server-errors row">
                <div class="error-img col-sm-5 col-md-5"><img src="<?php echo Yii::$app->toolKit->getImagePath() ?>server-error.svg"></div>
                <div class="error-number col-sm-7 col-md-7 align-middle"><?php echo $code; ?></div>
            </div>

            <div class="text-area">
                <div class="main-line"><?php echo Html::encode($message);
                    yii::$app->appLog->writeLog(Html::encode($message));
                ?></div>
                <div class="sub-line"><?php echo Yii::t('messages','Here are some helpful links instead') ?></div>

                <div>
                    <a href="<?php echo Yii::$app->request->referrer; ?>">
                        <?php echo Yii::t('messages','Previous page') ?></a>   |   <a href="<?php echo Yii::$app->urlManager->createUrl('dashboard/dashboard') ?>"><?php echo Yii::t('messages','Home') ?></a>
                </div>
            </div>
        </div>

    </div>
</div>