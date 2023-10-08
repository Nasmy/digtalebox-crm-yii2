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
                                'fieldConfig' => [
                                    'template' => "{label}\n<div class=\"\">{input}</div>\n{error}",
                                    'labelOptions' => ['class' => 'm-0 control-label'],
                                ],
                                'enableClientValidation' => true,
                                'enableAjaxValidation' => true,
                                'options' => [
                                    'class' => 'form-horizontal',
                                    'method' => 'post',
//                                    'validateOnSubmit' => true,
                                ],

                            ]); ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="KeywordUrl_title"><?php echo $attributeLabels['title']; ?></label>
                                        <?= $form->field($model, 'title')->textInput(['rows' => '2', 'class' => 'form-control'])->label(false); ?>
                                    </div>

                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="KeywordUrl_externalUrl"><?php echo $attributeLabels['externalUrl']; ?></label>
                                        <?= $form->field($model, 'externalUrl')->textInput(['rows' => '2', 'class' => 'form-control'])->label(false); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="KeywordUrl_keywords"><?php echo $attributeLabels['keywords']; ?></label>
                                    <div class="controls">
                                        <?php
                                        echo $form->field($model, 'keywords')->widget(Select2::className(), [
                                            'name' => 'keywords',
                                            'data' => $keywords,
                                            'size' => Select2::MEDIUM,
                                            'options' => [
                                                'placeholder' => 'Keywords',
                                                'class' => 'form-control form-control-selectize',
                                                'multiple' => true
                                            ],

                                        ])->label(false);
                                        ?>
                                    </div>
                                </div>
                                <?php if ($model->isNewRecord != true): ?>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="KeywordUrl_url"><?php echo $attributeLabels['url']; ?></label>
                                            <?php
                                            echo
                                            $form->field($model, 'url')
                                                ->textInput(['rows' => '2', 'class' => 'form-control', 'id' => 'KeywordUrl_url'])
                                                ->hint('<br><button class="btn btn-primary btn-small" onclick="copyToClipboard()" type="button">' . Yii::t('messages', 'Copy Url') . '</button>')
                                                ->label(false);

                                            ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="form-row text-left text-md-right">
                                <div class="form-group col-md-12">

                                    <?php
                                    echo Html::submitButton($model->isNewRecord ? Yii::t('messages', 'Create') : Yii::t
                                    ('messages', 'Save'), ['class' => 'btn btn-primary']);
                                    ?>
                                    <a href="<?= Yii::$app->urlManager->createUrl('keyword-url/admin') ?>"
                                       class="btn btn-secondary"><?= Yii::t('messages', 'Cancel'); ?></a>

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
