<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\User */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="user-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'password')->passwordInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'isUnsubEmail')->textInput() ?>

    <?= $form->field($model, 'emailStatus')->textInput() ?>

    <?= $form->field($model, 'formId')->textInput() ?>

    <?= $form->field($model, 'pwResetToken')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'resetPasswordTime')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
