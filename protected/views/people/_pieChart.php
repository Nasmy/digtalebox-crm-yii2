<?php

use app\models\PeopleStat;

$percentage = Yii::t('messages','Percentage');
$userType = Yii::t('messages','User Type');
if ($type == PeopleStat::AGE_GRAPH && (!$isNoData)) {
    $data1 = '';
    $categoryList = json_decode($category,true);
    $category = array();
    foreach ($categoryList as $cat){
        $category[] = Yii::t('messages',$cat);
    }
    $category = json_encode($category);
    $dataLists = json_decode($data,true);
    $data = array();
    foreach ($dataLists as $dataList){
        $c =0;
        foreach($dataList['drillDown']['categories'] as $userList){
                $dataList['drillDown']['categories'][$c] = Yii::t('messages',$userList);
            $c++;
        }
        $data[] = $dataList;
    }
$data1 = json_encode($data);
$script = <<<JS
    $(function () {
        var colors = Highcharts.getOptions().colors,
            categories = $category,
            data = $data1,
            ageData = [],
            userTypeData = [],
            i,
            j,
            dataLen = data.length,
            drillDataLen,
            brightness;


        // Build the data arrays
        for (i = 0; i < dataLen; i += 1) {

            // add Age data
            ageData.push({
                name: categories[i],
                y: data[i].y,
                color: data[i].color
            });

            // add User Type data
            drillDataLen = data[i].drillDown.data.length;
            for (j = 0; j < drillDataLen; j += 1) {
                brightness = 0.2 - (j / drillDataLen) / 5;
                userTypeData.push({
                    name: data[i].drillDown.categories[j],
                    y: data[i].drillDown.data[j],
                    peopleCount: data[i].drillDown.peopleCount[j],
                    peopleAge: categories[i],
                    color: Highcharts.Color(data[i].drillDown.color[j]).brighten(brightness).get()
                });   
            }
        }

        // Create the chart
        Highcharts.chart('$container', {
            chart: {
                type: 'pie'
            },
            title: {
                text: '$name'
            },
            subtitle: {
                text: '$total'
            },
            yAxis: {
                title: {
                    text: '{chart_pie_yAxis}'
                }
            },
            plotOptions: {
                pie: {
                    shadow: false,
                    center: ['50%', '50%'],
                }
            },
            tooltip: {
                valueSuffix: '%'
            },
            series: [{
                name: '$percentage',
                data: ageData,
                size: '60%',
                dataLabels: {
                    formatter: function () {
                         return this.y > 0 ? this.point.name : null;
                    },
                    color: '#ffffff',
                    distance: -30
                }
            }, {
                name: '$userType',
                data: userTypeData,
                size: '80%',
                innerSize: '60%',
                dataLabels: {
                    formatter: function () {
                        // display only if larger than 0
                        return this.y > 0 ? '<b>' + this.point.name + '</b> <br>' + this.y + '% ('+this.point.peopleCount+')' : null;
                    }
                }
            }],
            responsive: {
            rules: [{
                condition: {
                    maxWidth: 500
                },
                chartOptions: {
                    series: [{
                        id: 'versions',
                        dataLabels: {
                            useHTML: false,
                            enabled: false
                        }
                    }]
                }
            }]
        }
        });
    });
 
JS;
$this->registerJs($script);

} else if (($type == PeopleStat::TYPE_GRAPH || $type == PeopleStat::TEAM_GRAPH || $type == PeopleStat::EMAIL_GRAPH) && (!$isNoData)) {
    $data1 = '';
    $dataLists = json_decode($data,true);
    $data = array();
    foreach ($dataLists as $dataList){
    $dataList['name'] = Yii::t('messages',$dataList['name']);
        $data[] = $dataList;
    }
    $data1 = json_encode($data);
$script2 = <<<JS
$(document).ready(function () {

    // Build the chart
    Highcharts.chart('$container', {
        colors: ['#9B59B6','#2D5DE8','#EA4B4B','#FD80BE','#2CEBE8','#FFC300','#566A1F','#363754','#61355C','#E3F10B'],
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false,
            type: 'pie'
        },
        title: {
            text: '$name'
        },
        subtitle: {
        text: '$total'
        },
        tooltip: {
            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: false
                },
                showInLegend: true
            }
        },
        series: [{
            name: '$percentage',
            colorByPoint: true,
            data: $data1
        }]
    });
})
JS;
$this->registerJs($script2);

} else if (($type == PeopleStat::KEYWORD_GRAPH || $type == PeopleStat::CITY_GRAPH || $type == PeopleStat::ZIP_GRAPH ||
        $type == PeopleStat::GENDER_GRAPH || $type == PeopleStat::MEDIA_GRAPH) && (!$isNoData)
) {
    $categoryList = json_decode($category,true);
    $category = array();
    foreach ($categoryList as $cat){
        $category[] = Yii::t('messages',$cat);
    }
    $category = json_encode($category);
    $users = Yii::t('messages','Total Users');
    $data1 = '';
    $dataLists = json_decode($data,true);
    $data = array();
    foreach ($dataLists as $dataList){
        $dataList['name'] = Yii::t('messages',$dataList['name']);
        $data[] = $dataList;
    }
    $data1 = json_encode($data);

$script3 = <<<JS
$(function () {
    Highcharts.chart('$container', {
    colors: ['#9B59B6','#2D5DE8','#EA4B4B','#FD80BE','#2CEBE8'],
    chart: {
    type: 'column'
    },
    title: {
    text: '$name'
    },
    xAxis: {
    categories: $category,
    title: {
        text: '<b>$xAxis</b>'
        },
    },
    yAxis: {
    min: 0,
        title: {
        text: '$users'
        },
        stackLabels: {
        enabled: true,
            style: {
            fontWeight: 'bold',
                color: (Highcharts.theme && Highcharts.theme.textColor) || 'gray'
            }
        }
    },
    legend: {
    align: 'right',
        x: -30,
        verticalAlign: 'top',
        y: 25,
        floating: true,
        backgroundColor: (Highcharts.theme && Highcharts.theme.background2) || 'white',
        borderColor: '#CCC',
        borderWidth: 1,
        shadow: false
    },
    tooltip: {
    headerFormat: '<b>{point.x}</b><br/>',
        pointFormat: '{series.name}: {point.y}<br/>Total: {point.stackTotal}'
    },
    plotOptions: {
    column: {
        stacking: 'normal',
            dataLabels: {
            enabled: true,
                color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white'
            }
        }
    },
    series: $data1,
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
});
JS;
$this->registerJs($script3);

} else {
    if ($type == PeopleStat::KEYWORD_GRAPH) {
        $message = Yii::t('messages', 'No data to show');
$script4 = <<<JS
$(function () {
     $('#chart-keywords') . html('$message , $name');
 });
JS;
$this->registerJs($script4);
    }
}

?>