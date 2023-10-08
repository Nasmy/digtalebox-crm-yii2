<?php $this->widget('bootstrap.widgets.TbDetailView', array(
    'data'=>$model,
    'attributes'=>array(
    	array(
    		'type' => 'raw',
    		'value'=>$model->getPic($model->profImage, 50,50),
    	),
    	array(
    		'type' => 'raw',
    		'label'=>Yii::t('messages','Name'),
    		'value'=>$model->getName(),
    	),
        'email',	
    	array(
    		'type' => 'raw',
    		'label'=>Yii::t('messages','Networks'),
    		'value'=>$model->getNetworkSummary($model),
    	), 
    ),
)); ?>

<?php echo CHtml::label($model->isNetworkProfileExist($model,FacebookApi::FACEBOOK,$model->name,false),FacebookApi::FACEBOOK); ?>
<?php echo CHtml::label($model->isNetworkProfileExist($model,TwitterApi::TWITTER,$model->name,false),TwitterApi::TWITTER); ?>