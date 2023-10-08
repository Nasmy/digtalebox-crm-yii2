<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\SearchCriteria */

$this->title = 'Create Search Criteria';
$this->params['breadcrumbs'][] = ['label' => 'Search Criterias', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="search-criteria-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
