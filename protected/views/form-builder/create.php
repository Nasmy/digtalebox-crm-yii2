<?php
$attributeLabels = $model->attributeLabels();

$this->title = Yii::t('messages','Add Form');
$this->titleDescription = Yii::t('messages','Define new Form');
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'System'), 'url' => ['#']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Forms'),'url' => ['admin']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Add Form')];

?>
<?php
$params = array(
    'model' => $model,
    'keywords'=>$keywords,
    'attributeLabels' => $attributeLabels
);
?>
<?php echo Yii::$app->controller->renderPartial('_form', $params); ?>