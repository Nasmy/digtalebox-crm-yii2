<?php
$attributeLabels = $model->attributeLabels();

$this->title = Yii::t('messages','Update Keyword');
$this->titleDescription = Yii::t('messages','Modify keyword details');

$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'System'), 'url' => ['#']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Feed Keywords'), 'url' => ['feed-search-keyword/admin']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Update Keyword')];

?>
<?php echo Yii::$app->controller->renderPartial('_form', array('model'=>$model, 'attributeLabels' => $attributeLabels)); ?>