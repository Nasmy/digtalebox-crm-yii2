<?php /* @var $this Controller */

use app\assets\DialogAsset;
use yii\helpers\Html;
use app\components\View;

DialogAsset::register($this);


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
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="language" content="en" />

    <title><?php echo Html::encode($this->title); ?></title>

    <?php Yii::$app->toolKit->registerMainScripts(); ?>

	<?php //Yii::app()->toolKit->registerDialogScripts(); ?>
    <?php $this->head() ?>

</head>

<style>
    .app-body {
        background-color: #fff !important;
    }
</style>

<body class="<?php echo Yii::$app->session['themeStyle'] ?> p-0 ">
<?php $this->beginBody() ?>

<div class="app-body modal-body">
<div id="dialog-page">
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

	<div id="statusMsg"></div>
		<div class="row-fluid">
			<div class="span12 page-content-dialog ">
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
				<?php echo $content; ?>
			</div>
		</div>
	<div class="clear"></div>

</div><!-- page -->
</div>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>