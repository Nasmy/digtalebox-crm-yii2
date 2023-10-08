<?php /* @var $this Controller */ ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="language" content="en" />

	<title><?php echo CHtml::encode($this->pageTitle); ?></title>

	<?php Yii::app()->toolKit->registerMainScripts(); ?>
</head>

<body>

<div class="container" id="page">

	<?php 
	$this->widget('bootstrap.widgets.TbNavbar',array(
		'type'=>'null',
		'collapse'=>true,
		'fixed' => 'top',
		'items'=>array(
		),
	)); ?>

    <div class="span12">
        <div class="span9">
			<?php
			$this->widget('bootstrap.widgets.TbAlert', array(
				'id'=>'statusMsg',
				'block'=>false, // display a larger alert block?
				'fade'=>true, // use transitions?
				'closeText'=>'x', // close link text - if set to false, no close link is displayed
				'alerts'=>array(// configurations per alert type
					'success'=>array('block'=>false, 'fade'=>true, 'closeText'=>'&times;'), // success, info, warning, error or danger
					'error'=>array('block'=>false, 'fade'=>true, 'closeText'=>'&times;'),
					'info'=>array('block'=>true, 'fade'=>true, 'closeText'=>'&times;'),
					'warning'=>array('block'=>true, 'fade'=>true, 'closeText'=>'&times;'),
				),
			));
			?>
            <div class="page-content">
                <div class="page-content-inner">
                    <?php echo $content; ?>
                </div>
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
