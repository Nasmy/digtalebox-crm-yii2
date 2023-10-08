<?php

use app\models\ABTestingCampaign;
use app\models\MessageTemplate;
use app\models\SearchCriteria;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\jui\DatePicker;
use yii\jui\SliderInput;

Yii::$app->toolKit->registerJqueryStepsScripts();
Yii::$app->toolKit->setJsFlash();
$attributeLabels = $model->attributeLabels();
/* @var $this yii\web\View */
/* @var $model app\models\ABTestingCampaign */
// $this->registerJsFile(Yii::$app->request->baseUrl . '/js/youFile.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->title = 'Create AB Testing';
$this->titleDescription = Yii::t('messages', 'Add new AB Testing');
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Communication'), 'url' => ['#']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Add new AB Testing')];
$sentCampUri = Yii::$app->urlManager->createUrl('campaign/admin')
?>
<div id="dialog" title="Check"></div>

<?php
$getUserCountUrl = Yii::$app->urlManager->createUrl('a-b-testing/get-save-search-user-count');
$getUserTemplateUrl = Yii::$app->urlManager->createUrl('a-b-testing/get-message-template-by-id');
$abTestCampaignMax = ABTestingCampaign::MAXIMUM;
$abTestCampaignMin = ABTestingCampaign::MINIMUM;
$script = <<< JS
$(document).ready(function () {
    $("#abtestingcampaign-amounta-container").click(function () {
            $("#showAValue").html($("#abtestingcampaign-amounta-container").val());
            // console.log($("#abtestingcampaign-amounta-container").val());
        });
     $("select#abtestingcampaign-messagetemplateida").on('change', function () {
           // ajax call to get the template detail and view.
            var selected = $('#abtestingcampaign-messagetemplateida').val();
            jQuery.ajax({
                'type': 'POST',
                'url': '$getUserTemplateUrl'+'?id='+selected,
                'data': '',
                'dataType': 'json',
                'success': function (data) {
                 $('#abtestingcampaign-subjecta').val('');
                     console.log(data);
                    if (null != data && data.id > 0) {
                        $('#abtestingcampaign-subjecta').val(data.subject);
                    }
                    return false;
                },
                'cache': false
            });
        });
     $("select#abtestingcampaign-messagetemplateidb").on('change', function () {
           // ajax call to get the template detail and view.
            var selected = $('#abtestingcampaign-messagetemplateidb').val();
            jQuery.ajax({
                'type': 'POST',
                'url': '$getUserTemplateUrl'+'?id='+selected,
                'data': '',
                'dataType': 'json',
                'success': function (data) {
                 $('#abtestingcampaign-subjectb').val('');
                     console.log(data);
                    if (null != data && data.id > 0) {
                        $('#abtestingcampaign-subjectb').val(data.subject);
                    }
                    return false;
                },
                'cache': false
            });
        });
    $("select#abtestingcampaign-searchcriteriaid").on('change', function () {
         var selected = $('#abtestingcampaign-searchcriteriaid').val();
            jQuery.ajax({
                'type': 'POST',
                'url': '$getUserCountUrl'+'?id='+selected,
                'data': '',
                'success': function (data) {
                    $('#abtestingcampaign-usercount').val(0);
                    $('#abtestingcampaign-amounta-container').slider('value',1);
                    $('#abtestingcampaign-amountb-container').slider('value',1);
                    $('#abtestingcampaign-counta').val(1);
                    $('#abtestingcampaign-countb').val(1);
                    if (data != 0) {
                    $('#abtestingcampaign-usercount').val(data);
                    $('#abtestingcampaign-counta').attr({'max' : data});
                    $('#abtestingcampaign-countb').attr({'max' : data});
                    if(data > $abTestCampaignMin && data <= $abTestCampaignMax) {
                    $('#abtestingcampaign-amounta-container').slider('value',1);
                    $('#abtestingcampaign-amountb-container').slider('value',1);
                    $('#abtestingcampaign-amounta-container').slider("option", "max",data);
                    $('#abtestingcampaign-amountb-container').slider("option", "max",data);
                    } else if (data > $abTestCampaignMax) {
                    $('#abtestingcampaign-amounta-container').slider("option", "max", $abTestCampaignMax);
                    $('#abtestingcampaign-amountb-container').slider("option", "max", $abTestCampaignMax);
                    }                     
                    } else {
      
                    $('#abtestingcampaign-amounta-container').slider("option", "max",1);
                    $('#abtestingcampaign-amountb-container').slider("option", "max",1);
                    }
                    return false;
                },
                'cache': false
            });
    });
});
JS;

