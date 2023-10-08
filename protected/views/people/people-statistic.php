<?php

use app\models\PeopleStat;
use kartik\select2\Select2;
use yii\helpers\Html;

Yii::$app->toolKit->registerHighchartsScripts();


$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'People'), 'url' => '#'];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Population')];

$this->title = Yii::t('messages', 'Statistics');
$this->titleDescription = Yii::t('messages', 'Your online community graphical representation for Supporters, Prospect, Non Supporters & Unknown');

echo  Yii::$app->controller->renderPartial('_tabMenu');

?>
<style>
    .div-min-height{
        min-height: 400px !important;
    }
</style>
<script>
    $(document).ready(function () {
        $('#statistic-search').click(function () {

            var keywords = $('#User_keywords').val();
            if (null != keywords) {
                var keywordsArray = keywords.toString().split(",");
                if (keywordsArray.length > 1 && keywordsArray.length <= 5) {
                    $('#chart-keywords').html('<div class="progress loader themed-progress">\n' +
                        '                            <div class="progress-bar progress-bar-striped progress-bar-animated"\n' +
                        '                                 role="progressbar" style="width: 100%"\n' +
                        '                                 aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>\n' +
                        '                        </div>');
                    jQuery.ajax({
                        'type': 'GET',
                        'url': $(this).attr('href'),
                        'data': 'keywords=' + keywords,
                        'success': function (data) {
                            $('#chart-keywords').html(data +  '<div id="keywordChart"></div>');
                            return false;
                        }
                    });
                } else {
                    $('#chart-keywords').html('<span>Please select Minimum 2, Maximum 5 Keywords</span>');
                    alert(<?php echo Yii::t('messages', "'Please select Minimum 2, Maximum 5 Keywords'") ?>);
                }
            } else {
                $('#chart-keywords').html('<span>Please Select at least 2 Keywords.</span>');
                alert(<?php echo Yii::t('messages', "'Please Select at least 2 Keywords.'") ?>);
            }
        });
    });
</script>

<?php
$message = Yii::t('messages', 'No data to show');

$noDataMsg = <<<JS
  
        if($isKeywordNoData){
             $('#chart-keywords') . html('$message , $keywordChartTitle');
         }
         if($isAgeNoData){
         $('#chart-age') . html('$message , $ageChartTitle');
         }
         if($isZipNoData){
         $('#chart-zip') . html('$message , $zipChartTitle');
         }
         if($isCityNoData){
         $('#chart-city') . html('$message , $cityChartTitle');
         }
         if($isGenderNoData){
         $('#chart-gender') . html('$message , $genderChartTitle');
         }
         if($isTypeNoData){
         $('#chart-category') . html('$message , $typeChartTitle');
         }
         if($isTeamNoData){
         $('#teamChart') . html('$message , $teamChartTitle');
         }
         if($isEmailNoData){
         $('#chart-email') . html('$message , $emailChartTitle');
         }
         if($isContactNoData){
         $('#chart-media') . html('$message , $contactChartTitle');
         }
         
JS;
$this->registerJs($noDataMsg);

 ?>

