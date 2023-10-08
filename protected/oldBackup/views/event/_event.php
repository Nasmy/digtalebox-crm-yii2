<?php

use app\components\WebUser;
use app\models\Configuration;
use app\models\Event;
use app\models\EventUser;
use app\models\SearchCriteria;
use app\models\User;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;

Yii::$app->toolKit->registerTokenInputScripts();
$attributeLabels = $modelMsgBox->attributeLabels();

$url = Yii::$app->urlManager->createUrl('msg-box/get-names');
$hintText = Yii::t('messages', 'Type Name');
$searchingText = Yii::t('messages', 'Searching...');
$autoselect = <<< JS
   var userlist = {$userlist};
 
	$('#msgbox-userlist').tokenInput('{$url}', {theme: 'facebook', hintText:'{$hintText}', searchingText:'{$searchingText}'});
	if (userlist != '') {
		for (var i in userlist) {
			$('#msgbox-userlist').tokenInput('add', {id: userlist[i].id, name: userlist[i].name});
		}
	}
JS;
$this->registerJs($autoselect, View::POS_END);

$shareByEmailDetailsSubject = $shareByEmailDetails['subject'];
$shareByEmailDetailsBody = $shareByEmailDetails['body'];
$participateUrl = Yii::$app->urlManager->createUrl('/event/participate/');
$shareByEmailUrl = Yii::$app->urlManager->createUrl('/event/share-by-email/');

$shareByEmail = <<< JS
    
      function participate() {

        var email = $('#participate_email').val();
        var name = $('#participate_name').val();
        var firstName = $('#participate_firstname').val();
        var id = $('#id').val();
        if (email == '') {
            return;
        }
        $.ajax({
            url: '".$participateUrl."',
            type: "POST",
			data:'email=' + email +'&firstName='+firstName+'&name='+name+'&url='+'" . $shareUri . "'+'&id='+id,
	    	success: function(data) {
				var res = $.parseJSON(data);
				if (res.status == '1') {
				    $('#appendedInputButton').val('');
                                    alert(res.message);
                                window.location.reload();
				} else if (res.status == '0') {
				    alert(res.message);
				}
	    	}
		});
    }

        
    function shareByEmail() {
        var email = $('#appendedInputButton').val();
        if (email == '') {
            return;
        }
        $.ajax({
            url: '" .$shareByEmailUrl. "', 
			data:'email=' + email + '&subject=' + '" . $shareByEmailDetailsSubject . "' + '&body=' + '" . $shareByEmailDetailsBody. "',
	    	success: function(data) {
				var res = $.parseJSON(data);
				if (res.status == '1') {
				    $('#appendedInputButton').val('');
				    alert(res.message);
				} else if (res.status == '0') {
				    alert(res.message);
				}
	    	}
		});
    } 
   
JS;
$this->registerJs($shareByEmail);

$sendInvitationConfirm = Yii::t('messages', 'Are you sure you want to send the event?');

$sendEvent = <<< JS
$('.sendEvent').on('click', function() {
 	var subject  = $('#MsgBox_subject').val();
	var criteria = $('#msgbox-criteriaid').val();
        var userlist = $('#msgbox-userlist').val();
        var fromEmail = $('#MsgBox_fromEmail').val();
		if (window.confirm('".$sendInvitationConfirm.')) {
		  var form_value = {MsgBox:{subject:subject,criteriaId:criteria, userlist:userlist,fromEmail:fromEmail}};
			$.ajax({
				type: 'POST',
				url: $(this).attr('url'),
				data: form_value,
				success: function(data){
				$('#statusMsg2').html(data);
                                $('#MsgBox_subject').val('');
				}
			});
}
		return false;
	});
JS;

$this->registerJs($sendEvent);

$approveConfirm = Yii::t('messages', 'Are you sure you want to accept the event?');
$urlViewEvent = Yii::$app->urlManager->createUrl('/event/view');
$urlViewSingleEvent = Url::to(["event/view", "id" => $model->id]);
$eventAcceptUrl = Url::to(["event/accept", "id" => $model->id]);

$accept = <<< JS
$('.accept').on('click', function() {	
    console.log('test');
		if (window.confirm("$approveConfirm")) {
			$.ajax({
				type: 'GET',
                url: '$eventAcceptUrl',
				success: function(data){
				var res = $.parseJSON(data);
					if (res.status == 1) {
					$('#statusMsg2').html(res.message);
				        $.ajax({
				            type: 'POST',
				            url: '$urlViewSingleEvent',
				            data: 'ajax=true',
				            success: function(data){
                                $('#eventContent').html(data);
					        }
			        });
            }
				}
			});
		}
		return false;
	});