$this->registerJs($script);

$warning1 = addslashes(Yii::t('messages', 'With Same Template The SenderA & SenderB Cannot be Same'));
$warning2 = addslashes(Yii::t('messages', 'With Different Template the SubjectA & SubjectB Cannot be Same.'));
$warning3 = addslashes(Yii::t('messages', 'Please select a Saved Search with user count more than 10'));
?>

<div class="row no-gutters">
    <div class="content-panel col-md-12">
        <div class="content-inner">
            <div class="content-area">
                <?php
                $form = ActiveForm::begin([
                    'id' => 'ab-camp-form',
                    'options' => [
                        'method' => 'post',
                        'enableAjaxValidation' => true,
                        'validateOnSubmit' => true,
                    ],
                ]);
                ?>
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label for="ABTestingCampaign_name"><?php echo $attributeLabels['name']; ?></label>
                        <?php echo $form->field($model, 'name')->textInput(['class' => 'form-control', 'size' => 45, 'maxlength' => 45])->label(false); ?>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="ABTestingCampaign_searchCriteriaId"><?php echo $attributeLabels['searchCriteriaId']; ?></label>
                        <?php echo $form->field($model, 'searchCriteriaId')->dropDownList(SearchCriteria::getSavedSearchOptions(null, SearchCriteria::ADVANCED), ['class' => 'form-control'])->label(false); ?>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="ABTestingCampaign_userCount"><?php echo $attributeLabels['userCount']; ?></label>
                        <?php echo $form->field($model, 'userCount')->textInput(['class' => 'form-control', 'rows' => 3, 'cols' => 50])->label(false); ?>
                    </div>
                    <div class="form-group col-md-3">
                        <label class="control-label"
                               for="ABTestingCampaign_startDate"><?php echo $attributeLabels['startDate']; ?></label>
                        <?=
                        DatePicker::widget([
                            'model' => $model,
                            'attribute' => 'startDate',
                            'dateFormat' => 'yyyy-MM-dd',
                            'options' => [
                                'readonly' => true,
                                'class' => 'form-control datepicker',
                                'placeholder' => $attributeLabels['startDate'],
                                'showButtonPanel' => true,
                                'minDate' => date('Y-m-d'),
                                'startDate' => date('Y-m-d')
                            ],
                        ]); ?>
                    </div>
                </div>
                <div class="row campaigns">
                    <div class="col-md-6">
                        <div class="card mb3 campaign-a">
                            <div class="card-header">Campaign A</div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="ABTestingCampaign_messageTemplateIdA"><?php echo $attributeLabels['messageTemplateIdA']; ?></label>
                                    <div class="row">
                                        <div class="col-sm-9 col-md-8 col-xl-9">
                                            <div class="form-group">
                                                <?php echo $form->field($model, 'messageTemplateIdA')->dropDownList(MessageTemplate::getTemplateOptions(), ['class' => 'form-control'])->label(false) ?>
                                                <div class="form-feild-info">
                                                    <?php echo Yii::t('messages', 'Subscriber will receive an email using the selected email template.') ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="ABTestingCampaign_fromA"><?php echo $attributeLabels['fromA']; ?></label>
                                    <?php echo $form->field($model, 'fromA')->textInput(['class' => 'form-control', 'size' => 45, 'maxlength' => 45, 'hint' => Yii::t('messages', 'Hint: A Peter')])->label(false); ?>
                                </div>
                                <div class="form-group">
                                    <label for="ABTestingCampaign_subjectA"><?php echo $attributeLabels['subjectA']; ?></label>
                                    <?php echo $form->field($model, 'subjectA')->textInput(['class' => 'form-control', 'rows' => 3, 'cols' => 50, 'hint' => Yii::t('messages', 'Hint: Email Subject')])->label(false); ?>
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1"><?php echo Yii::t('messages', 'User Count A'); ?></label>
                                    <div class="row">
                                        <div class="col-sm-9 my-auto">
                                            <div class="controls">
                                                <?php
                                                echo SliderInput::widget([
                                                    'model' => $model,
                                                    'attribute' => 'amountA',
                                                    'value' => 1,
                                                    'clientOptions' => [
                                                        'min' => 1,
                                                        'max' => 1,
                                                    ],
                                                    'clientEvents' => [
                                                         'change' => 'function (event,ui) { $("#abtestingcampaign-counta").val(ui.value); }'
                                                     ],

                                                    'class'=>'valueSlider',



                                                ]);
                                                ?>
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <?php
                                            echo $form->field($model, 'countA')->textInput(['class' => 'form-control text-center', 'size' => 4, 'maxlength' => 4,
                                                'style' => "", 'value' => 1, 'onkeypress' => 'return isNumberKey(event)']); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mb3 campaign-b">
                            <div class="card-header">Campaign B</div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="ABTestingCampaign_messageTemplateIdB"><?php echo $attributeLabels['messageTemplateIdB']; ?></label>
                                    <div class="row">
                                        <div class="col-sm-9 col-md-8 col-xl-9">
                                            <div class="form-group">
                                                <?php echo $form->field($model, 'messageTemplateIdB')->dropDownList(MessageTemplate::getTemplateOptions(), ['class' => 'form-control'])->label(false) ?>
                                                <div class="form-feild-info">
                                                    <?php echo Yii::t('messages', 'Subscriber will receive an email using the selected email template.') ?>                                                    </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="ABTestingCampaign_fromB"><?php echo $attributeLabels['fromB']; ?></label>
                                    <?php echo $form->field($model, 'fromB')->textInput(['class' => 'form-control', 'size' => 45, 'maxlength' => 45, 'hint' => Yii::t('messages', 'Hint:Andrew Peter')])->label(false); ?>
                                </div>
                                <div class="form-group">
                                    <label for="ABTestingCampaign_subjectB"><?php echo $attributeLabels['subjectB']; ?></label>
                                    <?php echo $form->field($model, 'subjectB')->textInput(['class' => 'form-control', 'rows' => 3, 'cols' => 50, 'hint' => Yii::t('messages', 'Hint: Email Subject')])->label(false); ?>
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1"><?php echo Yii::t('messages', 'User Count B'); ?></label>
                                    <div class="row">
                                        <div class="col-sm-9 my-auto">
                                            <div class="controls">
                                                <?php
                                                echo SliderInput::widget([
                                                    'model' => $model,
                                                    'attribute' => 'amountB',
                                                    'clientOptions' => [
                                                        'min' => 1,
                                                        'max' => 1,
                                                    ],
                                                    'class' => 'valueSlider',
                                                    'clientEvents' => [
                                                        'change' => 'function (event,ui) { $("#abtestingcampaign-countb").val(ui.value); }'
                                                    ],
                                                ]);
                                                ?>
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <?php
                                            echo $form->field($model, 'countB')->textInput(['class' => 'form-control text-center', 'size' => 4, 'maxlength' => 4,
                                                'style' => "", 'value' => 1, 'onkeypress' => 'return isNumberKey(event)']); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="text-left text-md-right mt-3">
                    <?php
                    echo Html::submitButton(Yii::t('messages', 'Create'), ['id' => 'create', 'class' => 'btn btn-primary mr-2'], Yii::$app->urlManager->createUrl('a-b-testing/create-camp'));

                    echo Html::a(Yii::t('messages', 'Cancel'), Yii::$app->urlManager->createUrl('dashboard/dashboard'), ['id' => 'cancel', 'class' => 'btn-secondary btn']);
                    ?>
                </div>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
