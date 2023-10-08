<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\CandidateInfoSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="candidate-info-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'profImageName') ?>

    <?= $form->field($model, 'volunteerBgImageName') ?>

    <?= $form->field($model, 'slogan') ?>

    <?= $form->field($model, 'introduction') ?>

    <?php // echo $form->field($model, 'promoText') ?>

    <?php // echo $form->field($model, 'signupFields') ?>

    <?php // echo $form->field($model, 'frontImages') ?>

    <?php // echo $form->field($model, 'aboutText') ?>

    <?php // echo $form->field($model, 'headerText') ?>

    <?php // echo $form->field($model, 'themeStyle') ?>

    <?php // echo $form->field($model, 'bgImage') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
