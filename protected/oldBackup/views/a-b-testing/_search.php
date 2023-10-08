<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\ABTestingCampaignSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="abtesting-campaign-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'messageTemplateIdA') ?>

    <?= $form->field($model, 'messageTemplateIdB') ?>

    <?= $form->field($model, 'fromA') ?>

    <?php // echo $form->field($model, 'subjectA') ?>

    <?php // echo $form->field($model, 'countA') ?>

    <?php // echo $form->field($model, 'fromB') ?>

    <?php // echo $form->field($model, 'subjectB') ?>

    <?php // echo $form->field($model, 'countB') ?>

    <?php // echo $form->field($model, 'fromRemain') ?>

    <?php // echo $form->field($model, 'subjectRemain') ?>

    <?php // echo $form->field($model, 'countRemain') ?>

    <?php // echo $form->field($model, 'startDate') ?>

    <?php // echo $form->field($model, 'createdAt') ?>

    <?php // echo $form->field($model, 'createdBy') ?>

    <?php // echo $form->field($model, 'updatedAt') ?>

    <?php // echo $form->field($model, 'updatedBy') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
