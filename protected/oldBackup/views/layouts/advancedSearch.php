<?php

/* @var $this \yii\web\View */

/* @var $content string */

use app\widgets\Alert;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;
use app\models\CandidateInfo;
use app\models\Feed;
use yii\helpers\Url;
use app\models\User;
use app\models\AuthItem;
use app\models\App;

AppAsset::register($this);
?>
<style>
    .app-body {
        background-color: #fff !important;
    }
</style>
<?php $this->beginPage() ?>
<!doctype html>
<!--[if lt IE 7]>
<html class="no-js lt-ie9 lt-ie8 lt-ie7" lang=""> <![endif]-->
<!--[if IE 7]>
<html class="no-js lt-ie9 lt-ie8" lang=""> <![endif]-->
<!--[if IE 8]>
<html class="no-js lt-ie9" lang=""> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js" lang=""> <!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="language" content="en">
    <title><?php echo Html::encode($this->title); ?></title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>

    <!--favicon-->
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage"
          content="<?php echo Yii::$app->toolKit->getImagePath() ?>favicon/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
    <link rel="apple-touch-icon"
          href="<?php echo Yii::$app->toolKit->getImagePath() ?>favicon/apple-icon-114x114.png">
    <link rel="icon" type="image/png"
          href="<?php echo Yii::$app->toolKit->getImagePath() ?>favicon/favicon-96x96.png">
    <link rel="manifest" href="<?php echo Yii::$app->toolKit->getImagePath() ?>favicon/manifest.json">
    <link rel="icon" href="<?php echo Yii::$app->toolKit->getImagePath() ?>favicon/favicon.ico" type="image/x-icon">
    <script type="text/javascript" src="/themes/bootstrap_spacelab/js/jquery.min.js"></script>

    <?php Yii::$app->toolKit->registerAdvanceSearchUpdate(); ?>
    <?php $this->head() ?>
</head>
<!-- app header php end -->
<body class="<?php echo Yii::$app->session->get('themeStyle'); ?>" style="padding-top: 0px;padding-bottom: 0px">
<div class="app-body modal-body">
    <div id="dialog-page">
        <div id="statusMsg"></div>
        <div class="row-fluid">
            <div class="span12 page-content-dialog ">
                <div id="statusMsg">
                    <?php if (Yii::$app->session->hasFlash('success')): ?>
                        <div class="alert alert-success alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <?= Yii::$app->session->getFlash('success') ?>
                        </div>
                    <?php endif; ?>

                    <?php if (Yii::$app->session->hasFlash('error')): ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <?= Yii::$app->session->getFlash('error') ?>
                        </div>
                    <?php endif; ?>
                    <?= $content; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
