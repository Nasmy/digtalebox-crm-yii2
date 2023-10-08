<?php

use app\assets\ImageAsset;
use app\models\CandidateInfo;
use yii\bootstrap\Tabs;
use yii\helpers\Html;
use yii\web\UrlManager;
use yii\web\JqueryAsset;
use yii\widgets\ListView;

JqueryAsset::register($this);
ImageAsset::register($this);

$this->title = Yii::t('messages', 'Images');
$this->titleDescription = Yii::t('messages', 'Change images of the Portal');

$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'System'), 'url' => ['#']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Portal Settings'), 'url' => ['#']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Images')];

?>
<?php echo Yii::$app->controller->renderPartial('_tabMenu'); ?>

<div class="content-inner">
    <div class="content-area">

        <div class="form-row text-left">
            <div class="form-group col-md-12">

                <?php


                if (Yii::$app->user->checkAccess('CandidateInfo.UploadImage')) {
                    echo Html::a(
                        Yii::t('messages', 'Add New Image'),
                        '#',
                        //yii::$app->urlManager->createUrl('candidate-info/upload-image'),
                        [
                            'class' => "btn btn-primary addNewBtn",
                            'pjax-container' => 'pjax-list',
                            'title' => Yii::t('app', 'Add New Image'),
                            'id' => 'addNewBtn', 'data-toggle' => "modal",
                            'data-target' => "#addNew",
                            'class' => "btn btn-primary addNewBtn",
                            'data-backdrop' => "static"
                        ]
                    );

                }
                ?>
            </div>
        </div>

        <div class="content-panel-sub">
            <div class="panel-head">
                <?php echo Yii::t('messages', 'All Images'); ?>
            </div>
        </div>

        <?php
        echo ListView::widget([
            'dataProvider' => $dataProvider,
            'itemView' => '_imageView',
            'options' => ['class' => 'row', 'style' => '100%'],
            'summary' => '<div class="text-right results-count mt-4">' . Yii::t('messages', 'Displaying {begin}-{end} of {count} imports') . '</div>',
            'itemOptions' => ['tag' => null],
            'pager' => [
                'firstPageLabel' => '',
                'firstPageCssClass' => 'first',
                'activePageCssClass' => 'selected',
                'disabledPageCssClass' => 'hidden',
                'lastPageLabel' => 'last ',
                'nextPageLabel' => '<span aria-hidden="true">&raquo;</span>',
                'nextPageCssClass' => 'page-item next',
                'maxButtonCount' => 5,
                'pageCssClass' => 'page-item',
                'prevPageCssClass' => 'page-item previous',    // Set CSS class for the "previous" page button
                'options' => ['class' => 'pagination justify-content-md-end'],
            ],
            'layout' => '<div class="col-md-12 text-right results-count mb-3">{summary}</div>
                        {items}
                        <div class="col-md-12 row no-gutters d-flex flex-sm-row-reverse flex-sm-column-reverse flex-md-row"> 
                        <div class="col-md-6"></div>
                        <div class="col-md-6">
                            <div class="float-right"><nav aria-label="Page navigation">{pager}</nav></div>
                        </div></div>'

        ]);
        ?>
    </div>
</div>
</div> <!-- start at column1.php -->
</div><!-- start at column1.php -->
</div><!-- start at column1.php -->

