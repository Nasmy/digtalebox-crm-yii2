<?php /* @var $this Controller */ ?>
<?php $this->beginContent('@app/web/themes/bootstrap_spacelab/views/layouts/main'); ?>
<div class="page-content">
	<div class="page-content-inner">
		<div class="row">
			<div class="span3">
				<div class="submenu1">
					<?php
						$this->widget('bootstrap.widgets.TbMenu', array(
							'type'=>'list', // '', 'tabs', 'pills' (or 'list')
							'stacked'=>false, // whether this is a stacked menu
							'items'=>$this->menu,
						));
					?>
				</div>
			</div>
			<div class="span8">
				<?php
					$this->widget('bootstrap.widgets.TbAlert', array(
						'id'=>'statusMsg',
						'block'=>true, // display a larger alert block?
						'fade'=>true, // use transitions?
						'closeText'=>'&times;', // close link text - if set to false, no close link is displayed
						'alerts'=>array(// configurations per alert type
							'success'=>array('block'=>true, 'fade'=>true, 'closeText'=>'&times;'), // success, info, warning, error or danger
							'error'=>array('block'=>true, 'fade'=>true, 'closeText'=>'&times;'),
							'info'=>array('block'=>true, 'fade'=>true, 'closeText'=>'&times;'),
							'warning'=>array('block'=>true, 'fade'=>true, 'closeText'=>'&times;'),
						),
					));
				?>	
				<?php echo $content; ?>
			</div>
		</div>
	</div>
</div>
<?php $this->endContent(); ?>