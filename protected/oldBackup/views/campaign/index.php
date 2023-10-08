<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\CampaignSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Campaigns';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="campaign-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Campaign', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'messageTemplateId',
            'searchCriteriaId',
            'status',
            'fromName',
            //'fromEmail:email',
            //'startDateTime',
            //'endDateTime',
            //'campType',
            //'totalUsers',
            //'batchOffset',
            //'batchOffsetEmail:email',
            //'batchOffsetTwitter',
            //'batchOffesetLinkedIn',
            //'aBTestId',
            //'createdBy',
            //'createdAt',
            //'updatedBy',
            //'updatedAt',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
