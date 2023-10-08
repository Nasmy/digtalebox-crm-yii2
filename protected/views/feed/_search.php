<?php

use app\models\Feed;
use app\models\FeedSearchKeyword;
use app\models\User;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\FeedSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="feed-search search-form">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]);

    $test = $model->attributeLabels();
     ?>

    <div class="form-row">
        <div class="form-group col-md-3 m-0">

        <?= $form->field($model, 'fromDate')->textInput(
                ['class' => 'form-control datetimepicker-input', 'data-target' => '#from-date', 'id' => 'from-date', 'data-toggle' => 'datetimepicker', 'placeholder' => $test['fromDate'],'readonly' => true]
            )->label(false) ?>
        </div>
        <div class="form-group col-md-3 m-0">

            <?= $form->field($model, 'toDate')->textInput(
                    ['class' => 'form-control datetimepicker-input', 'data-target' => '#to-date', 'id' => 'to-date', 'data-toggle' => 'datetimepicker', 'placeholder' => $test['toDate'],'readonly' => true]
                )->label(false) ?>
        </div>
        <div class="form-group col-md-3 m-0">
    <?= $form->field($model, 'type')->dropDownList(
            $model->getUserTypeOptionsForSearch(),['class' => 'form-control']
        )->label(false)?>
        </div>
        <div class="form-group col-md-3 m-0">
        <?=
            $form->field($model, 'network')->dropDownList(
                    User::getNetworkTypes(),['class' => 'form-control']
            )->label(false)
        ?>
        </div>
        <div class="form-group col-md-6">
        <?=
            $form->field($model, 'keywordId')->dropDownList(
                    ArrayHelper::map(FeedSearchKeyword::find()->all(),'id','keyword'),['class' => 'form-control chosen-select','data-placeholder' => Yii::t('messages', 'Keywords'), 'multiple' => true]
            )->label(false)
        ?>
        </div>
        <div class="form-group col-md-6">
            <?=
            $form->field($model, 'type')->dropDownList(
                Feed::getTypeOptions(),['class' => 'form-control chosen-select','data-placeholder' => Yii::t('messages', 'Types'), 'multiple' => true]
            )->label(false)
            ?>
        </div>
    </div>

    <div class="form-row text-left text-md-right">
        <div class="form-group col-md-12">
            <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>

            <?= Html::a('<i class="fa fa-font"></i> ' . Yii::t('messages', 'Manage Keywords'), Yii::$app->urlManager->createUrl('feed-search-keyword/admin'), ['class' => 'btn btn-secondary ',]) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