<!-- View Modal -->
<div class="modal fade" id="viewDetails" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"
                    id="exampleModalLongTitle">   <?php echo Yii::t('messages', 'Image View'); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="text-center" id="viewTheme">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addNew" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle"> <?php echo Yii::t('messages', 'Add Image'); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <!-- <h3>Demo:</h3> -->
                        <label class="btn btn-primary btn-upload" for="inputImage">
                            <input type="file" class="sr-only" id="inputImage" name="file" accept=".jpg,.jpeg,.png">
                            <span class="docs-tooltip" data-animation="false">
                                                <i class="fa fa-upload"></i> <?php echo Yii::t('messages', 'Upload Image'); ?>
                                            </span>
                        </label>
                        <div class="form-feild-info"> <?php echo Yii::t('messages', 'Image Formats - *.jpg, *.jpeg, *.png. Minimum size 800px X 524px; Maximum size 1500px X 982px'); ?>
                        </div>

                        <div class="img-container mt-3" id="uploadImage">
                            <img src="/themes/bootstrap_spacelab/img/login-slide-1.jpg" alt="Upload Your Image"
                                 id="uploadedImage" class="uploadedImage">
                        </div>

                    </div>
                </div>
                <div class="row">
                    <!--                    <div id="imageDimension"></div>-->
                    <div id="imageDimensionError"></div>
                </div>
                <div class="row">
                    <div class="col-md-8 docs-buttons mb-3">
                        <!-- <h3>Toolbar:</h3> -->

                        <div class="btn-group">
                            <button type="button" class="btn btn-primary" data-method="zoom" data-toggle="tooltip"
                                    data-option="0.1" title=<?php echo Yii::t('messages', "Zoom In"); ?>>
                                        <span class="docs-tooltip" data-animation="false">
                                          <span class="fa fa-search-plus"></span>
                                        </span>
                            </button>
                        </div>

                        <div class="btn-group">
                            <button type="button" class="btn btn-primary" data-method="zoom" data-toggle="tooltip"
                                    data-option="-0.1" title=<?php echo Yii::t('messages', "Zoom Out"); ?>>
                                        <span class="docs-tooltip" data-animation="false">
                                          <span class="fa fa-search-minus"></span>
                                        </span>
                            </button>
                        </div>


                        <div class="btn-group">
                            <button type="button" class="btn btn-primary" data-method="rotate" data-toggle="tooltip"
                                    data-option="-45" title=<?php echo Yii::t('messages', "Rotate Left"); ?>>
                                        <span class="docs-tooltip" data-animation="false">
                                          <span class="fa fa-rotate-left"></span>
                                        </span>
                            </button>
                        </div>

                        <div class="btn-group">
                            <button type="button" class="btn btn-primary" data-method="rotate" data-toggle="tooltip"
                                    data-option="45" title=<?php echo Yii::t('messages', "Rotate Right"); ?>>
                                        <span class="docs-tooltip" data-animation="false">
                                          <span class="fa fa-rotate-right"></span>
                                        </span>
                            </button>
                        </div>

                        <div class="btn-group">
                            <button type="button" class="btn btn-primary" data-method="scaleX" data-toggle="tooltip"
                                    data-option="-1" title=<?php echo Yii::t('messages', "Flip Horizontal"); ?>>
                                        <span class="docs-tooltip" data-animation="false">
                                          <span class="fa fa-arrows-h"></span>
                                        </span>
                            </button>
                        </div>

                        <div class="btn-group">
                            <button type="button" class="btn btn-primary" data-method="scaleY" data-toggle="tooltip"
                                    data-option="-1" title=<?php echo Yii::t('messages', "Flip Vertical"); ?>>
                                        <span class="docs-tooltip" data-animation="false">
                                          <span class="fa fa-arrows-v"></span>
                                        </span>
                            </button>
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
                                    <div id="alertMsg"></div>
                                    <div class="modal-body"></div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                                id="btnCancel"> <?php echo Yii::t('messages', 'Cancel'); ?>
                                        </button>
                                        <button type="button" href="javascript:void(0);" class="btn btn-primary"
                                                id="btnSave"> <?php echo Yii::t('messages', 'Save'); ?>
                                        </button>
                                        <div id="btnLoading" class="btn btn-primary d-none">
                                            <i class="fa fa-spinner fa-spin fa-2x fa-fw"></i>
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                        <a class="btn btn-primary" id="download"
                                           href="javascript:void(0);"
                                           download="cropped.jpg"> <?php echo Yii::t('messages', 'Download'); ?> </a>
                                    </div>
                                </div>
                            </div>
                        </div><!-- /.modal -->

                    </div><!-- /.docs-buttons -->

                    <div class="docs-buttons col-md-4 text-left text-md-right mb-4">
                        <div class="btn-group">
                            <button type="button" href="javascript:void(0);" class="btn btn-primary"
                                    data-method="getCroppedCanvas" data-toggle="tooltip"
                                    title=<?php echo Yii::t('messages', "Crop"); ?>>
                                <span class="docs-tooltip" data-animation="false">
                                    <span class="fa fa-check"></span> </span>
                            </button>
                        </div>

                        <div class="btn-group">
                            <button type="button" href="javascript:void(0);" class="btn btn-primary" data-method="reset"
                                    data-toggle="tooltip"
                                    title="Reset">
                                        <span class="docs-tooltip" data-animation="false">
                                          <span class="fa fa-refresh"></span>
                                        </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropper/2.3.4/cropper.min.js"></script>
