<?php
Yii::$app->toolKit->registerHighchartsScripts();

use app\models\BroadcastMessage;
use app\models\User;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

?>
<?php
 //$attributeLabels = $model->attributeLabels();
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'People'), 'url' => '#'];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Analytics')];
$this->title = Yii::t('messages', 'Analytics');
$this->titleDescription = '';

$feedCount = json_encode($statSummary['feedCount']);
$newSupText = Yii::t('messages','New supporters');
$totalSupText = Yii::t('messages','Total Contacts');


?>

<?php echo  Yii::$app->controller->renderPartial('_tabMenu'); ?>

<?php
$feedCount = json_encode($statSummary['feedCount']);
$newSupText = Yii::t('messages', 'New supporters');
$totalSupText = Yii::t('messages', 'Total Contacts');
$lblProspect = Yii::t('messages', 'Prospects');
$lblSupporter = Yii::t('messages', 'Supporters');

$lineChart = <<<JS
        prospects = {$prospects},
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

            ////////////////////////////////  Line Chart  newsupcontainer ////////////////////////////////
    Highcharts.chart('newsupcontainer', {     
      credits: {
            enabled: false
        },
        tooltip: {
            valueDecimals: 0,
        },
        chart: {
            renderTo: 'newsupcontainer',
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
                text: '{$newSupText}'
            }
        },
        legend: {
            layout: 'horizontal',
            align: 'center',
            verticalAlign: 'top'
        },

        plotOptions: {
            series: {
                //pointStart: 2010
            }
        },

        colors: ['#3f80be'],

        series: [
        {
            name: '{$newSupText}',
            data: w_newSupporters,
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
            ////////////////////////////////  Line Chart Closed ////////////////////////////////
            
            ////////////////////////////////  Line Chart  Total Supporters ////////////////////////////////
Highcharts.chart('container', {     
       credits: {
            enabled: false
        },
        tooltip: {
            valueDecimals: 0,
        },
        chart: {
            renderTo: 'container',
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

        plotOptions: {
            series: {
                //pointStart: 2010
            }
        },

        colors: ['#fdd695', '#57bdb9'],

        series: [
            {
                dashStyle: 'dash',
                name: 'prospects',
                data: w_prospects,
                pointStart: lastweek,
                pointInterval: 24*36e5
            },{
                name: 'Supporters',
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

 
    ///////////////////////////////////////////////////////////////////////////
     var options = {
            credits: {
                enabled: false
            },
            tooltip: {
                valueDecimals: 0,
            },
            chart: {
                renderTo: 'container',
                defaultSeriesType: 'spline',
            },
            title: {
                text: ' '
            },
            xAxis: {
                type: 'datetime',
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

///////////////////////////////////////////////////////////////////////////////
        var options2 = {
            credits: {
                enabled: false
            },
            tooltip: {
                valueDecimals: 0,
            },
            chart: {
                renderTo: 'newsupcontainer',
                defaultSeriesType: 'spline',
            },
            title: {
                text: ' '
            },
            xAxis: {
                type: 'datetime',
                labels: {
                    format: '{value:%Y-%m-%d}',
                    rotation: 45,
                    align: 'left'
                }
            },
            yAxis: {
                title: {
                    text: '{$newSupText}'
                }
            },
            legend: {
                layout: 'horizontal',
                align: 'center',
                verticalAlign: 'top'
            },
            colors: ['#3f80be'],
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

        $('#inputGroupSelect01').change(function () {
            if ($('#inputGroupSelect01').val() == 'week') {
                options.series = [
                    {
                        dashStyle: 'dash',
                        name: 'prospects',
                        data: w_prospects,
                        pointStart: lastweek,
                        pointInterval: 24 * 36e5
                    }, {
                        name: 'Supporters',
                        data: w_supporters,
                        pointStart: lastweek,
                        pointInterval: 24 * 36e5
                    }]
                var chart = new Highcharts.Chart(options);

            }

            if ($('#inputGroupSelect01').val() == 'month') {
                options.series = [
                    {
                        dashStyle: 'dash',
                        name: 'prospects',
                        data: m_prospects,
                        pointStart: lastmonth,
                        pointInterval: 24 * 36e5
                    }, {
                        name: 'Supporters',
                        data: supporters,
                        pointStart: lastmonth,
                        pointInterval: 24 * 36e5
                    }]
                var chart = new Highcharts.Chart(options);
            }

            if ($('#inputGroupSelect01').val() == 'threemonth') {
                options.series = [
                    {
                        dashStyle: 'dash',
                        name: 'prospects',
                        data: t_prospects,
                        pointStart: threemonth,
                        pointInterval: 24 * 36e5
                    }, {
                        name: 'Supporters',
                        data: t_supporters,
                        pointStart: threemonth,
                        pointInterval: 24 * 36e5
                    }]
                var chart = new Highcharts.Chart(options);
            }

            if ($('#inputGroupSelect01').val() == 'sixmonth') {
                options.series = [
                    {
                        dashStyle: 'dash',
                        name: 'prospects',
                        data: prospects,
                        pointStart: sixmonth,
                        pointInterval: 24 * 36e5
                    }, {
                        name: 'Supporters',
                        data: supporters,
                        pointStart: sixmonth,
                        pointInterval: 24 * 36e5
                    }]
                var chart = new Highcharts.Chart(options);
            }

        });

        /////////////////////////////////////////////////////////////////////////
        $('#inputGroupSelect02').change(function () {
            if ($('#inputGroupSelect02').val() == 'week') {
                options2.series =  [
                    {
                        name: '{$newSupText}',
                        data: w_newSupporters,
                        pointStart: lastweek,
                        pointInterval: 24*36e5
                    }]

                var chart = new Highcharts.Chart(options2); 
            }
            
            if ($('#inputGroupSelect02').val() == 'month') {
                options2.series = [
                    {
                        name: '{$newSupText}',
                        data: m_newSupporters,
                        pointStart: lastmonth,
                        pointInterval: 24 * 36e5
                    }]

                var chart = new Highcharts.Chart(options2);
            }

            if ($('#inputGroupSelect02').val() == 'threemonth') {
                options2.series = [
                    {
                        name: '{$newSupText}',
                        data: t_newSupporters,
                        pointStart: threemonth,
                        pointInterval: 24 * 36e5
                    }]
                var chart = new Highcharts.Chart(options2);
            }

            if ($('#inputGroupSelect02').val() == 'sixmonth') {
                options2.series = [
                    {
                        name: '{$newSupText}',
                        data: newSupporters,
                        pointStart: sixmonth,
                        pointInterval: 24 * 36e5
                    }]
                var chart = new Highcharts.Chart(options2);
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
                    <div class="panel-head"><?php echo Yii::t('messages', 'Total Supporters');?></div>
                    <div class="content-area">
                        <div class="row no-gutters">
                            <div class="col-md-8 col-xl-9">

                            </div>
                            <div class="col-md-4 col-xl-3">
                                <form>
                                    <div class="input-group mb-3 mt-4 mt-md-0">
                                        <div class="input-group-prepend">
                                            <label class="input-group-text"
                                                   for="inputGroupSelect01">
                                                <?php echo Yii::t('messages', 'Range'); ?>
                                            </label>
                                        </div>
                                        <select class="custom-select" id="inputGroupSelect01">
                                            <option value="sixmonth"><?php echo Yii::t('messages','Since Six Month'); ?></option>
                                            <option value="threemonth"><?php echo Yii::t('messages','Since Three Month'); ?></option>
                                            <option value="month"><?php echo Yii::t('messages','Since a Month'); ?></option>
                                            <option selected value="week"><?php echo Yii::t('messages','Since a Week'); ?></option>
                                        </select>

                                    </div>

                                </form>
                            </div>
                        </div>
                        <div class="row no-gutters">
                            <div class="col-md-12">
                                <div id="container"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">

                <div class="content-panel-sub">
                    <div class="panel-head"><?php echo Yii::t('messages', 'New supporters'); ?></div>
                    <div class="content-area">
                        <div class="row no-gutters">
                            <div class="col-md-8 col-xl-9">

                            </div>
                            <div class="col-md-4 col-xl-3">
                                <form>
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <label class="input-group-text"
                                                   for="inputGroupSelect02"><?php echo Yii::t('messages', 'Range'); ?></label>
                                        </div>
                                        <select class="custom-select" id="inputGroupSelect02">
                                            <option value="sixmonth"><?php echo Yii::t('messages','Since Six Month'); ?></option>
                                            <option value="threemonth"><?php echo Yii::t('messages','Since Three Month'); ?></option>
                                            <option value="month"><?php echo Yii::t('messages','Since a Month'); ?></option>
                                            <option selected value="week"><?php echo Yii::t('messages','Since a Week'); ?></option>
                                        </select>
                                    </div>

                                </form>
                            </div>
                        </div>
                        <div class="row no-gutters">
                            <div class="col-md-12">
                                <div id="newsupcontainer"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <?php

        $this->registerJs("
          
                $(document).ready(function () { 
                  $(document).on('click', '.view', function (e){
                    e.preventDefault();
                    console.log
                    var Url     = $(this).attr('href');
                    var pjaxContainer = $(this).attr('pjax-container');
                    var keyId = $(this).attr('key-id');
                                $.ajax({
                                    url:   Url,
                                    type:  'post', 
                                    data: {
                                    _csrf:'".Yii::$app->request->csrfToken."',
                                    id:keyId,
                                },
                                error: function (xhr, status, error) {
                                    console.log('There was an error with your request.' 
                                          + xhr.responseText);
                                  }
                                }).done(function (data) {
                                      $('#iframe-viewDetails').html(data)
                                      return false;
                                });
                                
                             $('#viewDetails').on('hidden.bs.modal', function (e) {
                               $('#iframe-viewDetails').html('');
                            });
                            return false;
                    });
                });
                
                ");

        ?>
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