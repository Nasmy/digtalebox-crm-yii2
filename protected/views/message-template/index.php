<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\MessageTemplateSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Message Templates';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="message-template-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Message Template', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'name',
            'subject',
            'twMessage',
            'fbMessage:ntext',
            //'smsMessage',
            //'lnMessage',
            //'lnSubject',
            //'description',
            //'type',
            //'dateTime',
            //'createdBy',
            //'createdAt',
            //'updatedBy',
            //'updatedAt',
            //'dragDropMessageCode',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
