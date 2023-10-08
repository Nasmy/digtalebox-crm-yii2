<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\LnPageInfo */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="ln-page-info-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'pageId')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'pageName')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'postCollectedTime')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
