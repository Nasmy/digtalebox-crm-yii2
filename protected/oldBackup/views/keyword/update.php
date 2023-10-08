<?php

$attributeLabels = $model->attributeLabels();

$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'People'), 'url' => '#'];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Manage Keywords'),'url' => ['admin']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Update Keyword')];


$params = array(
    'model' => $model,
    'attributeLabels' => $attributeLabels
);

echo Yii::$app->controller->renderPartial('_form',$params); ?>