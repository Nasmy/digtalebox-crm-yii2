<style>
    .custom-file > .error {
        display: none;
    }
</style>

<?php

use app\models\AdvanceBulkInsert;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'People'), 'url' => ['admin']];
$this->params['breadcrumbs'][] = Yii::t('messages', 'FileTransform');

$this->title = Yii::t('messages', 'Re Format csv files');
$this->titleDescription = Yii::t('messages', 'Import Your "csv" file, Formated csv file will automatically download');
?>
<style type="text/css">
    .custom-file .has-error label {
        bottom: 2em;
        position: absolute;
    }

    input#inputGroupFile02 {
        position: relative;
        top: 10px;
    }

    .custom-file .has-error .help-block {

        height: 0px;
        margin-top: 20px;

    }
</style>
<div class="row no-gutters">
    <div class="content-panel col-md-12">
        <div class="content-inner">
            <div class="content-area">
                <p><?php echo Yii::t('messages', 'Re Format csv files'); ?></p>
                <br> <?php
                $form = ActiveForm::begin([
                    'id' => "excelForm",
                    'options' => ['enctype' => 'multipart/form-data'],
                ]);
                ?>

                <div class="form-row">
                    <div class="input-group mb-1 col-md-6">
                        <div class="custom-file">
                            <?php echo $form->field($model, 'fileToUpload')->fileInput(array('class' => 'custom-file-input', 'id' => 'inputGroupFile02')) ?>
                            <label class="custom-file-label"
                                   for="inputGroupFile02"><?php echo Yii::t('messages', 'Choose file'); ?></label>
                        </div>
                        <div class="input-group-append">
                            <button class="btn btn-secondary" type="button" data-toggle="modal"
                                    data-target="#fileFormat"><?php echo Yii::t('messages', 'Sample Format'); ?>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="form-row text-left text-md-left" style="padding-left: 05px;position: relative; top: 20px;">
                    <p><?php echo Yii::t('messages', 'Your file should be in csv with comma delimiter.Encoding should be in WinLatin 1 - Windows 1252'); ?></p>

                </div>
                <div class="form-row text-left text-md-left mt-3">
                    <?php
                    if (!empty($download)) {
                        echo AdvanceBulkInsert::getReWrightCsvFile($download);
                    }
                    ?>
                </div>
                <?php echo Html::img('@web/images/db_loading_icon.gif', ['id' => 'loading']) ?>
                <div class="form-row text-left text-md-right">
                    <div class="form-group col-md-12">
                        <?php
                        echo Html::submitButton(Yii::t('messages', 'Upload'), ['class' => 'btn btn-primary', 'id' => 'submit-button']);
                        ?>
                        <?php
                        echo Html::a(Yii::t('messages', 'Cancel'), Yii::$app->urlManager->createUrl('/advanced-bulk-insert/admin'), ['class' => 'btn btn-secondary']);
                        ?>

                    </div>
                </div>

                <?php
                ActiveForm::end();
                ?>
            </div>
        </div>
    </div>
</div>

<?php
$hint1 = "<strong>" . Yii::t('messages', 'Your file should be in csv with comma delimiter.Encoding should be in WinLatin 1 - Windows 1252') . "<br></strong>" . Html::encode(Yii::t('messages', '<FIRST NAME>,<LAST NAME>,<EMAIL>,<MOBILE>,<STREET>,<ZIP>,<CITY>,<GENDER>,<DOB>,<NOTE>,<KEYWORD>,<Category>,<Country>'));
$hint2 = "<br/><strong>" . Yii::t('messages', 'Maximum Number of Rows') . ":</strong>" . AdvanceBulkInsert::MAX_LINE_SIZE;
?>
<div class="modal fade" id="fileFormat" tabindex="-1" role="dialog" aria-labelledby="fileFormat" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"
                    id="exampleModalLongTitle"><?php echo Yii::t('messages', 'Sample Fields'); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <?php echo $hint1 . $hint2 ?>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        data-dismiss="modal"><?php echo Yii::t('messages', 'Close'); ?></button>
            </div>
        </div>
    </div>
</div>
<script>

    $("#loading").hide();
    $('#inputGroupFile02').on('change', function () {

        //get the file name
        var fileName = $(this).val();
        if (!!$(this).prop('files') && $(this).prop('files').length > 1) {
            fileName = $(this)[0].files.length + ' files';
        } else {
            fileName = fileName.substring(fileName.lastIndexOf('\\') + 1, fileName.length);
        }

        //replace the "Choose a file" label
        $('.custom-file-label').html(fileName);
    });

    $('#excelForm').on('submit', function (e) {
        $("#loading").show();
        e.preventDefault();
        var form = $(this);
        var formData = form.serialize();

        $.ajax({
            url: form.attr("action"),
            type: form.attr("method"),
            data: formData,
            success: function (data) {
                $("#loading").hide();
            },
            error: function () {
                $("#loading").hide();
            }
        });
        return false;

    });

</script>