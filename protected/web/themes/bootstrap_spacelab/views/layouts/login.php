<?php /* @var $this Controller */ ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="language" content="en" />

	<title><?php echo CHtml::encode($this->pageTitle); ?></title>

	<?php Yii::app()->toolKit->registerLoginScripts(); ?>
	<?php Yii::app()->toolKit->registerMainScripts(); ?>
</head>

<body>
	<?php echo $content ?>
</body>
</html>
