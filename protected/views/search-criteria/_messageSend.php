<?php

use app\models\Configuration;
use app\models\MessageTemplate;
use app\models\SearchCriteria;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;


/* @var $this yii\web\View */
/* @var $model app\models\SearchCriteria */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Search Criterias', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
$calLang = Yii::$app->toolKit->getComponenetSpecificLangIdentifier('juiDateTimePicker');
$attributeLabels = $model->attributeLabels();

?>

<div class="modal-body">

    <?php
    $form = ActiveForm::begin([
        'id' => 'form-message',
    ]); ?>

    <?php
    Yii::$app->toolKit->setJsFlash();
    $alertMsg1 = Yii::t('messages', 'Select template');
    $alertMsg2 = Yii::t('messages', 'Select campaign ending date');
    $alertMsg3 = Yii::t('messages', 'Select a from email');
    $newTempUrl = Yii::$app->urlManager->createUrl(['message-template/create-instant-template/', 'savedSearchId' => $id, 'messageType' => $type, 'reqFrom' => 'SAVED_SEARCH']);
    $this->registerJs("
        		    $('#send-button').on('click', function() {
        if ($('#fromEmail').val() == '') {
            setJsFlash('error', '{$alertMsg3}');
            return false;
        }
        else if ($('#selTemplate').val() == '') {
            setJsFlash('error', '{$alertMsg1}');
            return false;
        }

        $.ajax({
            type: 'POST',
            url: '" . Yii::$app->urlManager->createUrl('/campaign/add-campaign') . "',
            data: 'criteriaId=' + $('#criteriaId').val() + '&templateId=' + $('#selTemplate').val()  + '&fromEmail=' + $('#fromEmail').val() + '&type=' + $('#type').val(),
            success: function(data){
            var res = $.parseJSON(data);
            window.parent.$('.close').click();
            window.parent.$('#statusMsg').html(res.message);
        }
    });
    return false;
    });
    
    $('#new_template-button').click(function(){
        window.top.location.href = '{$newTempUrl}';
        return false;
    });
	");
    ?>
    <div class="form-row">
        <div class="form-group col-md-12">
            <label><?php echo Yii::t('messages', 'Select The Email Sender') ?></label>
            <?php echo Html::dropDownList("fromemail", '', Configuration::getConfigFromEmailOptions(), ['id' => 'fromEmail', 'class' => 'form-control']); ?>
        </div>
    </div>
    <div class="form-row">
        <div class="form-group col-md-12">
            <label><?php echo Yii::t('messages', 'Campaign Template') ?></label>
            <?php
            $model = (array)$model;
            echo Html::dropDownList($model, "template", MessageTemplate::getTemplateOptions(), ['class' => 'form-control', 'id' => 'selTemplate']); ?>
        </div>
    </div>
    <?php echo Html::hiddenInput('criteriaId', $id, ['id' => 'criteriaId']); ?>
    <?php echo Html::hiddenInput('type', $type, ['id' => 'type']); ?>
    <div class="form-row">
        <div class="form-group col-md-12">
            <?php
            echo Html::button(Yii::t('messages', 'Send'), ['id' => 'send-button', 'class' => 'btn mb-1 btn-block btn-primary']);
            echo Html::button(Yii::t('messages', 'New Template'), ['id' => 'new_template-button', 'class' => 'btn btn-block btn-secondary']);
            ?>
        </div>
    </div>

</div>
