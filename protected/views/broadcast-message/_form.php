<?php

use \kartik\datetime\DateTimePickerAsset;
use \kartik\datetime\DateTimePicker;
use app\models\Configuration;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\BroadcastMessage */
/* @var $form yii\widgets\ActiveForm */

$delConfirm = Yii::t('messages', 'Are you sure you want to remove the image.');
$sucMsg = Yii::t('messages', 'Image removed');
$failMsg = Yii::t('messages', 'Image removal failed');
Yii::$app->toolKit->registerCharcterCountdownScript();
$csrf = Yii::$app->request->csrfToken;

$script = <<< JS
	 // Init post length
        var twPostLength= {$twPostLength};
    
        // $('input[type=file]').bootstrapFileInput();
        
        $('#BroadcastMessage_twImageFile').change( function() {
            if ($(this).val() != '' ){
                twPostLength = twPostLength - 27;
                updateCountDown(twPostLength,'#twPost','#twCountDown');
            } else {
                twPostLength = {$twPostLength};
                updateCountDown(twPostLength,'#twPost','#twCountDown');
            } 
        });

	/*updateCountDown({$fbPostLength},'#fbPost','#fbCountDown');
	$('#fbPost').on('keyup keypress blur change', function(e) {
		updateCountDown({$fbPostLength},'#fbPost','#fbCountDown',e);
	});
	
    updateCountDown({$fbPostLength},'#fbProfPost','#fbProfPostCountDown');
	$('#fbProfPost').on('keyup keypress blur change', function(e) {
		updateCountDown({$fbPostLength},'#fbProfPost','#fbProfPostCountDown',e);
	});*/
	
	updateCountDown({$lnPostLength},'#lnPost','#lnCountDown');
	$('#lnPost').on('keyup keypress blur change', function(e) {
		updateCountDown({$lnPostLength},'#lnPost','#lnCountDown',e);
	});

	
	
	updateCountDown({$lnPostLength},'#lnPagePost','#lnPageCountDown');
	$('#lnPagePost').on('keyup keypress blur change', function(e) {
		updateCountDown({$lnPostLength},'#lnPagePost','#lnPageCountDown',e);
	});
	
	
	updateCountDown(twPostLength,'#twPost','#twCountDown');
	$('#twPost').on('keyup keypress blur change', function(e) {
		updateCountDown(twPostLength,'#twPost','#twCountDown',e);
	});
	 
	
	$('#fbImage, #twImage, #lnImage, #lnPageImage, #fbProfImage').fancybox();
	
	$('#fbImageDel, #lnImageDel, #twImageDel, #lnPageImageDel, #fbProfImageDel').on('click', function() {
		id = $(this).attr('id');
		if (window.confirm('{$delConfirm}')) {
			$.ajax({
				type: 'POST',
				url: $(this).attr('data-href'),
				data:{
				    'id':$(this).attr('data-id'),
				    'type':$(this).attr('data-type'),
                    '_csrf' : "$csrf",
 
				},
				success: function(data){      
				    var js = JSON.parse(data);
				    console.log(js.msg);
					if (js.msg == 1) {
						setJsFlash('success', '{$sucMsg}');
						if (js.type == 'fbPage') {
							$('#divFb').hide();
						}else if (js.type == 'ln') {
						    console.log(23);
							$('#divLn').hide();
						}else if (js.type == 'tw') {
							$('#divTw').hide();
						}else if (js.type == 'lnPage') {
							$('#divLnPage').hide();
						}else if (js.type == 'fbProf') {
							$('#divFbProf').hide();
						}
					} else {
						setJsFlash('error', '{$failMsg}');
					}
				}
			});
		}
		return false;
	});
JS;

$this->registerJs($script);
$linkShortenAction = Yii::$app->urlManager->createUrl('broadcast-message/shorten-url-in-text');


