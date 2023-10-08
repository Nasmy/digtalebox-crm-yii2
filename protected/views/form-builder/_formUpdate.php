<?php

use http\Url;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\Html;
use app\models\MessageTemplate;
?>

<style type="text/css">

    .options-checkbox .form-group ,.options-checkbox label {
        margin: 2px 0px !important;
    }
    .options-checkbox p {
        margin: 0;
    }
</style>
<script type="text/javascript">
    function copyToClipboard() {
        $('#textContent').select();
        document.execCommand("copy");
    }
</script>
<?php Yii::$app->toolKit->registerTinyMceScripts(); ?>
<?php
$script = <<< JS
 
	tinymce.init({
		readonly : 1,
		selector:'#textEditor',
		theme:'modern',
		menubar: false,
        statusbar: false,
        toolbar: false,
		setup: function(ed) {
		},
		relative_urls : false,
		remove_script_host : false,
		convert_urls : true,
		//valid_children : '+body[style],style[type],head,html',
		verify_html : false,
		cleanup: false,
		valid_elements : '*[*]',
	});
	
    jQuery('#Form_enablePayment').change(function () {
        showPaymentOptions();
    });

    function showPaymentOptions() {
        if ($('#Form_enablePayment').is(':checked')) {
            $('.isDonation').show();
            $('.isMembership').show();
        } else {
            $('.isDonation').hide();
            $('.isMembership').hide();
            $('#Form_isDonation').iCheck('uncheck');
            $('#Form_isMembership').iCheck('uncheck');
            $('#Form_enablePayment_em_').hide();
        }
    }

    showPaymentOptions();

    function toggleDependentErrorMsg(isMember,isDonation) {
            if(isMember == false && isDonation == false){
                $('#Form_enablePayment_em_:hidden').show();
            }else{
                $('#Form_enablePayment_em_:visible').hide();
            }
    }

    var isMember = false;
    var isDonation = false;
    $('#Form_isMembership, #Form_isDonation').on('ifToggled', function() {
        $('#Form_isMembership').on('ifChecked', function (event) {
            isMember = true;
            toggleDependentErrorMsg(isMember,isDonation);
        });
        $('#Form_isDonation').on('ifChecked', function (event) {
            isDonation = true;
            toggleDependentErrorMsg(isMember,isDonation);
        });
        $('#Form_isMembership').on('ifUnchecked', function (event) {
            isMember = false;
            toggleDependentErrorMsg(isMember,isDonation);
        });
        $('#Form_isDonation').on('ifUnchecked', function (event) {
            isDonation = false;
            toggleDependentErrorMsg(isMember,isDonation);
        });
    });
    
JS;

$this->registerJs($script);
?>

<script type="text/javascript">

    function copyToClipboard() {
        jQuery('.textEditor').select();
        document.execCommand("copy");
    }
</script>

