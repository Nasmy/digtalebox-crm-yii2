<?php

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

use yii\helpers\Html;
use yii\web\UrlManager;

$previousUrl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : Yii::$app->urlManager->createUrl('dashboard/dashboard');

?>
<?php echo $code; ?>