$fbImgDelUrl = Yii::$app->urlManager->createUrl('broadcast-message/del-image', array('type' => 'fb', 'id' => $model->id));
$fbProfImgDelUrl = Yii::$app->urlManager->createUrl('broadcast-message/del-image', array('type' => 'fbProf', 'id' => $model->id));
$lnImgDelUrl = Yii::$app->urlManager->createUrl('broadcast-message/del-image', array('type' => 'ln', 'id' => $model->id));
$twImgDelUrl = Yii::$app->urlManager->createUrl('broadcast-message/del-image', array('type' => 'tw', 'id' => $model->id));
$lnPageImgDelUrl = Yii::$app->urlManager->createUrl('broadcast-message/del-image', array('type' => 'lnPage', 'id' => $model->id));
$fbImgUrl = Url::base(true) . '/' . Yii::$app->toolKit->resourcePathRelative . $model->fbImageName . '?rand' . mt_rand(10, 100);
$fbProfImgUrl = Url::base(true) . '/' . Yii::$app->toolKit->resourcePathRelative . $model->fbProfImageName . '?rand' . mt_rand(10, 100);
$twImgUrl = Url::base(true) . '/' . Yii::$app->toolKit->resourcePathRelative . $model->twImageName . '?rand' . mt_rand(10, 100);
$lnImgUrl = Url::base(true) . '/' . Yii::$app->toolKit->resourcePathRelative . $model->lnImageName . '?rand' . mt_rand(10, 100);
$lnPageImgUrl = Url::base(true) . '/' . Yii::$app->toolKit->resourcePathRelative . $model->lnPageImageName . '?rand' . mt_rand(10, 100);

$fbImghtml = Html::img($fbImgUrl, ['width' => 20, 'height' => 20, 'class' => 'thumbnail', 'style' => ' display: inline-block;']);

$fbProfImghtml = Html::img($fbProfImgUrl, array('width' => 20, 'height' => 20, 'class' => 'thumbnail', 'style' => ' display: inline-block;'));
$twImghtml = Html::img($twImgUrl, array('width' => 20, 'height' => 20, 'class' => 'thumbnail', 'style' => ' display: inline-block;'));
$lnImghtml = Html::img($lnImgUrl, array('width' => 20, 'height' => 20, 'class' => 'thumbnail', 'style' => ' display: inline-block;'));
$lnPageImghtml = Html::img($lnPageImgUrl, array('width' => 20, 'height' => 20, 'class' => 'thumbnail', 'style' => ' display: inline-block;'));


$fancy = <<< JS
 	$('#btnShortUrl').on('click', function() {		
		$.fancybox.open({
			padding : 10,
			href:$(this).attr('href'),
			type: 'iframe',
			width: '500px',
			height: '200px',
			transitionIn: 'elastic',
			transitionOut: 'elastic',
			autoSize: false
		});
		return false;
	});
	
	$('#btnShortUrlInTextTw').on('click', function() {
		var text = shortenTextUrl($('#twPost').val(),'tw');
		$('#twPost').val(text.trim());
		updateCountDown(twPostLength,'#twPost','#twCountDown');
	});
	
	$('#btnShortUrlInTextFb').on('click', function() {
		var text = shortenTextUrl($('#fbPost').val(),'fb');
		$('#fbPost').val(text.trim());
		updateCountDown({$fbPostLength},'#fbPost','#fbCountDown');
	});
	
	$('#btnShortUrlInTextFbProf').on('click', function() {
		var text = shortenTextUrl($('#fbProfPost').val(),'fbProfile');
		$('#fbProfPost').val(text.trim());
		updateCountDown({$fbPostLength},'#fbProfPost','#fbProfCountDown');
	});
	
	$('#btnShortUrlInTextLn').on('click', function() {
		var text = shortenTextUrl($('#lnPost').val(),'ln');
		$('#lnPost').val(text.trim());
		updateCountDown({$lnPostLength},'#lnPost','#lnCountDown');
	});
	
	$('#btnShortUrlInTextLnPage').on('click', function() {
		var text = shortenTextUrl($('#lnPagePost').val(),'lnPage');
		$('#lnPagePost').val(text.trim());
		updateCountDown({$lnPostLength},'#lnPagePost','#lnPageCountDown');
	});
	
	function shortenTextUrl(text,tp){
		if ('' != text) {
			return $.ajax({
				type: 'POST',
				url: '{$linkShortenAction}',
				data: 'text=' + text+ '&type='+ tp,
				async: false,
				success: function(data){

				}
			}).responseText;
		}
	}
JS;

$this->registerJs($fancy);

?>
<style>
    .help-block {
        font-size: 13px;
        /*padding-top: 49px;*/
    }
