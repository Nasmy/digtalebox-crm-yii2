<?php

use yii\helpers\Html;
use yii\jui\DatePicker;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\SearchCriteriaSearch */
/* @var $form yii\widgets\ActiveForm */

$calLang = Yii::$app->toolKit->getComponenetSpecificLangIdentifier('juiDateTimePicker');
$attributeLabels = $model->attributeLabels();
 ?>
<?php $form = ActiveForm::begin([
    'action' => ['admin'],
    'method' => 'post',
    'enableAjaxValidation' => false,
    'enableClientValidation' => false,
    'validateOnBlur' => false,
    'validateOnChange' => false,
    'validateOnSubmit' => false,
    ]); ?>

<div class="form-row">

    <div class="form-group col-md-4 mb-0">
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
//            'readonly'=>true
        ),

        ]) ?>
    </div>
    <div class="form-group col-md-4 mb-0">
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
//            'readonly'=>true
        ),

        ]) ?>
    </div>

    <div class="form-group col-md-4 mb-0">
        <?php echo $form->field($model, 'criteriaName')->textInput( array('class' => 'form-control', 'placeholder' => Yii::t('messages', "Criteria Name"), 'maxlength' => 45))->label(false); ?>
    </div>
</div>
<div class="form-row text-left text-md-right">
    <div class="form-group col-md-12 mb-0">
        <?= Html::submitButton(Yii::t('messages','Search'), ['class' => 'btn btn-primary']) ?>
    </div>
</div>

    <?php ActiveForm::end(); ?>

</div>
