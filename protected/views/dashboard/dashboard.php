<?php
Yii::$app->toolKit->registerHighchartsScripts();
?>
<style type="text/css">
    #line-chart-2 .highcharts-container  {
        max-width: 100% !important;
    }
</style>
<div class="dashboard">
    <div class="row highlights">
        <div class="col-sm-6 col-xl-3">
            <div class="boxes" id="box1">
                <div class="row">
                    <div class="icon col-4">
                        <i class="fa fa-user-circle"></i>
                    </div>
                    <div class="text col-8">
                        <div class="title row">
                            <?php echo Yii::t('messages', 'Total Users'); ?>
                        </div>
                        <div class="value row">
                            <?php echo number_format($userCount['total']); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="boxes" id="box2">
                <div class="row">
                    <div class="icon col-4">
                        <i class="fa fa-thumbs-up"></i>
                    </div>
                    <div class="text col-8">
                        <div class="title row">
                            <?php echo Yii::t('messages', 'Supporters'); ?>
                        </div>
                        <div class="value row">
                            <?php echo number_format($userCount['supporter']); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="boxes" id="box3">
                <div class="row">
                    <div class="icon col-4">
                        <i class="fa fa-dot-circle-o"></i>
                    </div>
                    <div class="text col-8">
                        <div>
                            <div class="title row">
                                <?php echo Yii::t('messages', 'Prospects'); ?>
                            </div>
                            <div class="value row">
                                <?php echo number_format($userCount['prospect']); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="boxes" id="box4">
                <div class="row">
                    <div class="icon col-4">
                        <i class="fa fa-thumbs-down"></i>
                    </div>
                    <div class="text col-8">
                        <div class="title row">
                            <?php echo Yii::t('messages', 'Non Supporters'); ?>
                        </div>
                        <div class="value row">
                            <?php echo number_format($userCount['nonSupporter']); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12 col-xl-8">
            <div class="content-panel">
                <div class="content-inner">
                    <div class="panel-head"><?php echo Yii::t('messages', 'Community Growth'); ?></div>
                    <div class="content-area chart-side-padding">
                        <div id="line-chart"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-12 col-xl-4">
            <div class="content-panel">
                <div class="content-inner">
                    <div class="panel-head"><?php echo Yii::t('messages', 'Community by Networks'); ?></div>
                    <div class="content-area chart-side-padding">
                        <div id="donut-chart"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
         <div class="col-md-12">
            <div class="content-panel">
                <div class="content-inner">
                    <div class="panel-head"><?php echo Yii::t('messages', 'Campaign Growth'); ?></div>
                    <div class="content-area chart-side-padding">
                        <div id="line-chart-2"></div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php
for ($m = 1; $m <= date('m',time()); $m++) {
    $months[] = Yii::t('messages', date('M', strtotime(date("Y") . "-" . $m . "-01")) );
}
$graphMonths = json_encode($months);
$numberOfusers = "No. Users";
$year = date('Y',time());
// $messNumbers = Yii::t('messages', 'Text');

$lineChart = <<<JS
Highcharts.chart('line-chart', {

        title: {
            text: ''
        },
        xAxis: {
            title: {
            text: {$year}
        },
            categories: {$graphMonths}
        },
        yAxis: {
            title: {
                text: 'No. Users'
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
        exporting: { enabled: false },
        colors: ['#57bdb9', '#9B59B6', '#2D5DE8', '#EA4B4B', '#FD80BE','#2CEBE8'],

        series: {$userCountByTimeLine},
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
JS;
$this->registerJs($lineChart);

$donutChart = <<<JS
Highcharts.chart('donut-chart', {
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: 0,
            plotShadow: false
        },
        
        title: {
            text: '',
            align: 'center',
            verticalAlign: 'middle',
            y: -10
        },

        tooltip: {
            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
        },
        exporting: { enabled: false },
        plotOptions: {
            pie: {
                dataLabels: {
                    useHTML: true,
                    enabled: true,
                    distance: -40,
                    style: {
                        fontWeight: 'bold',
                        color: 'white'
                    }
                },
                startAngle: 0,
                endAngle: 360,
                center: ['50%', '50%']
            }
        },

        colors: ['#0274b3', '#2daae2', '#3b5998', '#ed7e7d', '#fdd695'],

        series: [{
            type: 'pie',
            name: 'User',
            innerSize: '50%',
            data: {$userCountByCampaignMedia}
        }]
    });
JS;

$this->registerJs($donutChart);

$lineChatTwo = <<<JS
Highcharts.chart('line-chart-2', {

        title: {
            text: ''
        },
        xAxis: {
        title: {
            text: {$year}
        },
            categories: {$graphMonths}
        },
        yAxis: {
            title: {
                // text: '".Yii::t('messages', 'No. Campaigns')."'
            }
        },
        legend: {
            layout: 'horizontal',
            align: 'center',
            verticalAlign: 'top'
        },
        exporting: { enabled: false },
        plotOptions: {
            series: {
                //pointStart: 2010
            }
        },

        colors: ['#57bdb9', '#3f80be', '#F57A0D', '#0E97D7', '#970ED7', '#0E2FD7'],

        series: {$userCountByCampaignMediaTimeLine},
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

JS;

$this->registerJs($lineChatTwo);

?>
