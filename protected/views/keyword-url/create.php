<?php

$this->title = Yii::t('messages','Add Url');
$this->titleDescription = Yii::t('messages','Define new url');
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Communication'), 'url' => ['#']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Keyword Url'), 'url' => ['admin']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Add Url')];


?>
<?php
$params = array(
    'model' => $model,
    'keywords'=>$keywords,
);
?>
<?php echo Yii::$app->controller->renderPartial('_form', $params); ?>