</div>

<script>
    $(document).ready(function () {
        $('#dialog').hide();
        $('input').change(function (e) {
            setTimeout(function () {
                inputValidation();
            }, 1000);
        });
        $('select').change(function (e) {
            setTimeout(function () {
                inputValidation();
            }, 1000);
        });
        $('#abtestingcampaign-counta').change(function (e) {
            var userCount = $('#abtestingcampaign-usercount').val();
            if (userCount > 0) {
                var percentageA = ($('#abtestingcampaign-counta').val() * 10 / userCount) * 100;
                $('#abtestingcampaign-amounta-container').slider('value', percentageA);
            } else {
                $('#dialog').show();
                $("#dialog").dialog({
                    height: 140,
                    modal: true,
                    open: function (event, ui) {
                        $('#abtestingcampaign-counta').val(1);
                        $('#dialog').html('<p class="alert-danger"><?php echo $warning3; ?></p>');
                        setTimeout("$('#dialog').dialog('close')", 5000);
                    }
                });
            }
        });
        $('#abtestingcampaign-countb').change(function (e) {
            var userCount = $('#abtestingcampaign-usercount').val();
            if (userCount > 0) {
                var percentageB = ($('#abtestingcampaign-countb').val() * 10 / userCount) * 100;
                $('#abtestingcampaign-amountb-container').slider('value', percentageB);
            } else {
                $('#dialog').show();
                $("#dialog").dialog({
                    height: 140,
                    modal: true,
                    open: function (event, ui) {
                        $('#abtestingcampaign-countb').val(1);
                        $('#dialog').html('<p class="alert-danger"><?php echo $warning3; ?></p>');
                        setTimeout("$('#dialog').dialog('close')", 5000);
                    }
                });
            }
        });
    });

    function inputValidation() {
        var templateA = $('#abtestingcampaign-messagetemplateida').val();
        var templateB = $('#abtestingcampaign-messagetemplateidb').val();
        var fromA = $('#abtestingcampaign-froma').val();
        var fromB = $('#abtestingcampaign-fromb').val();
        var subjectA = $('#abtestingcampaign-subjecta').val();
        var subjectB = $('#abtestingcampaign-subjectb').val();
        var templateChk = (templateA != '' && templateB == '' || templateA == '' && templateB != '') ? false : true;
        if (templateChk && templateA == templateB && fromA == fromB) {
            $('#dialog').show();
            $("#dialog").dialog({
                height: 140,
                modal: true,
                open: function (event, ui) {
                    $('#abtestingcampaign-fromb').val('');
                    $('#dialog').html('<p class="alert-danger"><?php echo $warning1; ?></p>');
                    setTimeout("$('#dialog').dialog('close')", 5000);
                }
            });
        }
        if (templateChk && templateA != templateB && subjectA == subjectB) {
            $('#dialog').show();
            $("#dialog").dialog({
                height: 140,
                modal: true,
                open: function (event, ui) {
                    $('#abtestingcampaign-subjectb').val('');
                    $('#dialog').html('<p class="alert-danger"><?php echo $warning2; ?></p>');
                    setTimeout("$('#dialog').dialog('close')", 10000);
                }
            });

        }
    }

    function isNumberKey(evt) {
        var charCode = (evt.which) ? evt.which : event.keyCode;
        if ((charCode < 48 || charCode > 57))
            return false;

        return true;
    }
</script>

