<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\CampaignUsers */

$this->title = 'Create Campaign Users';
$this->params['breadcrumbs'][] = ['label' => 'Campaign Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="campaign-users-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
