<?php

use app\components\RActiveRecord;
use yii\db\ActiveRecord;
use yii\helpers\Url;
use yii\jui\DatePicker;
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\web\urlManager;

$form = ActiveForm::begin([
    'id' => 'search-form',
    'action' => ['form-builder/admin'],
    'method' => 'get',
    'options' => [
        'data-pjax' => 1
    ],
]);
?>

<div class="form-row">
    <div class="form-group col-md-4">
        <?php
        echo $form->field($model, 'title')
            ->textInput(['class' => 'form-control', 'maxlength' => 45, 'placeholder' => Yii::t('messages', 'Title')])
            ->label(false);
        ?>
    </div>
    <div class="form-group col-md-4">
        <?php

        echo $form->field($model, 'enabled')->dropDownList(
            RActiveRecord::getBoolList(),
            ['prompt' => '-Enabled-'])->label(false);

        ?>
    </div>
    <div class="form-group col-md-4">
        <?= DatePicker::widget([
            'model' => $model,
            'attribute' => 'createdAt',
            'dateFormat' => 'yyyy-MM-dd',
            'language' => '',
            'clientOptions' => [
                'format' => 'yyyy-mm-dd',
                'maxDate' => 'js:new Date(' . date('Y-10,m,d,H,i') . ')',
                'changeYear' => true,
                'yearRange' => "1900:" . date("Y"),
                'type' => 'date',
            ],
            'options' => array('placeholder' => Yii::t('messages', "createdAt"),
                'class' => 'form-control datetimepicker-input'),

        ]) ?>
    </div>
</div>
<div class="form-row text-left text-md-right">
    <div class="form-group col-md-12">
        <?= Html::submitButton(Yii::t('messages', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('messages', 'Reset'), Url::to('form-builder/admin', true), ['class' => 'btn btn-primary']); ?>

    </div>
</div>

<?php ActiveForm::end(); ?>
