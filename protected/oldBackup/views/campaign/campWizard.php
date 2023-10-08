<style>
    .owl-carousel {
        display: block !important;
    }

    .owl-item .heading {
        color: #3f80be;
        font-size: 24px;
    }

    .wizard > .content {
        background: #ffffff;
    }

</style>
<?php
/* Campaign Wizard */

use app\models\Campaign;
use app\models\Configuration;
use app\models\MessageTemplate;
use app\models\SearchCriteria;
use yii\helpers\Html;
use yii\jui\DatePicker;
use yii\widgets\ActiveForm;

/* @var $searchModel app\models\CampaignSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('messages', 'New Campaign');
$this->titleDescription = Yii::t('messages', 'Launch new Email/SMS/Twitter campaign');
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Communication'), 'url' => ['#']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'New Campaign')];
$msg1 = Yii::t('messages', 'Campaign type required');
$msg2 = Yii::t('messages', 'Criteria required');
$msg3 = Yii::t('messages', 'End date & Template required');
$msg4 = Yii::t('messages', 'Email required');
$msg5 = Yii::t('messages', 'Phone number required');
$msg6 = Yii::t('messages', 'Twitter handler required');
$msg7 = Yii::t('messages', 'Please configure your email address first. Click {here} to configure email.', array(
    '{here}' => Html::a('here', ['configuration/update', 'hlt' => base64_encode('fromEmail')], ['id' => 'here'])
));
$msg8 = Yii::t('messages', 'Please configure your MailJet Account. unless It will be a limited Campaign up to {emails} emails, Click {here} to configure',
    array('{here}' => Html::a('here', ['configuration/update', 'hlt' => base64_encode('mailjetUsername,mailjetPassword')], ['id' => 'here']), '{emails}' => Yii::$app->params['campaign']['limit']));
$msg9 = Yii::t('messages', 'From Email required');
$msgSms = Yii::t('messages', 'Please configure your sms sender country');
$msgExceedSms = Yii::t('messages', 'Sms limit is exceeded please contact support team');
$lblNext = Yii::t('messages', 'Next');
$lblPrevious = Yii::t('messages', 'Previous');
$campTypeEmail = Campaign::CAMP_TYPE_EMAIL;
$campTypeSms = Campaign::CAMP_TYPE_SMS;
$campTypeTw = Campaign::CAMP_TYPE_TWITTER;
$campTypeAll = Campaign::CAMP_TYPE_ALL;
$sentCampUri = Yii::$app->urlManager->createUrl('campaign/admin');
Yii::$app->toolKit->registerJqueryStepsScripts();
Yii::$app->toolKit->setJsFlash();
$type = '';

$script = <<< JS
$('#camp-wizard').steps({
		labels: {
			next: '{$lblNext}',
			previous: '{$lblPrevious}',
		},
        enableFinishButton: false,
        titleTemplate: '#title#',
        headerTag: 'h3',
        bodyTag: 'section',
        transitionEffect: 'slideLeft',
        autoFocus: true,
		onStepChanging: function (event, currentIndex, newIndex) {
			if (currentIndex > newIndex) {
				// Allow previous step without validation
				return true;
			}
			switch (currentIndex) {
				case 0:
					if ('' == $('#type').val()) {
						setJsFlash('error', "{$msg1}");
						return false;
					}
					break;
					
				case 1:
					if ('' == $('#criteria').val()) {
						setJsFlash('error', "{$msg2}");
						return false;
					}
					break;
					
				case 2:
                    var typeOfCampaign = $('#type').val()
					if (typeOfCampaign == "{$campTypeEmail}" && '' == $('#template').val()) {
						setJsFlash('error', "{$msg3}");
						return false;
					}
                    
                    if (typeOfCampaign == "{$campTypeSms}" && '' == $('#templateSms').val()) {
						setJsFlash('error', "{$msg3}");
						return false;
					}
					break;
			}
			clearJsFlash();
			return true;
		}
    });

	$('.campaign-type-1').on('click', function() {
		if (('{$campTypeEmail}' == $(this).attr('data-type') || '{$campTypeAll}' == $(this).attr('data-type')) && '' == '{$isDefEmailChanged}') {
			alert('{$msg7}');
			return false;
		}
		if (('{$campTypeEmail}' == $(this).attr('data-type') || '{$campTypeAll}' == $(this).attr('data-type'))){
            if ('{$isLimitedCampaign}') {
                alert('{$msg8}');
                return false;
            }
		}
        if (('{$campTypeSms}' == $(this).attr('data-type'))){
            if ('{$isLimitedSms}') {
                alert('{$msgExceedSms}');
                return false;
            }
            
            if('{$isSmsConfiguration}') {
                alert('{$msgSms}');
                return false;
            }
		}
		resetTypes();
		showTestFields($(this).attr('data-type'));
		$(this).addClass('active');
		$('#type').val($(this).attr('data-type'));
		return false;
	});
	
	function resetTypes() {
		$('.campaign-type-1').each(function() {
			$(this).removeClass('active');
		});
	}
	
	function showTestFields(val)
	{
		switch (val) {
			case '{$campTypeEmail}':
				$('#divEmail').show();
				$('#divPhone').hide();
				$('#divFollower').hide(); 
                $('#emailTemplate').show();
                $('#emailCreate').show();
                $('#smsTemplate').hide();
                $('#smsCreate').hide();
                $('#fromSms').hide();
				break;
				
			case '{$campTypeSms}':
				$('#divEmail').hide();
				$('#divPhone').show();
				$('#divFollower').hide();
                $('#fromEmail').hide();
                $('#emailTemplate').hide();
                $('#emailCreate').hide();
                $('#smsTemplate').show();
                $('#smsCreate').show();
                $('#fromSms').show();
                break;
				
			case '{$campTypeTw}':
				$('#divEmail').hide();
				$('#divPhone').hide();
				$('#divFollower').show();
                $('#fromEmail').hide();                
				break;
				
			case '{$campTypeAll}':
				$('#divEmail').show();
				$('#divPhone').hide();
				$('#divFollower').show();
				break;	
		}
	}
	
	$('#send').on('click', function() {
                var isOk = true;
		switch ($('#type').val()) {
			case '{$campTypeEmail}':
				if ('' == $('#fromEmail option:selected').val()) 
                                {
					setJsFlash('error', '{$msg9}');
					isOk = false;
				}
				break;
                 }               
            if (isOk) {                    
                    $.ajax({
                                type: 'POST',
                                url: $('#send').attr('href'),
                                data: $('#camp-form').serialize(),
                                success: function(data) {
                                        var res = $.parseJSON(data);
                                        $('#statusMsg').html(res.message);
                                        if ('0' == res.status) {
                                                setInterval(function () {
                                                        window.location.href = '{$sentCampUri}';
                                                }, 2000);
                                        }
                                }
                        });
                }        
		return false;
	});
					
					$('#sendTest').on('click', function() {
		var isOk = true;
		switch ($('#type').val()) {
			case '{$campTypeEmail}':
				if ('' == $('#email').val()) {
					setJsFlash('error', '{$msg4}');
					isOk = false;
				}
                                else if ('' == $('#fromEmail option:selected').val()) 
                                {
					setJsFlash('error', '{$msg9}');
					isOk = false;
				}
				break;
				
			case '{$campTypeSms}':
				if ('' == $('#phoneNumber').val()) {
					setJsFlash('error', '{$msg5}');
					isOk = false;
				}
				break;
				
			case '{$campTypeTw}':
				if ('' == $('#follower').val()) {
					setJsFlash('error', '{$msg6}');
					isOk = false;
				}
				break;
				
			case '{$campTypeAll}':
				if ('' == $('#email').val()) {
					setJsFlash('error', '{$msg4}');
					isOk = false;
				} else if ('' == $('#follower').val()) {
					setJsFlash('error', '{$msg6}');
					isOk = false;
				}
				break;	
		}
	
		if (isOk) {
			$.ajax({
			   type: 'POST',
			   url: $('#sendTest').attr('href'),
			   data: $('#camp-form').serialize(),
			   success: function(data) {
				  var res = $.parseJSON(data);
				  $('#statusMsg').html(res.message);
			   }
			});
		}
		return false;
	});
JS;
$this->registerJs($script);
?>
<div class="row no-gutters">
    <div class="content-panel col-md-12">
        <div class="content-inner">
            <div class="content-area">
                <?php $form = ActiveForm::begin([
                    'method' => 'get',
                    'id' => 'camp-form'
                ]); ?>
                <?php echo Html::hiddenInput('type', '', ['id' => 'type']); ?>
                <div id="camp-wizard">
                    <h3><i class="fa fa-th-large fa-lg"></i><span
                                class="wizard-label">&nbsp;<?php echo Yii::t('messages', 'Type', array('id' => 'type')) ?></span>
                    </h3>
                    <section>
                        <div class="campaign-outer">
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="owl-carousel owl-theme campaign-slides">
                                        <div class="item">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="row no-gutters">
                                                        <div class="col-6 col-lg-6 col-xl-3">
                                                            <a class="campaign-type-1"
                                                               data-type="<?php echo $campTypeEmail ?>" href="#">
                                                                <div class="icon"><i class="fa fa-envelope"></i></div>
                                                                <div class="title d-none d-sm-block">
                                                                    <?php echo Yii::t('messages', 'Email Campaign') ?></div>
                                                            </a>
                                                        </div>
                                                        <div class="col-6 col-lg-6 col-xl-3">
                                                            <a class="campaign-type-1"
                                                               data-type="<?php echo $campTypeSms ?>" href="#">
                                                                <div class="icon"><i class="fa fa-tty"></i></div>
                                                                <div class="title d-none d-sm-block"><?php echo Yii::t('messages', 'SMS Campaign') ?></div>
                                                            </a>
                                                        </div>
                                                 <!-- <div class="col-6 col-lg-6 col-xl-3">
                                                            <a class="campaign-type-1"
                                                               data-type="<?php /*echo $campTypeTw */?>" href="#">
                                                                <div class="icon"><i class="fa fa-twitter"></i></div>
                                                                <div class="title d-none d-sm-block"><?php /*echo Yii::t('messages', 'Twitter Campaign') */?></div>
                                                            </a>
                                                        </div>
                                                        <div class="col-6 col-lg-6 col-xl-3">
                                                            <a class="campaign-type-1"
                                                               data-type="<?php /*echo $campTypeAll */?>" href="#">
                                                                <div class="icon"><i class="fa fa-send"></i></div>
                                                                <div class="title d-none d-sm-block"><?php /*echo Yii::t('messages', 'Email & Twitter') */?></div>
                                                            </a>
                                                        </div>-->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                    <h3><i class="fa fa-list fa-lg"></i><span
                                class="wizard-label">&nbsp;<?php echo Yii::t('messages', 'Lists') ?></span></h3>
                    <section>
                        <div class="owl-item">
                            <div class="item">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="heading text-center mb-3">
                                            <?php echo Yii::t('messages', 'Sending List') ?>
                                        </div>
                                        <div class="text-center">
                                            <div class="row">
                                                <div class="col-11 col-lg-6 col-xl-4 mx-auto">
                                                    <div class="form-group">
                                                        <label><?php echo Yii::t('messages', 'Select a existing user list') ?></label>
                                                        <?php echo Html::dropDownList('criteriaId', '', SearchCriteria::getSavedSearchOptions(null, SearchCriteria::ADVANCED), array('id' => 'criteria', 'class' => 'form-control')) ?>
                                                    </div>

                                                    <div class="form-group row">
                                                        <label for="staticEmail"
                                                               class="col-sm-6 col-form-label text-center text-sm-right"><?php echo Yii::t('messages', 'Need a new list?') ?></label>
                                                        <?= Html::a('<i class="fa fa-plus"></i> ' . Yii::t('messages', 'Add New'), Yii::$app->urlManager->createUrl('advanced-search/admin'), ['class' => 'btn btn-primary', 'id' => 'addSearch']); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <h3><i class="fa fa-address-card fa-lg"></i><span
                                class="wizard-label">&nbsp;<?php echo Yii::t('messages', 'Template') ?></span></h3>
                    <section>
                        <div class="owl-item">
                            <div class="item">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="heading text-center mb-3">
                                            <?php echo Yii::t('messages', 'Campaign Template') ?>
                                        </div>
                                        <div class="text-center">
                                            <div class="row">
                                                <div class="col-11 col-md-12 col-lg-10 col-xl-8 mx-auto">
                                                    <div class="row">
                                                        <div class="col-lg-3 col-sm-3 col-6 text-center mb-2 mb-lg-0 mx-auto">
                                                            <img src="<?php echo Yii::$app->toolKit->getImagePath() ?>/layout.svg"
                                                                 alt="" class="img-thumbnail mx-auto">
                                                        </div>
                                                        <div class="col-lg-9 col-sm-9">

                                                            <div class="row">
                                                                <div class="col-md-7">
                                                                    <input type="hidden" id="templateType" />
                                                                    <div class="form-group" id="emailTemplate">
                                                                        <label for="exampleFormControlSelect1"
                                                                               class="float-left"><?php echo Yii::t('messages', 'Select a existing Email templates') ?></label>
                                                                        <?php echo Html::dropDownList("templateEmailId", '', MessageTemplate::getTemplateOptions(null,MessageTemplate::MSG_CAT_EMAIL), array('id' => 'template', 'class' => 'form-control messageTemplate')); ?>
                                                                    </div>

                                                                    <div class="form-group" id="smsTemplate">
                                                                        <label for="exampleFormControlSelect1"
                                                                               class="float-left"><?php echo Yii::t('messages', 'Select a existing Sms templates') ?></label>
                                                                        <?php echo Html::dropDownList("templateSmsId", '', MessageTemplate::getTemplateOptions(null,MessageTemplate::MSG_CAT_SMS), array('id' => 'templateSms', 'class' => 'form-control messageTemplate')); ?>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="row form-group no-gutters" id="emailCreate">
                                                                <div class="col-12 col-md-8 col-lg-4 col-xl-3 text-center text-md-left">
                                                                    <?= Html::a('<i class="fa fa-plus"></i> ' . Yii::t('messages', 'Add Template'), Yii::$app->urlManager->createUrl(['message-template/create', 'templateCategory' => MessageTemplate::MSG_CAT_EMAIL]), ['class' => 'btn btn-primary', 'id' => 'addTemplate']); ?>
                                                                </div>
                                                            </div>
                                                            <div class="row form-group no-gutters" id="smsCreate">
                                                                <div class="col-12 col-md-8 col-lg-4 col-xl-3 text-center text-md-left">
                                                                    <?= Html::a('<i class="fa fa-plus"></i> ' . Yii::t('messages', 'Add Template'), Yii::$app->urlManager->createUrl(['message-template/create', 'templateCategory' => MessageTemplate::MSG_CAT_SMS]), ['class' => 'btn btn-primary', 'id' => 'addTemplate']); ?>
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
                    </section>
                    <h3><i class="fa fa-send fa-lg"></i><span
                                class="wizard-label">&nbsp;<?php echo Yii::t('messages', 'Send') ?></span></h3>
                    <section>
                        <div class="owl-item">
                            <div class="item">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="heading text-center mb-3">
                                            <?php echo Yii::t('messages', 'Start Campaign') ?>
                                        </div>
                                        <div class="text-center">
                                            <div class="row">
                                                <div class="col-11 col-md-7 col-lg-6 col-xl-5 mx-auto">
                                                    <form>
                                                        <div class="form-group">

                                                            <div id="fromEmail">
                                                                <label class="float-left"><?php echo Yii::t('messages', 'Select the email sender') ?></label>
                                                                <div class="input-group mb-3">
                                                                    <?php echo Html::dropDownList("fromEmail", '', Configuration::getConfigFromEmailOptions(), array('id' => 'fromEmail', 'class' => 'form-control')); ?>
                                                                </div>
                                                            </div>
                                                            <div id="fromSms">
                                                                <p class="font-weight-bold"><?php echo Yii::t('messages', 'You sending SMS with this number:'); ?></p>
                                                                <p><?php echo Configuration::getConfigFromSmsOption(); ?></p>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="float-left"><?php echo Yii::t('messages', 'Send test to') ?></label>
                                                            <div id="divEmail">
                                                                <div class="input-group mb-3">
                                                                    <?php echo Html::textInput('email', '', array('id' => 'email', 'placeholder' => Yii::t('messages', 'Test Email Address'), 'class' => 'form-control')); ?>
                                                                </div>
                                                            </div>
                                                            <div id="divPhone">
                                                                <div class="input-group mb-3">
                                                                    <?php echo Html::textInput('phoneNumber', '', array('id' => 'phoneNumber', 'placeholder' => Yii::t('messages','International phone number +44...'), 'class' => 'form-control')); ?>
                                                                </div>
                                                            </div>
                                                            <div id="divFollower">
                                                                <div class="input-group mb-3">
                                                                    <?php  echo Html::textInput('follower', '', array('id' => 'follower', 'placeholder' => 'Twitter Follower Handler', 'class' => 'form-control')); ?>
                                                                </div>
                                                            </div>
                                                            <div class="input-group-append">
                                                                <?= Html::a('<i class="fa fa-chevron-right"></i> ' . Yii::t('messages', 'Send Test'), Yii::$app->urlManager->createUrl('campaign/send-test-message'), ['class' => 'btn btn-secondary', 'id' => 'sendTest']); ?>
                                                            </div>
                                                        </div>
                                                    </form>
                                                    <div class="form-group row">
                                                        <label for="staticEmail"
                                                               class="col-sm-6 col-form-label text-center text-sm-right"><?php echo Yii::t('messages', 'Start your campaign') ?></label>
                                                        <div class="col-sm-6 text-center text-sm-left">
                                                            <?= Html::a('<i class="fa fa-chevron-right"></i> ' . Yii::t('messages', 'Start Now'), Yii::$app->urlManager->createUrl('campaign/add-campaign'), ['class' => 'btn btn-primary', 'id' => 'send']); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>
