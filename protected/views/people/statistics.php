<?php
Yii::$app->toolKit->registerHighchartsScripts();
use app\models\BroadcastMessage;
use app\models\User;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax; ?>
<?php

//$attributeLabels = $model->attributeLabels();
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'People'), 'url' => '#'];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Statistics')];
$this->title = Yii::t('messages', 'Statistics');
$this->titleDescription = Yii::t('messages', 'Your online community growth at a glance');

?>

<?php echo  Yii::$app->controller->renderPartial('_tabMenu'); ?>

<?php
$feedCount = json_encode($statSummary['feedCount']);
$newSupText = Yii::t('messages', 'New supporters');
$totalSupText = Yii::t('messages', 'Total Contacts');
$lblProspect = Yii::t('messages', 'Prospects');
$lblSupporter = Yii::t('messages', 'Supporters');

$lineChart = <<<JS
        supporters = {$supporters},
        newReg = {$newReg},
        newSupporters = {$newSupporters},
        w_prospects = {$w_prospects},
        w_supporters = {$w_supporters},
        w_newReg = {$w_newReg},
        w_newSupporters = {$w_newSupporters},
        t_prospects = {$t_prospects},
        t_supporters = {$t_supporters},
        t_newReg = {$t_newReg},
        t_newSupporters = {$t_newSupporters},
        m_prospects = {$m_prospects},
        m_supporters = {$m_supporters},
        m_newReg = {$m_newReg},
        m_newSupporters = {$m_newSupporters},
        today = Date.now(),
        lastweek = today - 604800000,
        lastmonth = today - 2629743000,
        threemonth = today - 7889229000,
        sixmonth = today - 15778458000,

            ////////////////////////////////  Line Chart ////////////////////////////////
    Highcharts.chart('line-chart', {     
    
        credits: {
        enabled: false
        },
        tooltip: {
            valueDecimals: 0,
        },
        chart: {
            renderTo: 'line-chart',
            defaultSeriesType: 'spline',
        },
        title: {
            text: ' '
        },
        xAxis: {
            type:'datetime',
            labels: {
                format: '{value:%Y-%m-%d}',
                rotation: 45,
                align: 'left'
            }
        },
        yAxis: {
            title: {
                text: '{$totalSupText}'
            }
        },
        legend: {
            layout: 'horizontal',
            align: 'center',
            verticalAlign: 'top'
        },
        colors: ['#fdd695', '#57bdb9'],

        series: [
        {
            //dashStyle: 'dash',
            name: '{$lblProspect}',
            data: w_prospects,
            pointStart: lastweek,
            pointInterval: 24*36e5
        },{
            name: '{$lblSupporter}',
            data: w_supporters,
            pointStart: lastweek,
            pointInterval: 24*36e5
        }],
        responsive: {
            rules: [{
                condition: {
                    maxWidth: 700
                },
                chartOptions: {
                    legend: {
                        x: 0,
                        y: 10,
                        floating: false,
                        align: 'center',
                        verticalAlign: 'bottom',
                        layout: 'horizontal'
                    },
                    yAxis: {
                        labels: {
                            align: 'left',
                            x: 0,
                            y: -5
                        },
                        title: {
                            text: null
                        }
                    },
                    subtitle: {
                        text: null
                    },
                    credits: {
                        enabled: false
                    }
                }
            }]
        }
    });


    var options = {
    credits: {
        enabled: false
    },
    tooltip: {
        valueDecimals: 0,
    },
    chart: {
        renderTo: 'line-chart',
        defaultSeriesType: 'spline',
    },
    title: {
        text: ''
    },
    xAxis: {
        type:'datetime',
            labels: {
                format: '{value:%Y-%m-%d}',
                rotation: 45,
                align: 'left'
        }
    },
    yAxis: {
        title: {
            text: '{$totalSupText}'
        }
    },
    legend: {
        layout: 'horizontal',
        align: 'center',
        verticalAlign: 'top'
    },
    colors: ['#fdd695', '#57bdb9'],
    series: [],
    responsive: {
        rules: [{
            condition: {
                maxWidth: 700
            },
            chartOptions: {
                legend: {
                    x: 0,
                    y: 10,
                    floating: false,
                    align: 'center',
                    verticalAlign: 'bottom',
                    layout: 'horizontal'
                },
                yAxis: {
                    labels: {
                        align: 'left',
                        x: 0,
                        y: -5
                    },
                    title: {
                        text: null
                    }
                },
                subtitle: {
                    text: null
                },
                credits: {
                    enabled: false
                    }
                }
            }]
        }
    };

    
            ////////////////////////////////  Line Chart Closed ////////////////////////////////
