<?php

use app\models\AdBannerData;
use app\models\Configuration;
use app\models\User;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var yii\web\View $this */
/* @var app\models\Event $model */
/* @var yii\widgets\ActiveForm $form */

Yii::$app->toolKit->registerTinyMceScripts();
$langs = Yii::$app->toolKit->getComponenetSpecificLangIdentifier('tinyMce');
$newContent = Url::to(['event/get-template-content', 'fileName' => 'event-template', 'eventID' => $model->id, 'tempDescription' => $model->description]);
$lang = false;
switch ($langs) {
    case 'en-Us':
        $lang = false;
        break;
    case 'fr_FR':
        $lang = 'fr_FR';
        break;
}
$endTimeValue = (!empty($model->endTime) ? $model->endTime : "");
$startTimeValue = (!empty($model->startTime) ? $model->startTime : "");

// TinyMCE Scripts
$selectorTinymce = 'textarea#' . Html::getInputId($model, "description");
$base_url = Yii::$app->request->getBaseUrl() . "/index.php/tiny-mice/upload";
$scriptTinymce = <<< JS
	tinymce.init({
		language : '{$lang}',
        selector:'{$selectorTinymce}',
        image_dimensions: true,
        automatic_uploads: true,
        plugins: [
			'advlist autolink lists link image charmap print preview anchor',
			'searchreplace visualblocks code fullscreen',
			'image code',
			'insertdatetime media table contextmenu paste'
		], 
		theme:'modern',
		relative_urls : false,
		remove_script_host : false,
		convert_urls : true,
		images_upload_url: '$base_url',
		images_upload_handler: function (blobInfo, success, failure) {
        var xhr, formData;
        xhr = new XMLHttpRequest();
        xhr.withCredentials = false;
        xhr.overrideMimeType("application/json");
        xhr.open('POST', '$base_url', true);
        xhr.onload = function() {
          var json;
    
          if (xhr.status != 200) {
            failure('HTTP Error: ' + xhr.status);
            return;
          }
           json = JSON.parse(xhr.responseText);
          if (!json || typeof json.location != 'string') {
            failure('Invalid JSON: ' + xhr.responseText);
            return;
          }
          success('/images/'+json.location);
        };
        formData = new FormData();
        formData.append('file', blobInfo.blob(), blobInfo.filename());
        xhr.send(formData);
      },
		// override default upload handler to simulate successful upload
        setup: function (ed) {
          ed.on('init', function (e) {
            $.ajax({ 
                type: 'GET', 
                url: '{$newContent}', 
                success: function(data){ 
                    ed.setContent(data); 
                } 
            });
            
          });
         },
         toolbar: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
         verify_html : false,
	});
JS;

$this->registerJs($scriptTinymce);

$mapStyle = Yii::$app->toolKit->getMapStyle(Yii::$app->session['themeStyle']);
Yii::$app->toolKit->registerDataOsmMapScript();

?>

<style>
    .mce-fullscreen {
        z-index: 1050;
    }
</style>

