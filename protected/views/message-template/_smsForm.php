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
$accentsContentMsg = Yii::t('messages', 'SMS will be limited to 48 characters with accents');

$this->registerJs("
   var remainFiled = {'remaining_sms_1':138, 'remaining_sms_2':138};
   var accentChars = /[À-ú]/gi
   $('#remaining').text(remainFiled['remaining_sms_1'] + remainFiled['remaining_sms_2']);
   
   const countRemainCharacter = (id, remainKey) => {
      if(/[À-ú]/i.test($(id).val())){
            var str = $(id).val();
            var totalCharCount = str.length;
            var remainChar = 138;
            if(totalCharCount > 48) {
               alert('$accentsContentMsg');
               remainChar = 48 - totalCharCount;  
               var val = str // get text, written in textarea
               val = val.slice(0, remainChar); // remove last char
               $(id).val(val);
               remainFiled[remainKey] = 0;
            } else {
                 remainFiled[remainKey] = 138 - ($(id).val().length);
            }
            
            /*if(/[À-ú]/i.test($(id).val())) {
               remainFiled[remainKey] = 0;
            } else {
               remainFiled[remainKey] = 138 - ($(id).val().length);
            }*/
                 
        } else {
            remainFiled[remainKey] = 138 - ($(id).val().length);
            $(id).prop('maxlength', 138);
        }
        
        $('#preview_sms').text($('#sms_1').val() + ' ' + $('#sms_2').val());
        $('#characterCount').val(remainFiled['remaining_sms_1'] + remainFiled['remaining_sms_2']);
        $('#remaining').text(remainFiled['remaining_sms_1'] + remainFiled['remaining_sms_2']);  
   }
   
   $('#sms_1').on('keyup',function(){       
        return countRemainCharacter('#sms_1', 'remaining_sms_1');
   });
   
   $('#sms_1').on('change', function () {
       return countRemainCharacter('#sms_1', 'remaining_sms_1');
   });
  
   $('#sms_2').on('keyup',function(){
         return countRemainCharacter('#sms_2', 'remaining_sms_2');
   }); 
   
   $('#sms_2').on('change', function () {
       return countRemainCharacter('#sms_2', 'remaining_sms_2');
   }); 
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

$smsNote = Yii::t('messages', 'SMS are limited to 138 characters, if you use accents such as "à" "é" "ï" "ô" "ù" SMS will be limited to 70 characters');

$this->registerCss("

.container {
    width: 400px;
    padding: 10px;
}

.message-blue {
    position: absolute;
    margin-top: 20px;
    margin-right: 10px;
    padding: 10px;
    background-color: #F3F3F3;
    width: 200px;
    height: 100px;
    overflow: auto;
    text-align: left;
    font: 400 .9em 'Open Sans', sans-serif;
    border: 1px solid #97C6E3;
    border-radius: 10px;
}

.message-content {
    padding: 0;
    margin: 0;
}

.phone {
    border: 40px solid #121212;
    border-width: 55px 7px;
    border-radius: 40px;
    margin: 50px auto;
    overflow: hidden;
    -webkit-transition: all 0.5s ease;
    transition: all 0.5s ease;
   -webkit-animation: fadein 2s; /* Safari, Chrome and Opera > 12.1 */
       -moz-animation: fadein 2s; /* Firefox < 16 */
        -ms-animation: fadein 2s; /* Internet Explorer */
         -o-animation: fadein 2s; /* Opera < 12.1 */
            animation: fadein 2s;
}

.phone.view_2 {
    -webkit-transform: rotateX(0deg) rotateY(0deg) rotateZ(0deg);
            transform: rotateX(0deg) rotateY(0deg) rotateZ(0deg);
    box-shadow: 0px 3px 0 #000, 0px 4px 0 #000, 0px 5px 0 #000, 0px 7px 0 #000, 0px 10px 20px #000;
  }
");
?>

<div>
    <div class="row no-gutters">
        <div class="content-panel col-md-12">
            <div class="content-inner">
                <div class="content-area">
                    <div class="row">
                        <div class="col-md-12">
                            <?php $form = ActiveForm::begin([
                                'id' => 'email-template-form',
                                'options' => ['name' => 'xyz']
                            ]); ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="FormMembershipType_name"
                                               class="font-weight-bold"><?php echo $attributeLabels['name']; ?></label>
                                        <?php echo $form->field($model, 'name')->textInput(['class' => 'form-control', 'maxlength' => 45, 'label' => false])->label(false); ?>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="FormMembershipType_description"
                                               class="font-weight-bold"><?php echo $attributeLabels['description']; ?></label>
                                        <?php echo $form->field($model, 'description')->label(false); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="row my-3">
                                <div class="col-md-12">
                                    <span class="font-weight-bold"><?php echo Yii::t('messages', 'SMS Message') ?></span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="col-md-12 p-0">
                                        <div class="form-group">
                                            <label for="FormMembershipType_type"
                                                   class="font-weight-bold"><?php echo Yii::t('messages', 'SMS 1'); ?></label>
                                            <?php echo $form->field($model, 'smsMessage')->textarea(['id' => 'sms_1', 'maxlength' => MessageTemplate::MSG_LEN_SMS])->label(false) ?>
                                        </div>
                                    </div>
                                    <div class="col-md-12 p-0">
                                        <div class="form-group">
                                            <label for="FormMembershipType_type"
                                                   class="font-weight-bold"><?php echo Yii::t('messages', 'SMS 2 (only for long SMS)'); ?></label>
                                            <?php echo $form->field($model, 'smsMessageTwo')->textarea(['id' => 'sms_2', 'maxlength' => MessageTemplate::MSG_LEN_SMS])->label(false) ?>
                                        </div>
                                    </div>
                                    <input type="hidden" name="characterCount" id="characterCount" value=0/>
                                    <div class="text-secondary" id="remaining"></div>
                                    <div class="col-md-12 p-0">
                                        <?php
                                        echo Yii::t('messages', 'If you want to send long SMS put your content in both text area.') . "<br /><br />";
                                        echo Yii::t('messages', 'Long SMS are billed 2 credits.') . '<br/><br/>';
                                        echo Yii::t('messages', 'You are limited to 138 characters in each SMS. If you use accents such as "à" "é" "ì" "ô" "ù" SMS will be limited to 48 characters.') . "<br /><br />";
                                        echo Yii::t('messages', 'You select your Sender country and Sender ID in Configuration.') . "<br /><br />";
                                        echo Yii::t('messages', 'Unsubcribe link is added automatically during the sending process.')
                                        ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="FormMembershipType_type"
                                               class="font-weight-bold"><?php echo Yii::t('messages', 'Preview'); ?></label>
                                        <div class="phone view_2" id="phone_1" style="width: 270px; height: 480px;">
                                            <div class="font-weight-bold text-center p-1">
                                                <?php
                                                $configuration = \app\models\Configuration::getConfigurations();
                                                echo $configuration['SMS_SENDER_ID'];
                                                ?>
                                            </div>
                                            <div class="container">
                                                <div class="message-blue">
                                                    <p class="message-content" id="preview_sms"><?php
                                                        if (isset($model['smsMessage']) && !empty($model['smsMessage'])) {
                                                            $smsMessageOne = $model['smsMessage'];
                                                            if (!empty($model['smsMessageTwo'])) {
                                                                $smsMessageOne = $smsMessageOne . ' ' . $model['smsMessageTwo'];
                                                            }
                                                            echo $smsMessageOne;
                                                        }
                                                        ?></p><br/>
                                                    <p><?php echo Yii::t('messages', '+ STOP SMS') ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

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
