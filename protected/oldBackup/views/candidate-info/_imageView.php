<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 5/9/2018
 * Time: 4:06 PM
 */

use app\models\CandidateInfo;

$Cmodel = CandidateInfo::find()->one();

$image = $Cmodel->getImageUrl($model);?>
    <div class="col-sm-6 col-md-4 col-xl-3 text-center mb-3">
        <div href="" class="resource">
            <a class="portal-setting-images" data-toggle="modal" data-target="#viewDetails"
               href=""  onclick="changeView('<?php echo $image ?>')">
                <?php echo $Cmodel->getImageLink($model); ?>
            </a>
            <div class="actions mt-2">
                <a onclick="updateImage('<?php echo $model['id']; ?>','<?php echo $image ?>')" href="#" data-toggle="modal" data-target="#addNew" class="mr-2"
                   title="Change Image" data-backdrop="static"><i class="fa fa-edit fa-lg"></i></a>
                <a onclick="deleteConfirmation('<?php echo Yii::$app->urlManager->createUrl("candidate-info/delete-image?id=".$model['id']."", array("id"=>$model['id']))?>')" href="#" title="Delete"><i class="fa fa-trash fa-lg"></i></a>
            </div>
        </div>
    </div>

<script>
    function deleteConfirmation(url) {
        if (confirm('<?php echo Yii::t('messages',"Are you Sure You want to Delete?");?>')) {
            window.location.href = url;
        } else {
        }

    }
    function changeView(srcUrl) {
        $('#viewTheme').html("<img class=\"img-thumbnail object-fit_cover\" style='height: auto !important;' src='"+srcUrl+"'>");
    }
    function updateImage(imgId,imgHtml){
        $('#uploadImage').html('<img src="' + imgHtml + '" alt="Upload Your Image" id="uploadedImage" class="uploadedImage">');
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
            var uploadedImageName = imgId+'.jpg';
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
                        $('#imageDimensionError').html('<div class="alert alert-success"> <?php Yii::t("messages","Selected image ratio is valid."); ?>' + imageWidth + 'px X ' + imageHeight + 'px</div>');
                    } else {
                        $('#imageDimensionError').html('<div class="alert alert-error"> <?php Yii::t("messages","Selected image ratio is valid."); ?>' + imageWidth + 'px X ' + imageHeight + 'px</div>');
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
                                // Bootstrap's Modal
                                $('#getCroppedCanvasModal').modal().find('.modal-body').html(result);

                                $('#btnCancel').on('click', function (e) {
                                    $('#getCroppedCanvasModal').hide(10);
                                    $(".modal-backdrop:eq(1)").remove();
                                });

                                $('#btnSave').on('click', function (e) {
                                    e.preventDefault();
                                    var dataURL = result.toDataURL();
                                    //$download.attr('href', result.toDataURL(uploadedImageType));
                                    if (imageWidth >= <?php echo CandidateInfo::MIN_IMG_WIDTH; ?>  && imageWidth <= <?php echo CandidateInfo::MAX_IMG_WIDTH; ?>) {
                                        $.ajax({
                                            type: "POST",
                                            url: "<?php echo Yii::$app->urlManager->createUrl('candidate-info/update-image'); ?>",
                                            data: {
                                                imgName: uploadedImageName,
                                                imgBase64: dataURL,
                                                imgId:imgId
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
                                        $('#alertMsg').html('<div class="alert alert-error"><?php echo Yii::t('messages', 'Image Size is not in the required range. Minimum size {minWidth}px X {minHeight}px; Maximum size {maxWidth}px X {maxHeight}px',array('{minWidth}'=>CandidateInfo::MIN_IMG_WIDTH, '{minHeight}'=>CandidateInfo::MIN_IMG_HEIGHT, '{maxWidth}'=>CandidateInfo::MAX_IMG_WIDTH, '{maxHeight}'=>CandidateInfo::MAX_IMG_HEIGHT)); ?></div>');
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
    }
</script>