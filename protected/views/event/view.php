<?php

use app\components\WebUser;
use app\models\Configuration;
use app\models\Event;
use app\models\EventReminder;
use app\models\EventReminderTracker;
use app\models\EventUser;
use app\models\MessageTemplate;
use app\models\SearchCriteria;
use app\models\User;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JqueryAsset;
use yii\web\View;
use yii\widgets\ActiveForm;
use yii\widgets\DetailView;
use yii\widgets\Pjax;

JqueryAsset::register($this);
$paramKey = 0; //RSVP disable
if (isset($_GET['key'])) {
    $eventReminderModel->rsvpStatus = $_GET['key'];
    $paramKey = $eventReminderModel->rsvpStatus;
}
$attributeLabels = $model->attributeLabels();
$attribute = EventUser::getRsvpString($paramKey);

echo Yii::$app->controller->renderPartial('_tabMenu', ['model' => $model, 'rsvpGroups' => $rsvpGroups, 'eventReminderModel' => $eventReminderModel]);


$this->title = Yii::t('messages', 'Event Members');
$this->titleDescription = Yii::t('messages', 'Members Details');


/* @var $this yii\web\View */
/* @var $model app\models\Event */

$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Manage Events'), 'url' => ['admin']];
$this->params['breadcrumbs'][] = Yii::t('messages', 'View Participants');
\yii\web\YiiAsset::register($this);


$approveConfirm = Yii::t('messages', 'Are you sure you want to send the email?');

$accept = <<< JS
 		$('.sendEmail').click(function() {		
		    
		if (window.confirm('.$approveConfirm.')) {
			$.ajax({
				type: 'POST',
				url: $(this).attr("href"),
				data: $('#msg-queue-form').serialize(),
				success: function(data){
					$('#statusMsg').html(data);
                    setTimeout( function(){location.reload();},5000);
				}
			});
		}
		return false;
	});
JS;

$this->registerJs($accept, View::POS_READY);

$showView = <<< JS
	$('.view').click(function() {
				$.ajax({
				type: 'POST',
				url: $(this).attr('href'),
				data: 'ajax=true',
				success: function(data){
                        $('#eventContent').html(data);
                        $('#eventEmail').modal({backdrop: 'static'});
					}
			});
		return false;
	});
JS;

$this->registerJs($showView, View::POS_READY);


$getExportFile = Url::to(['get-export-file', 'id' => $_GET['id'], 'key' => $paramKey]);

$exportCSV = <<< JS

$('#export-button').on('click', function() {
    console.log(2);
var firstName='';
var lastName='';
var email='';
if($('#user-firstname').val()!=''){
    firstName=$('#user-firstname').val();
}
if($('#user-lastName').val()!=''){
    lastName=$('#user-lastname').val();
}
if($('#user-email').val()!=''){
    email=$('#user-email').val();
}
    console.log('$getExportFile');
    console.log(firstName);

   $.ajax({
        url:'$getExportFile &firstName='+firstName+'&lastName='+lastName+'&email='+email,
        type:"post",
        data:'&export=true',
        success: function(data){ 
            $('#campaign-users-grid').removeClass('grid-view-loading');
            window.location = '$getExportFile &firstName='+firstName+'&lastName='+lastName+'&email='+email;                
        } 
    });

 });
 
JS;

$this->registerJs($exportCSV);


$search = <<< JS
    $(document).on('submit', '.search-form form', function(){
    
        var inputs = $('.search-form form :input');
        var values = {};
        inputs.each(function() {
            values[this.name] = $(this).val();
        });
    
        var searchType = values['User[searchType]'];
        var keywords = values['User[keywords][]'];
        var keywordsExclude = values['User[keywordsExclude][]'];
    
        var searchType2 = values['User[searchType2]'];
        var keywords2 = values['User[keywords2][]'];
        var keywordsExclude2 = values['User[keywordsExclude2][]'];
        
        if(searchType == " . User::SEARCH_EXCLUDE . "){
            if(keywords == null){
             alert('.Yii::t("messages", "Keywords or Exclude keywords cannot be empty").');
             return false;
            }
            else if(keywordsExclude == null){
             alert('. Yii::t("messages","Keywords or Exclude keywords cannot be empty") .');
             return false;
            }
            else {
                var res = keywords.filter(function(el) {
                    return keywordsExclude.indexOf(el) != -1
                  });
                if(res.length != 0){
                alert('.Yii::t("messages","Keywords or Exclude keywords cannot contain same value").');
                return false;
                }
            }
        }  
     });
JS;

$this->registerJs($search);

?>

