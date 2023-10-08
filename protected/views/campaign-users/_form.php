<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\CampaignUsers */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="campaign-users-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'campaignId')->textInput() ?>

    <?= $form->field($model, 'userId')->textInput() ?>

    <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'mobile')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'status')->textInput() ?>

    <?= $form->field($model, 'emailStatus')->textInput() ?>

    <?= $form->field($model, 'smsStatus')->textInput() ?>

    <?= $form->field($model, 'clickedUrls')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'emailTransactionId')->textInput() ?>

    <?= $form->field($model, 'smsId')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'createdAt')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
