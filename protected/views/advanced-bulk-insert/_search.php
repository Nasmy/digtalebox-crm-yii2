<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\advanceBulkInsertSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="advance-bulk-insert-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'source') ?>

    <?= $form->field($model, 'renameSource') ?>

    <?= $form->field($model, 'countryCode') ?>

    <?= $form->field($model, 'userType') ?>

    <?php // echo $form->field($model, 'keywords') ?>

    <?php // echo $form->field($model, 'size') ?>

    <?php // echo $form->field($model, 'errors') ?>

    <?php // echo $form->field($model, 'timeSpent') ?>

    <?php // echo $form->field($model, 'status') ?>

    <?php // echo $form->field($model, 'columnMap') ?>

    <?php // echo $form->field($model, 'createdAt') ?>

    <?php // echo $form->field($model, 'createdBy') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
