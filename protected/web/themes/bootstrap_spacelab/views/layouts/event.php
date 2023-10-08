<?php /* @var $this Controller */ ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="language" content="en" />

	<title><?php echo CHtml::encode($this->pageTitle); ?></title>

	<?php Yii::app()->toolKit->registerSignupScripts(); ?>
</head>

<body>

<div class="container" id="page">
	<div class="page-content">
		<div class="page-content-inner">
			<div class="row">
				<div class="row"><?php echo $content; ?></div>
			</div>
		</div>
	</div>
	
	<div class="clear"></div>

	<div class="footer">
		<?php echo Yii::app()->params['copyRight']; ?>
	</div><!-- footer -->

</div><!-- page -->

</body>
</html>
