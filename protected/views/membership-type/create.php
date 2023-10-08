<?php
$attributeLabels = $model->attributeLabels();

$this->title = Yii::t('messages','Create Membership Type');
$this->titleDescription = Yii::t('messages','Add new membership type to the system');
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'System'), 'url' => ['#']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Manage Membership Type'), 'url' => ['admin']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Create')];

?>
 <?php echo Yii::$app->controller->renderPartial('_form', array('model'=>$model, 'attributeLabels' => $attributeLabels)); ?>