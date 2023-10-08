<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Configuration */

// $this->title = 'Update Configuration: ' . $model->key;
// $this->params['breadcrumbs'][] = ['label' => 'Configurations', 'url' => ['index']];
// $this->params['breadcrumbs'][] = ['label' => $model->key, 'url' => ['view', 'id' => $model->key]];
// $this->params['breadcrumbs'][] = 'Update';
$attributeLabels = $configFormModel->attributeLabels();
?>
<div class="configuration-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'configFormModel'=>$configFormModel,
        'highlightFields' => $highlightFields,
        'attributeLabels' => $attributeLabels
    ]) ?>

</div>
