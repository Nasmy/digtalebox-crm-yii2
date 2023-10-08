<?php

use yii\helpers\Html;
$attributeLabels = $model->attributeLabels();
/* @var $this yii\web\View */
/* @var $model app\models\MessageTemplate */

$this->title = Yii::t('messages', 'Create Template');
$this->titleDescription = Yii::t('messages', 'Add Email/SMS message templates');
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Communication'), 'url' => ['admin']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Message Templates'), 'url' => ['admin']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Create')];
?>
<div class="message-template-create">

    <?= $this->render('_form', [
        'model' => $model,
        'templateOptions'=>$templateOptions,
        'attributeLabels' => $attributeLabels
    ]) ?>

</div>
