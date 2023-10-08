<?php

use app\models\StatSummary;
use yii\helpers\Html;
use yii\web\View;
use app\assets\DialogAsset;
DialogAsset::register($this);
/* @var $this yii\web\View */
/* @var $model app\models\StatSummary */
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'People'), 'url' => ['advancedSearch/admin']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Update')];
$attributeLabels = $model->attributeLabels();
?>
<div class="col-md-12" id="content">
    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'keywords' => $keywords,
        'closeUrl' => $closeUrl,
        'customFields' => $customFields,
        'attributeLabels' => $attributeLabels
    ]) ?>

</div>