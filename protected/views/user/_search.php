<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\UserSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="user-search">

    <?php $form = ActiveForm::begin([
        'action' => ['user/admin'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
        /*'method' => 'post',
        */
    ]); ?>
    <div class="row">
        <div class="form-group mb-0 col-md-4">
            <?= $form->field($model, 'firstName')->textInput(['class' => 'form-control', 'maxlength' => 45, 'placeholder'=> Yii::t('messages', 'First Name')])->label(false) ?>
        </div>
        <div class="form-group mb-0 col-md-4">
            <?= $form->field($model, 'lastName')->textInput(['class' => 'form-control', 'maxlength' => 45, 'placeholder'=>  Yii::t('messages', 'Last Name')])->label(false) ?>
        </div>
        <div class="form-group mb-0 col-md-4">
            <?= $form->field($model, 'username')->textInput(['class' => 'form-control', 'maxlength' => 45, 'placeholder'=> Yii::t('messages', 'Username')])->label(false) ?>
        </div>
        <div class="form-group mb-0 col-md-4">
            <?= $form->field($model, 'email')->textInput(['class' => 'form-control', 'maxlength' => 45, 'placeholder'=> Yii::t('messages', 'Email')])->label(false) ?>
        </div>
    </div>

    <div class="form-row text-left text-md-right">
        <div class="form-group col-md-12">
            <?= Html::submitButton(Yii::t('messages','Search'), ['class' => 'btn btn-primary']) ?>

        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
