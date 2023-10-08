<?php

use app\components\SCustomFields;
use app\controllers\SignupController;
use app\widgets\Alert;
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

$formControl = 'form-control';
?>
<script type="text/javascript" language="javascript">
    var customFieldList = new Array();

    <?php
    if(isset($customFields)) {
    foreach($customFields as $k => $customField){ ?>
    customFieldList.push('<?php echo $customField->customFieldId; ?>');
    <?php
    }}
    ?>
</script>
<?php

Yii::$app->toolKit->registerDataOsmMapScript();
$mapStyle = Yii::$app->toolKit->getMapStyle(Yii::$app->session->get('themeStyle'));
Yii::$app->toolKit->registerVolunteerThemeStyle();
?>
<?php
$initSignup = <<<JS
   $(document).ready(function () {
        $('#slimscroll').mCustomScrollbar({
            scrollbarPosition: "outside",
            theme:"dark-2",
            scrollInertia: 100
        });
    });
    
    function validateCustomFields(){
    var settings = $('#user-form').data('settings');
    customFieldList.forEach(function(customId) {
    settings.attributes.push({ 'id':'CustomValue_'+customId+'_fieldValue', 'inputID':'CustomValue_'+customId+'_fieldValue', 'errorID':'CustomValue_'+customId+'_fieldValue_em_', 'model':'CustomValue', 'name':'CustomValue['+customId+'][fieldValue]', 'enableAjaxValidation':true, 'inputContainer':'div.control-group', 'status':1 });
    });
    }
    
    $('#btnCloseTC').click(function () {
       $('#TCModalCenter').modal('hide')
    });
JS;
$this->registerJs($initSignup);
$checkEmail = Yii::$app->urlManager->createUrl('/signup/check-email/');
$verifyCode = Yii::$app->urlManager->createUrl('/signup/verify-code/');
$verifyEmail = <<<JS
$(document).keypress(
        function(event){
         if (event.which == '13') { // disable enter key form submission.
            event.preventDefault();
          }
    });
if('$model->isVerified' == '1') {
		$('.card').hide();
		$('.div-formdata').show();
	} else {
		$('.div-formdata').hide();
	}
$('#divVerifyCode').hide();
$('#check').click(function(){
	setTimeout(function() {
            map.invalidateSize();
            //map.invalidateSize(true)
        }, 500);
		$.ajax({
			url: '{$checkEmail}',
			type:'POST',
			data:'email=' + $('#verifyEmail').val(),
	    	success: function(data) {
				var res = $.parseJSON(data);
				if (res.status == 'EMAIL_NOT_EXISTS') {
					$('.div-formdata').show();
					$('.card').hide();
					$('#email').val($('#verifyEmail').val());
					$('#email').attr('readonly',true);
                    $('#email').attr('aria-required',true);
 					$('#isVerified').val('1');
				} else if (res.status == 'EMAIL_EXISTS') {
					$('#divVerifyCode').show();
					$('#email').val($('#verifyEmail').val());
				}
				$('#statusMsg').html(res.message);
	    	} 
		});	
	});

    $('#verify').click(function(){
            $.ajax({
                url: '{$verifyCode}',
                type:'POST',
                data:'verifyCode=' + $('#verifyCode').val(),
                success: function(data) {
                    var res = $.parseJSON(data);
                    if (res.status == 'SUCCESS') {
                        // $('#user-form').submit();
                        // document..submit()
                        document.getElementById("user-form").submit();
                    }
                    $('#statusMsg').html(res.message);
                } 
            });
        });
JS;
$this->registerJs($verifyEmail);
$verifyTerms = <<< JS
    $('#signup').click(function() {
      var terms = $('input[type=checkbox]').prop('checked');
       if (terms) {
          $('#user-form').submit();
          return true;
      } else {
          $('#statusMsg').html("<div class='alert in alert-danger' style='opacity: 1'> <a class='close' data-dismiss='alert'>×</a> Please check the terms and condition.</div>");
          return false;
      }
    });
