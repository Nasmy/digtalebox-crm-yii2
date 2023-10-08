<?php

use app\components\ToolKit;
use yii\grid\GridView;
use yii\helpers\Html;
use \yii\widgets\Pjax;
use app\models\Country;
use app\models\User;
use app\models\Keyword;

?>

<?php $markerImg = Yii::$app->toolKit->getMarkerImage(); ?>
<?php ToolKit::registerDataOsmMapScript(); ?>
<script>
    let teamZones = <?php echo $teamZoneData; ?>;
    let actionId = '';
    let osmMaxLimit =<?php echo $osmMaxLimit; ?>;
    let markerImg = "<?php echo $markerImg; ?>";
    actionId = "<?=Yii::$app->controller->action->id; ?>"
    window.onload = function () {
        let mapLayer = MQ.mapLayer(), map;
        let data = [];
        data = <?php echo $markersLongLat; ?>;

        map = L.map('map', {
            layers: mapLayer,
            center: [<?php echo $lat; ?>, <?php echo $long; ?>],
            zoom: actionId == 'all-map-zones' ? 3 : 13
        });

        let myIcon = L.icon({
            iconUrl: markerImg,
            iconSize: [29, 24],
            iconAnchor: [9, 21],
            popupAnchor: [0, -14]
        });

        let markerClusters = new L.markerClusterGroup({
            showCoverageOnHover: false,
        });

        teamZones.forEach((d) => {
            d.forEach((c) => {
                if (c.hasOwnProperty('coordinates')) {
                    L.polygon(c.coordinates).addTo(map);
                }
            });
        });

        console.log(data.length);
        if (osmMaxLimit >= data.length) {
            data.forEach((d) => {
                let m = L.marker([d.latitude, d.longitude], {icon: myIcon});
                m.bindPopup(getContentMap(m, d.id))
                markerClusters.addLayer(m);
            });
        } else {
            for (var i = 0; i < osmMaxLimit; i++) {
                var dataUser = data[i];
                var m = L.marker([dataUser.latitude, dataUser.longitude], {icon: myIcon});
                m.bindPopup(get_content_map(m, dataUser.id))
                markerClusters.addLayer(m);
            }
        }

        function getContentMap(marker, userId) {
            marker.on('click', function (e) {
                let popup = e.target.getPopup();
                var url = '<?php echo Yii::$app->urlManager->createUrl(['/advanced-search/load-map-info-window']) . '?id='; ?>' + userId;
                $.get(url).done(function (data) {
                    popup.setContent(data);
                    popup.update();
                });
            });
        }

        map.addLayer(markerClusters);

    }

</script>

<script>
    $(document).on('click', '.geo', function (e) {
        $('#viewZone').modal({backdrop: 'static'});
        $('#iframe-viewZone').attr('src', $(this).parents().attr('url'));
        $('#viewZone').on('hidden.bs.modal', function (e) {
            $('#iframe-viewZone').attr('url', '')
        });
        return false;
    });
</script>

<div id="message">
    <?= Yii::$app->session->getFlash('success'); ?>