<?php $this->registerJsFile('@web/themes/bootstrap_spacelab/js/jquery-cropper.js', ['depends' => 'yii\web\JqueryAsset']); ?>

<script>
    $.fn.cropper.noConflict();

    $(document).ready(function () {
        $('#addNewBtn').click(function () {
            $('#uploadImage').html('<img src="/themes/bootstrap_spacelab/img/login-slide-1.jpg" alt="Upload Your Image" id="uploadedImage" class="uploadedImage">');
            /*---- Add Image ----*/
            $(function () {

                'use strict';

                var console = window.console || {
                    log: function () {
                    }
                };
                var URL = window.URL || window.webkitURL;
                var $image = $('#uploadedImage');
                var $download = $('#download');
                var $dataX = $('#dataX');
                var $dataY = $('#dataY');
                var $dataHeight = $('#dataHeight');
                var $dataWidth = $('#dataWidth');
                var $dataRotate = $('#dataRotate');
                var $dataScaleX = $('#dataScaleX');
                var $dataScaleY = $('#dataScaleY');
                var imageWidth;
                var imageHeight;
                var options = {
                    aspectRatio: 16 / 10.47,
                    preview: '.img-preview',
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
                var originalImageURL = $image.attr('src');
                var uploadedImageName = 'cropped.jpg';
                var uploadedImageType = 'image/jpeg';
                var uploadedImageURL;

                // Tooltip
                $('[data-toggle="tooltip"]').tooltip();

                // Cropper
                $image.on({
                    ready: function (e) {
                        console.log(e.type);
                    },
                    cropstart: function (e) {
                        console.log(e.type);
                    },
                    cropmove: function (e) {
                        console.log(e.type);
                    },
                    cropend: function (e) {
                        console.log(e.type);
                    },
                    crop: function (e) {
                        console.log(e.type);
                        var $cropper = $(e.target);
                        var data = $cropper.cropper('getData');
                        imageHeight = Math.floor(data.height);
                        imageWidth = Math.floor(data.width);
                        // $('#imageDimension').html('Image Width: '+imageWidth+' Image Height: '+imageHeight);
                        if (imageWidth >= <?php echo CandidateInfo::MIN_IMG_WIDTH; ?>  && imageWidth <= <?php echo CandidateInfo::MAX_IMG_WIDTH; ?>) {
                            $('#imageDimensionError').html('<div class="alert alert-success"> <?php Yii::t("messages", "Selected image ratio is valid."); ?>' + imageWidth + 'px X ' + imageHeight + 'px</div>');
                        } else {
                            $('#imageDimensionError').html('<div class="alert alert-error"> <?php Yii::t("messages", "Selected image ratio is valid."); ?>' + imageWidth + 'px X ' + imageHeight + 'px</div>');
                        }
                    },
                    zoom: function (e) {
                        console.log(e.type);
                    }
                }).cropper(options);


                // Buttons
                if (!$.isFunction(document.createElement('canvas').getContext)) {

                    $('button[data-method="getCroppedCanvas"]').prop('disabled', true);
                }

                if (typeof document.createElement('cropper').style.transition === 'undefined') {
                    $('button[data-method="rotate"]').prop('disabled', true);
                    $('button[data-method="scale"]').prop('disabled', true);
                }


                // Download
                if (typeof $download[0].download === 'undefined') {
                    $download.addClass('disabled');
                }

                // Methods
                $('.docs-buttons').on('click', '[data-method]', function () {
                    var $this = $(this);
                    var data = $this.data();
                    var cropper = $image.data('cropper');
                    var cropped;
                    var $target;
                    var result;

                    if ($this.prop('disabled') || $this.hasClass('disabled')) {
                        return;
                    }

                    if (cropper && data.method) {
                        data = $.extend({}, data); // Clone a new one

                        if (typeof data.target !== 'undefined') {
                            $target = $(data.target);

                            if (typeof data.option === 'undefined') {
                                try {
                                    data.option = JSON.parse($target.val());
                                } catch (e) {
                                    console.log(e.message);
                                }
                            }
                        }

                        cropped = cropper.cropped;

                        switch (data.method) {
                            case 'rotate':
                                if (cropped && options.viewMode > 0) {
                                    $image.cropper('clear');
                                }

                                break;

                            case 'getCroppedCanvas':
                                if (uploadedImageType === 'image/jpeg') {
                                    if (!data.option) {
                                        data.option = {};
                                    }

                                    data.option.fillColor = '#fff';
                                }

                                break;
                        }

                        result = $image.cropper(data.method, data.option, data.secondOption);

                        switch (data.method) {
                            case 'rotate':
                                if (cropped && options.viewMode > 0) {
                                    $image.cropper('crop');
                                }

                                break;

                            case 'scaleX':
                            case 'scaleY':
                                $(this).data('option', -data.option);
                                break;

                            case 'getCroppedCanvas':
                                $('#alertMsg').html('');
                                if (result) {
                                    if ($('#getCroppedCanvasModal').is(":hidden")) {
                                        $('#getCroppedCanvasModal').show(10);

                                    }
                                    jQuery('#getCroppedCanvasModal').addClass('show');
                                    // Bootstrap's Modal
                                    $('#getCroppedCanvasModal').modal().find('.modal-body').html(result);

                                    $('#btnCancel').on('click', function (e) {
                                        $('#getCroppedCanvasModal').hide(10);
                                        $(".modal-backdrop").remove();
                                    });

                                    $('#btnSave').on('click', function (e) {
                                        e.preventDefault();
                                        var dataURL = result.toDataURL();
                                        //$download.attr('href', result.toDataURL(uploadedImageType));
                                        if (imageWidth >= <?php echo CandidateInfo::MIN_IMG_WIDTH; ?>  && imageWidth <= <?php echo CandidateInfo::MAX_IMG_WIDTH; ?>) {

                                            jQuery("#btnLoading").removeClass('d-none');
                                            jQuery("#btnSave").addClass('d-none');

                                            $.ajax({
                                                type: "POST",
                                                url: "<?php echo Yii::$app->urlManager->createUrl('candidate-info/upload-image'); ?>",
                                                data: {
                                                    imgName: uploadedImageName,
                                                    imgBase64: dataURL
                                                }
                                            }).done(function (result) {
                                                if (result) {
                                                    console.log('saved');
                                                    console.log(result);
                                                    $('#getCroppedCanvasModal').hide(10);
                                                    location.reload();
                                                }
                                            });
                                        } else {

                                            $('#alertMsg').html('<div class="alert alert-error"><?php echo Yii::t('messages', 'Image Size is not in the required range. Minimum size {minWidth}px X {minHeight}px; Maximum size {maxWidth}px X {maxHeight}px', array('{minWidth}' => CandidateInfo::MIN_IMG_WIDTH, '{minHeight}' => CandidateInfo::MIN_IMG_HEIGHT, '{maxWidth}' => CandidateInfo::MAX_IMG_WIDTH, '{maxHeight}' => CandidateInfo::MAX_IMG_HEIGHT)); ?></div>');
                                        }

                                    });

                                    if (!$download.hasClass('disabled')) {
                                        download.download = uploadedImageName;
                                        $download.attr('href', result.toDataURL(uploadedImageType));
                                    }
                                }

                                break;

                            case 'destroy':
                                if (uploadedImageURL) {
                                    URL.revokeObjectURL(uploadedImageURL);
                                    uploadedImageURL = '';
                                    $image.attr('src', originalImageURL);
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
                $(document.body).on('keydown', function (e) {

                    if (!$image.data('cropper') || this.scrollTop > 300) {
                        return;
                    }

                    switch (e.which) {
                        case 37:
                            e.preventDefault();
                            $image.cropper('move', -1, 0);
                            break;

                        case 38:
                            e.preventDefault();
                            $image.cropper('move', 0, -1);
                            break;

                        case 39:
                            e.preventDefault();
                            $image.cropper('move', 1, 0);
                            break;

                        case 40:
                            e.preventDefault();
                            $image.cropper('move', 0, 1);
                            break;
                    }

                });


                // Import image
                var $inputImage = $('#inputImage');

                if (URL) {
                    $inputImage.change(function () {
                        var files = this.files;
                        var file;

                        if (!$image.data('cropper')) {
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
                                $image.cropper('destroy').attr('src', uploadedImageURL).cropper(options);
                                $inputImage.val('');
                            } else {
                                window.alert('Please choose an image file. ( *.jpg, *.jpeg, *.png )');
                            }
                        }
                    });
                } else {
                    $inputImage.prop('disabled', true).parent().addClass('disabled');
                }

            });
        });
    });

</script>
