<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\CampaignUsers */

$this->title = 'Update Campaign Users: ' . $model->campaignId;
$this->params['breadcrumbs'][] = ['label' => 'Campaign Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->campaignId, 'url' => ['view', 'campaignId' => $model->campaignId, 'userId' => $model->userId]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="campaign-users-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
