<?php
$attributeLabels = $model->attributeLabels();

$this->title = Yii::t('messages','Update Membership Type');
$this->titleDescription = Yii::t('messages','Update membership type from the system');

$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'System'), 'url' => ['#']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Manage Membership Type'), 'url' => ['admin']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Update')];

?>
<?php echo Yii::$app->controller->renderPartial('_form', array('model'=>$model, 'attributeLabels' => $attributeLabels)); ?>