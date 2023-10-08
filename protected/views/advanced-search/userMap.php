<?php
$this->tabMenuPath = 'application.views.advancedSearch._tabMenu';
$this->title = Yii::t('messages', 'Map View');
$this->breadcrumbs = array(
    Yii::t('messages', 'People') => array('admin'),
    Yii::t('messages', 'Map View'),
);
?>
<?php Yii::app()->toolKit->registerGoogleMapScripts('false', 'drawing'); ?>
<?php $markerImg = Yii::app()->toolKit->getMarkerImage(); ?>
<?php Yii::app()->clientScript->registerScript('google-map', "

        var infowindow;
        var hasFilter = {$hasFilter};
        var markersAddress = {$markersAddress};
        var markersLongLat = {$markersLongLat};
        var geocoder = new google.maps.Geocoder();
        var markerImg = '{$markerImg}';
        var timeout = 600;
	        function initialize() {
            if(hasFilter == 1){
            var zoom = 2;
            var myLatlng = new google.maps.LatLng(0, 0);
            if('' != markersLongLat){
            var myLatlng = new google.maps.LatLng(markersLongLat[0].lat, markersLongLat[0].long);
            var zoom = 5;
            }
            var myOptions = {
                zoom: zoom,
                center: myLatlng,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            };
            }
            else{
            var myLatlng = new google.maps.LatLng(0, 0);
            var myOptions = {
                zoom: 2,
                center: myLatlng,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            };
            }
            map = new google.maps.Map(document.getElementById('googleMap'), myOptions);

            // Adding longLat markers
            if ('' != markersLongLat) {
            for (var x = 0; x < markersLongLat.length; x++) {
                var person =  new Object();
                person.id = markersLongLat[x].id
                person.name = markersLongLat[x].name
                person.lat = markersLongLat[x].lat
                person.long = markersLongLat[x].long
                person.address = markersLongLat[x].address
                var mobile = markersLongLat[x].mobile == '' ? '' : '<br> <span style=\'color: #36c;\'>Phone Number: </span>' + markersLongLat[x].mobile;
                var email = markersLongLat[x].email == '' ? '' : '<br> <span style=\'color: #36c;\'>Email: </span>' + markersLongLat[x].email;
                var twitter = markersLongLat[x].twitterUrl == '' ? '' : ' <a style=\'display: inline-block;\' href=' + markersLongLat[x].twitterUrl + '><img border=\"0\" alt=\"Twitter\" src=\"/images/tw.png\"></a>';
                var facebook = markersLongLat[x].facebookUrl == '' ? '' : ' <a style=\'display: inline-block;\' href=' + markersLongLat[x].facebookUrl + '><img border=\"0\" alt=\"Facebook\" src=\"/images/fb.png\"></a>';
                var googlePlus = markersLongLat[x].googlePlusUrl == '' ? '' : ' <a  style=\'display: inline-block;\' href=' + markersLongLat[x].googlePlusUrl + '><img border=\"0\" alt=\"Facebook\" src=\"/images/gp.png\"></a>';

                person.info = '<span style=\'color: #36c; font-weight: bold;\'>' + markersLongLat[x].name + '</span><br>' + markersLongLat[x].address + mobile + email + '<br>' + twitter + facebook + googlePlus;
                codeLongLat(person);
            }
            }

            // Adding address markers
            if ('' != markersAddress) {
            for (var x = 0; x < markersAddress.length; x++) {
                var person =  new Object();
                person.id = markersAddress[x].id
                person.name = markersAddress[x].name
                person.address = markersAddress[x].address
                var mobile = markersAddress[x].mobile == '' ? '' : '<br> <span style=\'color: #36c;\'>Phone Number: </span>' + markersAddress[x].mobile;
                var email = markersAddress[x].email == '' ? '' : '<br> <span style=\'color: #36c;\'>Email: </span>' + markersAddress[x].email;
                var twitter = markersAddress[x].twitterUrl == '' ? '' : ' <a style=\'display: inline-block;\' href=' + markersAddress[x].twitterUrl + '><img border=\"0\" alt=\"Twitter\" src=\"/images/tw.png\"></a>';
                var facebook = markersAddress[x].facebookUrl == '' ? '' : ' <a style=\'display: inline-block;\' href=' + markersAddress[x].facebookUrl + '><img border=\"0\" alt=\"Facebook\" src=\"/images/fb.png\"></a>';

                var googlePlus = markersAddress[x].googlePlusUrl == '' ? '' : ' <a  style=\'display: inline-block;\' href=' + markersAddress[x].googlePlusUrl + '><img border=\"0\" alt=\"Facebook\" src=\"/images/gp.png\"></a>';

                person.info = '<span style=\'color: #36c; font-weight: bold;\'>' + markersAddress[x].name + '</span><br>' + markersAddress[x].address + mobile + email + '<br>' + twitter + facebook + googlePlus;
                codeAddress(person);
            }
            }
        }

        function codeAddress(markersAddress) {
        var address = markersAddress.address;
        var name = markersAddress.name;
        var info = markersAddress.info;
        geocoder.geocode({
            'address': address
        }, function (results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                var latLng = results[0].geometry.location;
                $.ajax({
                    url: '" . Yii::app()->createUrl('/AdvancedSearch/UserMapLocationUpdate/') . "',
                    type:\"POST\",
                    data:'id=' + markersAddress.id+'&latLong=' + latLng
                });
                var markerObj = new MarkerWithLabel({
                    map: map,
                    position: latLng,
                    title: name,
                    labelContent: name,
                    labelClass: 'marker-labels',
                    icon:markerImg
                });
                google.maps.event.addListener(markerObj, 'click', function() {
                if (infowindow) infowindow.close();
                infowindow = new google.maps.InfoWindow({content: info});
                infowindow.open(map, markerObj);
            });
            }
            else
            {
            if (status == google.maps.GeocoderStatus.OVER_QUERY_LIMIT)
            {
                setTimeout(function() { codeAddress(markersAddress); }, (timeout * 3));
            }
            else {
                console.log('Geocode was not successful for the following reason: ' + status);
            }
        }
        });
    }

         function codeLongLat(markersLongLat) {
            var lat = markersLongLat.lat;
            var long = markersLongLat.long;
            var name = markersLongLat.name;
            var info = markersLongLat.info;
            var markerObj = new MarkerWithLabel({
                  map: map,
                  position: new google.maps.LatLng(lat, long),
                  title: name,
                  labelContent: name,
                  labelClass: 'marker-labels',
                  icon:markerImg
             });

              google.maps.event.addListener(markerObj, 'click', function() {
              if (infowindow) infowindow.close();
              infowindow = new google.maps.InfoWindow({content: info});
              infowindow.open(map, markerObj);
              });
         }

        google.maps.event.addDomListener(window, 'load', initialize);
