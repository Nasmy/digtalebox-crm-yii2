<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\StatSummarySearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="stat-summary-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <?= $form->field($model, 'date') ?>

    <?= $form->field($model, 'newSupporterCount') ?>

    <?= $form->field($model, 'dataUsage') ?>

    <?= $form->field($model, 'newRegistrationCount') ?>

    <?= $form->field($model, 'feedCount') ?>

    <?php // echo $form->field($model, 'supporterCount') ?>

    <?php // echo $form->field($model, 'prospectCount') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
