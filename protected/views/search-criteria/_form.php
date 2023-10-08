<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\SearchCriteria */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="search-criteria-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'firstName')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'lastName')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'mobile')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'mapZone')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'keywords')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'keywordsExclude')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'searchType')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'isDisplayKeywords2')->textInput() ?>

    <?= $form->field($model, 'keywordsExclude2')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'searchType2')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'keywords2')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'teams')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'gender')->textInput() ?>

    <?= $form->field($model, 'zip')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'fullAddress')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'city')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'countryCode')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'userType')->textInput() ?>

    <?= $form->field($model, 'criteriaName')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'emailStatus')->textInput() ?>

    <?= $form->field($model, 'formId')->textInput() ?>

    <?= $form->field($model, 'age')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'network')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'excludeFbPersonalContacts')->textInput() ?>

    <?= $form->field($model, 'critetiaType')->textInput() ?>

    <?= $form->field($model, 'date')->textInput() ?>

    <?= $form->field($model, 'createdBy')->textInput() ?>

    <?= $form->field($model, 'createdAt')->textInput() ?>

    <?= $form->field($model, 'updatedBy')->textInput() ?>

    <?= $form->field($model, 'updatedAt')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