JS;

$this->registerJs($accept);

$rejectConfirm = Yii::t('messages', 'Are you sure you want to reject the event?');
$reject = <<< JS
 $('.reject').on('click', function() {
		if (window.confirm("$rejectConfirm")) {
			$.ajax({
				type: 'GET',
				url: $(this).attr('href'),
				success: function(data){
					var res = $.parseJSON(data);
					if (res.status == 1) {
					$('#statusMsg2').html(res.message);
                    setTimeout( function(){location.reload();},5000);
            }
				}
			});
		}
		return false;
	});
JS;
$this->registerJs($reject);


$updateEvent = <<< JS
	$('.update-event').on('click', function() {		
		window.parent.location.replace($(this).attr('href')); 
		return false;
	});
JS;

$this->registerJs($updateEvent);


$delConfirm = Yii::t('messages', 'Are you sure you want to delete the Event?');
$deleteEvent = <<< JS
    $('.del-event').on('click', function() {		
		if (window.confirm("$delConfirm")) {
			$.ajax({
				type: 'POST',
				url: $(this).attr('href'),
				success: function(data){
				$('#eventView .close').click();
				 window.parent.location.reload();
				 window.parent.$('#calendar').FullCalendar('refetchEvents'); 
				}
			});
		}
		return false;
	});
JS;

$this->registerJs($deleteEvent);

$viewEvent = <<<JS
	$('.view-event').on('click', function() {		
		window.parent.location.replace($(this).attr('href')); 
		return false;
	});
JS;
$this->registerJs($viewEvent);


$composeEventUrl = Yii::$app->urlManager->createUrl("/msg-box/compose-test-event");
$sendTest = <<<JS
$('#sendTest').on('click', function() {
    var subject  = $('#MsgBox_subject').val();
	var email = $('#email').val();	
	var testUrl = '{$composeEventUrl}';
	console.log(testUrl);
    var fromEmail = $('#MsgBox_fromEmail').val();
		  var form_value = {MsgBox:{subject:subject,email:email,fromEmail:fromEmail,id:$model->id}};
			$.ajax({
				type: 'POST',
				url: testUrl,
				data: form_value,
				success: function(data){
                    $('#email').val('');	
				
				$('#statusMsg2').html(data);
				}
			});
});
JS;
$this->registerJs($sendTest);


?>
<script>
    var subject = $('#MsgBox_subject').val();
    var criteria = $('#msgbox-criteriaid').val();
    var form_value = {MsgBox: {subject: subject, criteriaId: criteria}};</script>
<style>
    div.token-input-dropdown-facebook {
        position: absolute;
        width: 400px;
        background-color: #fff;
        border-left: 1px solid #ccc;
        border-right: 1px solid #ccc;
        border-bottom: 1px solid #ccc;
        cursor: default;
        font-size: 11px;
        font-family: Verdana;
        z-index: 10000000 !important;
    }

    .token-input-dropdown-facebook ul {
        overflow: auto !important;
        max-height: 400px;
    }

    .event-description.mt-4 img {
        max-width: 100% !important;
        height: 100%  !important;;
    }

    .form-group.field-msgbox-criteriaid {
        margin: 0;
    }
