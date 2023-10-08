<?php

use yii\helpers\Html;
$attributeLabels = $model->attributeLabels();
/* @var $this yii\web\View */
/* @var $model app\models\MessageTemplate */

$this->title = Yii::t('messages', 'Create SMS Template');
$this->titleDescription = Yii::t('messages', 'Add SMS message templates');
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Communication'), 'url' => ['admin']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'SMS Templates'), 'url' => ['admin']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Create')];
?>
<div class="message-template-create">

    <?= $this->render('_smsForm', [
        'model' => $model,
        'templateOptions'=>$templateOptions,
        'attributeLabels' => $attributeLabels,
        'action' => 'create'
    ]) ?>

</div>