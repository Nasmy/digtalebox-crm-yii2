<?php

use app\components\RActiveRecord;
use app\models\CustomType;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$form = ActiveForm::begin([
    'id' => 'custom-field-search-form',
    'action' =>['custom-field/admin'],
    'method' => 'get',
    'options' => [
        'data-pjax' => 1
    ],
]);

$CustomType = new  CustomType();
?>

    <div class="form-row">
        <div class="form-group mb-0 col-md-4">
            <?php echo $form->field($model, 'fieldName')->textInput(['class' => 'form-control','placeholder' => Yii::t('messages', 'Field Name')])->label(false); ?>
        </div>
        <div class="form-group  mb-0 col-md-4">
            <?php echo $form->field($model, 'label')->textInput(['class' => 'form-control','placeholder' => Yii::t('messages', 'Label')])->label(false); ?>
        </div>
        <div class="form-group  mb-0 col-md-4">
            <?php echo $form->field($model, 'relatedTable')->dropDownList($CustomType->getAreas(),['class' => 'form-control','prompt' => Yii::t('messages', '- Related Area -')])->label(false); ?>
        </div>
        <div class="form-group  mb-0 col-md-4">
            <?php echo $form->field($model, 'display')->dropDownList($CustomType->getTypes(),['class' => 'form-control','prompt' => Yii::t('messages', '- Display -')])->label(false); ?>
        </div>
        <div class="form-group  mb-0 col-md-4">
            <?php echo $form->field($model, 'defaultValue')->textInput(['class' => 'form-control','placeholder' => Yii::t('messages', 'Default Value')])->label(false); ?>

        </div>
        <div class="form-group  mb-0 col-md-4">
            <?php echo $form->field($model, 'enabled')->dropDownList(RActiveRecord::getBoolList(),['class' => 'form-control','prompt' => Yii::t('messages', '- Enabled -')])->label(false); ?>

        </div>
    </div>
    <div class="form-row  mb-0 text-left text-md-right">
        <div class="form-group col-md-12">
            <?php
            echo Html::submitButton(Yii::t('messages', 'Search'),['class' => 'btn btn-primary']);
            ?>
        </div>
    </div>

<?php ActiveForm::end(); ?>
