<?php

use app\components\Bitly;
use app\components\TwitterApi;
use app\components\LinkedInApi;
use Mailchimp\MailChimpApi;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model app\models\Configuration */
/* @var $form yii\widgets\ActiveForm */
// Yii::$app->toolKit->registerBootstrapMultiselctScripts();
$smsCountry = $configFormModel->smsSenderCountry;
$this->registerJs("
    var hltFields = JSON.parse('{$highlightFields}');
     
	for (var i = 0; i < hltFields.length; i++) {
		$('#' + hltFields[i]).attr('style', 'background:#F7F391; border-color:#F7F391');
	}
	
	// Add new Configuration from email option  
// on load Display From name and emails	
    $(window).on('load',function () {
    $('#senderId').hide();
        if($('#FromEmailOption_isDisplay').is(':checked')){         
               $('.fromEmail-options').show();
            }
            else{
               $('.fromEmail-options').hide();
            }
    });

// on check Display Filters

   $('#FromEmailOption_isDisplay').change(function(){
       if(this.checked){        
               $('.fromEmail-options').show();
            }
            else{
               $('.fromEmail-options').hide();
            }
    });
    
    var smsSenderCountry = '{$smsCountry}';
    
    if(smsSenderCountry == 'FR') {
       $('.senderId').show();
    } else {
       $('.senderId').hide();
    }
    
    $('#smsSenderCountry').change(function(){
        var value = this.value;
        if (value == 'US' || value == '') {
        $('.senderId').hide();
        } else {
        $('.senderId').show();
        }
    });
");
?>
<script>

</script>
<div>
    <div class="row no-gutters">
        <div class="content-panel col-md-12">
            <div class="content-inner">
                <div class="content-area">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="content-panel-sub">
                                <div class="panel-head"><?php echo Yii::t('messages', 'General Settings') ?></div>
                            </div>
                            <?php $form = ActiveForm::begin(); ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="Configuration_language"><?php echo $attributeLabels['language']; ?></label>
                                        <?php echo $form->field($configFormModel, 'language')->dropDownList($configFormModel->getLanguageOptions())->label(false); ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="Configuration_timezone"><?php echo $attributeLabels['timezone']; ?></label>
                                        <?php echo $form->field($configFormModel, 'timezone')->dropDownList($configFormModel->getTimeZoneOptions(), ['class' => 'form-control', 'hint' => Yii::t('messages', '')])->label(false); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="Configuration_fromEmail"><?php echo $attributeLabels['fromEmail']; ?></label>
                                        <?php
                                        echo $form->field($configFormModel, 'fromEmail')->textInput(['class' => 'form-control'])->label(false)
                                        ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="Configuration_fromName"><?php echo $attributeLabels['fromName']; ?></label>
                                        <?php
                                        echo $form->field($configFormModel, 'fromName')->textInput(['class' => 'form-control'])->label(false)
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="form-check form-check-inline">
                                            <label class="checkbox" for="FromEmailOption_isDisplay">
                                                <input id="FromEmailOption_isDisplay" class="form-check-input checkbox"
                                                       name="User[FromEmailOption]" value="1"
                                                       type="checkbox"><?php echo Yii::t('messages', 'Other From Emails Options') ?>
                                            </label><br/>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="fromEmail-options" style="display:block">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="Configuration_fromEmail_1"><?php echo $attributeLabels['fromEmail_1']; ?></label>
                                            <?php /*echo $form->textFieldRow($configFormModel, 'fromEmail_1', array(
                                                'class' => 'form-control',
                                                'label' => false,
                                            ));*/ ?>
                                            <?php echo $form->field($configFormModel, 'fromEmail_1')->textInput(['class' => 'form-control'])->label(false); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="Configuration_fromName_1"><?php echo $attributeLabels['fromName_1']; ?></label>
                                            <?php echo $form->field($configFormModel, 'fromName_1')->textInput(['class' => 'form-control'])->label(false); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="Configuration_fromEmail_2"><?php echo $attributeLabels['fromEmail_2']; ?></label>
                                            <?php echo $form->field($configFormModel, 'fromEmail_2')->textInput(['class' => 'form-control'])->label(false); ?>

                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="Configuration_fromName_2"><?php echo $attributeLabels['fromName_2']; ?></label>
                                            <?php echo $form->field($configFormModel, 'fromName_2')->textInput(['class' => 'form-control'])->label(false); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="Configuration_fromEmail_3"><?php echo $attributeLabels['fromEmail_3']; ?></label>
                                            <?php echo $form->field($configFormModel, 'fromEmail_3')->textInput(['class' => 'form-control'])->label(false); ?>

                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="Configuration_fromName_3"><?php echo $attributeLabels['fromName_3']; ?></label>
                                            <?php echo $form->field($configFormModel, 'fromName_3')->textInput(['class' => 'form-control'])->label(false); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="Configuration_fromEmail_4"><?php echo $attributeLabels['fromEmail_4']; ?></label>
                                            <?php echo $form->field($configFormModel, 'fromEmail_4')->textInput(['class' => 'form-control'])->label(false); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="Configuration_fromName_4"><?php echo $attributeLabels['fromName_4']; ?></label>
                                            <?php echo $form->field($configFormModel, 'fromName_4')->textInput(['class' => 'form-control'])->label(false); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="Configuration_mailjetUsername"><?php echo $attributeLabels['mailjetUsername']; ?></label>
                                        <?php echo $form->field($configFormModel, 'mailjetUsername')->textInput(['class' => 'form-control'])->label(false); ?>
                                        <div class="form-feild-info"><?php echo Yii::t('messages',
                                                'our MailJet account(SMTP) username. Works only with new MailJet accounts that support API access. Username and password are in MailJet > Account > SMTP Settings.') ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="Configuration_mailjetPassword"><?php echo $attributeLabels['mailjetPassword']; ?></label>
                                        <?php echo $form->field($configFormModel, 'mailjetPassword')->textInput(['class' => 'form-control'])->label(false); ?>
                                        <div class="form-feild-info"><?php echo Yii::t('messages',
                                                'Your MailJet account(SMTP) password') ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="Configuration_donationType"><?php echo $attributeLabels['donationType']; ?></label>
                                        <div class="multi-select-dropdown">
                                            <?php
                                            echo $form->field($configFormModel, 'donationType')->widget(Select2::className(), [
                                                'name' => 'donationTypes',
                                                // 'value' => $configFormModel->donationType, // initial value
                                                'data' => $configFormModel->getDonationTypeList(),
                                                'size' => Select2::MEDIUM,
                                                'options' => [
                                                    'class' => 'form-control',
                                                    'multiple' => true
                                                ],

                                            ])->label(false);
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="Configuration_paypalId"><?php echo $attributeLabels['paypalId']; ?></label>
                                        <?php echo $form->field($configFormModel, 'paypalId')->textInput(['class' => 'form-control'])->label(false); ?>
                                        <div class="form-feild-info"><?php echo Yii::t('messages',
                                                'Merchant id or email of the Paypal account') ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="Configuration_currency"><?php echo $attributeLabels['currency']; ?></label>
                                        <?php echo $form->field($configFormModel, 'currency')->dropDownList(Yii::$app->toolKit->getCurrencyInfo('ALL_OPTIONS'), ['class' => 'form-control'])->label(false); ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="Configuration_currency"><?php // echo $attributeLabels['excludeFbPersonalContacts']; ?></label>
                                        <?php echo $form->field($configFormModel, 'excludeFbPersonalContacts')->checkbox(['class' => 'form-check-input custom-icheck']); ?>
                                        <div class="form-feild-info"><?php echo Yii::t('messages', 'Exclude personal Facebook contacts appearing on the system') ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="Configuration_validateVolunteer"><?php // echo $attributeLabels['validateVolunteer']; ?></label>
                                        <div class="form-check">
                                            <?php echo $form->field($configFormModel, 'validateVolunteer')->checkbox(['class' => 'form-check-input custom-icheck']); ?>
                                        </div>

                                    </div>
                                </div>
                                <div class="col-md-6">

                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="form-group">
                                            <label for="Configuration_timezone"><?php echo $attributeLabels['smsSenderCountry']; ?></label>
                                            <?php echo $form->field($configFormModel, 'smsSenderCountry')->dropDownList($configFormModel->getSmsSenderCountry(), ['class' => 'form-control','id'=>'smsSenderCountry', 'hint' => Yii::t('messages', '')])->label(false); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="senderId">
                                            <label for="Configuration_validateVolunteer"><?php echo $attributeLabels['smsSenderId']; ?></label>
                                            <div class="form-check">
                                                <?php echo $form->field($configFormModel, 'smsSenderId')->textInput(['class' => 'form-control'])->label(false); ?>
                                            </div>
                                            <div class="form-feild-info"><?php echo Yii::t('messages',
                                                    'Sender ID can only be a valid telephone number, company name or company product and it must only contain the following characters A-Z and 0-9 with a maximum of 11 characters for a name and 16 for a number.') ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="Configuration_validateVolunteer"><?php echo $attributeLabels['stripeClientId']; ?></label>
                                        <?php echo $form->field($configFormModel, 'stripeClientId')->textInput(['class' => 'form-control'])->label(false); ?>
                                        <div class="form-feild-info"><?php echo Yii::t('messages',
                                                'Stripe Client ID can be obtained from stripe.com when you sign up. ex: pk_test_6pRNASCoBOKtIshFeQd4XMUh.') ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="Configuration_validateVolunteer"><?php echo $attributeLabels['stripeSecretId']; ?></label>
                                        <?php echo $form->field($configFormModel, 'stripeSecretId')->textInput(['class' => 'form-control'])->label(false); ?>
                                        <div class="form-feild-info"><?php echo Yii::t('messages',
                                                'Stripe Client ID can be obtained from stripe.com when you sign up. ex: pk_test_6pRNASCoBOKtIshFeQd4XMUh.') ?></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="Configuration_paypalId"><?php echo $attributeLabels['fbPage']; ?></label>
                                        <?php echo $form->field($configFormModel, 'fbPage')->textInput(['class' => 'form-control'])->label(false); ?>
                                        <div class="form-feild-info"><?php echo Yii::t('messages',
                                                'Facebook Page Id') ?></div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row text-left text-md-right">
                                <div class="form-group col-md-12">

                                    <?php
                                    echo Html::submitButton(Yii::t('messages', 'Save'), ['class' => 'btn btn-primary']);
                                    ?>
                                </div>
                            </div>

                            <?php ActiveForm::end(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php if (Yii::$app->user->checkAccess("superadmin") || Yii::$app->user->checkAccess("Configuration.Update")) { ?>
            <div class="content-panel col-md-6">
                <div class="content-inner">
                    <div class="panel-head">Reset Accounts</div>
                    <div class="content-area">
                        <ul class="list-group">
                            <!--        <li class="list-group-item list-group-item-action">
                                <img src="<?php /* echo Yii::$app->toolKit->getImagePath() */ ?>social-twitter.svg" width="25"> <?php /* echo Yii::t('messages', 'Twitter') */ ?>
                                <button onclick="location.href = '<?php /*echo Yii::$app->urlManager->createUrl(['configuration/reset-social-media/','id'=>TwitterApi::TWITTER]); */ ?>';"
                                        class="btn btn-primary btn-sm  float-right">
                                    <?php /* echo Yii::t('messages', 'Reset'); */ ?>
                                </button>
                            </li>-->
                            <li class="list-group-item list-group-item-action"><img
                                        src="<?php echo Yii::$app->toolKit->getImagePath() ?>social-in.svg" width="25">
                                <?php echo Yii::t('messages', 'LinkedIn') ?>
                                <button onclick="location.href = '<?php echo Yii::$app->urlManager->createUrl(['configuration/reset-social-media/', 'id' => LinkedInApi::LINKEDIN]); ?>';"
                                        class="btn btn-primary btn-sm float-right"><?php echo Yii::t('messages', 'Reset') ?></button>
                            </li>
                            <li class="list-group-item list-group-item-action"><img
                                        src="<?php echo Yii::$app->toolKit->getImagePath() ?>social-mailchimp.svg"
                                        width="25"> <?php echo Yii::t('messages', 'MailChimp') ?>
                                <button onclick="location.href = '<?php echo Yii::$app->urlManager->createUrl(['configuration/reset-social-media/', 'id' => MailChimpApi::MAILCHIMP]); ?>';"
                                        class="btn btn-primary btn-sm float-right"><?php echo Yii::t('messages', 'Reset') ?></button>
                            </li>
                            <!-- <li class="list-group-item list-group-item-action"><img
                                        src="<?php // echo Yii::$app->toolKit->getImagePath() ?>social-bitly.svg"
                                        width="25"> <?php // echo Yii::t('messages', 'Bilty') ?>
                                <button onclick="location.href = '<?php // echo Yii::$app->urlManager->createUrl(['configuration/reset-social-media/','id'=>Bitly::BITLY]); ?>';"
                                        class="btn btn-primary btn-sm float-right"><?php // echo Yii::t('messages', 'Reset') ?></button>
                            </li>-->
                        </ul>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
