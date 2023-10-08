<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Campaign */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="campaign-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'messageTemplateId')->textInput() ?>

    <?= $form->field($model, 'searchCriteriaId')->textInput() ?>

    <?= $form->field($model, 'status')->textInput() ?>

    <?= $form->field($model, 'startDateTime')->textInput() ?>

    <?= $form->field($model, 'endDateTime')->textInput() ?>

    <?= $form->field($model, 'campType')->textInput() ?>

    <?= $form->field($model, 'totalUsers')->textInput() ?>

    <?= $form->field($model, 'batchOffset')->textInput() ?>

    <?= $form->field($model, 'batchOffsetEmail')->textInput() ?>

    <?= $form->field($model, 'batchOffsetTwitter')->textInput() ?>

    <?= $form->field($model, 'batchOffesetLinkedIn')->textInput() ?>

    <?= $form->field($model, 'aBTestId')->textInput() ?>

    <?= $form->field($model, 'createdBy')->textInput() ?>

    <?= $form->field($model, 'createdAt')->textInput() ?>

    <?= $form->field($model, 'updatedBy')->textInput() ?>

    <?= $form->field($model, 'updatedAt')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