</style>
<div class="broadcast-message-form">

    <?php $form = ActiveForm::begin([
        'id' => 'broadcast-message-form',
        'options' => [
            'class' => 'form-vertical',
            'method' => 'post',
            'enableAjaxValidation' => false,
            'validateOnSubmit' => true,
            'enctype' => 'multipart/form-data'
        ]
    ]); ?>
    <div class="form-row">
        <div class="form-group col-md-12" style="margin-top: 15px">
            <?php
        /*
         hided due to not using Bitly and mailchimp

         echo Html::a(
                '<i class="fa fa-link"></i> ' . Yii::t('messages', 'Shorten Long Links via Bitly'),
                Yii::$app->urlManager->createUrl('broadcast-message/shorten-url'), [
                'id' => 'btnShortUrl',
                'class' => 'btn btn-primary '
            ]);
            echo " ";

            if (null == $modelBlyProfile) {
                echo Html::a(Yii::t('messages', 'Authenticate your Bitly account'), array('site/index'), array('class' => 'ml-2'));
            }*/
            ?>
        </div>
    </div>
    <div class="form-row">
        <div class="form-group col-md-6">
            <?php
            echo $form->field($model, 'publishDate')->textInput(array('readonly' => true, 'class' => 'form-control datetimepicker-input', 'data-target' => '#publish-date', 'id' => 'publish-date', 'data-toggle' => 'datetimepicker', 'placeholder' => $attributeLabels['publishDate'], 'maxlength' => 45));
            ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 col-xl-4">
            <div class="card mb-3">
                <div class="card-header"><i
                            class="fa fa-linkedin-square"></i> <?php echo Yii::t('messages', 'LinkedIn Post') ?> </div>
                <div class="card-body">
                    <?php echo $form->field($model, 'lnPost')->textarea(array('rows' => 6, 'cols' => 50, 'class' => 'form-control', 'id' => 'lnPost'))->label(false); ?>
                    <div class="row mt-2">
                        <div class="col-6">
                            [ <span id="lnCountDown"></span> ]
                        </div>
                        <div class="col-6 text-right">
                            <?php
                            echo Html::button('<i class="fa fa-link"></i> ', [
                                'id' => 'btnShortUrlInTextLn',
                                'class' => 'btn btn-secondary btn-sm align-left',
                                'title' => Yii::t('messages', 'Shorten links in the message')
                            ]);
                            ?>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-12">
                            <div class="input-group mb-3">
                                <div class="input-group-prepend d-none d-sm-block">
                                    <span class="input-group-text"><i class="fa fa-photo"></i></span>
                                </div>
                                <div class="custom-file">
                                    <?php echo $form->field($model, 'lnImageFile')->fileInput(array('class' => 'custom-file-input', 'id' => 'inputGroupFile03', 'title' => '')); ?>
                                    <label class="custom-file-label"
                                           for="inputGroupFile03"><?php echo Yii::t('messages', 'Choose image file') ?></label>
                                </div>
                            </div>
                            <div id="divLn" class="row">
                                <?php
                                if ('' != $model->lnImageName): ?>
                                    <?php echo Html::a($lnImghtml, $lnImgUrl, ['id' => 'lnImage']); ?>
                                    <?php echo Html::a('<i class="fa fa-minus-circle"></i>', '#', array('id' => 'lnImageDel', 'data-href' => $lnImgDelUrl, 'data-type' => 'ln', 'data-id' => $_GET['id'], 'title' => Yii::t('messages', 'Remove image'))); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-4">
            <div class="card mb-3">
                <div class="card-header"><i
                            class="fa fa-linkedin-square"></i> <?php echo Yii::t('messages', 'LinkedIn Page Post') ?>
                </div>
                <div class="card-body">
                    <?php echo $form->field($model, 'lnPagePost')->textarea(array('rows' => 6, 'cols' => 50, 'class' => 'form-control', 'id' => 'lnPagePost'))->label(false); ?>
                    <div class="row mt-2">
                        <div class="col-6">
                            [ <span id="lnPageCountDown"></span> ]
                        </div>
                        <div class="col-6 text-right">
                            <?php
                            echo Html::button('<i class="fa fa-link"></i> ', [
                                'id' => 'btnShortUrlInTextLn',
                                'class' => 'btn btn-secondary btn-sm align-left',
                                'title' => Yii::t('messages', 'Shorten links in the message')
                            ]);
                            ?>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-12">
                            <div class="input-group mb-3">
                                <div class="input-group-prepend d-none d-sm-block">
                                    <span class="input-group-text"><i class="fa fa-photo"></i></span>
                                </div>
                                <div class="custom-file">
                                    <?php echo $form->field($model, 'lnPageImageFile')->fileInput(array('class' => 'custom-file-input', 'id' => 'inputGroupFile04', 'title' => '')); ?>
                                    <label class="custom-file-label"
                                           for="inputGroupFile04"><?php echo Yii::t('messages', 'Choose image file') ?></label>
                                </div>
                            </div>
                            <div id="divLnPage" class="row">
                                <?php
                                if ('' != $model->lnPageImageName): ?>
                                    <?php echo Html::a($lnPageImghtml, $lnPageImgUrl, ['id' => 'lnPageImage']); ?>
                                    <?php echo Html::a('<i class="fa fa-minus-circle"></i>', '#', array('id' => 'lnPageImageDel', 'data-href' => $lnPageImgDelUrl, 'data-type' => 'lnPage', 'data-id' => $_GET['id'], 'title' => Yii::t('messages', 'Remove image'))); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-4">
            <div class="card mb-3">
                <div class="card-header"><i
                            class="fa fa-linkedin-square"></i> <?php echo Yii::t('messages', 'Twitter Post') ?> </div>
                <div class="card-body">
                    <?php echo $form->field($model, 'twPost')->textarea(array('rows' => 6, 'cols' => 50, 'class' => 'form-control', 'id' => 'twPost'))->label(false); ?>
                    <div class="row mt-2">
                        <div class="col-6">
                            [ <span id="twCountDown"></span> ]
                        </div>
                        <div class="col-6 text-right">
                            <?php
                            echo Html::button('<i class="fa fa-link"></i> ', [
                                'id' => 'btnShortUrlInTextLn',
                                'class' => 'btn btn-secondary btn-sm align-left',
                                'title' => Yii::t('messages', 'Shorten links in the message')
                            ]);
                            ?>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-12">
                            <div class="input-group mb-3">
                                <div class="input-group-prepend d-none d-sm-block">
                                    <span class="input-group-text"><i class="fa fa-photo"></i></span>
                                </div>
                                <div class="custom-file">
                                    <?php echo $form->field($model, 'twImageFile')->fileInput(array('class' => 'custom-file-input', 'id' => 'inputGroupFile05', 'title' => '')); ?>
                                    <label class="custom-file-label"
                                           for="inputGroupFile05"><?php echo Yii::t('messages', 'Choose image file') ?></label>
                                </div>
                            </div>
                            <div id="divTw" class="row">
                                <?php if ('' != $model->twImageName): ?>
                                    <?php echo Html::a($twImghtml, $twImgUrl, ['id' => 'twImage']); ?>
                                    <?php echo Html::a('<i class="fa fa-minus-circle"></i>', '#', array('id' => 'twImageDel', 'data-href' => $twImgDelUrl, 'data-type' => 'tw', 'data-id' => $_GET['id'], 'title' => Yii::t('messages', 'Remove image'))); ?>
                                <?php endif; ?>
                            </div>
                            <br/>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>

<div class="form-row text-left text-md-right">
    <div class="form-group col-md-12">
        <div class="form-actions">
            <?= Html::submitButton(Yii::t('messages', 'Save'), ['class' => 'btn btn-success']) ?>

            <?= Html::a(Yii::t('messages', 'Back'), Yii::$app->urlManager->createUrl('broadcast-message/admin'), ['class' => 'btn btn-secondary']) ?>

        </div>
    </div>
</div>
</div>


<?php ActiveForm::end();
$config = new Configuration();
$timeZone = $config->getTimeZone(); ?>

<script>
    $(document).ready(function () {
        var defaultStartTime = defaultEndTime = new Date().toLocaleString('en-US', {timeZone: '<?php echo $timeZone; ?>'});
        $('#publish-date').datetimepicker({
            /*debug:true*/
            format: 'YYYY-MM-DD HH:mm',
            defaultDate: defaultStartTime,
            ignoreReadonly: true
        });
    });
</script>

</div>
