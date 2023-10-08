<?php

use yii\bootstrap\Alert;
use yii\jui\Dialog;

/* @var yii\web\View $this */
/* @var app\models\EventSearch $searchModel */
/* @var yii\data\ActiveDataProvider $dataProvider */

$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Admin'), 'url' => ['#']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Manage Events')];


$this->title = Yii::t('messages', 'Manage Events');
$this->titleDescription = Yii::t('messages', 'Events created by users');

// Get osm information
$mapStyle = Yii::$app->toolKit->getMapStyle(Yii::$app->session['themeStyle']);
Yii::$app->toolKit->registerDataOsmMapScript();
$markerImg = Yii::$app->toolKit->getMarkerImage();
?>

<?php
Yii::$app->toolKit->getFullCalendarScripts();
$urlEvents = Yii::$app->urlManager->createUrl('/event/showEvents');
$urlViewEvent = Yii::$app->urlManager->createUrl('/event/view');
$urlEditEvent = Yii::$app->urlManager->createUrl('/event/update');
$script = <<< JS
	$(document).on('submit', '.search-form form', function() {
	$('#calendar').fullCalendar( 'refetchEvents' );	
		return false;
	});
JS;
$this->registerJs($script);
?>
<script>
    $(document).ready(function () {
        var startDate = $('#Event_startDate').val();
        var defaultDate = moment().format('YYYY-MM-DD');
        if (startDate != '') {
            defaultDate = startDate;
        }
        // Full calendar
        $('#calendar').fullCalendar({
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,listWeek,listDay'
            },
            dayNamesShort: [
                '<?php echo Yii::t('messages', 'Sun'); ?>',
                '<?php echo Yii::t('messages', 'Mon'); ?>',
                '<?php echo Yii::t('messages', 'Tue'); ?>',
                '<?php echo Yii::t('messages', 'Wed'); ?>',
                '<?php echo Yii::t('messages', 'Thu'); ?>',
                '<?php echo Yii::t('messages', 'Fri'); ?>',
                '<?php echo Yii::t('messages', 'Sat'); ?>',
            ],
            monthNames: [
                '<?php echo Yii::t('messages', 'January'); ?>',
                '<?php echo Yii::t('messages', 'February'); ?>',
                '<?php echo Yii::t('messages', 'March'); ?>',
                '<?php echo Yii::t('messages', 'April'); ?>',
                '<?php echo Yii::t('messages', 'May'); ?>',
                '<?php echo Yii::t('messages', 'June'); ?>',
                '<?php echo Yii::t('messages', 'July'); ?>',
                '<?php echo Yii::t('messages', 'August'); ?>',
                '<?php echo Yii::t('messages', 'September'); ?>',
                '<?php echo Yii::t('messages', 'October'); ?>',
                '<?php echo Yii::t('messages', 'November'); ?>',
                '<?php echo Yii::t('messages', 'December'); ?>'
            ],

            // customize the button names,
            // otherwise they'd all just say "list"
            views: {
                month: {buttonText: '<?php echo Yii::t('messages', 'Month'); ?>'},
                listDay: {buttonText: '<?php echo Yii::t('messages', 'Day'); ?>'},
                listWeek: {buttonText: '<?php echo Yii::t('messages', 'Week'); ?>'},
                // today: {buttonText: '<?php echo Yii::t('messages', 'Today'); ?>'}
            },
            defaultDate: defaultDate,
            contentHeight: 'auto',
            navLinks: true, // can click day/week names to navigate views
            editable: true,
            eventLimit: true, // allow "more" link when too many events
            events: function (start, end, timezone, callback) {
                $.ajax({
                    url: '<?php echo Yii::$app->urlManager->createUrl(['event/calendar-events']); ?>',
                    dataType: 'json',
                    type: 'get',
                    data: $('.search-form form').serialize() + '&start=' + start.unix() + '&end=' + end.unix(),
                    success: function (data) {
                        var events = data;
                        callback(events);
                    }
                });
            },
            editable: true,
            lazyFetching: false,
            timeFormat: 'h(:mm)t',
            selectable: true,
            eventClick: function (calEvent, jsEvent, view) {
                $.ajax({
                    type: 'POST',
                    url: '<?php echo $urlViewEvent; ?>/' + calEvent.id,
                    data: 'ajax=true',
                    success: function (data) {
                        $('#eventContent').html(data);
                        $('#exampleModalCenterTitle').html("<?php echo Yii::t('messages', 'Event Details : '); ?>" + calEvent.title);
                        $('#eventView').modal('show');
                    }
                });
            },
        });
        var todayButtons = document.getElementsByClassName('fc-today-button');
        todayButtons[0].textContent = "<?php echo Yii::t('messages', 'Today'); ?>";
    });
</script>
<div class="row no-gutters">
    <div class="content-panel col-md-12">
        <div class="content-inner">
            <div class="content-area pb-5">
                <?php echo $this->render('_search', ['model' => $model]); ?>
                <div class="row mt-5 mb-3">
                    <div class="col-md-12">
                        <div id='calendar'></div>
                    </div>
                </div>
                <div id="myMenu"></div>
                <?php
                foreach ($model->fillDropDown('priority') as $key => $priority) {

                    if ($key != -1) {
                        echo Alert::widget([
                            'options' => [
                                //'closeButton' => false,
                                'style' => " text-align: center;margin: 0px 3px;float: left;min-width: 6%;background-color:" . $model->eventPriority($key) . "; font-weight: bold; color:white; padding:2px; border-radius:2px;",
                                'class' => "show"
                            ],
                            'closeButton' => false,
                            'body' => $priority,
                        ]);
                    }
                }
                Dialog::begin([
                    'id' => 'eventlist-dialog',
                    'clientOptions' => [
                        'title' => Yii::t('messages', 'Date'),
                        'autoOpen' => false,
                        'modal' => true,
                        'width' => 800,
                        'height' => 400,
                        'dialogClass' => 'ui-dialog-event-list',
                        'close' => 'js:function (event, ui) {
                $("#eventlist-frame").attr("src", "");
            }',
                    ],
                ]);
                echo '<iframe id="eventlist-frame" frameborder="0" scrolling="auto" width="100%" height="100%"></iframe>';
                Dialog::end();
                ?>
            </div>
        </div>
    </div>
    <div class="modal fade" id="eventView" tabindex="-1" role="dialog" aria-labelledby="guideVideo" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content col-md-12 mb-3">
                <div class="modal-header">
                    <h5 class="modal-title text-center w-100"
                        id="exampleModalCenterTitle"></h5>
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
</div>
