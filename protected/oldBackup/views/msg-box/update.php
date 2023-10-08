<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\MsgBox */

$this->title = 'Update Msg Box: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Msg Boxes', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="msg-box-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