"); ?>

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-search"></i> <?php echo Yii::t('messages', 'Search by criteria') ?>
        </h3>
    </div>
    <div class="panel-body">
        <div class="search-form" style="display:block">
            <?php $this->renderPartial('_searchMap', array(
                'model' => $model, 'tagList' => $keywords, 'teams' => $teams, 'isOwner' => $isOwner, 'modelConfig' => $modelConfig
            )); ?>
        </div>
    </div>
</div><!-- search-form -->

<div id="googleMap" style="width:100%;height:480px;"></div>
<br/>

<?php $this->widget('bootstrap.widgets.TbGridView', array(
    'id' => 'people-grid',
    'dataProvider' => $model->searchUsers(),
    'type' => 'striped',
    'columns' => array(
        array(
            'type' => 'raw',
            'name' => '',
            'value' => 'User::model()->getPic($data->profImage)',
        ),
        array(
            'type' => 'raw',
            'value' => 'User::model()->getGenderLabel($data->gender, 1)',
            'htmlOptions' => array('style' => 'text-align: center;'),
        ),
        'firstName',
        'lastName',
        array(
            'type' => 'raw',
            'name' => 'email',
            'value' => '$data->email != null ? "<a href=\"#\" class=\"emailLink\" data=\"$data->id\">$data->email</a>" : ""',
            'htmlOptions' => array('style' => 'word-wrap: break-word; max-width:100px;'),
        ),
        'mobile',
        //'address1',
        'zip',
        array(
            'type' => 'raw',
            'name' => 'userType',
            'value' => 'User::model()->getUserTypes($data->userType)',
        ),
    ),
)); ?>
