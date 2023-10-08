<?php

use app\models\CandidateInfo;
use yii\bootstrap\Button;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\assets\ImageAsset;


/* @var $this yii\web\View */
/* @var $model app\models\CandidateInfo */
/* @var $form yii\widgets\ActiveForm */
ImageAsset::register($this);
?>
<div>
    <div class="row no-gutters">
        <div class="content-panel col-md-12">
            <div class="content-inner">
                <div class="content-area pt-5">
                    <?php $form = ActiveForm::begin([
                        'id' => 'candidate-info-form',
                        'options' => ['enctype' => 'multipart/form-data']]); ?>
                    <div class="row">
                        <div class="col-md-4" id="refresh">
                            <div class="form-row">
                                <div class="form-group profile-image text-center">
                                    <div class="account-picture">
                                        <?php echo $profImage; ?>
                                    </div>
                                    <a class="btn btn-primary" data-toggle="modal"
                                       data-target="#addImage"
                                       data-backdrop="static"><?php echo Yii::t('messages', 'Add/Change Profile Image'); ?> </a>
                                </div>
                            </div>
                        </div>
                        <?php
                        $hint = Yii::t('messages', "Minimum width:") . CandidateInfo::MIN_VOL_IMG_WIDTH . 'px, ';
                        $hint .= Yii::t('messages', "Minimum height:") . CandidateInfo::MIN_VOL_IMG_HEIGHT . 'px ';
                        ?>
                        <!---Col8 column will come hear-->
                        <div class="col-md-8">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <?= $form->field($model, 'volunteerBgImageFile')->fileInput(['class' => 'form-control', 'placeholder' => $attributeLabels['volunteerBgImageFile']]) ?>
                                    <div class="form-feild-info"><?php echo $hint; ?></div>
                                </div>
                                <div class="form-group col-md-6">
                                    <label style="display: block;">&nbsp;</label>
                                    <?php // echo Button::widget(["label" => Yii::t('messages','Set Default'), "options" => ["class" => "btn-success grid-button", 'name'=>'setDefault']]); ?>
                                    <?= Html::submitButton(Yii::t('messages', 'Set Default'), ['class' => 'btn btn-success', 'value' => 'Create', 'name' => 'setDefault']) ?>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="email"><?php echo $attributeLabels['slogan']; ?></label>
                                    <?= $form->field($model, 'slogan')->textInput(['class' => 'form-control'])->label(false); ?>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="email"><?php echo $attributeLabels['introduction']; ?></label>
                                    <?php echo $form->field($model, 'introduction')->textarea(['class' => 'form-control'])->label(false); ?>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="address"><?php echo $attributeLabels['promoText']; ?></label>
                                    <?php echo $form->field($model, 'promoText')->textInput(['class' => 'form-control'])->label(false) ?>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="zipcode"><?php echo $attributeLabels['regUrl']; ?></label>
                                    <?php echo $form->field($model, 'regUrl')->textInput(['class' => 'form-control'])->label(false) ?>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="zipcode"><?php echo $attributeLabels['signupFields']; ?></label>
                                    <?php echo $form->field($model, 'signupFields')->dropDownList($customSinupFields, ['class' => 'form-control', 'multiple' => true])->label(false) ?>
                                </div>
                            </div>
                        </div>
                        <!----End Col8 --->
                    </div>

                    <div class="text-left text-md-right">
                        <?= Html::submitButton($model->isNewRecord ? Yii::t('messages', 'Create') : Yii::t('messages', 'Save'), ['class'=> 'btn btn-primary']); ?>
                        <a target="_blank" href="<?php echo $model->regUrl; ?>"
                           class="btn btn-secondary"><?php echo Yii::t('messages', 'Preview') ?></a>
                    </div>

                    <?php ActiveForm::end(); ?>
                    <!-- Add Modal -->
                    <div class="modal fade" id="addImage" tabindex="-1" role="dialog"
                         aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title"
                                        id="exampleModalLongTitle"><?php echo Yii::t('messages', 'Add/Change Profile Image'); ?></h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>


                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="row">
                                                <div class="col-md-12 mb-3">
                                                    <h6> <?php echo Yii::t('messages', 'Current Profile image'); ?> </h6>
                                                    <?php echo $profImage; ?>
                                                </div>
                                                <div class="col-md-12 mb-3">
                                                    <!-- <h3>Demo:</h3> -->
                                                    <label class="btn btn-primary btn-upload" for="inputImage">
                                                        <input type="file" class="sr-only" id="inputImage" name="file"
                                                               accept=".jpg,.jpeg,.png">
                                                        <span class="docs-tooltip" data-animation="false">
                                                                    <i class="fa fa-upload"></i> <?php echo Yii::t('messages', 'Upload Image'); ?>
                                                                </span>
                                                    </label>
                                                    <div class="form-feild-info"> <?php echo Yii::t('messages', 'Image Formats - *.jpg, *.jpeg, *.png.
                                                        Minimum size 200px X 200px'); ?>
                                                    </div>

                                                    <div class="img-container mt-3">
                                                        <img id="uploadedImage" class="uploadedImage"
                                                             src="<?php echo $profImageUrl; ?>"
                                                             alt="<?php echo Yii::t('messages', 'Upload Your Image'); ?>">
                                                    </div>

                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-8 docs-buttons mb-3">
                                                    <!-- <h3>Toolbar:</h3> -->

                                                    <div class="btn-group">
                                                        <a href="javascript:void(0);" type="button"
                                                           class="btn btn-primary" data-method="zoom"
                                                           data-toggle="tooltip" data-option="0.1"
                                                           title="<?php echo Yii::t('messages', 'Zoom In'); ?>">
                                                                    <span class="docs-tooltip" data-animation="false">
                                                                      <span class="fa fa-search-plus"></span>
                                                                    </span>
                                                        </a>
                                                    </div>

                                                    <div class="btn-group">
                                                        <a href="javascript:void(0);" type="button"
                                                           class="btn btn-primary" data-method="zoom"
                                                           data-toggle="tooltip" data-option="-0.1"
                                                           title=<?php echo Yii::t('messages', "Zoom Out"); ?>>
                                                                    <span class="docs-tooltip" data-animation="false">
                                                                      <span class="fa fa-search-minus"></span>
                                                                    </span>
                                                        </a>
                                                    </div>


                                                    <div class="btn-group">
                                                        <a href="javascript:void(0);" type="button"
                                                           class="btn btn-primary"
                                                           data-method="rotate" data-toggle="tooltip"
                                                           data-option="-45"
                                                           title=<?php echo Yii::t('messages', "Rotate Left"); ?>>
                                                                    <span class="docs-tooltip" data-animation="false">
                                                                      <span class="fa fa-rotate-left"></span>
                                                                    </span>
                                                        </a>
                                                    </div>

                                                    <div class="btn-group">
                                                        <a href="javascript:void(0);" type="button"
                                                           class="btn btn-primary"
                                                           data-method="rotate" data-toggle="tooltip"
                                                           data-option="45"
                                                           title=<?php echo Yii::t('messages', "Rotate Right"); ?>>
                                                                    <span class="docs-tooltip" data-animation="false">
                                                                      <span class="fa fa-rotate-right"></span>
                                                                    </span>
                                                        </a>
                                                    </div>

                                                    <div class="btn-group">
                                                        <a href="javascript:void(0);" type="button"
                                                           class="btn btn-primary"
                                                           data-method="scaleX" data-toggle="tooltip"
                                                           data-option="-1"
                                                           title=<?php echo Yii::t('messages', "Flip Horizontal"); ?>>
                                                                    <span class="docs-tooltip" data-animation="false">
                                                                      <span class="fa fa-arrows-h"></span>
                                                                    </span>
                                                        </a>
                                                    </div>

                                                    <div class="btn-group">
                                                        <a href="javascript:void(0);" type="button"
                                                           class="btn btn-primary"
                                                           data-method="scaleY" data-toggle="tooltip"
                                                           data-option="-1"
                                                           title=<?php echo Yii::t('messages', "Flip Vertical"); ?>>
                                                                    <span class="docs-tooltip" data-animation="false">
                                                                      <span class="fa fa-arrows-v"></span>
                                                                    </span>
                                                        </a>
                                                    </div>


                                                    <!-- Show the cropped image in modal -->
                                                    <div class="modal fade docs-cropped" id="getCroppedCanvasModal"
                                                         aria-hidden="true" aria-labelledby="getCroppedCanvasTitle"
                                                         role="dialog" tabindex="-1">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="getCroppedCanvasTitle">
                                                                        <?php echo Yii::t('messages', 'Cropped'); ?></h5>
                                                                </div>
                                                                <div class="modal-body"></div>
                                                                <div class="modal-footer">
                                                                    <a href="javascript:void(0);" type="button"
                                                                       class="btn btn-secondary"
                                                                       id="btnCancel"> <?php echo Yii::t('messages', 'Cancel'); ?>
                                                                    </a>
                                                                    <a href="javascript:void(0);" type="button"
                                                                       class="btn btn-primary"
                                                                       id="btnSave"> <?php echo Yii::t('messages', 'Save'); ?>
                                                                    </a>
                                                                    <a class="btn btn-primary" id="download"
                                                                       href="javascript:void(0);"
                                                                       download="cropped.jpg"> <?php echo Yii::t('messages', 'Download'); ?> </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div><!-- /.modal -->

                                                </div><!-- /.docs-buttons -->

                                                <div class="col-md-4 text-left text-md-right docs-buttons mb-4">
                                                    <div class="btn-group">
                                                        <a href="javascript:void(0);" class="btn btn-primary"
                                                           data-method="getCroppedCanvas" data-toggle="tooltip"
                                                           title=<?php echo Yii::t('messages', "Crop"); ?>>
                                                                    <span class="docs-tooltip" data-animation="false">
                                                                      <span class="fa fa-check"></span>
                                                                    </span>
                                                        </a>
                                                    </div>

                                                    <div class="btn-group">
                                                        <a href="javascript:void(0);" type="button"
                                                           class="btn btn-primary"
                                                           data-method="reset" data-toggle="tooltip"
                                                           title=<?php echo Yii::t('messages', "Reset"); ?>>
                                                                    <span class="docs-tooltip" data-animation="false">
                                                                      <span class="fa fa-refresh"></span>
                                                                    </span>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php // Yii::$app->toolKit->getImageCropperScripts();
$imageSaveUrl = Yii::$app->urlManager->createUrl('/candidate-info/image-save');
$this->registerJs('
$(document).ready(function () {
        $(\'#dob1\').datetimepicker({
            format: \'YYYY-MM-DD\'
        });

    });

    $(function () {

        \'use strict\';

        var console = window.console || {
            log: function () {
            }
        };
        var URL = window.URL || window.webkitURL;
        var $image = $(\'#uploadedImage\');
        var $download = $(\'#download\');
        var $dataX = $(\'#dataX\');
        var $dataY = $(\'#dataY\');
        var $dataHeight = $(\'#dataHeight\');
        var $dataWidth = $(\'#dataWidth\');
        var $dataRotate = $(\'#dataRotate\');
        var $dataScaleX = $(\'#dataScaleX\');
        var $dataScaleY = $(\'#dataScaleY\');
        var options = {
            aspectRatio: 1 / 1,
            preview: \'.img-preview\',
            crop: function (e) {
                $dataX.val(Math.round(e.detail.x));
                $dataY.val(Math.round(e.detail.y));
                $dataHeight.val(Math.round(e.detail.height));
                $dataWidth.val(Math.round(e.detail.width));
                $dataRotate.val(e.detail.rotate);
                $dataScaleX.val(e.detail.scaleX);
                $dataScaleY.val(e.detail.scaleY);
            }
        };
        var originalImageURL = $image.attr(\'src\');
        var uploadedImageName = \'cropped.jpg\';
        var uploadedImageType = \'image/jpeg\';
        var uploadedImageURL;


        // Tooltip
        $(\'[data-toggle="tooltip"]\').tooltip();


        // Cropper
        $image.on({
            ready: function (e) {
                console.log(e.type);
            },
            cropstart: function (e) {
                console.log(e.type, e.detail.action);
            },
            cropmove: function (e) {
                console.log(e.type, e.detail.action);
            },
            cropend: function (e) {
                console.log(e.type, e.detail.action);
            },
            crop: function (e) {
                console.log(e.type);
            },
            zoom: function (e) {
                console.log(e.type, e.detail.ratio);
            }
        }).cropper(options);


        // Buttons
        if (!$.isFunction(document.createElement(\'canvas\').getContext)) {
            $(\'button[data-method="getCroppedCanvas"]\').prop(\'disabled\', true);
        }

        if (typeof document.createElement(\'cropper\').style.transition === \'undefined\') {
            $(\'button[data-method="rotate"]\').prop(\'disabled\', true);
            $(\'button[data-method="scale"]\').prop(\'disabled\', true);
        }


        // Download
        if (typeof $download[0].download === \'undefined\') {
            $download.addClass(\'disabled\');
        }


        // Options
        $(\'.docs-toggles\').on(\'change\', \'input\', function () {
            var $this = $(this);
            var name = $this.attr(\'name\');
            var type = $this.prop(\'type\');
            var cropBoxData;
            var canvasData;

            if (!$image.data(\'cropper\')) {
                return;
            }

            if (type === \'checkbox\') {
                options[name] = $this.prop(\'checked\');
                cropBoxData = $image.cropper(\'getCropBoxData\');
                canvasData = $image.cropper(\'getCanvasData\');

                options.ready = function () {
                    $image.cropper(\'setCropBoxData\', cropBoxData);
                    $image.cropper(\'setCanvasData\', canvasData);
                };
            } else if (type === \'radio\') {
                options[name] = $this.val();
            }

            $image.cropper(\'destroy\').cropper(options);
        });


        // Methods
        $(\'.docs-buttons\').on(\'click\', \'[data-method]\', function () {
            var $this = $(this);
            var data = $this.data();
            var cropper = $image.data(\'cropper\');
            var cropped;
            var $target;
            var result;

            if ($this.prop(\'disabled\') || $this.hasClass(\'disabled\')) {
                return;
            }

            if (cropper && data.method) {
                data = $.extend({}, data); // Clone a new one

                if (typeof data.target !== \'undefined\') {
                    $target = $(data.target);

                    if (typeof data.option === \'undefined\') {
                        try {
                            data.option = JSON.parse($target.val());
                        } catch (e) {
                            console.log(e.message);
                        }
                    }
                }

                cropped = cropper.cropped;

                switch (data.method) {
                    case \'rotate\':
                        if (cropped && options.viewMode > 0) {
                            $image.cropper(\'clear\');
                        }

                        break;

                    case \'getCroppedCanvas\':
                        if (uploadedImageType === \'image/jpeg\') {
                            if (!data.option) {
                                data.option = {};
                            }

                            data.option.fillColor = \'#fff\';
                        }

                        break;
                }

                result = $image.cropper(data.method, data.option, data.secondOption);

                switch (data.method) {
                    case \'rotate\':
                        if (cropped && options.viewMode > 0) {
                            $image.cropper(\'crop\');
                        }

                        break;

                    case \'scaleX\':
                    case \'scaleY\':
                        $(this).data(\'option\', -data.option);
                        break;

                    case \'getCroppedCanvas\':
                        if (result) {
                            if ($(\'#getCroppedCanvasModal\').is(":hidden")) {
                                $(\'#getCroppedCanvasModal\').show(10);
                            }
                            // Bootstrap\'s Modal
                            $(\'#getCroppedCanvasModal\').modal().find(\'.modal-body\').html(result);

                            $(\'#btnCancel\').on(\'click\', function (e) {
                                $(\'#getCroppedCanvasModal\').hide(10);
                                $(".modal-backdrop:eq(1)").remove();
                            });

                            $(\'#btnSave\').on(\'click\', function (e) {
                                var dataURL = result.toDataURL();
                                //$download.attr(\'href\', result.toDataURL(uploadedImageType));
                                $.ajax({
                                    type: "POST",
                                    url: "/candidate-info/image-save",
                                    data: {
                                        imgName: uploadedImageName,
                                        imgBase64: dataURL
                                    }
                                }).done(function (result) {
                                    if (result) {
                                        console.log(\'saved\');
                                        console.log(result);
                                        $(\'#getCroppedCanvasModal\').hide(10);
                                        location.reload();
                                    } else {
                                    }
                                });
                            });

                            if (!$download.hasClass(\'disabled\')) {
                                download.download = uploadedImageName;
                                $download.attr(\'href\', result.toDataURL(uploadedImageType));
                            }
                        }

                        break;

                    case \'destroy\':
                        if (uploadedImageURL) {
                            URL.revokeObjectURL(uploadedImageURL);
                            uploadedImageURL = \'\';
                            $image.attr(\'src\', originalImageURL);
                        }

                        break;
                }

                if ($.isPlainObject(result) && $target) {
                    try {
                        $target.val(JSON.stringify(result));
                    } catch (e) {
                        console.log(e.message);
                    }
                }

            }
        });

        // Keyboard
        $(document.body).on(\'keydown\', function (e) {

            if (!$image.data(\'cropper\') || this.scrollTop > 300) {
                return;
            }

            switch (e.which) {
                case 37:
                    e.preventDefault();
                    $image.cropper(\'move\', -1, 0);
                    break;

                case 38:
                    e.preventDefault();
                    $image.cropper(\'move\', 0, -1);
                    break;

                case 39:
                    e.preventDefault();
                    $image.cropper(\'move\', 1, 0);
                    break;

                case 40:
                    e.preventDefault();
                    $image.cropper(\'move\', 0, 1);
                    break;
            }

        });


        // Import image
        var $inputImage = $(\'#inputImage\');

        if (URL) {
            $inputImage.change(function () {
                console.log("Image change");
                var imgWidth = 0;
                var imgHeight = 0;
                var minWidth = 200;
                var minHeight = 200;
                var files = this.files;
                var file;
                // var img = new Image();
                if (!$image.data(\'cropper\')) {
                    return;
                }

                if (files && files.length) {
                    file = files[0];

                    if (/^image\/\w+$/.test(file.type)) {
                        uploadedImageName = file.name;
                        uploadedImageType = file.type;

                        if (uploadedImageURL) {
                            URL.revokeObjectURL(uploadedImageURL);
                        }

                        uploadedImageURL = URL.createObjectURL(file);
                        $image.cropper(\'destroy\').attr(\'src\', uploadedImageURL).cropper(options);
                        $inputImage.val(\'\');
                    } else {
                        window.alert(\'Please choose an image file.\');
                    }
                }
            });
        } else {
            $inputImage.prop(\'disabled\', true).parent().addClass(\'disabled\');
        }
    });
');
?>

<script>

</script>
