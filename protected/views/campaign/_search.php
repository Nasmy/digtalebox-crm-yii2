<?php

use app\models\SearchCriteria;
use yii\helpers\Html;
use yii\jui\DatePicker;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\CampaignSearch */
/* @var $form yii\widgets\ActiveForm */
$attributeLabels = $model->attributeLabels();
?>

<div class="campaign-search">

    <?php $form = ActiveForm::begin([
        'action' => ['admin'],
        'method' => 'post',
    ]); ?>

    <div class="form-row">
        <div class="form-group mb-0 col-md-4">
            <?=
            DatePicker::widget([
                'model' => $model,
                'attribute' => 'startDateTime',
                'dateFormat' => 'yyyy-MM-dd',
                'options' => [
                    'class' => 'form-control datepicker',
                    'placeholder' => Yii::t('messages', "Start Date"),
                    'showButtonPanel' => true,
                ],
                'clientOptions' => [
                    'maxDate' => 'today',
                    'onSelect' => new \yii\web\JsExpression('function(selectedDate) {
                        var endDate = $("#campaignsearch-enddatetime").val();
						if(endDate != "" && selectedDate > endDate) {
						    $("#campaignsearch-enddatetime").val("");
						}
                    }')
                ],
            ]); ?>
        </div>
        <div class="form-group  mb-0 col-md-4">
            <?=
            DatePicker::widget([
                'model' => $model,
                'attribute' => 'endDateTime',
                'dateFormat' => 'yyyy-MM-dd',
                'options' => [
                    'class' => 'form-control datepicker',
                    'placeholder' => Yii::t('messages', "End Date"),
                    'showButtonPanel' => true,
                ],
                'clientOptions' => [
                    'maxDate' => 'today',
                    'onSelect' =>  new \yii\web\JsExpression('function(selectedDate) { 
                    var startDate = $("#campaignsearch-startdatetime").val();
                     if(startDate != "" && selectedDate < startDate) {
                          $("#campaignsearch-startdatetime").val("");
                     }
                    }')
                ],
            ]); ?>
        </div>

        <div class="form-group  mb-0 col-md-4">
            <?php echo $form->field($model, 'searchCriteriaId')->dropDownList(SearchCriteria::getSavedSearchOptions(null, SearchCriteria::ADVANCED))->label(false); ?>
        </div>

        <div class="form-group  mb-0 col-md-4">
            <?php echo $form->field($model, 'status')->dropDownList($model->getStatusList(), array('prompt'=>Yii::t('messages','- Status -')))->label(false); ?>
        </div>

        <div class="form-group  mb-0 col-md-4">
            <?php
            echo $form->field($model, 'campType')->dropDownList($model->getCampaignTypeList(), array('class'=>'form-control', 'prompt'=>Yii::t('messages','- Type -')))->label(false); ?>
        </div>
    </div>

    <div class="form-group mb-0 text-right">
        <?= Html::submitButton(Yii::t('messages','Search'), ['class' => 'btn btn-primary  mb-0']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
