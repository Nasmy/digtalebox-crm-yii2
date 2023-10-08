<?php

 use yii\helpers\Html;
use yii\widgets\ActiveForm;

$attributeLabels = $model->attributeLabels();
//$form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
//    //'action' => Yii::app()->createUrl($this->route),
//    'method' => 'get',
//));

$form = ActiveForm::begin([
    'action' => ['admin'],
    'method' => 'get',
    'options' => ['enctype' => 'multipart/form-data']
]);

 ?>
<div class="form-row">
    <div class="form-group col-sm-6 col-md-6 col-lg-4 col-xl-4">
         <?php echo $form->field($model, 'firstName')->textInput(array('class' => 'form-control', 'placeholder' => $attributeLabels['firstName'], 'label' => false)); ?>
    </div>
    <div class="form-group col-sm-6 col-md-6 col-lg-4 col-xl-4">
         <?php echo $form->field($model, 'lastName')->textInput( array('class' => 'form-control', 'placeholder' => $attributeLabels['lastName'], 'label' => false)); ?>
    </div>

    <p></p>
</div>
<div class="form-row text-left text-md-right">
    <div class="form-group col-md-12">

        <?= Html::submitButton(Yii::t('messages', 'Search'),['class' =>'btn btn-primary']) ?>

    </div>
</div>
<?php ActiveForm::end(); ?>
