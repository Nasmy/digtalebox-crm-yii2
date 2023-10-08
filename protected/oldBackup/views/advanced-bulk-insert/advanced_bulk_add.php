<?php

use app\models\AdvanceBulkInsert;
use app\models\Country;
use app\models\CustomField;
use app\models\CustomType;
use app\models\CustomValue;
use app\models\User;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\advanceBulkInsert */

$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'People'), 'url' => ['admin']];
$this->params['breadcrumbs'][] =     Yii::t('messages', 'Advanced Bulk Insert');

$this->title = Yii::t('messages', 'Add Zip File');
$this->titleDescription = Yii::t('messages', 'Import Mass people details via ".zip" file');

?>

<style>
    .custom-file > .error {
        display: none;
    }

    .custom-file .has-error .help-block {
        margin: 0px 0px 25px !important;
    }

   .custom-file-label::after {
        content: "<?php echo Yii::t('messages', 'Browse') ?>";
    }

</style>

<div class="row no-gutters">
    <div class="content-panel col-md-12">
        <div class="content-inner">
            <div class="content-area">
                <br> <?php
                $form = ActiveForm::begin([
                    'id' => 'bulk-user-form',
                    'options' => ['enctype' => 'multipart/form-data'],
                    'fieldConfig' => ['errorOptions' => ['encode' => false, 'class' => 'help-block', 'style' => 'margin-top: 70px !important']]
                ]);
                ?>
                <?php
                $hint1 = $hintData['hint1'];
                $hint2 = $hintData['hint2'];
                ?>

                <?php
                $link = Html::a(Yii::t('messages', 'keyword'), array('keyword/admin'), array('class' => ''));
                $keywordHint = "<strong>" . Yii::t('messages', 'Note') . ":</strong>" . Yii::t('messages', 'Keywords needs to be defined via') . " {$link} " . Yii::t('messages', ' section');
                ?>

                <div class="form-group">
                    <label style="font-size: 18px"><?php echo Yii::t('messages','File'); ?></label>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <div class="input-group mb-1">
                            <div class="custom-file">
                                <?php echo $form->field($model, 'bulkFile')->fileInput(array('class' => 'custom-file-input', 'id' => 'inputGroupFile02'))->label(false); ?>
                                <label class="custom-file-label" for="inputGroupFile02" id="choose-file"><?php echo Yii::t('messages','Choose file'); ?></label>
                            </div>
                            <div class="input-group-append">
                                <button class="btn btn-secondary" type="button" data-toggle="modal"
                                        data-target="#fileFormat"><?php echo Yii::t('messages','Sample Format'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-row text-left text-md-left" style="padding-left: 25px;">
                    <input type="checkbox" class="form-check-input checkbox" name="excel"  value="excel" id="reFormat"><?php echo Yii::t('messages','I want to clean My CSV file'); ?><br>
                </div>
                <div class="form-row text-left text-md-left" style="padding-left: 31px;padding-top: 18px;">
                    <?php if ($model->isNewRecord) {
                        echo   Html::a(Yii::t('messages','Re Format CSV File'),
                            Yii::$app->urlManager->createUrl('/advanced-bulk-insert/re-write'),
                            [ 'class' => 'btn btn-info',
                                'id' => 'reWriteButton',
                                'style' => 'visibility: hidden;color: white;'
                            ]);
                    } ?>
                </div>
                <div class="form-row text-left text-md-right">
                    <div class="form-group col-md-12">
                        <?php
                        echo  Html::submitButton(Yii::t('messages', 'Upload'),['class'=>'btn btn-primary']);
                        ?>
                        <?php if ($model->isNewRecord) {
                            echo  Html::a(Yii::t('messages', 'Cancel'),Yii::$app->urlManager->createUrl('/advanced-bulk-insert/admin'),['class'=>'btn btn-secondary']);
                        } ?>

                    </div>
                </div>

                <?php
                ActiveForm::end();
                ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="fileFormat" tabindex="-1" role="dialog" aria-labelledby="fileFormat" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle"><?php echo Yii::t('messages','Sample File Format'); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <?php echo $hint1 . $hint2 ?>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo Yii::t('messages','Close'); ?></button>
            </div>
        </div>
    </div>
</div>


<script>
    $(document).ready(function(){
        // $("#inputGroupFile02").pseudo(":before","content","brown")
        $('.custom-file-label:after').css({
            "content": "red",
            "border-top-color": " #88b7d5",
            "border-width": "12px",
            "left": "0%"
        });
    });

    $('#inputGroupFile02').on('change', function () {
        //get the file name
        var fileName = $(this).val();
        if (!!$(this).prop('files') && $(this).prop('files').length > 1) {
            fileName = $(this)[0].files.length + ' files';
        }
        else {
            fileName = fileName.substring(fileName.lastIndexOf('\\') + 1, fileName.length);
        }
        //replace the "Choose a file" label
        $('.custom-file-label').html(fileName);
    });
    $('#excelForm').submit(function(e) {
        e.preventDefault(); // don't submit multiple times
        this.submit(); // use the native submit method of the form element
        $('#fileToUpload').val(''); // blank the input
    });

    $('#reFormat').on('change',function() {

        if($(this).prop("checked") == true){
            $("#reWriteButton").attr("style", "visibility: visible;color: white;")
        }

        else if($(this).prop("checked") == false){
            $("#reWriteButton").attr("style", "visibility: hidden")
        }
    });

</script>
