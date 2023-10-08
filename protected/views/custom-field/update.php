<?php
$attributeLabels = $model->attributeLabels();

$this->title = Yii::t('messages', 'Update Custom Field');
$this->titleDescription = Yii::t('messages', 'Update Custom Field details');

$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'System'), 'url' => ['#']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Custom Fields'),'url'=>['admin']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Update Custom Field')];

$params = array(
    'customTypesCodes' => $customTypesCodes,
    'model' => $model,
    'action' => 'update',
    'attributeLabels' => $attributeLabels
);

echo Yii::$app->controller->renderPartial('_form', $params);
?>