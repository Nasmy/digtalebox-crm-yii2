<?php

use yii\helpers\Url;
use yii\jui\DatePicker;
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\web\urlManager;

$form = ActiveForm::begin([
    'id' =>'search-keyword',
    'action' =>['membership-type/admin'],
    'method' => 'get',
    'options' => [
        'data-pjax' => 1
    ],
]);
 ?>

<div class="form-row">
    <div class="form-group mb-0 col-md-4">
        <?php
        echo $form->field($model, 'title')
            ->textInput(['class' => 'form-control', 'maxlength' => 45, 'placeholder'=> Yii::t('messages', 'Title')])
            ->label(false);
        ?>
    </div>
</div>
    <div class="form-row  mb-0 text-left text-md-right">
        <div class="form-group col-md-12">
            <?= Html::submitButton(Yii::t('messages', 'Search'), ['class' => 'btn btn-primary']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
