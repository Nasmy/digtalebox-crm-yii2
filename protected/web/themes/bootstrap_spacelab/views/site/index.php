<?php
/* @var $this SiteController */

$this->pageTitle=Yii::$app->name;
?>

<?php /*$this->beginWidget('bootstrap.widgets.TbHeroUnit',array(
    'heading'=>Yii::t('messages', 'Welcome to {name}', array('{name}'=>\yii\helpers\Html::encode(Yii::$app->name))),
	'htmlOptions'=>array('class'=>'hero-unit-home')
));*/ ?>

<?php // $this->endWidget(); ?>

<?php
	if (!$isSiteGuideViewed) {
		$this->renderPartial('_siteGuide');
	}
?>
