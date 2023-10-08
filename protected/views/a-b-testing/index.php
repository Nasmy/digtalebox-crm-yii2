<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ABTestingCampaignSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Ab Testing Campaigns';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="abtesting-campaign-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Ab Testing Campaign', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'name',
            'messageTemplateIdA',
            'messageTemplateIdB',
            'fromA',
            //'subjectA',
            //'countA',
            //'fromB',
            //'subjectB',
            //'countB',
            //'fromRemain',
            //'subjectRemain',
            //'countRemain',
            //'startDate',
            //'createdAt',
            //'createdBy',
            //'updatedAt',
            //'updatedBy',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
