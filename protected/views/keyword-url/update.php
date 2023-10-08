<?php

$this->title = Yii::t('messages','Update Keyword Url');
$this->titleDescription = Yii::t('messages','Update Keyword Url');
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Communication'), 'url' => ['#']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Keyword Url'), 'url' => ['admin']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Update')];


?>
<?php
$params = array(
    'model' => $model,
    'keywords'=>$keywords,
);
?>
<?php echo Yii::$app->controller->renderPartial('_form', $params); ?>
