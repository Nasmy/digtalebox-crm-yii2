<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\widgets\Alert;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\LoginAsset;
use app\components\View;

LoginAsset::register($this);

$js = <<<JS
 
    $(document).on("pjax:beforeSend",function(e){
        jQuery('.grid-view').addClass('grid-view-loading');
    }).on("pjax:end",function(){
                jQuery('.grid-view').removeClass('grid-view-loading');
    });
JS;
$this->registerJs($js, View::POS_READY);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <?= Yii::$app->toolKit->registerMainScripts(); ?>
    <?= Yii::$app->toolKit->registerBackgroundImage(); ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="<?php echo Yii::$app->toolKit->getImagePath() ?>favicon/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
    <link rel="apple-touch-icon" href="<?php echo Yii::$app->toolKit->getImagePath() ?>favicon/apple-icon-114x114.png">
    <link rel="icon" type="image/png" href="<?php echo Yii::$app->toolKit->getImagePath() ?>favicon/favicon-96x96.png">
    <link rel="manifest" href="<?php echo Yii::$app->toolKit->getImagePath() ?>favicon/manifest.json">
    <link rel="icon" href="<?php echo Yii::$app->toolKit->getImagePath() ?>favicon/favicon.ico" type="image/x-icon">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<body class="<?php echo Yii::$app->session->get('themeStyle') ?>">
<div id="app-main">
    <div class="frame">
        <div id="page">
            <div class="row-fluid">
                <div class="span12 page-content">
                    <?= $content; ?>
                </div>
            </div>
        </div><!-- page -->
        <div class="footer"></div>
    </div>
</div>
<!--[if lt IE 8]>
<p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade
    your browser</a> to improve your experience.</p>
--->
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
