<style>
    .mce-fullscreen {
        z-index: 1050;
    }

</style>
<?php

use app\components\ToolKit;
use app\models\DragDropMessageTemplate;
use app\models\MessageTemplate;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\MessageTemplate */
/* @var $form yii\widgets\ActiveForm */

Yii::$app->toolKit->registerTinyMceScripts();
Yii::$app->toolKit->registerCharcterCountdownScript();
Yii::$app->toolKit->registerFancyboxScripts();

$fbMsgLen = MessageTemplate::MSG_LEN_FB;
$twMsgLen = MessageTemplate::MSG_LEN_TW;
$lnMsgLen = MessageTemplate::MSG_LEN_LN;
$smsMsgLen = MessageTemplate::MSG_LEN_SMS;
$smsMsgLenSpecial = MessageTemplate::MSG_LEN_SMS_SPECIAL;
$specialChars = Yii::$app->params['smsSpecialChars'];
$specialWords = Yii::$app->params['smsSpecialWords'];

$this->registerJs("
var specialChars = '{$specialChars}';
var specialWords = '{$specialWords}';
var specialWordsExist = false;
var maxLengths = {$smsMsgLen}+specialWords.length; 

	/*updateCountDown(maxLengths,'#smsMessage','#smsCountDown');
	$('#smsMessage').on('keyup keypress blur change', function(e) {
	   	var contentSMS = $(this).val();
        if (checkSpecialChars($('#smsMessage').val())) {
            maxLengths = {$smsMsgLenSpecial}+specialWords.length; 
           	updateCountDown(maxLengths,'#smsMessage','#smsCountDown',e);
        } else if(contentSMS.includes(specialWords)){
            maxLengths = {$smsMsgLen}+specialWords.length;   
            updateCountDown(maxLengths,'#smsMessage','#smsCountDown',e); 
        }else {
            maxLengths = {$smsMsgLen}+specialWords.length; 
			updateCountDown({$smsMsgLen},'#smsMessage','#smsCountDown',e);
		}
		
	});*/
	
	function checkSpecialChars(string) { 

       for(i = 0; i < specialChars.length; i++) {
 			if(string.indexOf(specialChars[i]) > -1){
				return true;
			} 
		}
		return false;
	}
	
	function checkSpecialWord(string) { 

       for(i = 0; i < specialChars.length; i++) {
 			if(string.indexOf(specialChars[i]) > -1){
				return true;
			} 
		}
		return false;
	}
");

$btnUrl = Yii::$app->toolKit->getImageUrlPath() . '/templates-icon-16x16.png';

$title = Yii::t('messages', 'Change Template');

$tempUrl = Yii::$app->urlManager->createUrl('message-template/show-templates');

$btnNewTemplateUrl = Yii::$app->toolKit->getImageUrlPath() . '/create-new-template.png';

//---Drag & Drop Email Edit
$titleCreateTemplate = Yii::t('messages', 'Drag & Drop Email Edit');
$themeColor = DragDropMessageTemplate::getThemeColour();

$baseUrlParam = Url::base('https');

if (YII_ENV == 'dev') {
    $baseUrlParam = Url::base('http');
}

$baseUrl = $baseUrlParam;

if (strpos($baseUrl, "https://") !== false) {
    $baseUrl = Yii::$app->params['emailDomain']['url1'];
} else {
    $baseUrl = Yii::$app->params['emailDomain']['url2'];
}
$baseUrl = Yii::$app->params['emailDomain']['url1'];
$tempRandomCode = '';
$isDuplicate = false;
if(isset($templateOptions['isDuplicate'])) {
    $tempRandomCode = substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', mt_rand(1,
        10))), 1, 7);
    $isDuplicate = true;
}