<div class="event-form">

    <?php $form = ActiveForm::begin([
        'id' => 'event-form',

        'options' => [
            'enctype' => 'multipart/form-data',
            'class' => 'form-horizontal',
            'method' => 'post',
            'enableAjaxValidation' => true,
            'validateOnSubmit' => true,
        ],

    ]); ?>

    <?php
    $hint = Yii::t('messages', "Maximum width:") . AdBannerData::MAX_IMG_WIDTH . 'px, ';
    $hint .= Yii::t('messages', "Maximum height:") . AdBannerData::MAX_IMG_HEIGHT . 'px, ';
    $hint .= Yii::t('messages', "Maximum size:") . ceil(AdBannerData::MAX_SIZE / 1024) . 'Kb';
    $calLang = Yii::$app->toolKit->getComponenetSpecificLangIdentifier('juiDateTimePicker');
    $attributeLabels = $model->attributeLabels();
    ?>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <?php
                echo $form->field($model, 'name')->textInput(array('class' => 'form-control', 'maxlength' => 45, 'placeholder' => $attributeLabels['name'], 'label' => false));
                ?>
            </div>
        </div>

        <?php if (is_null($model->imageName)): ?>
            <div class="form-group col-md-6 m-0">
                <?php echo $form->field($model, 'imageFile')->fileInput(array('class' => 'form-control ', 'placeholder' => $attributeLabels['imageFile'], 'maxlength' => 45, 'label' => false));
                echo '<div class="form-feild-info">' . $hint . '</div>'; ?>
            </div>
        <?php endif; ?>

        <?php if (!is_null($model->imageName)): ?>
            <div class="form-group m-0 col-md-3">
                <?php
                echo $form->field($model, 'imageFile')->fileInput(array('class' => 'form-control m-0', 'placeholder' => $attributeLabels['imageFile'], 'maxlength' => 45, 'label' => false));
                echo '<div class="form-feild-info">' . $hint . '</div>';
                ?>
            </div>
            <div class="form-group col-md-3">
                <label for="CandidateInfo_volunteerBgImageFile">&nbsp;</label>
                <div class="control-group ">
                    <div class="controls">
                        <?= Html::submitButton(Yii::t('messages', 'Remove Image'), ['class' => 'btn btn-primary','name' => 'removeImage']); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="col-md-12">
            <div class="form-group">
                <?php echo $form->field($model, 'location')->textInput(array('class' => 'form-control', 'placeholder' => Yii::t('messages', "Event Location"), 'label' => false));
                ?>
                <?php echo $form->field($model, 'locationMapCordinates')->hiddenInput()->label(false); ?>
            </div>

            <div class="event-map mb-4">
                <div id="map_canvas" style="height: 100%"></div>
                <?php if (!$osmCanProceed) { ?>
                    <div>
                        <p class="error">
                            <b>
                                <?= Yii::t('messages', 'You have reach your map contacts limits, however all extra contacts are available in the database, you may upgrade your subscribtion or reach out to us for more informations contact@digitalebox.com') ?>
                            </b>
                        </p>
                    </div>
                <?php } ?>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <div class="input-group date" id="from-date" data-target-input="nearest">
                    <?php
                    $errorMsg = Yii::t('messages', "Selected event end time is Invalid");
                    $errorMsgEventDate = Yii::t('messages', "Please Select Event Date");
                    echo $form->field($model, 'startDate')->widget(yii\jui\DatePicker::className(), [
                        'dateFormat' => 'yyyy-MM-dd',
                        'language' => '',
                        'clientOptions' => [
                            'format' => 'yyyy-mm-dd',
                            'minDate' => User::convertDBTime(),
                            'changeYear' => true,
                            'type' => 'date',
                        ],
                        'options' => [
                            'readonly' => true,
                            'placeholder' => 'Created At',
                            'class' => 'form-control datetimepicker-input',
                            'onClose' => 'js:function( selectedDate ) {
                                 
                            var endTimeVal = $("#endTime").val(); 
                            var startTimeVal = $("#startTime").val();
                            
                            if (endTimeVal != "" && startTimeVal != "") { 
                                var diff = ( new Date(selectedDate+" "+endTimeVal) - new Date(selectedDate+" "+startTimeVal) ) / 1000 / 60 / 60;
                            
                                if (diff <= 0) { 
                                    alert("' . $errorMsg . '"); 
                                } 
                            }
                            
                            if (selectedDate != "" ) { 
                                $("#' . Html::getInputId($model, 'endDate') . '").datetimepicker( "option", "minDate", selectedDate );
                                
                                } 
                            }',
                        ],

                    ])->label($attributeLabels['date']);
                    ?>
                </div>
            </div>
        </div>


        <div class="col-md-3">
            <div class="form-group">
                <label class="control-label"
                       for="<?php echo Html::getInputId($model, 'startTime'); ?>"><?php echo $attributeLabels['startTime']; ?>
                </label>

                <div class="input-group date" id="startTime" data-target-input="nearest">
                    <input type="text" class="form-control datetimepicker-input"
                           name="Event[startTime]" data-target="#startTime" onkeydown="return false"
                           value="<?php echo $startTimeValue; ?>"
                           placeholder="<?php echo $attributeLabels['startTime']; ?>"
                    />
                    <div class="input-group-append" data-target="#startTime" data-toggle="datetimepicker">
                        <div class="input-group-text"><i class="fa fa-clock-o"></i></div>
                        <?php echo $form->field($model, 'startTime')->hiddenInput()->label(false); ?>
                    </div>
                </div>
            </div>
        </div>


        <div class="col-md-3">
            <div class="form-group">
                <label class="control-label"
                       for="<?php echo Html::getInputId($model, 'endTime'); ?>"><?php echo $attributeLabels['endTime']; ?>
                </label>
                <div class="input-group date" id="endTime" data-target-input="nearest">
                    <input type="text" class="form-control datetimepicker-input"
                           name="Event[endTime]" data-target="#endTime" onkeydown="return false"
                           value="<?php echo $endTimeValue; ?>"
                           placeholder="<?php echo $attributeLabels['endTime']; ?>"/>
                    <div class="input-group-append" data-target="#endTime" data-toggle="datetimepicker">
                        <div class="input-group-text"><i class="fa fa-clock-o"></i></div>
                        <?php echo $form->field($model, 'endTime')->hiddenInput()->label(false); ?>
                    </div>
                </div>
            </div>
        </div>


        <div class="col-md-12">
            <div class="form-group">
                <?php echo $form->field($model, 'description')->textarea(array('rows' => 8, 'cols' => 50,
                    'class' => 'form-control tinyMCE', 'hint' => Yii::t('messages', 'Provide the event details inside the editor box')));
                ?>
            </div>
        </div>


        <div class="col-md-6">
            <div class="form-group">
                <?php
                echo $form->field($model, 'priority')->dropDownList($model->fillDropDown('priority'), array('class' => 'form-control'));
                ?>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <?php
                if (Yii::$app->user->checkAccessList(array('Event.Accept', 'Event.Reject'))) {
                    echo $form->field($model, 'comments')->textarea(array('rows' => 2, 'cols' => 50, 'class' => 'form-control'));
                } else {
                    echo $form->field($model, 'comments')->textarea(array('class' => 'form-control', 'readonly' => true));
                }
                ?>
            </div>
        </div>
    </div>

    <div class="form-row text-left text-md-right">

        <div class="form-group col-md-12">
            <?= Html::submitButton(Yii::t('messages', 'Save'), ['class' => 'btn btn-success']) ?>
            <?= Html::a(Yii::t('messages', 'Cancel'), '/event/admin', ['class' => 'btn btn-secondary btn btn-info']) ?>
        </div>

    </div>
    <?php
    ActiveForm::end();
    $conf = new Configuration();
    $timeZone = $conf->getTimeZone();
    ?>

