<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\ABTestingCampaign */

$this->title = 'Create Ab Testing Campaign';
$this->params['breadcrumbs'][] = ['label' => 'Ab Testing Campaigns', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="abtesting-campaign-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
