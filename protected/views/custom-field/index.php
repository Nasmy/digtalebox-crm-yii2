<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\CustomFieldSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Custom Fields';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="custom-field-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Custom Field', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'customTypeId',
            'fieldName',
            'relatedTable',
            'defaultValue:ntext',
            //'sortOrder',
            //'enabled',
            //'listItemTag',
            //'required',
            //'onCreate',
            //'onEdit',
            //'onView',
            //'listValues:ntext',
            //'label',
            //'htmlOptions:ntext',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
