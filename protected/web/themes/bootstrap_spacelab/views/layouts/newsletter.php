<?php /* @var $this Controller */

use yii\helpers\Html; ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="language" content="en" />
 	<title><?php echo Html::encode($this->pageTitle); ?></title>

	<?php 
		Yii::app()->toolKit->registerMainScripts(); 
		//Yii::app()->toolKit->registerCustomScript();
	?>
	
	<style>
		#page{
			padding-top: 30px;
            width: 480px;
		}
	</style>
</head>

<body>
<div class="container" id="page">



	<div class="row-fluid">
		<div class="span12 page-content">

			<?php echo $content; ?>
		</div>
	</div>

	<div class="footer">
		<?php echo Yii::$app->params['copyRight']; ?>
	</div><!-- footer -->

</div><!-- page -->
</body>
</html>
