<?php
header('Content-Type:text/html; charset=UTF-8');
use app\components\ToolKit;
use yii\helpers\Html;
ToolKit::registerDataOsmMapScript();
$markerImg = Yii::$app->toolKit->getMarkerImage();
$markersLongLat=json_encode($markersLongLat);
?>
<form id="mapview">
    <div id="map_canvas" style="width:100%;height:600px;">
    </div>
    <?php
    if (!$osmCanProceed) { ?>
        <div>
            <p class="error">
                <b>
                    <?= Yii::t('messages', 'You have reach your map contacts limits, however all extra contacts are available in the database, you may upgrade your subscribtion or reach out to us for more informations contact@digitalebox.com') ?>
                </b>
            </p>
        </div>
    <?php } ?>
</form>
<?= html::activeHiddenInput($modelMapZone, 'teamZoneData', array('value' => $modelMapZone->teamZoneData, 'id' => 'teamZoneData')); ?>
<?= html::error($modelMapZone, 'teamZoneData'); ?>
<?= html::activeHiddenInput($modelMapZone, 'searchFormData', array('value' => $modelMapZone->teamZoneData, 'id' => 'searchFormData')); ?>
<?= Html::submitButton(Yii::t('messages', 'Save selected zone'), ['type' => 'link', 'encodeLabel' => false, 'size' => 'mini', 'class' => 'btn btn-primary', 'id' => 'save', 'style' => 'margin-top: 5px;']); ?>
<div id="legend"></div>
<script>

             var osmCanProceed;
             var osmMaxLimit = <?php echo $osmMaxLimit?>;
            <?php if ($osmCanProceed) { ?> osmCanProceed = true;
            <?php }?>;
             var mapLayer = MQ.mapLayer(), map;
             var data = markersLongLat =<?php echo $markersLongLat; ?>;
             var msg1 = '<?php echo addslashes(Yii::t('messages', 'Please create a zone.')) ?>';
             var msg2 = '<?php echo addslashes(Yii::t('messages', 'You are allowed to create one zone at a time.')) ?>';
             var shapeArray = [];
             var teamZoneData = {};
            console.log(osmCanProceed);
            console.log(osmMaxLimit);
            map = L.map('map_canvas', {
                layers: mapLayer,
                center: [ <?php echo $lat; ?>, <?php echo $lon; ?> ],
                zoom: 3
            });
            var myIcon = L.icon({
                iconUrl: '/themes/bootstrap_spacelab/img/markerman.png',
                // iconRetinaUrl: '/themes/bootstrap_spacelab/img/markerman.png',
                iconSize: [29, 24],
                iconAnchor: [9, 21],
                popupAnchor: [0, -14]
            });

            var markerClusters = new L.markerClusterGroup({
                showCoverageOnHover: false,
            });

            if (osmMaxLimit >= data.length) {
                for (var i = 0; i < data.length; i++) {
                    var dataUser = data[i];
                    var m = L.marker([dataUser.latitude, dataUser.longitude], {icon: myIcon});
                    m.bindPopup(get_content_map(m, dataUser.id))
                    markerClusters.addLayer(m);
                }
            } else {
                for (var i = 0; i < osmMaxLimit; i++) {
                    var dataUser = data[i];
                    var m = L.marker([dataUser.latitude, dataUser.longitude], {icon: myIcon});
                    m.bindPopup(get_content_map(m, dataUser.id))
                    markerClusters.addLayer(m);
                }
            }

            function get_content_map(marker, userId) {
                marker.on('click', function (e) {
                    var popup = e.target.getPopup();
                    var url = '<?php echo  Yii::$app->urlManager->createUrl(['/advanced-search/load-map-info-window']) . '?id='; ?>' + userId;
                    $.get(url).done(function (data) {
                        popup.setContent(data);
                        popup.update();
                    });
                });
            }

            map.addLayer(markerClusters);
            var drawnItems = new L.FeatureGroup();
            map.addLayer(drawnItems);
            var drawPluginOptions = {
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
                    featureGroup: drawnItems,
                    remove: true
                }
            };

            map.on('zoomend', function (event) {
                // console.log(event); // This will keep for future developments
            });

            map.on(L.Draw.Event.CREATED, function (event) {
                var layer = event.layer;

                drawnItems.addLayer(layer);
            });

            // Initialise the draw control and pass it the FeatureGroup of editable layers
            if (osmCanProceed) {
                let drawControl = new L.Control.Draw(drawPluginOptions);
                map.addControl(drawControl);
            }
            // create Polygon layer
            map.on('draw:created', function (e) {
                var type = e.layerType,
                    layer = e.layer;

                if (type === 'marker') {
                    layer.bindPopup('A popup!');
                }

                drawnItems.addLayer(layer);
            });

            // delete Polygon layer
            map.on('draw:deleted', function (e) {
                var layers = e.layers;
                layers.eachLayer(function (feature) {
                    drawnItems.eachLayer(function () {
                        if (drawnItems.hasLayer) {
                            drawnItems.removeLayer(feature);
                        }
                    });
                });
            });

            $(document).on('click', '#save', function () {
                var data = '';
                data = drawnItems.toGeoJSON();
                var convertedData = JSON.parse(JSON.stringify(data));
                var shapeArray = convertedData.features;
                if (shapeArray.length == 0) {
                    setJsFlash('error', msg1);
                    return false;
                } else if (shapeArray.length > 1) {
                    setJsFlash('error', msg2);
                    return false;
                } else {
                    let coordinates = shapeArray[0].geometry.coordinates[0];
                    coordinates = coordinates.map((d) => {
                        return [d[1], d[0]];
                    });
                    $('#teamZoneData').val(JSON.stringify([{coordinates: coordinates}]));
                    $('#searchFormData').val($('#map-form').serialize()); //todo: only if form is submit
                    $('#createZone').modal({backdrop: 'static'});
                    $("#iframe-createZone").attr("src", '<?php echo Yii::$app->urlManager->createUrl(['advanced-search/map-zone-create']);?>');
                    $('#createZone').on('hidden.bs.modal', function (e) {
                        $("#iframe-createZone").attr("src", "")
                    });
                    return false;
                }

            });
            function setJsFlash(type, message) {
                type = 'alert alert-' + type;
                var msgStr = '<div id=\"flash-inner\" class=\"' + type + '\">';
                msgStr += '<button class=\"close\" data-dismiss=\"alert\" type=\"button\">×</button>';
                msgStr += message;
                msgStr += '</div>';

                $('#statusMsg').html(msgStr);
                jQuery('html, body').animate({scrollTop: 0}, 'slow');
            }


</script>
<br/>
