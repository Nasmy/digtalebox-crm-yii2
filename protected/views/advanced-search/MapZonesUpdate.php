<?php
use app\components\ToolKit;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
$this->title = Yii::t('messages', 'Update Map Zone');
$this->titleDescription = Yii::t('messages', 'Update your Map zone');
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Map Zone'), 'url' => ['advanced-search/all-map-zones']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Update')];
Yii::$app->toolKit->setJsFlash();
$message1 = addslashes(Yii::t('messages', 'Please create a zone.'));
$message2 = addslashes(Yii::t('messages', 'You are allowed to create one zone at a time.'));
$markerImg = Yii::$app->toolKit->getMarkerImage();
ToolKit::registerDataOsmMapScript();
echo Yii::$app->toolKit->registerAdvanceSearchScript();
?>

<script>
    let osmCanProceed;
    let osmMaxLimit = <?php echo $osmMaxLimit?>;
    <?php if ($osmCanProceed) { ?> osmCanProceed = true; <?php }?>;
    let noZoneError = '<?php echo addslashes(Yii::t('messages', 'Please create a zone.')) ?>';
    let multipleZoneError = '<?php echo addslashes(Yii::t('messages', 'You are allowed to create one zone at a time.')) ?>';
    let teamZones = <?php echo $teamZoneData; ?>;

    window.onload = () => {
        drawMap();
    };

    let drawMap = () => {
        let mapLayer = MQ.mapLayer(), osmMap;
        let drawnItems = new L.FeatureGroup();
        let drawPluginOptions = {
            position: 'topright',
            draw: {
                polygon: {
                    allowIntersection: false, // Restricts shapes to simple polygons
                    drawError: {
                        color: '#e1e100', // Color the shape will turn when intersects
                        message: '<strong>Oh snap!<strong> you can\'t draw that!' // Message that will show when intersect
                    },
                    shapeOptions: {
                        color: '#97009c'
                    }
                },
                // disable toolbar item by setting it to false
                polyline: false,
                circle: false, // Turns off this drawing tool
                rectangle: false,
                marker: false,
            },
            edit: {
                featureGroup: {},
                remove: true
            }
        };

        osmMap = L.map('osmMap', {
            layers: mapLayer,
            center: [ <?php echo $lat; ?>, <?php echo $long; ?> ],
            zoom: 6
        });

        osmMap.addLayer(drawnItems);

        teamZones.forEach((d) => {
            if (d.hasOwnProperty('coordinates')) {
                let polygon = L.polygon(d.coordinates);
                drawnItems.addLayer(polygon);
                drawPluginOptions.edit.featureGroup = drawnItems;
                if (osmCanProceed) {
                    osmMap.addControl(new L.Control.Draw(drawPluginOptions));
                }
            }

        });
        if (osmCanProceed) {
            osmMap.on(L.Draw.Event.CREATED, function (event) {
                let layer = event.layer;
                drawnItems.addLayer(layer);
            });
        }

        osmMap.on('draw:created', function(e) {
            let type = e.layerType,
                layer = e.layer;

            if (type === 'marker') {
                layer.bindPopup('A popup!');
            }

            drawnItems.addLayer(layer);
        });

        osmMap.on('draw:deleted', function(e) {
            let layers = e.layers;
            layers.eachLayer(function(feature) {
                drawnItems.eachLayer(function() {
                    if (drawnItems.hasLayer) {
                        drawnItems.removeLayer(feature);
                    }
                });
            });
        });

        $(document).on('click', '#save', function(){

            let data = drawnItems.toGeoJSON();
            let convertedData = JSON.parse(JSON.stringify(data));
            let shapeArray = convertedData.features;
            if (shapeArray.length == 0) {
                setJsFlash('error', noZoneError);
                return false;
            } else if (shapeArray.length > 1) {
                setJsFlash('error', multipleZoneError);
                return false;
            } else {
                let coordinates = shapeArray[0].geometry.coordinates[0];
                coordinates = coordinates.map((d) => {
                    return [d[1],d[0]];
                });
                $('#teamZoneData').val(JSON.stringify([{coordinates: coordinates}]));
                return true;
            }

        });
    };
    let setJsFlash = (type, message) => {
        type = 'alert alert-' + type;
        let msgStr  = '<div id=\"flash-inner\" class=\"' + type +'\">';
        msgStr += '<button class=\"close\" data-dismiss=\"alert\" type=\"button\">×</button>';
        msgStr += message;
        msgStr += '</div>';

        $('#statusMsg').html(msgStr);
        jQuery('html, body').animate({scrollTop:0}, 'slow');
    }
</script>
<div id="message">
</div>
<div class="row no-gutters map-view">
    <div class="content-panel col-md-12">
        <div class="content-inner">
            <div class="content-area">
                <?php
                $form = ActiveForm::begin(['action'=>'', 'id' => 'team-zone-form','class' => 'form-vertical','enableAjaxValidation' => false,]);
                ?>
                <div class="row no-gutters">
                    <div class="form-group col-md-4">
                        <?php echo Html::activeLabel($model,'title'); ?>
                        <?php echo $form->field($model, 'title')->textInput(['class' => 'form-control', 'maxlength' => 45,'disabled' => 'disabled'])->label(false); ?>
                    </div>
                </div>

                <div class="row flex-column-reverse flex-sm-row mt-3">

                    <div class="col-sm-6 col-xl-7 mb-2">
                        <div id="color-palette" style=""></div>

                    </div>
                    <div class="col-sm-6 col-xl-5 mb-2">
                        <?=html::activeHiddenInput($model, 'teamZoneData', array('value'=>$teamZoneData, 'id'=>'teamZoneData'));?>
                    </div>

                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="map-layout">
                            <div id="osmMap" style="height: 100%"></div>
                            <?php
                            if (!$osmCanProceed) { ?>
                                <div>
                                    <p class="error" style="padding: 2px">
                                        <b>
                                            <?=Yii::t('messages', 'You have reach your map contacts limits, however all extra contacts are available in the database, you may upgrade your subscribtion or reach out to us for more informations contact@digitalebox.com')?>
                                        </b>
                                    </p>
                                </div>

                            <?php } ?>
                        </div>
                    </div>
                </div>
                <div class="row no-gutters mt-5">
                    <div class="col-md-12">
                        <div class="form-group text-left text-md-right">
                            <?= Html::submitButton($model->isNewRecord ? Yii::t('messages', 'Create') : Yii::t('messages', 'Save'), ['type'=>'submit','class' => 'btn btn-primary','id'=>'save']); ?>
                            <?= Html::tag('a',Yii::t('messages', 'Cancel'), ['href' => Yii::$app->urlManager->createUrl(['advanced-search/all-map-zones']),'class' => 'btn btn-info']); ?>
                        </div>
                    </div>
                </div>
                <?php ActiveForm::end(); ?>

            </div>
        </div>
    </div>

</div>