</div>
<?php
$isAll = strtolower(Yii::$app->controller->action->id) == strtolower('all-map-zones');
if ($isAll) {
    echo Yii::$app->controller->renderPartial('_tabMenu');
}
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'People'), 'url' => ['admin']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Map View')];
if ($isAll) {
    $this->title = Yii::t('messages', 'Map View');
    $this->titleDescription = Yii::t('messages', 'Map view of all zone');
} else {
    $this->title = Yii::t('messages', 'Map View of') . " <strong>" . $model->title . " </strong>";
}
?>
<?php if (in_array(Yii::$app->controller->action->id, array('all-map-zones'))) { ?>
    <div class="content-inner">
        <div class="content-area">
            <div class="row">
                <div class="col-md-12">
                    <div class="map-layout">
                        <input type="hidden" name="mapFilters" value="<?php echo $mapFilters; ?>">
                        <div id="map" style="width:100%;height:600px;"></div>
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
            </div>
            <br/>
            <?php Pjax::begin(['id' => 'people-map-zone-grid-update']); ?>
            <div class="table-wrap table-custom">
                <?= GridView::widget([
                    'id' => 'map-zone-grid',
                    'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => ''],
                    'tableOptions' => ['class' => 'table table-striped table-bordered'],
                    'summary' => '<div class="text-right results-count mt-4">' . Yii::t('messages', 'Displaying {begin}-{end} of {count} imports') . '</div>',
                    'pager' => [
                        'firstPageLabel' => '',
                        'firstPageCssClass' => 'first',
                        'activePageCssClass' => 'selected',
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
                    'headerRowOptions' => array('class' => 'table-wrap table-custom'),
                    'dataProvider' => $model->search(),
                    'columns' => [
                        [
                            'format' => 'raw',
                            'header' => 'Title',
                            'value' => function ($data) {
                                return $data['title'];
                            },
                        ],
                        [
                            'template' => '{edit} {del} {geo}',
                            'class' => 'yii\grid\ActionColumn',
                            'options' => ['style' => 'text-align:right;width:100px'],
                            'buttons' => [
                                'edit' => function ($url, $model, $key) {
                                    $return = '';
                                    if ((Yii::$app->user->checkAccess("AdvancedSearch.MapZoneUpdate"))) {
                                        $return = Html::tag('a', '<i class="fa fa-pencil fa-lg"></i>',
                                            ['href' => Yii::$app->urlManager->createUrl(['advanced-search/map-zone-update',
                                                "id" => $model['id'],
                                            ]), 'title' => Yii::t('messages', 'Update Zone'),]);
                                    }
                                    return $return;
                                },
                                'del' => function ($url, $model, $key) {
                                    $return = '';
                                    $url = Yii::$app->urlManager->createUrl(['advanced-search/map-zone-delete', 'id' => $model['id']]);
                                    if ((Yii::$app->user->checkAccess("AdvancedSearch.MapZoneDelete"))) {
                                        $delConfirmMsg = Yii::t('messages', "Are you sure you want to delete user?");
                                        $return = Html::tag('a', '<i class="fa fa-trash-o del-team fa-lg"></i>', ['href' => '',
                                            'onClick' => "
                           if (confirm('$delConfirmMsg')) {
                                $.ajax('$url', {
                                    type: 'POST'
                                }).done(function(data) {
                                    $.pjax.reload({container: '#people-map-zone-grid-update'});
                                });
                            }
                            return false;
                            ",
                                            'title' => Yii::t('messages', 'Delete zone')
                                        ]);
                                    }
                                    return $return;
                                },
                                'geo' => function ($url, $model, $key) {
                                    $return = '';
                                    if ((Yii::$app->user->checkAccess("AdvancedSearch.MapZoneView"))) {
                                        $return = Html::tag('a', '<i class="fa fa-map-marker geo fa-lg"></i>',
                                            ['href' => '#', 'id' => 'map-zone-view', 'url' => Yii::$app->urlManager->createUrl(['advanced-search/map-zone-view', "id" => $model['id'],]), 'title' => Yii::t('messages', 'View zone')]);

                                    }
                                    return $return;
                                },
                            ],
                        ]
                    ]

                ]); ?>
            </div>
            <?php Pjax::end(); ?>
        </div>
    </div>
<?php } else { ?>
    <div class="modal-body edit-keyword app-body">
        <div class="row">
            <div class="col-md-12">
                <div class="map-layout">
                    <div class="mapouter">
                        <input type="hidden" name="mapFilters" value="<?php echo $mapFilters; ?>">
                        <div id="map" style="width:100%;height:600px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <?php Pjax::begin(); ?>
                <?= GridView::widget([
                    'id' => 'map-zone-user-grid',
                    'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => ''],
                    'tableOptions' => ['class' => 'table table-striped table-bordered'],
                    'summary' => '<div class="text-right results-count mt-4">' . Yii::t('messages', 'Displaying {begin}-{end} of {totalCount} results') . '</div>',
                    'pager' => [
                        'firstPageLabel' => '',
                        'firstPageCssClass' => 'first',
                        'activePageCssClass' => 'selected',
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
                    'headerRowOptions' => array('class' => 'table-wrap table-custom'),
                    'dataProvider' => $model->getSingleMapZone($model->id),
                    'columns' => [
                        [
                            'attribute' => 'firstName',
                            'label' => 'First Name',
                            'format' => 'raw',
                        ],
                        [
                            'attribute' => 'lastName',
                            'label' => 'Last Name',
                            'format' => 'raw',
                        ],
                        [
                            'attribute' => 'fullAddress',
                            'label' => 'Full Address',
                            'format' => 'raw',

                        ],
                        [
                            'format' => 'raw',
                            'label' => 'Age',
                            'attribute' => 'age',
                        ],
                        [
                            'attribute' => 'gender',
                            'value' => function ($data) {
                                return (User::FEMALE == $data['gender']) ? "Female" : (User::MALE == $data['gender']) ? "Male" : "";
                            },
                            'format' => 'raw',
                        ],
                        [
                            'attribute' => 'zip',
                            'label' => 'Zip',
                            'format' => 'raw',
                        ],
                        [
                            'header' => Yii::t('messages', 'Country Name'),
                            'value' => function ($data) {
                                return Country::getContryByCode($data['countryCode']);
                            },
                        ],
                        [
                            'attribute' => 'city',
                            'label' => 'City',
                        ],
                        [
                            'attribute' => 'keywords',
                            'value' => function ($data) {
                                return Keyword::getKeywordsByIdAndList($data['keywords']);
                            },
                            'format' => 'raw',
                        ],
                        [
                            'attribute' => 'keywordsExclude',
                            'value' => function ($data) {
                                Keyword::getKeywordsByIdAndList($data['keywordsExclude']);
                            },
                            'format' => 'raw',
                        ],
                        [
                            'attribute' => 'status',
                            'value' => function ($data) {
                                (0 == $data['status'] && null != $data['status']) ? "Active" : ((1 == $data['status']) ? "InActive" : "");
                            },
                            'format' => 'raw',
                        ],
                    ],

                ]); ?>
                <?php Pjax::end(); ?>
            </div>
        </div>
    </div>
<?php } ?>
<!-- START Create Zone Modal -->
<div class="modal fade" id="viewZone" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">

        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id=""> <?php echo Yii::t('messages', 'View Zone Details'); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <iframe id="iframe-viewZone" src="" frameborder="0" scrolling="auto" width="95%" height="700px"
                    style="margin: auto"></iframe>
            <?php if (!$osmCanProceed) { ?>
                <div>
                    <p class="error" style="padding:2px; text-align: center;">
                        <b>
                            <?= Yii::t('messages', 'You have reach your map contacts limits, however all extra contacts are available in the database, you may upgrade your subscribtion or reach out to us for more informations contact@digitalebox.com') ?>
                        </b>
                    </p>
                </div>
            <?php } ?>

        </div>
    </div>
</div>
<!-- END Create Zone Modal -->