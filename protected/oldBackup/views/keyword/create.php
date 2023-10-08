<?php

$attributeLabels = $model->attributeLabels();

$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'People'), 'url' => '#'];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Manage Keywords'),'url' => ['admin']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Add Keyword')];

$this->title = Yii::t('messages', 'Add Keyword');
$this->titleDescription = Yii::t('messages', 'Define new keyword');

?>

<?php
$params = array(
    'model' => $model,
    'attributeLabels' => $attributeLabels
);
?>

<?php echo Yii::$app->controller->renderPartial('_form',$params); ?>