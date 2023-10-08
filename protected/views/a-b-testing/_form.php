<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\ABTestingCampaign */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="abtesting-campaign-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'messageTemplateIdA')->textInput() ?>

    <?= $form->field($model, 'messageTemplateIdB')->textInput() ?>

    <?= $form->field($model, 'fromA')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'subjectA')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'countA')->textInput() ?>

    <?= $form->field($model, 'fromB')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'subjectB')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'countB')->textInput() ?>

    <?= $form->field($model, 'fromRemain')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'subjectRemain')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'countRemain')->textInput() ?>

    <?= $form->field($model, 'startDate')->textInput() ?>

    <?= $form->field($model, 'createdAt')->textInput() ?>

    <?= $form->field($model, 'createdBy')->textInput() ?>

    <?= $form->field($model, 'updatedAt')->textInput() ?>

    <?= $form->field($model, 'updatedBy')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
