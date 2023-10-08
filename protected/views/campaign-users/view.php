<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\CampaignUsers */

$this->title = $model->campaignId;
$this->params['breadcrumbs'][] = ['label' => 'Campaign Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="campaign-users-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'campaignId' => $model->campaignId, 'userId' => $model->userId], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'campaignId' => $model->campaignId, 'userId' => $model->userId], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'campaignId',
            'userId',
            'email:email',
            'mobile',
            'status',
            'emailStatus:email',
            'smsStatus',
            'clickedUrls:ntext',
            'emailTransactionId:email',
            'smsId',
            'createdAt',
        ],
    ]) ?>

</div>
