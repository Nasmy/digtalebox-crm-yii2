<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;

$attributeLabels = $model->attributeLabels(); ?>
<script type="text/javascript">
    function copyToClipboard() {
        $('#KeywordUrl_url').select();
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
                            <div class="content-panel-sub">
                                <div class="panel-head"><?php Yii::t('messages', 'Keyword Url Details') ?></div>
                            </div>
                            <?php
                            $form = ActiveForm::begin([
                                'id' => 'keyword-url-form',
                                'options' => [
                                    'class' => 'form-horizontal',
                                    'method' => 'post',
                                    'enableAjaxValidation'=>true
                                ],

//                                'enableAjaxValidation' => true,
//                                'options' =>[
//                                    'validateOnSubmit' => true,
//                                ]
                            ]); ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="KeywordUrl_title"><?php echo $attributeLabels['title']; ?></label>
                                        <?= $form->field($model, 'title')->textInput(['rows' => '2','class' => 'form-control'])->label(false); ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="KeywordUrl_title"><?php echo $attributeLabels['fee']; ?></label>
                                        <?= $form->field($model, 'fee')->textInput(['rows' => '2','class' => 'form-control'])->label(false); ?>
                                    </div>
                                </div>

                            </div>

                            <div class="form-row text-left text-md-right">
                                <div class="form-group col-md-12">

                                    <?php
                                        echo Html::submitButton($model->isNewRecord ? Yii::t('messages', 'Create') : Yii::t
                                        ('messages', 'Save'), ['class' => 'btn btn-primary']);
                                     ?>
                                    <a href="<?= Yii::$app->urlManager->createUrl('membership-type/admin')?>" class="btn btn-secondary">Cancel</a>

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

<?php
$this->registerJs('$("#submitForm").prop("disabled", "disabled");');
?>