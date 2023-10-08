<?php
$this->tabMenuPath = 'application.views.advancedSearch._tabMenu';
$this->title = Yii::t('messages', 'Map View');
$this->titleDescription = Yii::t('messages', 'Search people in map view');
$this->breadcrumbs = array(
    Yii::t('messages', 'People') => array('admin'),
    Yii::t('messages', 'Map View'),
);
?>
<?php ToolKit::registerDataMapScript(); ?>
<?php $markerImg = Yii::app()->toolKit->getMarkerImage(); ?>
<script>
    function loadUpdate(url){
        $('#updateDetails').modal({backdrop:'static'})
       $("#iframe-updateDetails").attr("src",url);
        resizeIframe('iframe-updateDetails');
        return false;


    }
    $('#updateDetails').on('hidden.bs.modal', function (e) {
        $(".modal-backdrop:eq(1)").remove();
        $(".modal-backdrop").hide();
    });

    function resizeIframe(iframeID)
    {
        var iframe = window.parent.document.getElementById(iframeID);
        var container = document.getElementById('content');
        iframe.style.height = container.offsetHeight + 'px';
    }
</script>
<?php

Yii::app()->clientScript->registerScript('search', "
$(document).on('click', '.search-button', function(){
	$('.search-form').toggle();
	return false;
});
		
$(document).on('submit', '.search-form form', function(){

	var inputs = $('.search-form form :input');
    var values = {};
    inputs.each(function() {
        values[this.name] = $(this).val();
    });

	var searchType = values[\"User[searchType]\"];
	var keywords = values[\"User[keywords][]\"];
	var keywordsExclude = values[\"User[keywordsExclude][]\"];

	if(searchType == ".User::SEARCH_EXCLUDE."){
		if(keywords == null){
		 alert('".Yii::t('messages', 'Keywords or Exclude keywords cannot be empty')."');
		 return false;
		}
		else if(keywordsExclude == null){
		 alert('".Yii::t('messages', 'Keywords or Exclude keywords cannot be empty')."');
		 return false;
		}
		else {
			var res = keywords.filter(function(el) {
				return keywordsExclude.indexOf(el) != -1
			  });
			if(res.length != 0){
			alert('".Yii::t('messages', 'Keywords or Exclude keywords cannot contain same value')."');
			return false;
			}
		}
	}
	$.fn.yiiGridView.update('people-map-grid', {
		data: $(this).serialize()
	});
	$.fn.yiiGridView.update('map_canvas', {
		data: $(this).serialize()
	});
	return false;
});");
?>
<div class="row no-gutters map-view">
    <div class="content-panel col-md-12">
        <div class="content-inner">
            <div class="content-area">

                <div class="form-row mb-2">
                    <div class="form-group col-md-12">

                    </div>
                </div>

                <div class="content-panel-sub">
                    <div class="panel-head">
                        <?php echo Yii::t('messages','Search by') ?>
                    </div>
                </div>
                <div class="search-form" style="display:block">
                    <?php $this->renderPartial('_searchDataMap', array(
                        'model' => $model,'tagList' => $keywords
                    )); ?>
                </div><!-- search-form -->
                <div class="col-md-12">
                    <div class="float-right">
                        <nav aria-label="Page navigation">
                            <div class="pagination">
                                <?php $this->widget('bootstrap.widgets.TbPager', array(
                                    'pages' => $pages,
                                    'displayFirstAndLast' => true,
                                   // 'htmlOptions' => array('class' => 'pagination justify-content-md-end'),
                                )); ?>
                            </div>
                        </nav>
                    </div>
                </div>
                <div id="color-palette"></div>
                <div id="map_canvas" style="width:100%;height:780px;"></div>
                <?php echo CHtml::activeHiddenField($modelMapZone, 'teamZoneData', array('value'=>$modelMapZone->teamZoneData, 'id'=>'teamZoneData'));?>
                <?php echo CHtml::error($modelMapZone, 'teamZoneData');?>
                <?php echo CHtml::activeHiddenField($modelMapZone, 'searchFormData', array('value'=>$modelMapZone->teamZoneData, 'id'=>'searchFormData'));?>
                <?php $this->widget('bootstrap.widgets.TbButton', array(
                    'buttonType'=>'button',
                    //'type'=>'primary',
                    'size'=>'mini',
                    'htmlOptions' => array('id'=>'delete-shape', 'style'=>'margin-top: 5px;', 'class'=>"btn btn-danger"),
                    'label'=>Yii::t('messages','Delete selected zone'),
                )); ?>

                <?php $this->widget('bootstrap.widgets.TbButton', array(
                    'buttonType'=>'link',
                    'type'=>'primary',
                    'size'=>'mini',
                    'htmlOptions' => array('id'=>'save', 'style'=>'margin-top: 5px;'),
                    'label'=>Yii::t('messages','Save selected zone'),
                    'url'=>Yii::app()->createUrl('AdvancedSearch/MapZoneCreate/'),
                )); ?>
                <div id="legend"></div>

                <br/>

                <script>
                    var colors = {
                        "ANAR": "#000000",
                        "EXG": "#BB0000",
                        "LO": "#BB0000",
                        "NPA": "#BB0000",
                        "BC-ANAR": "#000000",
                        "BC-EXG": "#BB0000",
                        "BC-LO": "#BB0000",
                        "BC-NPA": "#BB0000",
                        "FG": "#DD0000",
                        "BC-FG": "#DD0000",
                        "PCF": "#DD0000",
                        "BC-PCF": "#DD0000",
                        "COM": "#DD0000",
                        "BC-COM": "#DD0000",
                        "PG": "#DD0000",
                        "MRC": "#CC6666",
                        "ND": "#CC6666",
                        "VEC": "#00C000",
                        "EELV": "#00C000",
                        "CAP": "#77FF77",
                        "DVE": "#77FF77",
                        "PS": "#FF8080",
                        "BC-PG": "#DD0000",
                        "BC-MRC": "#CC6666",
                        "BC-ND": "#CC6666",
                        "BC-VEC": "#00C000",
                        "BC-EELV": "#00C000",
                        "BC-CAP": "#77FF77",
                        "BC-DVE": "#77FF77",
                        "BC-PS": "#FF8080",
                        "SOC": "#FF8080",
                        "BC-SOC": "#FF8080",
                        "PRG": "#FFD1DC",
                        "BC-PRG": "#FFD1DC",
                        "RDG": "#FFD1DC",
                        "BC-RDG": "#FFD1DC",
                        "DVG": "#FFC0C0",
                        "BC-DVG": "#FFC0C0",
                        "DVG2": "#FFC0C0",
                        "MDM": "#FF9900",
                        "UC": "#74C2C3",
                        "NC": "#00FFFF",
                        "UDI": "#00FFFF",
                        "BC-DVG2": "#FFC0C0",
                        "BC-MDM": "#FF9900",
                        "BC-UC": "#74C2C3",
                        "BC-NC": "#00FFFF",
                        "BC-UDI": "#00FFFF",
                        "DVD": "#ADC1FD",
                        "BC-DVD": "#ADC1FD",
                        "DVD2": "#ADC1FD",
                        "BC-DVD2": "#ADC1FD",
                        "UMP": "#0066CC",
                        "BC-UMP": "#0066CC",
                        "PR": "#0066CC",
                        "DLR": "#8040C0",
                        "MPF": "#8040C0",
                        "PP": "#8040C0",
                        "FN": "#C0C0C0",
                        "SP": "#C0C0C0",
                        "EXD": "#404040",
                        "DIV": "#F0F0F0",
                        "DIV2": "#F0F0F0",
                        "DLF": "#CCC",
                        "BC-PR": "#0066CC",
                        "BC-DLR": "#8040C0",
                        "BC-MPF": "#8040C0",
                        "BC-PP": "#8040C0",
                        "BC-FN": "#C0C0C0",
                        "BC-SP": "#C0C0C0",
                        "BC-EXD": "#404040",
                        "BC-DIV": "#F0F0F0",
                        "BC-DIV2": "#F0F0F0",
                        "BC-DLF": "#CCC",
                        "UD": "#ADC1FD",
                        "BC-UD": "#ADC1FD",
                        "UG": "#FFC0C0",
                        "BC-UG": "#FFC0C0",
                        "egal": "#fff",
                        "BLANC": "#fff"
                    };
                    accentsTidy = function (s) {
                        var r = s.toLowerCase();
                        r = r.replace(new RegExp("[àáâãäå]", 'g'), "a");
                        r = r.replace(new RegExp("æ", 'g'), "ae");
                        r = r.replace(new RegExp("ç", 'g'), "c");
                        r = r.replace(new RegExp("[èéêë]", 'g'), "e");
                        r = r.replace(new RegExp("[ìíîï]", 'g'), "i");
                        r = r.replace(new RegExp("ñ", 'g'), "n");
                        r = r.replace(new RegExp("[òóôõö]", 'g'), "o");
                        r = r.replace(new RegExp("œ", 'g'), "oe");
                        r = r.replace(new RegExp("[ùúûü]", 'g'), "u");
                        r = r.replace(new RegExp("[ýÿ]", 'g'), "y");
                        var fr = r.toUpperCase();
                        return fr;
                    };

                    var mapOptions = {
                        zoom: 3,
                        center: {lat: <?php echo $lat; ?>, lng: <?php echo $lon; ?>},
                        mapTypeId: google.maps.MapTypeId.ROADMAP
                    };
                    var map;
                    var bkp_feature;
                    var layer_region;
                    var layer_region_isvisible = false;
                    var layer_departement;
                    var layer_departement_isvisible = true;
                    var layer_canton;
                    var layer_canton_isvisible = false;
                    var istoggle = false;

                    var infowindow;
                    var data = <?php echo $markersLongLat; ?>;
                    var geocoder = new google.maps.Geocoder();
                    var markerImg = "<?php echo $markerImg; ?>";
                    function initMap() {
                        map = new google.maps.Map(document.getElementById('map_canvas'), mapOptions);
                        addRegion();
                        addDepartement();

                        var oms = new OverlappingMarkerSpiderfier(map, {
                            markersWontMove: true,
                            markersWontHide: true,
                            keepSpiderfied: true
                        });

                        var infoWindow = new google.maps.InfoWindow();

                        oms.addListener('click', function (marker, event) {
                            load_content(map, marker, infoWindow);
                        });
                        oms.addListener('spiderfy', function (markers) {
                            infoWindow.close();
                        });

                        var markers = [];
                        for (var i = 0; i < data.length; i++) {
                            var dataUser = data[i];
                            var latLng = new google.maps.LatLng(dataUser.latitude,
                                dataUser.longitude);
                            var marker = new google.maps.Marker({
                                position: latLng,
                                labelClass: 'marker-labels',
                                icon:markerImg
                            });
                            marker.desc = dataUser.id;
                            oms.addMarker(marker);
                            markers.push(marker);
                        }

                        var markerCluster = new MarkerClusterer(map, markers, {
                            imagePath: 'https://cdn.rawgit.com/googlemaps/js-marker-clusterer/gh-pages/images/m'
                        });
                        markerCluster.setMaxZoom(14);

                        //////////////// TURF CUT ///////////////////

                        var msg1 = '<?php echo addslashes(Yii::t('messages', 'Please create a zone.')) ?>';
                        var msg2 = '<?php echo addslashes(Yii::t('messages', 'You are allowed to create one zone at a time.')) ?>';
                        var drawingManager;
                        var selectedShape;
                        var colors = ['#1E90FF', '#FF1493', '#32CD32', '#FF8C00', '#4B0082'];
                        var colorsId = ['blue', 'red', 'green', 'yellow', 'purple'];
                        //var colors = ['#32CD32'];
                        var selectedColor;
                        var colorButtons = {};
                        var shapeArray = [];
                        var teamZoneData = {};

                        function clearSelection() {
                            if (selectedShape) {
                                selectedShape.setEditable(false);
                                selectedShape = null;
                            }
                        }

                        function setJsFlash(type, message) {
                            type = 'alert alert-' + type;
                            var msgStr  = '<div id=\"flash-inner\" class=\"' + type +'\">';
                            msgStr += '<button class=\"close\" data-dismiss=\"alert\" type=\"button\">×</button>';
                            msgStr += message;
                            msgStr += '</div>';

                            $('#statusMsg').html(msgStr);
                            jQuery('html, body').animate({scrollTop:0}, 'slow');
                        }

                        function setSelection(shape) {
                            clearSelection();
                            selectedShape = shape;
                            shape.setEditable(true);
                            selectColor(shape.get('fillColor') || shape.get('strokeColor'));
                        }

                        function deleteSelectedShape() {
                            if (selectedShape) {
                                selectedShape.setMap(null);
                                deleteShape(selectedShape.id);
                            }
                        }

                        function selectColor(color) {
                            selectedColor = color;
                            for (var i = 0; i < colors.length; ++i) {
                                var currColor = colors[i];
                                colorButtons[currColor].style.border = currColor == color ? '2px solid #789' : '2px solid #fff';
                            }

                            // Retrieves the current options from the drawing manager and replaces the
                            // stroke or fill color as appropriate.
                            var polylineOptions = drawingManager.get('polylineOptions');
                            polylineOptions.strokeColor = color;
                            drawingManager.set('polylineOptions', polylineOptions);

                            var rectangleOptions = drawingManager.get('rectangleOptions');
                            rectangleOptions.fillColor = color;
                            drawingManager.set('rectangleOptions', rectangleOptions);

                            var circleOptions = drawingManager.get('circleOptions');
                            circleOptions.fillColor = color;
                            drawingManager.set('circleOptions', circleOptions);

                            var polygonOptions = drawingManager.get('polygonOptions');
                            polygonOptions.fillColor = color;
                            polygonOptions.strokeColor = color;
                            drawingManager.set('polygonOptions', polygonOptions);
                        }

                        function setSelectedShapeColor(color) {
                            if (selectedShape) {
                                if (selectedShape.type == google.maps.drawing.OverlayType.POLYLINE) {
                                    selectedShape.set('strokeColor', color);
                                } else {
                                    selectedShape.set('fillColor', color);
                                    selectedShape.set('strokeColor', color);
                                }
                            }
                        }

                        function makeColorButton(color,currColorId) {
                            var li = document.createElement('li');
                            var div = document.createElement('div');
                            div.setAttribute('id',currColorId);
                            div.className = 'color-box';
                            //div.style.backgroundColor = color;
                            li.appendChild(div);
                            google.maps.event.addDomListener(li, 'click', function () {
                                selectColor(color);
                                setSelectedShapeColor(color);
                            });

                            return li;
                        }

                        function buildColorPalette() {
                            var colorPalette = document.getElementById('color-palette');
                            var ul = document.createElement('ul');
                            ul.setAttribute('class','zone-colors pull-left');
                            colorPalette.appendChild(ul);
                            for (var i = 0; i < colors.length; ++i) {
                                var currColor = colors[i];
                                var currColorId = colorsId[i];
                                var colorButton = makeColorButton(currColor,currColorId);
                                ul.appendChild(colorButton);
                                colorButtons[currColor] = colorButton;
                            }
                            selectColor(colors[0]);
                        }

                        var polyOptions = {
                            strokeWeight: 1,
                            fillOpacity: 0.45,
                            editable: true
                        };
                        // Creates a drawing manager attached to the map that allows the user to draw
                        // markers, lines, and shapes.
                        drawingManager = new google.maps.drawing.DrawingManager({
                            drawingMode: null,//google.maps.drawing.OverlayType.POLYGON,
                            markerOptions: {
                                draggable: true
                            },
                            polylineOptions: {
                                editable: true
                            },
                            drawingControlOptions: {
                                position: google.maps.ControlPosition.TOP_CENTER,
                                drawingModes: [
                                    google.maps.drawing.OverlayType.POLYGON,
                                ]
                            },
                            rectangleOptions: polyOptions,
                            circleOptions: polyOptions,
                            polygonOptions: polyOptions,
                            map: map
                        });

                        google.maps.event.addListener(drawingManager, 'overlaycomplete', function (e) {
                            if (e.type != google.maps.drawing.OverlayType.MARKER) {
                                // Switch back to non-drawing mode after drawing a shape.
                                drawingManager.setDrawingMode(null);

                                // Add an event listener that selects the newly-drawn shape when the user
                                // mouses down on it.
                                var newShape = e.overlay;
                                newShape.type = e.type;
                                newShape.id = getShapeId();
                                google.maps.event.addListener(newShape, 'click', function () {
                                    setSelection(newShape);
                                });
                                shapeArray.push(newShape);
                                setSelection(newShape);
                            }
                        });

                        // google.maps.event.addListener(drawingManager, 'overlaycomplete', function(polygon) {
                        // shapeArray.push(polygon);
                        // });

                        // Clear the current selection when the drawing mode is changed, or when the
                        // map is clicked.
                        google.maps.event.addListener(drawingManager, 'drawingmode_changed', clearSelection);
                        google.maps.event.addListener(map, 'click', clearSelection);
                        buildColorPalette();
                        drawShapes();

//        google.maps.event.addDomListener(window, 'load', initialize);

                        function getShapeId() {
                            return Math.floor(Math.random() * 1000);
                        }

                        function deleteShape(id) {
                            var tmpShapeArray = [];
                            $.each(shapeArray, function(index, objShape){
                                if (id != objShape.id) {
                                    tmpShapeArray.push(objShape);
                                }
                            });

                            shapeArray = tmpShapeArray;
                        }

                        function drawShapes() {
                            if ('' != teamZoneData) {
                                for (var i in teamZoneData) {
                                    var parsedCoordinates = parseCoordinates(teamZoneData[i].coordinates);
                                    var color = teamZoneData[i].color;
                                    var id = teamZoneData[i].id;
                                    //console.log(parsedCoordinates);
                                    // Construct the polygon.
                                    var newShape = new google.maps.Polygon({
                                        paths: parsedCoordinates,
                                        strokeWeight: 1,
                                        fillOpacity: 0.45,
                                        fillColor: color,
                                        strokeColor: color,
                                        strokeOpacity: 0.8,
                                    });

                                    newShape.type = 'polygon';
                                    newShape.id = id;

                                    google.maps.event.addListener(newShape, 'click', function() {
                                        setSelection(newShape);
                                    });

                                    newShape.setMap(map);

                                    shapeArray.push(newShape);
                                    setSelection(newShape);
                                }
                            }
                        }

                        function parseCoordinates(coordinates) {
                            var latLngs = [];

                            for (var i in coordinates) {
                                latLngs[i] = new google.maps.LatLng(coordinates[i][0], coordinates[i][1]);
                            }

                            return latLngs;
                        }

                        // Button click envents goes here

                        $('#save').click(function(){
                            if (shapeArray.length == 0) {
                                setJsFlash('error', msg1);
                                return false;
                            } else if (shapeArray.length > 1) {
                                setJsFlash('error', msg2);
                                return false;
                            } else {
                                console.log(shapeArray); return false;
                                var shapeObjects = new Array();
                                $.each(shapeArray, function(index, objPolygon){
                                    var shapeData = new Object();
                                    var strCoordinates = Array();
                                    shapeData.id = objPolygon.id;
                                    shapeData.color = objPolygon.get('fillColor');
                                    var polygonBounds = objPolygon.getPath();
                                    polygonBounds.forEach(function(xy, i) {
                                        var lat = xy.lat();
                                        var lon = xy.lng();
                                        lonLat = new Array(lat, lon);
                                        strCoordinates.push(lonLat);
                                    });
                                    shapeData.coordinates = strCoordinates;
                                    shapeObjects.push(shapeData);
                                });

                                $('#teamZoneData').val(JSON.stringify(shapeObjects));
                                $('#searchFormData').val($('#map-form').serialize()); //todo: only if form is submit
                                $('#createZone').modal({backdrop: 'static'});
                                $("#iframe-createZone").attr("src",'<?php echo Yii::app()->createUrl('/AdvancedSearch/MapZoneCreate') ?>');
                                $('#createZone').on('hidden.bs.modal', function (e) {
                                    $("#iframe-createZone").attr("src", "")
                                });
                                return false;
                            }
                        });

                        $('#delete-shape').click(function(){
                            deleteSelectedShape();
                        });

                        //////////////////// POPUP BOX //////////////////////// fancybox-frame1489666231354

                        function openMsgBox(url) {
                            $.fancybox.open({
                                padding : 10,
                                href:url,
                                type: 'iframe',
                                width: 500,
                                height: 250,
                                transitionIn: 'elastic',
                                transitionOut: 'elastic',
                                autoSize: false,
                                "tpl" : {
                                    iframe   : '<iframe id="receiver" name="fancybox-myFancyboxFrame" class="fancybox-iframe" frameborder="0" vspace="0" hspace="0"' + ($.browser.msie ? ' allowtransparency="true"' : '') + '></iframe>'
                                }
                            });
                            return false;
                        };

                        //////////////// EVENT LISTENERS /////////////////////
                        map.addListener('zoom_changed', function () {
                            if (istoggle == true) {
                                if (map.getZoom() >= 8 && map.getZoom() < 9) {
                                    // if(layer_canton_isvisible) { layer_canton.setMap(null); }
                                    layer_region.setMap(null);
                                    layer_region_isvisible = false;
                                    layer_departement.setMap(map);
                                    layer_departement_isvisible = true;
                                } else if (map.getZoom() < 7) {
                                    //        if(layer_canton_isvisible) { layer_canton.setMap(null); }
                                    /*
                                     layer_region.setMap(map);
                                     layer_region_isvisible = true;
                                     layer_departement.setMap(null);
                                     layer_departement_isvisible = false;
                                     */
                                }
                            }
                        });


                        layer_departement.addListener('click', function (event) {
                            $.getJSON('<?php Yii::app()->toolKit->registerGeo('/departements_tour2.json'); ?>', function (data) {
                                contentString = '<div id="content">' +
                                    '<div id="siteNotice">' +
                                    '</div>' +
                                    '<h1 id="firstHeading" class="firstHeading">' + event.feature.getProperty('NOM_DEPT') + '</h1><p>Départementales 2015 (2eme tour)</p>' +
                                    '<div id="bodyContent"><ul style="list-style-type: none;">';

                                headstring = "";
                                cString = "";
                                $.each(data, function (i, v) {
                                    if (v['LibDpt'] == event.feature.getProperty('NOM_DEPT')) {
                                        var nb_elus = 0;
                                        winner_name = "";
                                        winner_rapporExp = "";
                                        arr = v['Resultats']['NuancesBin']['NuanceBin'];
                                        arr.sort(function (a, b) {
                                            return a['NbVoix'] - b['NbVoix'];
                                        });
                                        arr.reverse();
                                        $.each(arr, function (ii, vv) {
                                            if (nb_elus < parseInt(vv['NbElus'])) {
                                                winner = vv['CodNuaBin'];
                                                nb_elus = vv['NbElus'];
                                                winner_name = vv['LibNuaBin'];
                                                winner_rapportExp = vv['RapportExprime'];
                                            }
                                            cString += "<li><strong>" + vv['LibNuaBin'] + " : " + vv['NbElus'] + " Elus (" + vv['RapportExprime'] + "%)</strong></li>";
                                            cString += '<li><div style="display:inline-block;width:' + Math.round(parseFloat(vv['RapportExprime'])) + 'px;height:10px;background-color:' + colors[vv['CodNuaBin']] + ';"></div></li>';
                                        });
                                        headstring += "<li>" + winner_name + " : " + nb_elus + " Elus (" + winner_rapportExp + "%)</li>";
                                        headstring += '<li><div style="display:inline-block;width:' + Math.round(parseFloat(winner_rapportExp)) + 'px;height:10px;background-color:' + colors[winner] + ';"></div></li>';


                                    }

                                });
                                //  contentString += headstring;
                                contentString += '<hr />';
                                contentString += cString;
                                contentString += '</ul></div>';
                                contentString += '<a onclick="addCanton(' + event.feature.getProperty('CODE_DEPT') + ')">See details</a></div>';
                                var infowindow = new google.maps.InfoWindow({
                                    content: contentString
                                });
                                infowindow.open(map, markers[0]);
                                clearMarkers();
                                var center = new google.maps.LatLng(event.latLng.lat(), event.latLng.lng());
                                map.panTo(center);
                                addMarker(center, infowindow);
                                map.setZoom(8);
                                var tot = 0;
                                var tot_exprime = 0;
                                var votes_exprimes = [];

                                /* $.each(data[accentsTidy(event.feature.getProperty('NOM_DEPT'))].scores, function (index, value) {
                                 votes_exprimes.push({ parti: index, score: value});
                                 tot += parseInt(value,10);
                                 if( index != "ABSTENTION" && index != "NUL" && index != "BLANC") {
                                 tot_exprime += parseInt(value, 10);
                                 }
                                 });

                                 votes_exprimes = votes_exprimes.map(function(d){
                                 d.ratio = (100 * d.score / tot_exprime).toFixed(1);
                                 return d;
                                 }).sort(function(a, b){
                                 return b.score - a.score;
                                 });
                                 headstring = "";
                                 cString = "";
                                 $.each(votes_exprimes, function (i,value) {
                                 var isWinner    = (value.score === data[accentsTidy(event.feature.getProperty('NOM_DEPT'))].winner.score);
                                 v = (parseInt(value,10)*100)/tot_exprime;
                                 if( value.parti == "ABSTENTION" || value.parti == "NUL" || value.parti == "BLANC") {
                                 headstring += '<li>'+value.parti+" ("+value.score+" voix)</li>";
                                 } else {
                                 if(value.ratio >= 5) {
                                 cString += '<li>';
                                 cString += isWinner ? '<strong>' : '';
                                 cString += value.parti+" : "+value.ratio +"% ("+value.score+" voix)</li>";
                                 cString += isWinner ? '</strong>' : '';
                                 cString += '<li><div style="display:inline-block;width:'+value.ratio+';height:10px;background-color:'+colors[value.parti]+';"></div></li>';
                                 }
                                 }
                                 });
                                 contentString += headstring;
                                 contentString += '<hr />';
                                 contentString += cString;
                                 contentString += '</ul></div>';
                                 contentString += '<a onclick="addCanton('+event.feature.getProperty('CODE_DEPT')+')">See details</a></div>';
                                 var infowindow = new google.maps.InfoWindow({
                                 content: contentString
                                 });
                                 infowindow.open(map, markers[0]);
                                 clearMarkers();
                                 var center = new google.maps.LatLng(event.latLng.lat(),event.latLng.lng());
                                 map.panTo(center);
                                 addMarker(center,infowindow);
                                 map.setZoom(8);
                                 */
                            });


                        });

                        function load_content(map, marker, infoWindow) {
                            $.ajax({
                                url: '<?php echo Yii::app()->createUrl('/AdvancedSearch/LoadMapInfoWindow') .'/'; ?>' + marker.desc,
                                success: function (data) {
                                    infoWindow.setContent(data);
                                    infoWindow.open(map, marker);
                                }
                            });
                        }

                        layer_region.addListener('click', function (event) {
                            //console.log(event.feature);
                            var d = new Object();
                            $.getJSON('<?php Yii::app()->toolKit->registerGeo('/regions.json'); ?>', function (data) {
                                contentString = '<div id="content">' +
                                    '<div id="siteNotice">' +
                                    '</div>' +
                                    '<h1 id="firstHeading" class="firstHeading">' + event.feature.getProperty('nom') + '</h1>' +
                                    '<div id="bodyContent"><ul style="list-style-type: none;">';
                                var tot = 0;
                                var tot_exprime = 0;
                                var votes_exprimes = [];
                                $.each(data[accentsTidy(event.feature.getProperty('nom'))].scores, function (index, value) {
                                    votes_exprimes.push({parti: index, score: value});
                                    tot += parseInt(value, 10);
                                    if (index != "ABSTENTION" && index != "NUL" && index != "BLANC") {
                                        tot_exprime += parseInt(value, 10);
                                    }
                                });
                                votes_exprimes = votes_exprimes.map(function (d) {
                                    d.ratio = (100 * d.score / tot_exprime).toFixed(1);
                                    return d;
                                }).sort(function (a, b) {
                                    return b.score - a.score;
                                });
                                headstring = "";
                                cString = "";
                                $.each(votes_exprimes, function (i, value) {
                                    var isWinner = (value.score === data[accentsTidy(event.feature.getProperty('nom'))].winner.score);
                                    v = (parseInt(value, 10) * 100) / tot_exprime;
                                    if (value.parti == "ABSTENTION" || value.parti == "NUL" || value.parti == "BLANC") {
                                        headstring += '<li>' + value.parti + " (" + value.score + " voix)</li>";
                                    } else {
                                        if (value.ratio >= 5) {
                                            cString += '<li>';
                                            cString += isWinner ? '<strong>' : '';
                                            cString += value.parti + " : " + value.ratio + "% (" + value.score + " voix)</li>";
                                            cString += isWinner ? '</strong>' : '';
                                            cString += '<li><div style="display:inline-block;width:' + value.ratio + ';height:10px;background-color:' + colors[value.parti] + ';"></div></li>';
                                        }
                                    }
                                });
                                contentString += headstring;
                                contentString += '<hr />';
                                contentString += cString;
                                contentString += '</ul></div>';

                                contentString += '<a onclick="todept()">See details</a></div>';
                                var infowindow = new google.maps.InfoWindow({
                                    content: contentString
                                });
                                infowindow.open(map, markers[0]);
                                clearMarkers();
                                var center = new google.maps.LatLng(event.latLng.lat(), event.latLng.lng());
                                map.panTo(center);
                                addMarker(center, infowindow);
                                map.setZoom(7);

                            });
                        });
                    }

                    //////////FIN INIT///////////////////
                    var markers = [];
                    function addMarker(location, infowindow) {
                        var marker = new google.maps.Marker({
                            position: location,
                            map: map
                        });
                        markers.push(marker);
                        infowindow.open(map, markers[0]);

                    }
                    function setMapOnAll(map) {
                        for (var i = 0; i < markers.length; i++) {
                            markers[i].setMap(map);
                        }
                    }
                    function clearMarkers() {
                        setMapOnAll(null);
                        markers = [];
                    }
                    function todept() {
                        clearMarkers();
                        map.setZoom(8);

                    }

                    function insertionSort(arr) {
                        console.log(arr);
                        for (i = 1; i < arr.length; i++) {
                            var tmp = arr[i],
                                j = i;
                            while (j > 0 && arr[j - 1]['NbVoix'] > tmp['NbVoix']) {
                                arr[j] = arr[j - 1];
                                --j;
                            }
                            arr[j] = tmp;
                        }
                        console.log(arr);
                        return arr;
                    }

                    function addRegion() {
                        layer_region = new google.maps.Data();
                        layer_region.loadGeoJson('<?php Yii::app()->toolKit->registerGeo('/regions.geojson'); ?>');
                        $.getJSON('<?php Yii::app()->toolKit->registerGeo('/regions.json'); ?>', function (data) {
                            layer_region.setStyle(function (feature) {
                                var final_col = colors[data[accentsTidy(feature.getProperty('nom'))].winner.parti];
                                return {
                                    fillColor: final_col,
                                    fillOpacity: 0.8,
                                    strokeWeight: 1
                                };
                            });
                        });

                    }

                    function addDepartement() {
                        layer_departement = new google.maps.Data();
                        layer_departement.loadGeoJson('<?php Yii::app()->toolKit->registerGeo('/departements.geojson'); ?>');
                        $.getJSON('<?php Yii::app()->toolKit->registerGeo('/departements_tour2.json'); ?>', function (data) {
                            layer_departement.setStyle(function (feature) {
                                var winner = "";
                                $.each(data, function (i, v) {
                                    if (v['LibDpt'] == feature.getProperty('NOM_DEPT')) {
                                        var nb_elus = 0;
                                        $.each(v['Resultats']['NuancesBin']['NuanceBin'], function (ii, vv) {
                                            if (nb_elus < parseInt(vv['NbElus'])) {
                                                winner = vv['CodNuaBin'];
                                                nb_elus = vv['NbElus'];
                                            }
                                        });
                                    }

                                });
                                var final_col = colors[winner];
                                return {
                                    fillColor: final_col,
                                    fillOpacity: 0.8,
                                    strokeWeight: 1
                                };

                            });
                        });
                    }

                    function addCanton(id) {

                        layer_canton = new google.maps.Data();
                        $.ajax({
                            url: '<?php echo Yii::app()->createUrl('/AdvancedSearch/Url'); ?>',
                            type: "POST",
                            data: 'id=' + id,
                            success: function (rez) {
                                r = rez.split('::');
                                layer_canton.loadGeoJson(r[1]);
                                $.getJSON(r[2], function (data) {
                                    layer_canton.setStyle(function (feature) {
                                        var winner = "";
                                        $.each(data['Cantons'], function (i, v) {
                                            if (v['CodCan'] == feature.getProperty('CT')) {
                                                var nb_elus = 0;
                                                $.each(v['Resultats'], function (ii, vv) {
                                                    if (nb_elus < parseInt(vv['NbVoix'])) {
                                                        winner = vv['CodNuaBin'];
                                                        nb_elus = vv['NbVoix'];
                                                    }
                                                });
                                            }

                                        });

                                        var final_col = colors[winner];
                                        return {
                                            fillColor: final_col,
                                            fillOpacity: 0.8,
                                            strokeWeight: 1
                                        };

                                    });
                                });
                            }
                        });
                        clearMarkers();
                        map.setZoom(9);

                        layer_canton.setMap(map);
                        layer_canton_isvisible = true;
                        layer_region.setMap(null);
                        layer_region_isvisible = false;
                        layer_departement.setMap(null);
                        layer_departement_isvisible = false;

                        layer_canton.addListener('click', function (event) {

                            $.ajax({
                                url: '<?php echo Yii::app()->createUrl('/AdvancedSearch/Url'); ?>',
                                type: "POST",
                                data: 'id=' + id,
                                success: function (rez) {
                                    r = rez.split('::');
                                    $.getJSON(r[2], function (data) {
                                        //console.log(event.feature);
                                        $.each(data['Cantons'], function (i, v) {
                                            contentString = "";
                                            cString = "";
                                            headstring = "";

                                            if (v['CodCan'] == event.feature.getProperty('CT')) {
                                                contentString = '<div id="content">' +
                                                    '<div id="siteNotice">' +
                                                    '</div>' +
                                                    '<h1 id="firstHeading" class="firstHeading">' + v['LibCan'] + '</h1><p>Départementales 2015 (2eme tour)</p>' +
                                                    '<div id="bodyContent"><ul style="list-style-type: none;">';

                                                var nb_elus = 0;
                                                winner_name = "";
                                                winner_rapporExp = "";
                                                arr = v['Resultats'];
                                                arr.sort(function (a, b) {
                                                    return a['NbVoix'] - b['NbVoix'];
                                                });
                                                arr.reverse();
                                                $.each(arr, function (ii, vv) {
                                                    if (nb_elus < parseInt(vv['NbVoix'])) {
                                                        winner = vv['CodNuaBin'];
                                                        nb_elus = vv['NbVoix'];
                                                        winner_name = vv['LibBin'];
                                                        winner_nua = vv['LibNuaBin'];
                                                        winner_rapportExp = vv['RapportExprime'];
                                                    }
                                                    cString += "<li><strong>" + vv['LibBin'] + " : " + vv['NbVoix'] + " Voix (" + vv['RapportExprime'] + "%)</strong></li>";
                                                    cString += '<li><div style="display:inline-block;width:' + Math.round(parseFloat(vv['RapportExprime'])) + 'px;height:10px;background-color:' + colors[vv['CodNuaBin']] + ';"></div></li>';
                                                });
                                                headstring += "<li><strong>" + winner_name + " : " + nb_elus + " Voix (" + winner_rapportExp + "%)</strong></li>";
                                                headstring += '<li><div style="display:inline-block;width:' + Math.round(parseFloat(winner_rapportExp)) + 'px;height:10px;background-color:' + colors[winner] + ';"></div></li>';
                                                //                         contentString += headstring;
                                                contentString += '<hr />';
                                                contentString += cString;
                                                contentString += '</ul></div>';
                                                contentString += '</div>';
                                                var infowindow = new google.maps.InfoWindow({
                                                    content: contentString
                                                });
                                                infowindow.open(map, markers[0]);
                                                clearMarkers();
                                                var center = new google.maps.LatLng(event.latLng.lat(), event.latLng.lng());
                                                map.panTo(center);
                                                addMarker(center, infowindow);
                                                map.setZoom(10);
                                            }
                                        });
                                        /*                         var tot = 0;
                                         var tot_exprime = 0;
                                         var votes_exprimes = [];
                                         $.each(data[event.feature.getProperty('CT')].scores, function (index, value) {
                                         votes_exprimes.push({ parti: index, score: value});
                                         tot += parseInt(value,10);
                                         if( index != "ABSTENTION" && index != "NUL" && index != "BLANC") {
                                         tot_exprime += parseInt(value, 10);
                                         }
                                         });
                                         votes_exprimes = votes_exprimes.map(function(d){
                                         d.ratio = (100 * d.score / tot_exprime).toFixed(1);
                                         return d;
                                         }).sort(function(a, b){
                                         return b.score - a.score;
                                         });
                                         headstring = "";
                                         cString = "";
                                         $.each(votes_exprimes, function (i,value) {
                                         var isWinner    = (value.score === data[event.feature.getProperty('CT')].winner.score);
                                         v = (parseInt(value,10)*100)/tot_exprime;
                                         if( value.parti == "ABSTENTION" || value.parti == "NUL" || value.parti == "BLANC") {
                                         headstring += '<li>'+value.parti+" ("+value.score+" voix)</li>";
                                         } else {
                                         if(value.ratio >= 5) {
                                         cString += '<li>';
                                         cString += isWinner ? '<strong>' : '';
                                         cString += value.parti+" : "+value.ratio +"% ("+value.score+" voix)</li>";
                                         cString += isWinner ? '</strong>' : '';
                                         cString += '<li><div style="display:inline-block;width:'+value.ratio+';height:10px;background-color:'+colors[value.parti]+';"></div></li>';
                                         }
                                         }
                                         });
                                         */


                                    });
                                }
                            });


                        });


                    }

                    function hide() {
                        //    map.data.forEach(function(feature) {
                        //  bkp_feature = feature;
                        //  map.data.remove(feature);
                        layer_region.setMap(null);
                        layer_departement.setMap(null);
                        //    layer_canton.setMap(null);
                        istoggle = false;
                        //});
                    }
                    function show() {
                        istoggle = true;
                        if (layer_region_isvisible) {
                            //   layer_region.setMap(map);
                        } else if (layer_departement_isvisible) {
                            layer_departement.setMap(map);
                        }

                    }

                    google.maps.event.addDomListener(window, 'load', initMap);
                    // map.controls[google.maps.ControlPosition.RIGHT_TOP].push(
                    //     document.getElementById('legend'));

                    function toggle() {
                        if (istoggle) {
                            hide();
                        } else {
                            show();
                        }
                    }

                    $("#dm-toggle").click(function () {
                        toggle();
                    });
                </script>
                <style>
                    #legend {
                        background: white;
                        padding: 10px;
                    }
                </style>

                <div class="search-grid" style="display:block">
                    <?php $this->renderPartial('_grid',array('model' => $model,'mapView'=>true, 'gridId'=>'people-map-grid')); ?>
                </div>
<!--                --><?php //$this->widget('bootstrap.widgets.TbGridView',array(
//                    'id' => 'people-map-grid',
//                    'htmlOptions'=>array('class'=>'table-wrap table-custom'),
//                    'type' => 'striped custom hover',
//                    'summaryText' => '<div class="text-right results-count mt-4">'.Yii::t('messages','Displaying {start}-{end} of {count} imports').'</div>',
//                    'pager' => array(
//                        'header' => '',
//                        'firstPageLabel' => '',
//                        'prevPageLabel' => '<span  aria-hidden="true">&laquo;</span>',
//                        'previousPageCssClass' => 'page-item',
//                        'nextPageLabel' => '<span aria-hidden="true">&raquo;</span>',
//                        'nextPageCssClass' => 'page-item',
//                        'lastPageLabel' => '',
//                        'internalPageCssClass' => 'page-item',
//                        'htmlOptions' => array('class' => 'pagination justify-content-md-end'),
//                    ),
//                    'template' => '<div class="text-right results-count">{summary}</div>
//                        <div class="table-wrap">{items}</div>
//                        <div class="row no-gutters d-flex flex-sm-row-reverse flex-sm-column-reverse flex-md-row">
//                        <div class="col-md-6"></div>
//                        <div class="col-md-6">
//                            <div class="float-right"><nav aria-label="Page navigation">{pager}</nav></div>
//                        </div></div>',
//                    'dataProvider' => $model->searchUsers(),
//                    'columns' => array(
////        array(
////            'class' => 'bootstrap.widgets.TbButtonColumn',
////            'template' => '{view}{update}{delete}',
////            'deleteConfirmation' => Yii::t('messages', 'Are you sure you want to delete the Custom Field?'),
////            'afterDelete' => 'function(link,success,data){$("#statusMsg").html(data);}',
////            'buttons' => array(
////                'view' => array(
////                    'visible' => 'Yii::app()->user->checkAccess("AdvancedSearch.View")',
////                    'options' => array('class' => 'fa fa-eye fa-lg')
////                ),
////                'update' => array(
////                    'visible' => 'Yii::app()->user->checkAccess(\'People.Update\')',
////                    'options' => array('class' => 'fa fa-edit fa-lg'),
////                    'url' => 'Yii::app()->createUrl(\'people/update\', array(\'id\'=>$data->id,
////                                    \'q\'=>base64_encode(json_encode(array("reqFrom"=>"ADVANCED_SEARCH")))))',
////                ),
////                'delete' => array(
////                    'visible' => 'Yii::app()->user->checkAccess(\'People.Delete\')',
////                    'options' => array('class' => 'fa fa-trash fa-lg'),
////                    'url' => 'Yii::app()->createUrl(\'people/delete\', array(\'id\'=>$data->id))',
////                ),
////            ),
////        ),
//                        array(
//                            'type' => 'raw',
//                            'header' => Yii::t('messages', 'Profile'),
//                            'value' => function ($data, $row) {
//                                $imgSrc = User::model()->getPic($data->profImage);
//                                $gender = User::model()->getGenderLabel($data->gender, 1);
//                                $networks = User::model()->getNetworkIcons($data);
//                                if ($networks['count'] > 1) {
//                                    $str = '<span class="social"><a href="" data-toggle="tooltip" data-html="true" title="' . $networks['network'] . '"><i class="fa fa-angle-right fa-lg"></i></a></span>';
//                                } else {
//                                    $str = '<span class="social">' . $networks['network'] . '</span>';
//                                }
//
//                                return '<span class="profile-pic">' . $imgSrc . '</span>
//                                    <span class="gender" data-toggle="tooltip">' . $gender . '</span>' . $str;
//                            },
//                        ),
//                        'firstName',
//                        'lastName',
//                        array(
//                            'type' => 'raw',
//                            'header' => Yii::t('messages', 'keywords'),
//                            'value' => function ($data, $row, $tagList) {
//                                $userKeywords = User::model()->getUserKeywordNames($data->keywords);
//                                $str = null;
//                                if (!Toolkit::isEmpty($data->keywords)) {
//                                    $str = '<a href="#" data-toggle="tooltip" title="' . $userKeywords . '"><i class="fa fa-eye fa-lg"></i></a>';
//                                } else {
//                                    $str = '<a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a>';
//                                }
//                                return $str . '<a class="editUserKeywords" onclick=popup("'.Yii::app()->createUrl("people/updatePeopleAjax",
//                                        array("id" => $data->id)) . '") href="#"> <i class="fa fa-edit fa-lg"></i> </a>';
//
////                                return $str . '<a href="#" data-toggle="modal" onclick=editUserKeywords() class="editUserKeywords"
////                                title="Keyword Edit"> <i class="fa fa-edit fa-lg"></i></a>';
//                            }
//                        ),
//                        array(
//                            'type' => 'raw',
//                            'name' => 'email',
//                            'value' => '$data->email != null ? "<a href=\"#\" class=\"emailLink\" data=\"$data->id\">$data->email</a>" : ""',
//                            'htmlOptions' => array('style' => 'word-wrap: break-word;'),
//                        ),
//                        'mobile',
//'zip',
//
////        array(
////            'template' => '{keywordView} {keywordUpdate}',
////            'class' => 'bootstrap.widgets.TbButtonColumn',
////            'buttons' => array(
////                'keywordUpdate' => array(
////                    //'icon'=>'signal',
////                    'label' => Yii::t('messages', ''),
////                    'visible' => 'Yii::app()->user->checkAccess("People.Update")',
////                    'url' => 'Yii::app()->createUrl("People/updatePeopleAjax",
////                                    array("id"=>$data->id, "check"=>1))',
////                    'click' => 'function() {
////						                $("#iframe-stat").attr("src",$(this).attr("href"));
////						                return false;
////					                }',
////                    'options' => array(
////                        'class' => 'fa fa-edit fa-lg',
////                        'title' => Yii::t('messages', 'Keyword Edit'),
////                        'data-toggle' => 'modal',
////                        'data-target' => '#editKeywords',
////                    ),
////                ),
////            ),
////        ),
//
//                        array(
//                            'type' => 'raw',
//                            'header' => Yii::t('messages', 'Category'),
//                            'value' => function ($data, $row, $tagList) {
//                                $userTypeLabel = User::model()->getUserTypeLabel($data->userType);
//                                return '<label style="' . $userTypeLabel['color'] . '">' . $userTypeLabel['type'] . '</label>';
//                            }
//                        ),
//                    ),
//                )); ?>
            </div>
        </div>
    </div>
</div>


<!-- START // Edit Keywords Modal -->
<div class="modal fade" id="editKeywords" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle"> <?php echo Yii::t('messages', 'Edit Keywords'); ?> </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <iframe id="iframe-editKeywords" src="" frameborder="0" scrolling="yes" width="100%" height="200px"></iframe>
        </div>
    </div>
</div>
<!-- END // Edit Keywords Modal -->

<!-- START View Modal -->
<div class="modal fade" id="viewDetails" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">

        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id=""> <?php echo Yii::t('messages','View Details'); ?> </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <iframe id="iframe-viewDetails" src="" frameborder="0" scrolling="auto" width="100%" height="700px"></iframe>
        </div>
    </div>
</div>
<!-- END View Modal -->


<!-- START Update Modal -->
<div class="modal fade" id="updateDetails" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">

        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id=""> <?php echo Yii::t('messages', 'Update Details'); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="row">
                <div class="col-md-12 no-gutters">
                    <iframe id="iframe-updateDetails" src="" frameborder="0" scrolling="auto" width="100%" height="700px"></iframe>
                </div>
            </div>

        </div>
    </div>
</div>
<!-- END Update Modal -->

<!-- START Create Zone Modal -->
<div class="modal fade" id="createZone" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">

        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id=""><?php echo Yii::t('messages','Create Zone'); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <iframe id="iframe-createZone" src="" frameborder="0" scrolling="auto" width="100%" height="200px"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END Create Zone Modal -->

<!-- START // Edit Keywords Modal -->
<div class="modal fade" id="editCategory" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"
                    id="exampleModalLongTitle"> <?php echo Yii::t('messages', 'Edit Category'); ?> </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <iframe id="iframe-editCategory" src="" frameborder="0" scrolling="yes" width="100%"
                    height="200px"></iframe>
        </div>
    </div>
</div>
<!-- END // Edit Keywords Modal -->