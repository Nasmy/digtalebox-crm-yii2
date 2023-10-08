<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\LnPageInfoSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Ln Page Infos';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ln-page-info-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Ln Page Info', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'pageId',
            'pageName',
            'postCollectedTime',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
