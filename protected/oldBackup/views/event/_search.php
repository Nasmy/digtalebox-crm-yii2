<?php

use kartik\datetime\DateTimePicker;
use yii\bootstrap\Button;
use yii\helpers\Html;
use yii\jui\AutoComplete;
use yii\jui\DatePicker;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\EventSearch */
/* @var $form yii\widgets\ActiveForm */
?>


<div class="search-form" style="display:block">

    <div class="form-row mb-2">
        <div class="form-group col-md-12">
            <?php

            if (Yii::$app->user->checkAccess('Form.Create')) {
                echo Html::a("<i class=\"fa fa-plus\"></i> " . Yii::t('messages', 'Create Event'), ['event/create'], ['class' => 'btn-primary grid-button btn']);
            }
            ?>
        </div>
    </div>

    <div class="content-panel-sub">
        <div class="panel-head">
            <?php echo Yii::t('messages', 'Search by'); ?>
        </div>
    </div>

    <?php
            $form = ActiveForm::begin([
                'id' =>'search-form',
                'action' =>['event/admin'],
                'method' => 'get',
                'enableClientValidation' => false,
                'options' => [
                    'data-pjax' => 1,
                    'autocomplete'=>"off",
                    'enableAjaxValidation'=>false,
                ],
            ]);
  ?>

    <?php
    $calLang = Yii::$app->toolKit->getComponenetSpecificLangIdentifier('juiDateTimePicker');
    $attributeLabels = $model->attributeLabels();

    ?>

    <div class="form-row">

        <div class="form-group col-md-4 col-xl-2">
            <div class="input-group date" id="from-date" data-target-input="nearest">
                <?= DatePicker::widget([
                   'model' => $model,
                    'language' => $calLang,
                    'attribute' => 'startDate',
                    'dateFormat' => 'php:Y-m-d',
                    'clientOptions' => [
                       'type' => 'date',
                   ],
                   'options' => [
                       'placeholder' => $attributeLabels['startDate'],
                       'class' => 'form-control datetimepicker-input',
                       'onClose' => 'js:function( selectedDate ) { 
                            if (selectedDate != "" ) { 
                                    $("#' . Html::getInputId($model, 'endDate') . '").datetimepicker( "option", "minDate", selectedDate );
                            } 
                        }'
                   ]
                ]) ?>

            </div>
        </div>

        <div class="form-group col-md-4 col-xl-2">
            <?php echo $form->field($model, 'name')->textInput(['class' => 'form-control', 'label' => false, 'maxlength' => 45, 'placeholder' => $attributeLabels['name']])->label(false); ?>
            <?php echo $form->field($model, 'updatedBy')->hiddenInput()->label(false); ?>
        </div>


        <div class="form-group col-md-4 col-xl-2">
            <?php
            echo $form->field($model,'priority')->dropDownList($model->fillDropDown('priority'),
                ['class' => 'form-control', 'maxlength' => 55,
                'prompt' => Yii::t('messages', '- Priority -'),
                ])->label(false); ?>
        </div>

        <div class="form-group col-md-4 col-xl-2">
            <?php echo $form->field($model, "status")->dropDownList($model->fillDropDown('status'),
                ['class' => 'form-control',
                'prompt' => Yii::t('messages', '- Status -')
                ])->label(false); ?>
        </div>

        <div class="form-group col-md-4 col-xl-2">
            <?php
            echo AutoComplete::widget([
                'model' => $model,
                'attribute' => 'createdBy',
                'clientOptions' => [
                    'source' => Yii::$app->urlManager->createUrl('event/event-owner'),// <- path to controller which returns dynamic data
                    'minLength' => '1', // min chars to start search
                ],
                'clientEvents' => [
                    'select' => 'function (event, ui){ 
                                      console.log(1);
                                    console.log(ui);
                                    $("#' . Html::getInputId($model, 'updatedBy') . '").val(ui.item.id);
                                }',
                    'change' => 'function (event, ui){
                                                                     console.log(2);
                                                                     console.log(ui);
                                   if (!ui.item) {
                                        $("#' . Html::getInputId($model, 'updatedBy') . '").val("");
                                    }
                                }',

                 ],
        'options' => [
            'class' => 'form-control',
            'placeholder' => $attributeLabels['createdBy'],
        ]

            ]);

            ?>
        </div>
    </div>

    <div class="form-row text-left text-md-right">
        <div class="form-group col-md-12">
            <?php
            echo Html::submitButton(Yii::t('messages', 'Search'), ['class' => 'btn btn-primary']);
            ?>
        </div>
    </div>

    <?php $form->end(); ?>

</div>
<br>