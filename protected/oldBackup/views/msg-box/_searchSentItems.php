<?php

use yii\helpers\Html;
use yii\jui\DatePicker;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\MsgBoxSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="msg-box-search">

    <?php $form = ActiveForm::begin([
        'action' => ['sent-items'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>
    <div class="form-row">
        <div class="form-group col-md-4">
     <?=  DatePicker::widget([
        'model' => $model,
        'attribute' => 'fromDate',
        'dateFormat' => 'yyyy-MM-dd',
        'options'=>[
            'readonly'=>true,'class'=>'form-control datepicker', 'placeholder'=>$attributeLabels['fromDate'], 'maxDate'=>'js:new Date(' . date('Y,m-1,d+1') . ')',

        ],

    ]); ?>
        </div>
        <div class="form-group col-md-4">

        <?=  DatePicker::widget([
        'model' => $model,
        'attribute' => 'toDate',
        'dateFormat' => 'yyyy-MM-dd',
        'options'=>array('readonly'=>true,'class'=>'form-control datepicker', 'placeholder'=>$attributeLabels['toDate']),

    ]); ?>
    </div>
    </div>
    <div class="form-row text-left text-md-right">
        <div class="form-group col-md-12">
            <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?> 
        </div>
    </div>

    <?php ActiveForm::end(); ?>
    <script>
        $( function() {
            $( ".datepicker" ).datepicker({
                maxDate: "+1D",
                dateFormat : 'yy-mm-dd',
            });
        } );
    </script>
</div>
