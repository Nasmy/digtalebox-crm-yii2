<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\CandidateInfoSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Candidate Infos';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="candidate-info-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Candidate Info', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'profImageName',
            'volunteerBgImageName',
            'slogan',
            'introduction:ntext',
            //'promoText',
            //'signupFields:ntext',
            //'frontImages:ntext',
            //'aboutText:ntext',
            //'headerText',
            //'themeStyle',
            //'bgImage',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