<div>
    <div class="row no-gutters">
        <div class="content-panel col-md-12">
            <div class="content-inner">
                <div class="content-area">
                    <div class="row">
                        <div class="col-md-12">
                            <?php
                            $form = ActiveForm::begin([
                                'id' => 'form-builder-form',
                                'options' => [
                                    'class' => 'form-horizontal',
                                    'method' => 'post',
                                    'enableAjaxValidation'=>true,
                                    'validateOnSubmit' => true,
                                ],
                            ]);
                            ?>

                            <div class="row">
                            <div class="col-md-8 col-lg-7 col-xl-8">

                                    <div class="content-panel-sub">
                                        <div class="panel-head">
                                            <?php echo Yii::t('messages', 'Form Details') ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="Form_title"><?php echo $attributeLabels['title']; ?></label>
                                        <?php echo $form->field($model, 'title')->textInput(['class' => 'form-control'])->label(false); ?>
                                    </div>
                                    <div class="form-group">
                                    <label for="Form_fieldList"><?php echo $attributeLabels['fieldList']; ?></label>
                                    <div class="controls">
                                        <?php
                                        echo $form->field($model, 'fieldList')->widget(Select2::className(), [
                                            'name' => 'fieldList',
                                            'data' => $model->isNewRecord ? $model->hasErrors() ? $model->getFieldList($model->fieldList) : $model->getFieldList() : $model->getFieldList($model->fieldList),
                                            'size' => Select2::MEDIUM,
                                            'options' => [
                                                'fullWidth' => false,
                                                'placeholder' => 'FieldList',
                                                'class' => 'form-control form-control-selectize',
                                                'multiple' => true,
                                                'id'=>'Form_fieldList'
                                            ],
                                        ])->label(false);
                                        ?>

                                    </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="Form_redirectAddress"><?php echo $attributeLabels['redirectAddress']; ?></label>
                                        <?php echo $form->field($model, 'redirectAddress')->textInput(['class' => 'form-control','placeholder' => 'http://www.example.com'])->label(false); ?>
                                     </div>
                                    <div class="form-group">
                                    <label for="Form_keywords"><?php echo $attributeLabels['keywords']; ?></label>
                                    <div class="controls">
                                        <?php
                                        echo $form->field($model, 'keywords')->widget(Select2::className(), [
                                            'name' => 'keywords',
                                            'data' => $keywords,
                                            'size' => Select2::MEDIUM,
                                            'options' => [
                                                'fullWidth' => false,
                                                'placeholder' => 'Keywords',
                                                'class' => 'form-control form-control-selectize',
                                                'multiple' => true,
                                                'id'=>'Form_keywords'
                                            ],
                                        ])->label(false);
                                        ?>
                                     </div>
                                    </div>
                                    <div class="form-group mt-3 options-checkbox">
                                        <label for="form-check" class="d-block">Options</label>
                                        <div class="form-check form-check-inline">
                                            <?php echo $form->field($model, 'enabled')->checkbox(['class'=>'form-check-input custom-icheck']); ?>
                                          </div>
                                        <div class="form-check form-check-inline">
                                            <?php echo $form->field($model, 'notified')->checkbox(['class'=>'form-check-input custom-icheck']); ?>
                                          </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="Form_templateId"><?php echo $attributeLabels['templateId']; ?></label>
                                        <?php
                                        $messageTemplate = new MessageTemplate();

                                        echo  $form->field($model,'templateId')->dropDownList($messageTemplate->getTemplateOptions(),['class' => 'form-control','id' => 'Form_templateId'])->label(false)
                                        ?>
                                         <div class="form-feild-info"><?php echo Yii::t('messages','Subscriber will receive an email using the selected email template.') ?></div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-lg-12 col-xl-5">
                                                <div class="switch switch-sm">
                                                    <?php
                                                    echo $form->field($model, 'enablePayment',
                                                        ['checkboxTemplate'=>"{input}{beginLabel}\n{labelTitle}\n{endLabel}\n{error}\n{hint}"])
                                                        ->checkbox(['class'=>'switch form-control','id'=>'Form_enablePayment']);
                                                    ?>

                                                </div>
                                            </div>
                                            <div class="col-lg-12 col-xl-7">
                                                <div class="form-check form-check-inline isMembership">
                                                    <?php
                                                        echo $form->field($model, 'isMembership')->checkbox(['class'=>'form-check-input custom-icheck']);
                                                    ?>
                                                </div>
                                                <div class="form-check form-check-inline isDonation">
                                                    <?php
                                                        echo $form->field($model, 'isDonation')->checkbox(
                                                                ['class'=>'form-check-input custom-icheck']);
                                                    ?>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <div class="col-md-4 col-lg-5 col-xl-4">
                                <div class="content-panel-sub">
                                    <div class="panel-head">
                                        <?php echo Yii::t('messages', 'Form Preview') ?>
                                    </div>
                                </div>
                                    <div class="row">
                                        <div class="col-md-12">

                                            <div class="form-group mt-3">
                                                <a data-toggle="modal" data-target="#showCode" class="btn btn-primary btn-sm" ><?php echo Yii::t('messages', 'Show HTML Code') ?></a>
                                            </div>

                                            <div class="form-group">
                                                <?php echo $form->field($model, 'preview')->textarea(['readonly' => 'readonly', 'rows' => 15, 'cols' => 50, 'id' => 'textEditor', 'class' => 'form-preview'])->label(false); ?>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>

                        <!-- Add Modal -->
                        <div class="modal fade" id="showCode" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered model-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLongTitle"><?php echo Yii::t('messages', 'HTML Code') ?></h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>


                                    <div class="modal-body">
                                        <div class="form-group">
                                            <?php echo $form->field($model, 'content')->textarea(['readonly' => 'readonly', 'rows' => 15, 'cols' => 50, 'id' => 'textEditor', 'class' => 'textEditor form-preview'])->label(false); ?>
                                        </div>
                                        <div class="form-group mt-3">
                                            <button type="button" onclick="copyToClipboard()" class="btn btn-primary">Copy Code</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="form-row text-left text-md-right">
                            <div class="form-group col-md-12">
                                <?php
                                echo Html::submitButton($model->isNewRecord ? Yii::t('messages', 'Create') : Yii::t
                                ('messages', 'Save'), ['class' => 'btn btn-primary']);
                                ?>
                                <a href="<?= Yii::$app->urlManager->createUrl('form-builder/admin')?>" class="btn btn-secondary"><?php echo Yii::t('messages','Cancel');?></a>
                            </div>
                        </div>

                            </div>
                        <?php ActiveForm::end(); ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<script type="text/javascript">

    $('#Form_enablePayment').change(function () {
        showPaymentOptions();
    });

    function showPaymentOptions() {
        if ($('#Form_enablePayment').is(':checked')) {
            $('.isDonation').show();
            $('.isMembership').show();
        } else {
            $('.isDonation').hide();
            $('.isMembership').hide();
        }
    }

    showPaymentOptions();
</script>

