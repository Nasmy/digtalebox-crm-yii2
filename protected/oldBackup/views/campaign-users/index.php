<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\CampaignUsersSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Campaign Users';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="campaign-users-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Campaign Users', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'campaignId',
            'userId',
            'email:email',
            'mobile',
            'status',
            //'emailStatus:email',
            //'smsStatus',
            //'clickedUrls:ntext',
            //'emailTransactionId:email',
            //'smsId',
            //'createdAt',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
