<?php

use yii\helpers\Html;
use yii\jui\DatePicker;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\BroadcastMessageSearch */
/* @var $form yii\widgets\ActiveForm */
?>


    <?php $form = ActiveForm::begin([
        'action' => ['admin'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]);
    $calLang = Yii::$app->toolKit->getComponenetSpecificLangIdentifier('juiDateTimePicker');
      ?>

    <div class="form-row">
         <div class="form-group col-md-5">
             <?= DatePicker::widget([
                 'model' => $model,
                 'attribute' => 'fromDate',
                 'dateFormat' => 'yyyy-MM-dd',
                 'language' => $calLang,
                 'clientOptions' => [
                      'dateFormat'=>'yy-mm-dd',
                        'yearRange' => "1900:" . date("Y"),
                     'type' => 'date',
                 ],
                 'options' => array( 'placeholder' =>$attributeLabels['fromDate'],
                     'class' => 'form-control datetimepicker-input',
                     'readonly'=>true
                 ),

             ]) ?>

          </div>
        <div class="form-group col-md-5">
            <?= DatePicker::widget([
                'model' => $model,
                'attribute' => 'toDate',
                'dateFormat' => 'yyyy-MM-dd',
                'language' => $calLang,
                'clientOptions' => [
                    'dateFormat'=>'yy-mm-dd',
                    'yearRange' => "1900:" . date("Y"),
                    'type' => 'date',
                ],
                'options' => array( 'placeholder' =>$attributeLabels['toDate'],
                    'class' => 'form-control datetimepicker-input',
                    'readonly'=>true
                ),

            ]) ?>

        </div>
        <div class="form-group col-md-2">
            <div class="form-group">
                <?= Html::submitButton(Yii::t('messages','Search'), ['class' => 'btn btn-primary']) ?>   
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