</style>
<div class="event-details">
    <div id="statusMsg2"></div>
    <div class="col-md-12" id="eventButton" style="margin: 5px;">
        <div class="control-group">
            <div class="controls w-100 text-center">
                <?php if (Event::REJECTED == $model->status) { ?>
                    <div class="alert alert-danger"><?php echo $alertMsg; ?></div>
                    <p></p>
                <?php } ?>
                <?php
                $form = ActiveForm::begin([
                    'id' => 'msg-queue-form',
                    'options' => [
                        'enableAjaxValidation' => 1,
                        'template' => 'horizontal'
                    ],
                ]);
                ?>
                <?php if (Yii::$app->user->checkAccess("Event.Accept") && $model->status == Event::PENDING) { ?>
                    <button type="button"
                            href="<?php echo Url::to(["event/accept", "id" => $model->id]); ?>"
                            class="accept btn btn-success mb-1"><i class="fa fa-check-circle"></i>
                        <?php echo Yii::t('messages', 'Accept'); ?> </button>
                <?php } ?>

                <?php if (Yii::$app->user->checkAccess("Event.Reject") && $model->status == Event::PENDING) { ?>
                    <button type="button"
                            href="<?php echo Url::to(["event/reject", "id" => $model->id]); ?>"
                            class="reject btn btn-danger mb-1"><i class="fa fa-times-circle"></i>
                        <?php echo Yii::t('messages', 'Reject'); ?> </button>
                <?php } ?>

                <?php
                if ((Yii::$app->user->checkAccess("Event.Update") && $model->createdBy == Yii::$app->user->id) || Event::checkEditable(Yii::$app->user->id)) {
                    ?>
                    <button type="button"
                            href="<?php echo Url::to(["event/update", "id" => $model->id]); ?>"
                            class="update-event btn btn-secondary mb-1"><i class="fa fa-edit"></i>
                        <?php echo Yii::t('messages', 'Update'); ?> </button>
                <?php } ?>

                <?php
                if ((Yii::$app->user->checkAccess("Event.Delete") && $model->createdBy == Yii::$app->user->id) || Event::checkEditable(Yii::$app->user->id)) {
                    ?>
                    <button type="button"
                            href="<?php echo Url::to(["event/delete", "id" => $model->id]); ?>"
                            class="btn btn-danger del-event mb-1"><i class="fa fa-trash-o"></i>
                        <?php echo Yii::t('messages', 'Delete'); ?> </button>
                <?php } ?>

                <?php if (Yii::$app->user->checkAccess("Event.View")) { ?>
                    <button type="button"
                            href="<?php echo Url::to(["event/view", "id" => $model->id, "key" => EventUser::ATTENDING]);
                            ?>"
                            class="btn btn-info view-event mb-1"><i class="fa fa-eye"></i>
                        <?php echo Yii::t('messages', 'View Participants'); ?> </button>
                <?php } ?>
                <?php
                if (!$model->status == Event::PENDING) {
                    $previewUrl = "http://" . Yii::$app->toolKit->domain . '/index.php/event/preview?code=' . base64_encode('{"event":' . $model->id . '}'); ?>
                    <a style="margin-top: 20px;background-color: #6edb0a; color: #fff; padding: 10px 25px; text-decoration: none; margin-right: 5px;"
                       target="_blank" href="<?php echo $previewUrl; ?>">
                        <i class="fa fa-calendar"></i>&nbsp;<?php echo Yii::t('messages', 'Preview'); ?> </a>
                <?php } ?>

            </div>
        </div>
        <br>
        <?php
        if ((Event::ACCEPTED == $model->status && !$isExpired)): ?>
            <?php
            // Send invitations
            if ($isInvite) {
                if (Yii::$app->user->identity->userType == User::POLITICIAN || Yii::$app->user->checkAccess('Event.SendEvent') || Yii::$app->user->checkAccess('Event.SendEmailInvitation')  || Event::checkEditable(Yii::$app->user->id)) {
                    ?>
                    <?php if (!Yii::$app->user->checkAccess(WebUser::SUPPORTER_ROLE_NAME) || Yii::$app->session->get('is_super_admin')): ?>
                        <label for="MsgBox_fromEmail"
                               class="float-left font-weight-bold"><?php echo Yii::t('messages', 'Select the email sender') ?></label>
                        <?php echo Html::dropDownList("fromEmail", '', Configuration::getConfigFromEmailOptions(), array('id' => 'MsgBox_fromEmail', 'class' => 'form-control')); ?>
                        <br/>
                        <label for="location" class="font-weight-bold"><?php echo Yii::t('messages', 'Select your saved search') ?> <br/><span class="font-italic font-weight-light small text-muted"> <?php echo Yii::t('messages', 'only users with email') ?> </span></label>
                        <?php
                        echo $form->field($modelMsgBox, 'criteriaId')->dropDownList($criteriaOptions, array('class' => 'form-control'))->label(false);
                        // echo Yii::t('messages', 'Note:Message sent only to users with email.');
                        ?>
                    <?php endif; ?>

                    <label for="msgbox-userlist" class="font-weight-bold mt-4"><?php echo Yii::t('messages', 'Add receivers') ?><br/><span class="font-italic font-weight-light small text-muted"> <?php echo Yii::t('messages', 'only users with email') ?></span></label>
                    <?php echo $form->field($modelMsgBox, 'userlist')->textInput(array('class' => 'form-control'))->label(false); ?>

                    <label for="location" class="font-weight-bold"><?php echo $attributeLabels['subject'] ?> </label>
                    <?php
                    echo $form->field($modelMsgBox, 'subject')->textInput(['class' => 'form-control', 'id' => 'MsgBox_subject'])->label(false);
                }
            }
            ?>
        <?php endif; ?>
    </div>
    <br>
    <div class="col-md-12 font-weight-bold"> <?php echo Yii::t('messages', 'Event Details'); ?> </div>
    <div class="mt-3 col-md-12"><?php echo $model->name; ?></div>
    <div class="col-md-12"><?php echo $model->location; ?></div>
    <div class="col-md-12">
        <!-- Apply translate to dates -->
        <?php echo Yii::t('messages', date('F', strtotime($model->startTimeStamp))); ?>
        <?php echo date('Y', strtotime($model->startTimeStamp)); ?>
        <?php echo date('d', strtotime($model->startTimeStamp)); ?>
        <em><?php echo Yii::t('messages', date("l", strtotime($model->startTimeStamp))); ?></em>
    </div>

    <?php if (null != $model->imageName): ?>
        <div align="center"><?php
            echo Html::img(Url::base(true) . '/' . Yii::$app->toolKit->resourcePathRelative . $model->imageName, array(
                'style' => 'max-width: 630px;', 'max-height: 320px;', 'align' => "center"));
            ?></div>
    <?php endif; ?>
    <div class="col-md-12"><?php echo $model->eventDuration("eventview"); ?></div>
    <div class="row mt-4 mb-3">
        <div class="col-md-12">
            <div class="event-map form-group col-md-12">
                <label class="font-weight-bold"><?php echo Yii::t('messages', 'Event Location'); ?></label>

                <div id="map" style="height: 100%"></div>

            </div>
        </div>
    </div>