$('#inputGroupSelect01').change(function(){
    if ($('#inputGroupSelect01').val() == '3'){
        options.series = [
            {
                //dashStyle: 'dash',
                name: '{$lblProspect}',
                data: w_prospects,
                pointStart: lastweek,
                pointInterval: 24*36e5
            },{
                name: '{$lblSupporter}',
                data: w_supporters,
                pointStart: lastweek,
                pointInterval: 24*36e5
            }]
        var chart = new Highcharts.Chart(options); 
        $('#week').addClass('active');
        $('#month').removeClass('active');
        $('#sixmonth').removeClass('active');
        $('#threemonth').removeClass('active');
    
    }
    if ($('#inputGroupSelect01').val() == '2'){
        options.series = [
            {
                //dashStyle: 'dash',
                name: '{$lblProspect}',
                data: m_prospects,
                pointStart: lastmonth,
                pointInterval: 24*36e5
            },{
                name: '{$lblSupporter}',
                data: m_supporters,
                pointStart: lastmonth,
                pointInterval: 24*36e5
            }]
        var chart = new Highcharts.Chart(options); 
        $('#month').addClass('active');
        $('#week').removeClass('active');
        $('#sixmonth').removeClass('active');
        $('#threemonth').removeClass('active');
     }


    if ($('#inputGroupSelect01').val() == '1'){
         options.series = [
            {
                //dashStyle: 'dash',
                name: '{$lblProspect}',
                data: t_prospects,
                pointStart: threemonth,
                pointInterval: 24*36e5
            },{
                name: '{$lblSupporter}',
                data: t_supporters,
                pointStart: threemonth,
                pointInterval: 24*36e5
            }]
        var chart = new Highcharts.Chart(options); 
        $('#threemonth').addClass('active');
        $('#week').removeClass('active');
        $('#sixmonth').removeClass('active');
        $('#month').removeClass('active');
     }

    if ($('#inputGroupSelect01').val() == '0'){
             options.series = [
            {
                //dashStyle: 'dash',
                name: '{$lblProspect}',
                data: prospects,
                pointStart: sixmonth,
                pointInterval: 24*36e5
            },{
                name: '{$lblSupporter}',
                data: supporters,
                pointStart: sixmonth,
                pointInterval: 24*36e5
            }]
        var chart = new Highcharts.Chart(options); 
        $('#sixmonth').addClass('active');
        $('#week').removeClass('active');
        $('#month').removeClass('active');
        $('#threemonth').removeClass('active');
    }
});

JS;
$this->registerJs($lineChart);

?>

