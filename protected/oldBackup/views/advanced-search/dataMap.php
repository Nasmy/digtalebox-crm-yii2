<?php

use app\components\ToolKit;
use app\models\User;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use rmrevin\yii\fontawesome\FA;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\View;

echo Yii::$app->toolKit->registerAdvanceSearchScript();
$this->title = Yii::t('messages', 'Map View');
$this->titleDescription = Yii::t('messages', 'Search people in map view');
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'People'), 'url' => ['#']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Map View')];
?>

<?php ToolKit::registerDataOsmMapScript(); ?>
<?php $markerImg = Yii::$app->toolKit->getMarkerImage();
$searchType = User::SEARCH_EXCLUDE;
$keywordNull = Yii::t('messages', 'Keywords or Exclude keywords cannot be empty');
$keywordExcludeSameValue = Yii::t('messages', 'Keywords or Exclude keywords cannot contain same value');
$url = Yii::$app->urlManager->createUrl(['advanced-search/grid-update-map']);
$urlDataMapView = Yii::$app->urlManager->createUrl(['advanced-search/grid-update-map-view']);

$js = <<<JS
        $(document).on('click', '.search-button', function () {
                $('.search-form').toggle();
                return false;
        });
        $(document).on('click', '.pagination-btn', function(event) {
          $("#page").val($(this).html());
          $.pjax.submit(event, '#people-map-grid')
        })
        $(document).on('submit', '.search-form form', function(e){
            e.preventDefault();
            var inputs = $('.search-form form :input');
            var values = {};
            inputs.each(function () {
                values[this.name] = $(this).val();
            });
            var searchType = values["User[searchType]"];
            var keywords = values["User[keywords][]"];
            var keywordsExclude = values["User[keywordsExclude][]"];
            

            if(searchType == $searchType){
                // alert(keywordsExclude);
                if(keywords == ''){
                 alert('{$keywordNull}');
                 return false;
                }
                else if(keywordsExclude == ''){
                 alert('{$keywordNull}');
                 return false;
                }
                else {
                    var res = keywords.filter(function(el) {
                        return keywordsExclude.indexOf(el) != -1
                      });
                    if(res.length != 0){
                    alert('{$keywordExcludeSameValue}');
                    return false;
                    }
                }
            }
            var dataarray = $('.search-form form').serialize();
            $(".loader").show();
            $.ajax({
                url: '{$url}',
                type: 'post',
                data: {data: dataarray},
                success: function(response) {
                    $('.search-map-grid').html(response);
                    return false;
                }
            });
            $.ajax({
                url: '{$urlDataMapView}',
                type: 'post',
                data: {data: dataarray},
                success: function(response) {
                     $('.search-map-view').html(response);
                     $(".loader").hide();
                    return false;
                }
            });
            return false;
        });
JS;
$this->registerJs($js);
?>
<script>
    function loadUpdate(url) {
        $('#updateDetailsMap').modal({backdrop: 'static'})
        $("#iframe-updateDetailsMap").attr("src", url);
        resizeIframe('iframe-updateDetailsMap');
        return false;
    }

    $('#updateDetailsMap').on('hidden.bs.modal', function (e) {
        $(".modal-backdrop:eq(1)").remove();
        $(".modal-backdrop").hide();
    });

    function resizeIframe(iframeID) {
        var iframe = window.parent.document.getElementById(iframeID);
        var container = document.getElementById('content');
        iframe.style.height = container.offsetHeight + 'px';
    }
