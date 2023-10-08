<?php /* @var $this Controller */

use app\assets\DialogAsset;
use yii\helpers\Html;

DialogAsset::register($this);
?>
<?php $this->beginPage() ?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="language" content="en" />

    <title><?php echo Html::encode($this->title); ?></title>

    <?php Yii::$app->toolKit->registerMainScripts(); ?>

    <?php //Yii::app()->toolKit->registerDialogScripts(); ?>
    <?php $this->head() ?>

</head>

<body class="app-body"  style="padding-top: 0px;padding-bottom: 0px">
<?php $this->beginBody() ?>

<div class="row no-gutters">
    <div class="content-panel col-md-12">
        <div class="content-inner">
            <div class="content-area">
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
                <?php if (null != $this->title): ?>
                    <div class="content-panel col-md-12">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="title">
                                    <?php echo $this->title ?>
                                </div>
                                <div class="desc">
                                    <?php echo $this->titleDescription ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php echo $content; ?>
            </div>
        </div>
    </div>
</div>
<div class="clear"></div>

</div><!-- page -->
<?php $this->endBody() ?>

</body>
</html>
<?php $this->endPage() ?>