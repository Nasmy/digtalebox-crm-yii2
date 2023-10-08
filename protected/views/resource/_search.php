<?php

use app\components\WebUser;
use yii\helpers\Html;
use yii\jui\DatePicker;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\ResourceSearch */
/* @var $form yii\widgets\ActiveForm */
?>


    <?php $form = ActiveForm::begin([
        'action' => ['admin'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>
     <div class="form-row">
        <div class="form-group col-md-4">
            <?= DatePicker::widget([
                'model' => $model,
                'dateFormat' => 'yyyy-MM-dd',
                'attribute' => 'fromDate',
                'language' => '',
                'clientOptions' => [
                    'maxDate' => 'js:new Date(' . date('Y-10,m,d,H,i') . ')',
                    'changeYear' => true,
                    'yearRange' => "1900:" . date("Y"),
                    'type' => 'date',
                    'maxDate'=>"+1D",
                ],
                'options' => array('readonly' => true, 'placeholder' => Yii::t('messages','Start Date'),
                    'class' => 'form-control datetimepicker-input datepicker'),

            ]) ?>
        </div>

         <div class="form-group col-md-4">
            <?= DatePicker::widget([
                'model' => $model,
                'attribute' => 'toDate',
                'dateFormat' => 'yyyy-MM-dd',
                'language' => '',
                'clientOptions' => [
                    'format' => 'yyyy-mm-dd',
                    'maxDate' => 'js:new Date(' . date('Y-10,m,d,H,i') . ')',
                    'changeYear' => true,
                    'yearRange' => "1900:" . date("Y"),
                    'type' => 'date',
                    'maxDate'=>"+1D",
                ],
                'options' => array('readonly' => true, 'placeholder' => Yii::t('messages','End Date'),
                'class' => 'form-control datetimepicker-input datepicker'),

            ]) ?>
        </div>

        <div class="form-group col-md-4">
            <?php echo $form->field($model, 'type')->dropDownList($model->getResourceTypeOptions(true),['class' => 'form-control','placeholder' => 'Title'])->label(false); ?>
        </div>

         <?php if (Yii::$app->user->checkUserPermissions(WebUser::RESOURCE)) { ?>
             <div class="form-group col-md-4">
                 <?php
                 echo $form->field($model, 'status')->dropDownList($model->getStatusOptions(true), ['class' => 'form-control'])->label(false);
                 ?>
             </div>
         <?php } ?>

        <div class="form-group col-md-4">
             <?= $form->field($model, 'title')->textInput(['placeholder' => Yii::t('messages','Title')])->label(false) ?>
        </div>
        <div class="form-group col-md-4">
            <?= $form->field($model, 'tag')->textInput(['placeholder' => 'Tag'])->label(false) ?>
        </div>
        <div class="form-group col-md-6">
            <div class="form-check form-check-inline">
                <?php
                echo $form->field($model,'createdBy')->checkbox(['label' => Yii::t('messages','My Resources'),'value'=>Yii::$app->user->id,'uncheckValue'=>"",'class'=>'form-check-input custom-icheck'])->label(false);
                ?>
            </div>
        </div>
    </div>
    <div class="form-row mb-0 text-left text-md-right">
        <div class="form-group col-md-12">
            <?= Html::submitButton(Yii::t('messages','Search'), ['class' => 'btn btn-primary']) ?>
        </div>
    </div>


    <?php ActiveForm::end(); ?>

