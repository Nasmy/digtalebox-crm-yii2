<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\advanceBulkInsert */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Advance Bulk Inserts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="advance-bulk-insert-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
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
            'id',
            'source',
            'renameSource',
            'countryCode',
            'userType',
            'keywords:ntext',
            'size',
            'errors',
            'timeSpent',
            'status',
            'columnMap:ntext',
            'createdAt',
            'createdBy',
        ],
    ]) ?>

</div>
