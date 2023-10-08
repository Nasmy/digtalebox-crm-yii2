<?php
$attributeLabels = $model->attributeLabels();

$this->title = Yii::t('messages', 'Add Custom Field');
$this->titleDescription = Yii::t('messages', 'Define new Custom Field');

$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'System'), 'url' => ['#']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Custom Fields'),'url'=>['admin']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Add Custom Field')];

$params = array(
    'customTypesCodes' => $customTypesCodes,
    'model' => $model,
    'action' => 'create',
    'attributeLabels' => $attributeLabels
);

echo yii::$app->controller->renderPartial('_form', $params);

?>