if (!ToolKit::isEmpty($model->dragDropMessageCode)) {
    $random = $model->dragDropMessageCode;
    $action = 'edit';
    $accountUrl = $baseUrl . "/update.html?";
} else {
    $random = substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', mt_rand(1,
        10))), 1, 7);
    $action = 'add';
    $accountUrl = $baseUrl . "?";
}
$tempCreateUrl = $accountUrl . "appId=" . $baseUrlParam . "&key=" . $random . "&userId=" . Yii::$app->user->identity->getId() . "&lang=" . MessageTemplate::getMessageTempateLang(Yii::$app->language) . '&theme=' . $themeColor . "&action=" . $action . "&isDuplicate=" . $isDuplicate. "&dupCode=" . $tempRandomCode;
if(isset($templateOptions['isDuplicate'])) {
    $newContent = Yii::$app->urlManager->createUrl(['message-template/get-new-content', 'code' => $tempRandomCode, 'MsgId' => null, 'isDuplicate'=>$isDuplicate]);
} else {
    $newContent = Yii::$app->urlManager->createUrl(['message-template/get-new-content', 'code' => $random, 'MsgId' => $model->id]);
}


$lang = Yii::$app->toolKit->getComponenetSpecificLangIdentifier('tinyMce');

$this->registerJs("
tinymce.init({
		language : '{$lang}',
		selector:'textarea:not(.mceNoEditor)',
		theme:'modern',
		plugins: [
			'advlist autolink link image lists charmap print preview hr anchor pagebreak',
			'searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime nonbreaking',
			'save table contextmenu directionality emoticons template paste textcolor jbimages'
		],
		toolbar1: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
		toolbar2: 'print preview media | forecolor backcolor emoticons | templates | dragDropTemplates',
		setup: function(ed) {
			objEditor = ed;
			ed.addButton('templates', {
				title: '{$title}',
				image: '{$btnUrl}',
				onclick: function() {
					$.fancybox.open({
						padding : 10,
						href:'{$tempUrl}',
						type: 'iframe',
						width: '96%',
						height: '94%',
						transitionIn: 'elastic',
						transitionOut: 'elastic',
						autoSize: false,
					});
					return false;
				}
			});
				ed.addButton('dragDropTemplates', {
				title: '{$titleCreateTemplate}',
				image: '{$btnNewTemplateUrl}',
				onclick: function() {
					$.fancybox.open({
						padding : 10,
						href:'{$tempCreateUrl}',
						type: 'iframe',
						width: '96%',
						height: '94%',
						transitionIn: 'elastic',
						transitionOut: 'elastic',
						autoSize: false,
						afterClose : function(){
						$.ajax({
						type: 'GET',
						url: '{$newContent}',
						 dataType: 'json',
						success: function(data){
						    if (data.content != '0') {		
						    if('{$isDuplicate}') {
						    $('#MessageTemplate_dragDropMessageCode').val('{$tempRandomCode}');
						    } else {
						     $('#MessageTemplate_dragDropMessageCode').val('{$random}');
						    }		
						   
						    window.parent.objEditor.setContent(data.content, {format : 'raw'});
						    if ('<center><\/center>' == window.parent.objEditor.getContent()){
						    window.parent.objEditor.setContent('', {format : 'raw'});
						    }
						    }
						}
					});
				},
				helpers   : { 
				overlay : {closeClick: false} // prevents closing when clicking OUTSIDE fancybox 
				}
			});
			return false;
				}
			});
		},
		relative_urls : false,
		remove_script_host : false,
		convert_urls : true,
		//valid_children : '+body[style],style[type],head,html',
		verify_html : false,
		cleanup: false,
		valid_elements : '*[*]',
	});
");
$curType = $model->type;
$typeMass = MessageTemplate::MASS_TEMPLATE;
$typeSingle = MessageTemplate::SINGLE_TEMPLATE;
$this->registerJs("
$('#type').on('change',function(){
		showKeywords($('#type').val());
	});
	
	function showKeywords(type) {
		if (type == '{$typeMass}') {
			$('#single_keywords').hide();
			$('#mass_keywords').show();
			$('#custom_keywords').show();
		} else {
			$('#single_keywords').show();
			$('#mass_keywords').hide();
			$('#custom_keywords').hide();
		}
	}
	showKeywords('{$curType}');
");

$smsNote = Yii::t('messages', 'SMS are limited to 160 characters, if you use accents such as "à" "é" "ï" "ô" "ù" SMS will be limited to 70 characters');
?>

<div>
    <div class="row no-gutters">
        <div class="content-panel col-md-12">
            <div class="content-inner">
                <div class="content-area">
                    <div class="row">
                        <div class="col-md-12">
                            <?php $form = ActiveForm::begin([
                                'id' => 'email-template-form'
                            ]); ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="FormMembershipType_type"><?php echo $attributeLabels['type']; ?></label>
                                        <?php echo $form->field($model, 'type')->dropDownList($templateOptions, array('class' => 'form-control', 'readonly' => true, 'id' => 'type', 'label' => false))->label(false) ?>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="FormMembershipType_name"><?php echo $attributeLabels['name']; ?></label>
                                        <?php echo $form->field($model, 'name')->textInput(['class' => 'form-control', 'maxlength' => 45, 'label' => false])->label(false); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="FormMembershipType_subject"><?php echo $attributeLabels['subject']; ?></label>
                                        <?php echo $form->field($model, 'subject')->textInput()->label(false); ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="FormMembershipType_description"><?php echo $attributeLabels['description']; ?></label>
                                        <?php echo $form->field($model, 'description')->label(false); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 col-xl-9">
                                    <div class="form-group">
                                        <label for="FormMembershipType_content"><?php echo $attributeLabels['content']; ?></label>
                                        <?php echo $form->field($model, 'content')->textarea(['rows' => 10, 'cols' => 50, 'id' => 'MessageTemplate_content', 'class' => 'form-control'])->label(false) ?>
                                        <div class="form-feild-info"><?php echo Yii::t('messages', 'Template for send Emails') ?></div>
                                    </div>
                                </div>
                                <div class="form-group pl-xl-3 pt-xl-4 col-md-12 col-xl-3">
                                    <label><?php echo Yii::t('messages', 'Available keywords'); ?></label>
                                    <?php echo $model->getTemplateKeywordHtml(MessageTemplate::MASS_TEMPLATE); ?>

                                    <label class="mt-3"><?php echo Yii::t('messages', 'Available custom keywords'); ?></label>
                                    <?php echo $model->getTemplateCustomKeywordHtml(); ?>
                                    <div>
                                        <div class="badge badge-primary">{cDropdown}</div>
                                        <div class="badge badge-primary">{cText Area}</div>
                                        <div class="badge badge-primary">{cMiddle Name}</div>
                                        <div class="badge badge-primary">{cDropdown}</div>
                                        <div class="badge badge-primary">{cText Area}</div>
                                        <div class="badge badge-primary">{cMiddle Name}</div>
                                    </div>
                                </div>
                            </div>

                            <!--<div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="FormMembershipType_smsMessage"><?php // echo $attributeLabels['smsMessage']; ?></label>
                                        <?php // echo $form->field($model, 'smsMessage')->textarea(array('rows' => 4, 'cols' => 50, 'id' => 'smsMessage', 'class' => 'form-control mceNoEditor'))->label(false); ?>
                                        <div class="form-feild-info"><span id='smsCountDown'></span>
                                            <p><?php // echo $smsNote ?></p></div>
                                    </div>
                                </div>
                            </div>-->

                            <?php echo $form->field($model, 'dragDropMessageCode')->hiddenInput(['id' => 'MessageTemplate_dragDropMessageCode'])->label(false) ?>
                            <?php echo $form->field($model, 'savedSearchId')->hiddenInput(['id' => 'MessageTemplate_savedSearchId'])->label(false) ?>
                            <?php echo $form->field($model, 'messageType')->hiddenInput(['id' => 'MessageTemplate_messageType'])->label(false) ?>

                            <div class="form-row text-left text-md-right">
                                <div class="form-group col-md-12">


                                    <?php if (!$model->isInstant) { ?>

                                        <div class="form-actions">
                                            <?php
                                            echo Html::submitButton($model->isNewRecord || isset($templateOptions['isDuplicate']) ? Yii::t('messages', 'Create') : Yii::t('messages', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary'])
                                            ?>

                                            <?php echo Html::a(Yii::t('messages', 'Cancel'), Yii::$app->urlManager->createUrl('message-template/admin'), ['class' => 'btn btn-secondary']) ?>
                                        </div>

                                    <?php } else { ?>

                                        <div class="form-actions">
                                            <?php
                                            echo Html::submitButton('Send', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary'])
                                            ?>
                                            <?php echo Html::a('cancel', $model->retUrl, ['class' => 'btn btn-secondary']) ?>
                                        </div>

                                    <?php } ?>
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