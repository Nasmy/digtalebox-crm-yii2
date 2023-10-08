<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\MessageTemplate */
$attributeLabels = $model->attributeLabels();

$this->title = Yii::t('messages', 'Update Template');
$this->titleDescription = Yii::t('messages', 'Update Email/SMS message templates');
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Communication'), 'url' => ['admin']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Message Templates'), 'url' => ['admin']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Update')];
?>
<div class="message-template-update">

    <?= $this->render('_form', [
        'model' => $model,
        'templateOptions'=>$templateOptions,
        'attributeLabels' => $attributeLabels
    ]) ?>

</div>