</div>
<?php if ((Event::ACCEPTED == $model->status && !$isExpired)): ?>
    <?php
    // Send invitations
    if ($isInvite) {
        if (Yii::$app->user->identity->userType == User::POLITICIAN || Yii::$app->user->checkAccess('Event.SendEvent') || Yii::$app->user->checkAccess('Event.SendEmailInvitation') || Event::checkEditable(Yii::$app->user->identity->id)) {
            ?>
            <div class="form-group row" style="margin-left: 0px; margin-bottom: 0px;"><label
                        class="text-left font-weight-bold my-4"><?php echo Yii::t('messages', 'Send a test') ?></label></div>
            <div class="form-group row">
                <div class="input-group mb-3 col-md-6">
                    <?php echo Html::textInput('email', '', array('id' => 'email', 'placeholder' => Yii::t('messages', 'Email Address'), 'class' => 'form-control')); ?>
                </div>
                <div class="input-group-append col-md-6">
                    <?php
                    echo Html::submitButton(Yii::t('messages', 'Send a test'), ['class' => 'btn btn-info   btn-secondary w-100', 'id' => 'sendTest',]);
                    ?>
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="MsgBox_criteriaId"></label>
                <div class="controls w-100">
                    <?php

                    echo Html::button(Yii::t('messages', 'Send Invitation'), [
                        'class' => 'btn btn-primary  sendEvent w-100',
                        'url' => Url::to(['msg-box/compose-event', 'message' => $shareUri . "&q={USER_ID}", 'eventId' => $model->id, 'isEvent' => true]),
                    ]);

                    ?>
                </div>
            </div>
            <?php
        }
    }
    ?>
<?php endif; ?>
<?php $form->end(); ?>
<script>
    var latLng = [<?= $model->locationMapCordinates ?>];

    $(document).ready(function () {
        setTimeout(myfunc(latLng), 2000);
    });

    function myfunc(latLng) {
        mapLayer = MQ.mapLayer();

        map = L.map('map', {
            center: latLng,
            layers: mapLayer,
            zoom: 12,
            fullscreenControl: true,
            fullscreenControlOptions: {
                position: 'topleft'
            },
        });
        map.on('load', onMapLoad(map));
        L.marker(latLng).addTo(map);
    }

    function onMapLoad(map) {
        setTimeout(() => {
            map.invalidateSize();
        }, 1000);
    }
</script>
