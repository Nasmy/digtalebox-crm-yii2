<?php
/* @var $this SiteController */

$this->pageTitle=Yii::$app->name;
?>

<div class="container">
    <div class="row justify-content-md-center">
            <div class="alert alert-error w-100">
                <strong><?php echo Yii::t('messages', 'Sorry !') ?></strong> <?php echo Yii::t('messages', 'Service currently unavailable') ?>
            </div>
    </div>
</div>

