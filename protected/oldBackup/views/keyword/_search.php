<?php

use yii\helpers\Html;
use yii\jui\DatePicker;
use yii\widgets\ActiveForm;


$form = ActiveForm::begin([
    'id' =>'search-form',
    'action' =>['keyword/admin'],
    'method' => 'get',
    'options' => [
        'data-pjax' => 1
    ],
]);

?>

<?php
//$calLang = Yii::$app->toolKit->getComponenetSpecificLangIdentifier('juiDateTimePicker');
$attributeLabels = $model->attributeLabels();
?>
<div class="form-row">
    <div class="form-group mb-0 col-md-4">
        <?= DatePicker::widget([
            'model' => $model,
            'attribute' => 'fromDate',
            'dateFormat' => 'yyyy-MM-dd',
            'language' => '',
            'clientOptions' => [
                'format' => 'yyyy-mm-dd',
                'maxDate' => '0',
                'changeYear' => true,
                'yearRange' => "1900:" . date("Y"),
                'type' => 'date',
            ],
            'options' => array( 'placeholder' =>$attributeLabels['fromDate'],
                'class' => 'form-control datetimepicker-input'),

        ]) ?>
    </div>
    <div class="form-group mb-0 col-md-4">
        <?= DatePicker::widget([
            'model' => $model,
            'attribute' => 'toDate',
            'dateFormat' => 'yyyy-MM-dd',
            'language' => '',
            'clientOptions' => [
                'format' => 'yyyy-mm-dd',
                'maxDate' => '0',
                'changeYear' => true,
                'yearRange' => "1900:" . date("Y"),
                'type' => 'date',
            ],
            'options' => array('readonly' => false, 'placeholder' => $attributeLabels['toDate'],
                'class' => 'form-control datetimepicker-input'),

        ]) ?>
    </div>

    <div class="form-group mb-0 col-md-4">
        <?php
        echo  $form->field($model,'status')->dropDownList($model->fillDropDown('status'),['class' => 'form-control', 'prompt' => Yii::t('messages', '- Status -')])->label(false);
        ?>
     </div>
    <div class="form-group mb-0 col-md-4">
        <?php
        echo  $form->field($model,'behaviour')->dropDownList($model->fillDropDown('behaviour'),['class' => 'form-control', 'prompt' => Yii::t('messages', '- Behaviour -')])->label(false);
        ?>
    </div>
    <div class="form-group mb-0 col-md-4">
        <?php
            echo $form->field($model, 'name')->textInput(['class' => 'form-control', 'placeholder' => Yii::t('messages', "Name")])->label(false);
        ?>
    </div>
</div>

<div class="form-row text-left text-md-right">
    <div class="form-group mb-0 col-md-12">
        <?= Html::submitButton(Yii::t('messages','Search'),['class' => 'btn btn-primary']) ?>
    </div>
</div>

<?php ActiveForm::end(); ?>