</script>
<div id="statusMsg"></div>
<?php echo Yii::$app->controller->renderPartial('_tabMenu'); ?>
<div class="row no-gutters map-view">
    <div class="content-panel col-md-12">
        <div class="content-inner">
            <div class="content-area">
                <?php \yii\widgets\Pjax::begin(); ?>
                <div class="form-row mb-2">
                    <div class="form-group col-md-12">
                        <div class="content-panel-sub">
                            <div class="panel-head">
                                <?php echo Yii::t('messages', 'Search by') ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="search-form map-view-form" style="display:block">
                    <?= $this->render('_searchDataMap', array('model' => $model, 'tagList' => $keywords, 'lat' => $lat, 'lon' => $lon, 'markersLongLat' => $markersLongLat, 'osmMaxLimit' => $osmMaxLimit, 'osmCanProceed' => $osmCanProceed)); ?>
                </div>
                <div class="col-md-12">
                    <div class="float-right">
                        <nav aria-label="Page navigation">
                            <div class="pagination">

                            </div>
                        </nav>
                    </div>
                </div>

                <div id="color-palette"></div>
                <div class="loader" style="display:none"></div>
                <div class="search-map-view" style="display:block">
                    <form id="mapview">
                        <div id="map_canvas" style="width:100%;height:600px;"></div>
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
                    <script>
                        window.onload = function () {
                            drawMap();
                        };
                        let drawMap = () => {
                            let osmCanProceed;
                            let osmMaxLimit = <?php echo $osmMaxLimit?>;
                            <?php if ($osmCanProceed) { ?> osmCanProceed = true;
                            <?php }?>;
                            let mapLayer = MQ.mapLayer(), map;
                            let data = markersLongLat =<?php echo $markersLongLat; ?>;
                            let msg1 = '<?php echo addslashes(Yii::t('messages', 'Please create a zone.')) ?>';
                            let msg2 = '<?php echo addslashes(Yii::t('messages', 'You are allowed to create one zone at a time.')) ?>';
                            let shapeArray = [];
                            let teamZoneData = {};
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
                                    var url = '<?php echo Yii::$app->urlManager->createUrl(['/advanced-search/load-map-info-window']) . '?id='; ?>' + userId;
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
                        }

                        let findPos = (obj) => {
                            var curtop = 0;
                            if (obj.offsetParent) {
                                do {
                                    curtop += obj.offsetTop;
                                } while (obj = obj.offsetParent);
                                return [curtop];
                            }
                        }

                    </script>
                    <div id="legend"></div>
                    <br/>
                    <style>
                        #legend {
                            background: white;
                            padding: 10px;
                        }
                    </style>
                </div>
                <div class="search-map-grid" style="display:block">
                    <?= $this->render('_gridmap', array('model' => $model, 'dataProvider' => $dataProvider, 'mapView' => false, 'gridId' => 'people-map-grid')); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- START Create Zone Modal -->
<div class="modal fade" id="createZone" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id=""><?php echo Yii::t('messages', 'Create Zone'); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <iframe id="iframe-createZone" src="" frameborder="0" scrolling="auto" width="100%"
                            height="240px"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).on("click", ".ajaxMapDelete", function (e) {
        e.preventDefault();
        var deleteUrl = $(this).attr('delete-url');
        var pjaxContainer = $(this).attr('pjax-container');
        var result = confirm("<?=Yii::t('messages', "Are you sure you want to delete user?")?>");
        if (result) {
            $.ajax({
                url: deleteUrl,
                type: "post",
                data: {
                    YII_CSRF_TOKEN: '<?=Yii::$app->request->csrfToken?>'
                },
                success: function (data) {
                    $("#statusMsg").html(data);
                    refreshDeleteSearchMapGrid(deleteUrl);
                    return false;

                }
            });
        }
    });

    function refreshDeleteSearchMapGrid(url) {
        var prameters = url.split('?');
        var filters = [];
        $(".filter:checked").each(function () {
            filters.push($(this).val());
        });
        var criteriaId = $("#criteriaId option:selected").val();
        var url = '<?=Yii::$app->urlManager->createUrl(['advanced-search/grid-update-map'])?>?' + prameters[1];
        var dataArray = $('.search-form form').serialize();
        $.post(url, {filters: filters, data: dataArray, criteriaId: criteriaId},
            function (returnedData) {
                if (returnedData) {
                    $('.search-map-grid', window.parent.document).html(returnedData);
                    return false;
                }
            });
    }

</script>
<!-- END Create Zone Modal -->