</div>

<script>
    $(document).ready(function () {
        let date;
        const startDate = $("#event-startdate").val();
        const endTime = $("#event-endtime").val();

        const startTime = $("#event-starttime").val();

        let defaultStartTime = new Date().toLocaleString('en-US', {timeZone: '<?php echo $timeZone; ?>'});
        if (startTime != '') {

            date = moment(startDate + ' ' + startTime);

            defaultStartTime = date;

        }
        let defaultEndTime = new Date().toLocaleString('en-US', {timeZone: '<?php echo $timeZone; ?>'});
        if (endTime != '') {
            date = moment(startDate + ' ' + endTime);

            defaultEndTime = date;

        }

        $('#startTime').datetimepicker({
            defaultDate: defaultStartTime,
            format: 'HH:mm',
        });

        $('#endTime').datetimepicker({
            defaultDate: defaultEndTime,
            format: 'HH:mm',
        });
    });

</script>


<script>

    let latLng = [<?php  echo $model->locationMapCordinates ?>];
     console.log(latLng);
    let markerImage = '<?= Yii::$app->toolKit->getMarkerImage() ?>';

    let satelliteLayer, mapLayer, map, m, myIcon;

    let osmCanProceed;

    let osmMaxLimit = <?php echo $osmMaxLimit ?>;

    <?php if ($osmCanProceed) { ?> osmCanProceed = true;
    <?php } ?>;

    window.onload = function () {

        mapLayer = MQ.mapLayer();

        satelliteLayer = MQ.satelliteLayer();

        myIcon = L.icon({

            iconUrl: markerImage,

            iconSize: [29, 24],

            iconAnchor: [9, 21],

            popupAnchor: [0, -14]

        });

        let baseMaps = {

            "Street Map": mapLayer,

            "Satellite View": satelliteLayer

        };


        map = L.map('map_canvas', {

            center: latLng,

            layers: mapLayer,

            zoom: 10,

            fullscreenControl: true,

            fullscreenControlOptions: {

                position: 'topleft'

            }

        });


        L.control.layers(baseMaps, {}, {position: 'topright'}).addTo(map);


        m = L.marker(latLng, {draggable: osmCanProceed}).addTo(map);


        m.on('dragend', () => {

            updateForm(m.getLatLng());

        });

    };

    $("#<?= Html::getInputId($model, 'location') ?>").blur(function () {

        codeAddress();

    });

    ;

    $('#save').click(function () {

        let latLang = m.getLatLng();

        $('#longLat').val(latLang.lat + ',' + latLang.lng);

    });

    let updateForm = (latLng) => {

        $('#<?= Html::getInputId($model, 'locationMapCordinates') ?>').val(latLng.lat + ',' + latLng.lng);

    }

    let updateMarker = (latLng) => {

        map.panTo(latLng);

        m.setLatLng(latLng);

    };

    let codeAddress = () => {

        let address = $('#<?=Html::getInputId($model, 'location') ?>').val();

        MQ.geocode()

            .search(address)

            .on('success', (d) => {

                updateMarker(d.result.best.latlng);

                updateForm(d.result.best.latlng);

            });

    }

</script>
