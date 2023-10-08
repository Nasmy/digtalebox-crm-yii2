<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\SearchCriteria */

$this->title = 'Update Search Criteria: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Search Criterias', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="search-criteria-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
