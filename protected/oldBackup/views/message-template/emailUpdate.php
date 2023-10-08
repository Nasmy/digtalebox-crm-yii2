<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\MessageTemplate */
$attributeLabels = $model->attributeLabels();
if (isset($templateOptions['isDuplicate'])) {
    $title = "Create Duplicate Email Template";
    $titleDescription = "Duplicate Email message templates";
    $action = "Duplicate";
} else {
    $title = "Update Email Template";
    $titleDescription = "Update Email message templates";
    $action = "Update";
}

$this->title = Yii::t('messages', $title);
$this->titleDescription = Yii::t('messages', $titleDescription);
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Communication'), 'url' => ['admin']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Email Templates'), 'url' => ['admin']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', $action)];
?>
<div class="message-template-update">

    <?= $this->render('_emailForm', [
        'model' => $model,
        'templateOptions'=>$templateOptions,
        'attributeLabels' => $attributeLabels
    ]) ?>

</div>