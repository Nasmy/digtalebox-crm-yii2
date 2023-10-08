<?php

use app\models\MessageTemplate;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

?>
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

<style type="text/css">

    .enable-payment p.help-block {
        margin: 0px !important;
    }

    .enable-payment label {
        margin: 0;
    }
</style>
<div>
    <div class="row no-gutters">
        <div class="content-panel col-md-12">
            <div class="content-inner">
                <div class="content-area">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="content-panel-sub">
                                <div class="panel-head">
                                    <?php Yii::t('messages', 'Form Details') ?></div>
                            </div>
                            <?php
                            $form = ActiveForm::begin([
                                'id' => 'form-builder-form',
                                'options' => [
                                    'class' => 'form-horizontal',
                                    'method' => 'post',
                                    'enableAjaxValidation' => true,
                                    'validateOnSubmit' => true,
                                ],
                            ]);
                            ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="Form_title"><?php echo $attributeLabels['title']; ?></label>
                                        <?php echo $form->field($model, 'title')->textInput(['class' => 'form-control'])->label(false); ?>
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
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
                                                'id' => 'Form_fieldList'
                                            ],
                                        ])->label(false);
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="Form_redirectAddress"><?php echo $attributeLabels['redirectAddress']; ?></label>
                                        <?php echo $form->field($model, 'redirectAddress')->textInput(['class' => 'form-control', 'placeholder' => 'http://www.example.com'])->label(false); ?>

                                    </div>
                                </div>
                                <div class="form-group col-md-6">
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
                                                'id' => 'Form_keywords'
                                            ],
                                        ])->label(false);
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mt-3">
                                        <label for="form-check" class="d-block">Options</label>
                                        <div class="form-check form-check-inline">

                                            <?php

                                            echo $form->field($model, 'enabled')->checkbox(['class' => 'form-check-input custom-icheck']); ?>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <?php echo $form->field($model, 'notified')->checkbox(['class' => 'form-check-input custom-icheck']); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="Form_templateId"><?php echo $attributeLabels['templateId']; ?></label>
                                        <?php
                                        $messageTemplate = new MessageTemplate();
                                        echo $form->field($model, 'templateId')->dropDownList($messageTemplate->getTemplateOptions(), ['class' => 'form-control', 'id' => 'Form_templateId'])->label(false)
                                        ?>
                                        <div class="form-feild-info"><?php echo Yii::t('messages', 'Subscriber will receive an email using the selected email template.') ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="row enable-payment">
                                            <div class="col-lg-12 col-xl-5 ">
                                                <div class="switch switch-sm">
                                                    <?php
                                                    echo $form->field($model, 'enablePayment',
                                                        ['checkboxTemplate' => "{input}{beginLabel}\n{labelTitle}\n{endLabel}\n{error}\n{hint}"])
                                                        ->checkbox(['class' => 'switch form-control', 'id' => 'Form_enablePayment']);
                                                    ?>
                                                </div>
                                            </div>
                                            <div class="col-lg-12 col-xl-7">
                                                <div class="form-check form-check-inline isMembership">
                                                    <?php
                                                    echo $form->field($model, 'isMembership')->checkbox(['class' => 'form-check-input custom-icheck']);
                                                    ?>
                                                </div>
                                                <div class="form-check form-check-inline isDonation">
                                                    <?php
                                                    echo $form->field($model, 'isDonation')->checkbox(['class' => 'form-check-input custom-icheck']);
                                                    ?>
                                                </div>
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
                                    <a href="<?= Yii::$app->urlManager->createUrl('form-builder/admin') ?>"
                                       class="btn btn-secondary"><?= Yii::t('messages', 'Cancel') ?></a>
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