<div class="content-inner">
    <div id="statusMsg"></div>
    <div class="content-area">
        <?php Pjax::begin(['id' => 'people-grid-pjax']); ?>
        <?php
        $visible = false;
        foreach ($rsvpGroups as $key => $group) {
            if (0 < $group && $paramKey == $key) {
                $visible = true;
                $eventReminderModel->totalRecipient = $group;
            }
        }
        if (($visible && Event::ACCEPTED == $model->status && !$isExpired)):
            // Send invitations
            if ($isInvite) {
                if (Yii::$app->user->checkAccess('Event.SendEmailInvitation') || Yii::$app->user->checkAccess('EventReminder.EventReminder')  || Yii::$app->user->identity->userType == User::SUPER_ADMIN) {
                    // if (!Yii::$app->user->checkAccess(WebUser::SUPPORTER_ROLE_NAME) || Yii::$app->session->get('is_super_admin')):
                        if ($paramKey != EventUser::DECLINED) {
                            $form = ActiveForm::begin([
                                'id' => 'msg-queue-form',
                                'enableAjaxValidation' => false,

                            ]);
                            ?>
                            <label for="EventReminder_fromEmail"
                                   class="float-left"><?php echo Yii::t('messages', 'Select the email sender') ?></label>
                            <?php echo Html::dropDownList("EventReminder[fromEmail]", '', Configuration::getConfigFromEmailOptions(), array('id' => 'EventReminder_fromEmail', 'class' => 'form-control')); ?>
                            <br/>
                            <div class="content-panel-sub">
                                <div class="panel-head">
                                    <i class="fa fa-envelope"></i> <?php echo Yii::t('messages', 'Send more emails'); ?>
                                </div>

                                <div class="panel-body">
                                    <?php
                                    echo $form->field($eventReminderModel, 'messageTemplateId')->dropDownList(MessageTemplate::getTemplateOptions(null,MessageTemplate::MSG_CAT_EMAIL), array('class' => 'mb-0 form-control'));
                                    echo $form->field($eventReminderModel, 'rsvpStatus')->hiddenInput(['class' => 'mb-0'])->label(false);
                                    echo $form->field($eventReminderModel, 'totalRecipient')->hiddenInput(['class' => 'mb-0'])->label(false);
                                    ?>
                                    <button type="primary"
                                            href="<?php
                                            echo Url::to(["event-reminder/event-reminder", 'eventId' => $model->id, 'isEvent' => true, 'action' => EventReminder::REMINDER]);;
                                            ?>"
                                            class="sendEmail btn btn-primary mt-2"><i class="fa fa-envelope"></i>
                                        <?php echo Yii::t('messages', 'Send Email'); ?> </button>
                                </div>
                            </div>
                            <?php if ($paramKey != EventUser::ATTENDING) { ?>

                                <div class="content-panel-sub">
                                    <div class="panel-head">
                                        <i class="fa fa-envelope"></i> <?php echo Yii::t('messages', 'Send Event Invitation Again'); ?>
                                    </div>

                                    <div class="panel-body">
                                        <?php echo $form->field($eventReminderModel, 'subject')->textInput(array('class' => 'form-control', 'id' => 'subject')); ?>
                                        <button type="primary"
                                                href="<?php echo Url::to(
                                                    ["event-reminder/event-reminder",
                                                        'eventId' => $model->id,
                                                        'isEvent' => true,
                                                        'action' => EventReminder::EVENT_INVITATION]);
                                                ?>"
                                                class="sendEmail btn btn-primary mt-2"><i class="fa fa-envelope"></i>
                                            <?php echo Yii::t('messages', 'Send Email'); ?> </button>
                                    </div>
                                </div>
                                <?php
                            }
                            $form->end();
                        }
                    // endif;
                }
            }
        endif;

        $columns = [
            [
                'format' => 'raw',
                'label' => '',
                'value' => function ($data) {
                    $User = new User();
                    return $User->getPic(null, 30, 30, null, $data['userId']);
                },
            ],
            [
                'format' => 'raw',
                'attribute' => Yii::t('messages', 'firstName'),
                'value' => function ($data) {
                    return $data['firstName'];
                },
            ],
            [
                'format' => 'raw',
                'attribute' => Yii::t('messages', 'lastName'),
                'value' => function ($data) {
                    return $data['lastName'];
                },
            ],
            [
                'format' => 'raw',
                'attribute' => Yii::t('messages', 'email'),
                'value' => function ($data) {
                    return $data['email'];
                },
            ],
            /*[
                'class' => 'yii\grid\ActionColumn',
                'headerOptions' => ['style' => 'text-align: center'],
                'contentOptions' => ['style' => 'text-align: center'],
                'template' => ' {info}  ',
                'buttons' => [
                    'info' => function ($url, $model, $key) {
                        $return = '';
                        if ((Yii::$app->user->checkAccess('MsgBox.ViewInboxMsg'))
                        ) {
                            $return = Html::a(
                                '<span class="fa fa-eye "></span>',
                                Url::to(["event-reminder/view-event-reminder", "eventId" => $model['eventId'],
                                    "rsvpStatus" => $model['rsvpStatus'], "userId" => $model['userId']]),
                                [
                                    'title' => Yii::t('app', 'View Send Emails'),
                                    'class' => 'view'
                                ]
                            );
                        }
                        return $return;
                    },

                ]
            ],*/
            [
                'format' => 'raw',
                'attribute' => Yii::t('messages', 'updatedDateTime'),
                'value' => function ($model) {
                    if (isset($data['updatedDateTime'])) {
                        return EventReminderTracker::convertFRdateTime($model['updatedDateTime']);
                    } else {
                        return "-";
                    }
                }
            ],
            [
                'format' => 'raw',
                'attribute' => Yii::t('messages', 'EmailCount'),
                'value' => function ($data) {
                    return EventReminderTracker::getCount($data['eventId'], $data['userId'], EventReminder::REMINDER);
                },
            ],
            [
                'format' => 'raw',
                'attribute' => Yii::t('messages', 'InviteCount'),
                'value' => function ($data) {
                    return EventReminderTracker::getCount($data['eventId'], $data['userId'], EventReminder::EVENT_INVITATION);
                },
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'header' => Yii::t('messages', 'Confirmation'),
                'headerOptions' => ['style' => 'text-align: center'],
                'contentOptions' => ['style' => 'text-align: center'],
                'template' => ' {absent} {present} ',
                'buttons' => [
                    'absent' => function ($url, $data, $key) {
                        $return = '';
                        if (Yii::$app->user->checkAccess("Event.Update") && intval($data['isParticipate']) == EventUser::ABSENT) {
                            $return = Html::a(
                                '<span class="fa fa-ban "></span>',
                                'javascript:void(0);',
                                [
                                    'title' => Yii::t('app', 'Update Participate'),
                                    'class' => 'modify',
                                    'url' => Url::to(["event-user/modify"]),
                                    'eventId' => $data['eventId'],
                                    'rsvpStatus' => $data['rsvpStatus'],
                                    'user' => $data['userId'],
                                    'q' => '1',

                                ]
                            );
                        }
                        return $return;
                    },
                    'present' => function ($url, $data, $key) {

                        $return = '';
                        if (Yii::$app->user->checkAccess("Event.Update") && intval($data['isParticipate']) == EventUser::PRESENT) {
                            $return = Html::a(
                                '<span class="fa fa-check "></span>',
                                'javascript:void(0);',
                                [
                                    'title' => Yii::t('app', 'Update Participate'),
                                    'class' => 'modify',
                                    'url' => Url::to(["event-user/modify"]),
                                    'eventId' => $data['eventId'],
                                    'rsvpStatus' => $data['rsvpStatus'],
                                    'user' => $data['userId'],
                                    'q' => '0',

                                ]
                            );

                        }
                        return $return;
                    },

                ]
            ],

        ];

        if ($paramKey == EventUser::INVITED || $paramKey == EventUser::NOT_REPLIED) {
            unset($columns[5]);
        }
        if ($paramKey == EventUser::ATTENDING) {
            unset($columns[7]);
        } else {
            unset($columns[8]);
        }
        ?>

        <?php
        $absentConfirmMessage = Yii::t('messages', 'Are you sure this person participate') . ' `' . $model->name . '` ' . Yii::t('messages', 'event?');
        $absentConfirm = Yii::t('messages', 'Are you sure you want to send the email?');


        $this->registerJs("

                $(document).ready(function () { 
                  $(document).on('click', '.modify', function (e){
                     e.preventDefault();
                    var  Url     = $(this).attr('url');
                    
                    var pjaxContainer = $(this).attr('pjax-container');
                    
                    var eventId = $(this).attr('eventId');
                    var rsvpStatus = $(this).attr('rsvpStatus');
                    var user = $(this).attr('user');
                    var q = $(this).attr('q');
                    var trimContainer = $.trim(pjaxContainer);
                    
                    var result = confirm('Are you sure you want to change status of this item?'); 
                               if (result) {
                                $.ajax({
                                  url:   Url,
                                  type:  'post', 
                                 data: {
                                     eventId:eventId,
                                     rsvpStatus:rsvpStatus,
                                     user:user,
                                     q:q,
                                  },
                                  error: function (xhr, status, error) {
                                    console.log('There was an error with your request.' 
                                          + xhr.responseText);
                                  }
                                }).done(function (data) {
                                  $.pjax.reload({container: '#people-grid-pjax'});

//                                    location.reload();
                                });
                              }
                   });
                });
                
                ");


        ?>

        <div class="content-inner">
            <div class="content-area">

                <div class="form-row mb-2">
                    <div class="form-group col-md-12 m-0">
                        <div class="content-panel-sub">
                            <div class="panel-head">
                                <?php echo Yii::t('messages', 'Search by') ?>
                            </div>
                        </div>
                        <div class="search-form" style="display:block">
                            <?php
                            echo Yii::$app->controller->renderPartial('_eventSearch', array(
                                'userModel' => $userModel,
                            ));
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        // echo Html::submitButton('<span class="fa fa-floppy-o "></span> Export ', ['id' => 'export-button', 'class' => 'btn btn-secondary'])
        echo Html::submitButton('<span class="fa fa-floppy-o "></span> ' . Yii::t('messages', 'Export'), ['id' => 'export-button', 'class' => 'btn btn-secondary']);
        ?>

        <?= GridView::widget([
            'id' => 'people-grid',
            'dataProvider' => $members,
            'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => ''],
            'options' => ['class' => 'table-wrap table-custom'],
            'tableOptions' => ['class' => 'table table-striped table-bordered '],
            'summary' => '<div class="text-right table-custom results-count mt-4">' . Yii::t('messages', 'Displaying {begin}-{end} of {count} imports') . '</div>',
            'pager' => [
                'firstPageLabel' => '',
                'firstPageCssClass' => 'first',
                'activePageCssClass' => 'selected active',
                'disabledPageCssClass' => 'hidden',
                'lastPageLabel' => 'last ',
                'nextPageLabel' => '<span aria-hidden="true">&raquo;</span>',
                'nextPageCssClass' => 'page-item next',
                'maxButtonCount' => 5,
                'pageCssClass' => 'page-item',
                'prevPageCssClass' => 'page-item previous',    // Set CSS class for the "previous" page button
                'options' => ['class' => 'pagination justify-content-md-end'],
            ],
            'layout' => '<div class="text-right results-count">{summary}</div>
                        <div class="table-wrap">{items}</div>
                        <div class="row no-gutters d-flex flex-sm-row-reverse flex-sm-column-reverse flex-md-row">
                        <div class="col-md-6"></div>
                        <div class="col-md-6">
                            <div class="float-right"><nav aria-label="Page navigation">{pager}</nav></div>
                        </div></div>',
            'headerRowOptions' => ['class' => 'table-wrap '],
//                'filterRowOptions' => '$data->status == "1" ? "unread" : ""',
            'columns' => $columns
        ]); ?>
        <?php

        /*$this->widget('bootstrap.widgets.TbGridView', array(
            'id' => 'people-grid',
            'type' => 'striped custom hover',
            'htmlOptions' => array('class' => 'table-wrap table-custom'),
            'summaryText' => Yii::t('messages', "Displaying {start}-{end} of {count} results"),
            'pager' => array(
                //'cssFile'=>Yii::app()->theme->baseUrl."/css/pager.css",
                'header' => '',
                'firstPageLabel' => '',
                'prevPageLabel' => '<span  aria-hidden="true">&laquo;</span>',
                'previousPageCssClass' => 'page-item',
                'nextPageLabel' => '<span aria-hidden="true">&raquo;</span>',
                'nextPageCssClass' => 'page-item',
                'lastPageLabel' => '',
                'internalPageCssClass' => 'page-item',
                'htmlOptions' => array('class' => 'pagination justify-content-md-end'),
            ),
            'template' => '<div class="text-right results-count">{summary}</div>
                    <div class="table-wrap">{items}</div>
                    <div class="row no-gutters d-flex flex-sm-row-reverse flex-sm-column-reverse flex-md-row">
                    <div class="col-md-6"></div>
                    <div class="col-md-6">
                        <div class="float-right"><nav aria-label="Page navigation">{pager}</nav></div>
                    </div></div>',
            'dataProvider' => $members,
            'columns' => $columns,
        ));*/
        ?>
        <?php Pjax::end(); ?>

    </div>
</div>

<div class="modal fade" id="eventEmail" tabindex="-1" role="dialog" aria-labelledby="guideVideo" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content col-md-12 mb-3">
            <div class="modal-header">
                <h5 class="modal-title"
                    id="exampleModalCenterTitle"> <?php echo Yii::t('messages', 'Email Templates'); ?></h5>
                <button type="button" id="guide-video-close" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="eventContent">
                <!-- content here -->
            </div>
        </div>
    </div>
</div>