<div class="content-inner">
    <div class="content-area">
        <div class="row">
            <div class="col-md-12">
                <div class="content-panel-sub">
                    <div class="panel-head"><?php echo Yii::t('messages', 'Total Supporters'); ?></div>
                    <div class="content-area">
                        <div class="row no-gutters">
                            <div class="col-md-8 col-xl-9">
                                <div class="">
                                    <strong><?php echo Yii::t('messages', 'Trends') ?></strong> -
                                    <?php echo Yii::t('messages', 'Supporters') ?> <?php echo $nowsup[0][0]; ?>
                                    <div> + <?php echo isset($nowsup[0][0]) ? $nowsup[0][0] - $weeksup : '0'; ?>
                                        <small><?php echo Yii::t('messages', 'since last week'); ?> </small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-xl-3">
                                <form>
                                    <div class="input-group mb-3 mt-4 mt-md-0">
                                        <div class="input-group-prepend">
                                            <label class="input-group-text"
                                                   for="inputGroupSelect01"><?php echo Yii::t('messages', 'Range'); ?></label>
                                        </div>
                                        <select class="custom-select" id="inputGroupSelect01">
                                            <option value="0"><?php echo Yii::t('messages', 'Since Six Month'); ?></option>
                                            <option value="1"><?php echo Yii::t('messages', 'Since Three Month'); ?></option>
                                            <option value="2"><?php echo Yii::t('messages', 'Since a Month'); ?></option>
                                            <option selected
                                                    value="3"><?php echo Yii::t('messages', 'Since a Week'); ?></option>
                                        </select>
                                    </div>

                                </form>
                            </div>
                        </div>
                        <div class="row no-gutters">
                            <div class="col-md-12">
                                <div class="content-area chart-side-padding">
                                    <div id="line-chart"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php Pjax::begin(); ?>

        <?= GridView::widget([
            'dataProvider' => $modelbr->search(),
            'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => ''],
            'tableOptions' => ['class' => 'table table-striped table-bordered'],
            'summary' => '<div class="text-right results-count mt-4">' . Yii::t('messages', 'Displaying {begin}-{end} of {count} imports') . '</div>',
            'pager' => [
                'firstPageLabel' => '',
                'firstPageCssClass' => 'first',
                'activePageCssClass' => 'selected active',
                'disabledPageCssClass' => 'hidden',
                'lastPageLabel' => 'last ',
                'nextPageLabel' => '<span aria-hidden="true">&raquo;</span>',
                'nextPageCssClass' => 'page-item next',
                'maxButtonCount' =>5,
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
            'headerRowOptions' => ['class' => 'table-wrap table-custom'],

            'columns' => [
                [
                    'format'=>'html',
                    'label' => 'publishDate',
                    'attribute' => 'publishDate',
                    'value' => function ($data) {
                        $user = new User();
                        return $user->convertDBTime($data['publishDate']);
                    }
                ],
                [
                    'format'=>'html',
                    'label' => 'Ln',
                    'attribute' => 'Ln',
                    'value' => function ($data) {
                        return   BroadcastMessage::getStat(3,$data);
                    }
                ],
                [
                    'format'=>'html',
                    'label' => 'Ln Page',
                    'attribute' => 'LnPage',
                    'value' => function ($data) {
                        return    BroadcastMessage::getStat(4,$data);
                    }
                ],
                [
                    'format'=>'html',
                    'label' => 'Tw',
                    'attribute' => 'Tw',
                    'value' => function ($data) {
                        return  BroadcastMessage::getStat(1,$data);
                    }
                ],
                [
                    'format'=>'html',
                    'label' => 'Fb Page',
                    'attribute' => 'FbPage',
                    'value' => function ($data) {
                        return BroadcastMessage::getStat(2,$data);
                    }
                ],
                [
                    'format'=>'html',
                    'label' => 'Fb',
                    'attribute' => 'Fb',
                    'value' => function ($data) {
                        return  BroadcastMessage::getStat(2,$data);
                    }
                ],
                [
                    'format'=>'html',
                    'label' => 'Post',
                    'attribute' => 'Post',
                    'value' => function ($data) {
                        return  BroadcastMessage::getPostText($data);
                    }
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'headerOptions' => ['style' => 'text-align: center'],
                    'contentOptions' => ['style' => 'text-align: center'],
                    'template'=>'{view}',

                    'buttons' => [
                        'view' => function ($url, $model, $key) {
                            $return = '';
                            if ((Yii::$app->user->checkAccess('BroadcastMessage.View'))) {
                                $url_new = Yii::$app->urlManager->createUrl(['broadcast-message/view-pop', 'id' => $model['id']]);
                                $return = Html::a('<span class="fa fa-eye " style="cursor: pointer;"></span>', false, [
                                    'title' => Yii::t('app', 'Details'),
                                    'class' => 'view',
                                    'data-target' => '#viewDetails',
                                    'data-backdrop' => 'static',
                                    'onClick' => "
                                                  $.ajax('$url_new', {
                                                    type: 'POST',
                                                   }).done(function(data) {
                                                    $('#iframe-viewDetails').html(data);
                                                    $('#viewDetails').modal('show');
                                                      return false;
                                                    });
                                                    $('#viewDetails').on('hidden.bs.modal', function (e) {
                                                        $('#iframe-viewDetails').html('');
                                                    });
                                                    return false;
                                        ",
                                ]);
                            }
                            return $return;
                        },
                    ]
                ]
            ],
        ]); ?>

        <?php Pjax::end(); ?>

    </div>
</div>
</div>
</div>
</div>

<!-- START View Modal -->
<div class="modal fade" id="viewDetails" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">

        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id=""> <?php echo Yii::t('messages', 'View Details'); ?> </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="row">
                <div class="col-md-12 no-gutters">
                    <div id="iframe-viewDetails" style="vertical-align: center;" class="div-min-height">
                        <div class="progress loader themed-progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated"
                                 role="progressbar" style="width: 100%"
                                 aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END View Modal -->
<script>
    $(document).ready(function () {
        $(document).on("hidden.bs.modal", function () {
            $("#iframe-viewDetails").html('<div class="progress loader themed-progress">\n' +
                '                            <div class="progress-bar progress-bar-striped progress-bar-animated"\n' +
                '                                 role="progressbar" style="width: 100%"\n' +
                '                                 aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>\n' +
                '                        </div>');
        });

        $("#viewDetails").on("hidden.bs.modal", function (e) {
            $(".modal-backdrop:eq(1)").remove();
            $(".modal-backdrop").hide();
        });
    });
</script>