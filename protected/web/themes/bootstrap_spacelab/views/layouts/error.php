<?php
/* @var $this \yii\web\View */
/* @var $content string */

use app\models\CandidateInfo;
use app\models\Feed;
use app\widgets\Alert;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;
use yii\helpers\Url;
use app\models\AuthItem;
use app\models\App;
AppAsset::register($this);
?>
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

    <?php Yii::$app->toolKit->registerMainScripts(); ?>
    <?php $this->head() ?>
</head>
<body class="error-page">

<?php $this->beginBody() ?>
<!--[if lt IE 8]>
<p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a
        href="http://browsehappy.com/">upgrade
    your browser</a> to improve your experience.</p>
<![endif]-->
<div class="container">
    <?php echo $content; ?>
</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