JS;
$this->registerJs($verifyTerms);
?>
<div class="register">
    <div class="container">
        <div class="mainframe">
            <div class="row d-flex flex-md-row flex-column-reverse">
                <div class="col-md-6">
                    <div class="title"><?php echo Yii::t('messages', 'Sign Up'); ?></div>
                </div>
                <div class="col-md-6 mb-2 text-center text-lg-right">
                    <img src="<?php echo Yii::$app->toolKit->getImagePath() ?>digitalebox-logo.png">
                </div>
            </div>
            <div id="statusMsg"></div>
            <?php
            $form = ActiveForm::begin([
                'id' => 'user-form',
//                'validateOnSubmit'=> true,
                'fieldConfig' => [
                    'template' => "{label}\n<div class=\"\">{input}</div>\n{error}",
                    'labelOptions' => ['class' => 'm-0 control-label'],
                ],
            ]);
            ?>
            <div class="card">
                <div class="card-head">
                    <?php echo Yii::t('messages', 'Check Email'); ?>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label><?php echo Yii::t('messages', 'Email Address'); ?></label>
                                <div class="input-group mb-3">
                                    <?= $form->field($model, 'verifyEmail')->textInput(['autofocus' => true, 'size' => '70%', 'class' => $formControl, 'id' => 'verifyEmail'])->label(false); ?>
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="button"
                                                id="check"><?php echo Yii::t('messages', 'Check'); ?></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col" id="divVerifyCode">
                            <label><?php echo Yii::t('messages', 'Verify Code'); ?></label>
                            <div class="input-group mb-3">
                                <?= $form->field($model, 'verifyCode')->textInput(['autofocus' => true, 'class' => $formControl, 'id' => 'verifyCode'])->label(false); ?>
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="button" id="verify">
                                        <?php echo Yii::t('messages', 'Verify Code'); ?></button>
                                    <?= $form->field($model, 'isVerified')->hiddenInput(['id' => 'isVerified'])->label(false) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="div-formdata">
                <div class="row">
                    <div class="col-lg-7">
                        <div class="loading">
                            <div class="spinner">
                                <div class="bounce1"></div>
                                <div class="bounce2"></div>
                                <div class="bounce3"></div>
                            </div>
                        </div>
                        <div class="register-form-area" id="slimscroll">
                            <div class="row">
                                <?php
                                $leftCol = '';
                                $rightCol = '';
                                $count = 0;

                                foreach ($formFields as $fieldName => $value):
                                    ?>
                                    <?php
                                    if ($value != "") {
                                        echo "<div class=\"col-md-6\"><div class=\"form-group\">" . $form->field($model, $fieldName)->textInput(['value' => $value]) . "</div></div>";
                                        continue;
                                    }
                                    ?>

                                    <?php if (in_array($fieldName, $signupRequiredFields)): ?>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <?=
                                            SignupController::getFormField($fieldName, $model, $form, $signupRequiredFields);
                                            ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php
                                endforeach;
                                ?>
                                <?php
                                echo SCustomFields::widget(['customFields' => $customFields,
                                    'template'=>'<div class="form-group col-md-6">{label}{input}</div>',
                                    'hideLabel' => false,
                                    'enableAjaxValidation'=>true,
                                    'rowClass' => $formControl]);
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div><label><?php echo Yii::t('messages', 'Point Your Location'); ?></label></div>
                        <div class="map">
                            <div id="map_canvas" style="height: 100%;"></div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-7">
                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input custom-icheck" checked="checked" type="checkbox"
                                       id="checkTC" name="User[iagree]" aria-required="true">
                                <label class="form-check-label" for="gridCheck">
                                    <?php echo Yii::t('messages', 'I Agree to the '); ?><a
                                            href="<?php echo Yii::$app->params['privacyUrl']; ?>">
                                        <?php echo Yii::t('messages', 'Terms & Conditions'); ?></a>
                                </label>
                            </div>
                        </div>

                        <?= Html::submitButton('Sign Up', ['class' => 'btn btn-primary', 'id' => "signup", 'name' => 'login-button']) ?>
                    </div>
                    <div class="col-md-5">
                        <div class="text-md-right mt-3 mt-md-5">
                            <?php echo Yii::t('messages', 'Already have an account? '); ?><span><a
                                        href="<?php echo Yii::$app->urlManager->createUrl('site/init'); ?>"><?php echo Yii::t('messages',
                                        'Sign In'); ?></a></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<script>
    let markerImage = '<?=Yii::$app->toolKit->getMarkerImage()?>';
    let lat = <?=$lat ?>;
    let long = <?=$long?>;
    let satelliteLayer, mapLayer, map, m;
    window.onload = function () {
        mapLayer = MQ.mapLayer();
        satelliteLayer = MQ.satelliteLayer();
        let baseMaps = {
            "Street Map": mapLayer,
            "Satellite View": satelliteLayer
        };

        map = L.map('map_canvas', {
            center: [lat, long],
            layers: mapLayer,
            zoom: 12,
            fullscreenControl: true,
            fullscreenControlOptions: {
                position: 'topleft'
            }
        });

        L.control.layers(baseMaps, {}, {position: 'topright'}).addTo(map);

        m = L.marker([lat, long],
            {
                draggable: true
            }).addTo(map);

        map.setView([lat, long], 12);

    };

    /*$('#User_city').blur(function(){
        codeAddress();
    });
    $('#save').click(function(){
        let latLang = m.getLatLng();
        $('#longLat').val(latLang.lat + ',' + latLang.lng);
    });*/
    let updateMarker = (latLng) => {
        map.panTo(latLng);
        m.setLatLng(latLng);
    };

    let codeAddress = () => {
        let address = document.getElementById('User_city').value;
        MQ.geocode()
            .search(address)
            .on('success', (d) => {
                updateMarker(d.result.best.latlng);
            });
    }
</script>