<div class="content-inner">
    <div class="content-area">

        <div class="row">
            <div class="col-md-12">
                <ul class="nav nav-pills mb-3" id="chartList" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="keywordStat" data-toggle="pill" href="#v-pills-keywords"
                           role="tab" aria-controls="pills-home"
                           aria-selected="true"><?php echo Yii::t('messages', 'Keywords'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="ageStat" data-toggle="pill" href="#v-pills-age" role="tab"
                           aria-controls="pills-profile"
                           aria-selected="false"><?php echo Yii::t('messages', 'Age'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="cityStat" data-toggle="pill" href="#v-pills-city" role="tab"
                           aria-controls="pills-contact"
                           aria-selected="false"><?php echo Yii::t('messages', 'City'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="zipStat" data-toggle="pill" href="#v-pills-zip" role="tab"
                           aria-controls="pills-contact"
                           aria-selected="false"><?php echo Yii::t('messages', 'Zip'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="genderStat" data-toggle="pill" href="#v-pills-gender" role="tab"
                           aria-controls="pills-contact"
                           aria-selected="false"><?php echo Yii::t('messages', 'Gender'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="typeStat" data-toggle="pill" href="#v-pills-type" role="tab"
                           aria-controls="pills-contact"
                           aria-selected="false"><?php echo Yii::t('messages', 'Category'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="campaignStat" data-toggle="pill" href="#v-pills-media" role="tab"
                           aria-controls="pills-contact"
                           aria-selected="false"><?php echo Yii::t('messages', 'Media'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="emailStat" data-toggle="pill" href="#v-pills-email" role="tab"
                           aria-controls="pills-contact"
                           aria-selected="false"><?php echo Yii::t('messages', 'Email'); ?></a>
                    </li>
                </ul>
            </div>
            <div class="col-md-12">
                <div class="tab-content" id="v-pills-tabContent">
                    <div class="tab-pane active" id="v-pills-keywords" role="tabpanel">
                        <div class="form-row">
                            <div class="form-group col-md-10">
                                <?php
                                echo Select2::widget([
                                    'name' => 'keywords',
                                    'data' => $tagList,
                                    'size' => Select2::MEDIUM,
                                    'options' => [
                                        'class' => 'form-control form-control-selectize',
                                        'multiple' => true,
                                        'create' => false,
                                        'placeholder' => Yii::t('messages', 'Keywords'),
                                        'useWithBootstrap' => true,

                                    ]]);
                                ?>


                            </div>
                            <div class="form-group col-md-2">

                                <?php
                                echo Html::submitButton('<i class="fa fa-search"></i> '.Yii::t
                                ('messages', 'Search'), ['class' => 'btn btn-primary','id' => 'statistic-search']);
                                ?>
                            </div>
                        </div>
                        <?php yii::$app->controller->renderPartial('_pieChart', array('isNoData' => $isKeywordNoData, 'category' => $keywordOrder, 'data' => $keywordData, 'name' => $keywordChartTitle, 'total' => $keywordTotalCount, 'container' => 'chart-keywords', 'script_name' => 'keyword-Chart-top5', 'type' => PeopleStat::KEYWORD_GRAPH, 'xAxis' => Yii::t('messages', 'Keywords'))); ?>
                        <div id="chart-keywords"></div>
                    </div>
                    <div class="tab-pane" id="v-pills-age" role="tabpanel">
                        <?php yii::$app->controller->renderPartial('_pieChart', array('isNoData' => $isAgeNoData, 'category' => $ageRangeOrder, 'data' => $ageData, 'name' => $ageChartTitle, 'total' => $total, 'tagList' => $tagList, 'container' => 'chart-age', 'script_name' => 'age-Chart', 'type' => PeopleStat::AGE_GRAPH)); ?>
                        <div id="chart-age"></div>
                    </div>
                    <div class="tab-pane" id="v-pills-city" role="tabpanel">
                        <?php yii::$app->controller->renderPartial('_pieChart', array('isNoData' => $isCityNoData, 'category' => $cityOrder, 'data' => $cityData, 'name' => $cityChartTitle, 'total' => $cityTotalCount, 'container' => 'chart-city', 'script_name' => 'city-Chart', 'type' => PeopleStat::CITY_GRAPH, 'xAxis' => Yii::t('messages', 'Cities'))); ?>
                        <div id="chart-city"></div>
                    </div>
                    <div class="tab-pane" id="v-pills-zip" role="tabpanel">
                        <?php yii::$app->controller->renderPartial('_pieChart', array('isNoData' => $isZipNoData, 'category' => $zipOrder, 'data' => $zipData, 'name' => $zipChartTitle, 'total' => $zipTotalCount, 'container' => 'chart-zip', 'script_name' => 'zip-Chart', 'type' => PeopleStat::ZIP_GRAPH, 'xAxis' => Yii::t('messages', 'Zipcodes'))); ?>
                        <div id="chart-zip"></div>
                    </div>
                    <div class="tab-pane" id="v-pills-gender" role="tabpanel">
                        <?php yii::$app->controller->renderPartial('_pieChart', array('isNoData' => $isGenderNoData, 'category' => $genderOrder, 'data' => $genderData, 'name' => $genderChartTitle, 'total' => $genderTotalCount, 'container' => 'chart-gender', 'script_name' => 'gender-Chart', 'type' => PeopleStat::GENDER_GRAPH, 'xAxis' => Yii::t('messages', 'Gender'))); ?>
                        <div id="chart-gender"></div>
                    </div>
                    <div class="tab-pane" id="v-pills-type" role="tabpanel">
                        <?php yii::$app->controller->renderPartial('_pieChart', array('isNoData' => $isTypeNoData, 'data' => $typeData, 'name' => $typeChartTitle, 'total' => $typeTotalCount, 'container' => 'chart-category', 'script_name' => 'type-Chart', 'type' => PeopleStat::TYPE_GRAPH)); ?>
                        <div id="chart-category"></div>
                    </div>
                    <div class="tab-pane" id="v-pills-media" role="tabpanel">
                        <?php yii::$app->controller->renderPartial('_pieChart', array('isNoData' => $isContactNoData, 'category' => $contactOrder, 'data' => $contactData, 'name' => $contactChartTitle, 'total' => $contactTotalCount, 'container' => 'chart-media', 'script_name' => 'contact-Chart', 'type' => PeopleStat::MEDIA_GRAPH, 'xAxis' => Yii::t('messages', 'Campaign Media'))); ?>
                        <div id="chart-media"></div>
                    </div>
                    <div class="tab-pane" id="v-pills-email" role="tabpanel">
                        <?php yii::$app->controller->renderPartial('_pieChart', array('isNoData' => $isEmailNoData, 'data' => $emailData, 'name' => $emailChartTitle, 'total' => $emailTotalCount, 'container' => 'chart-email', 'script_name' => 'email-Chart', 'type' => PeopleStat::EMAIL_GRAPH)); ?>
                        <div id="chart-email"></div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

</div>
</div>

</div>