<?php
$attributeLabels = $model->attributeLabels();

$this->title = Yii::t('messages','Update Form');
$this->titleDescription = Yii::t('messages','Update Form');

$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'System'), 'url' => ['#']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Forms'),'url' => ['admin']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Update Form')];

?>
<?php
$params = array(
    'model' => $model,
    'keywords'=>$keywords,
    'attributeLabels' => $attributeLabels
);
?>
<?php echo Yii::$app->controller->renderPartial('_formUpdate', $params); ?>