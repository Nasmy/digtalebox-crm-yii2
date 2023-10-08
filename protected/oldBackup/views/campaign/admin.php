<?php

use app\models\ABTestingCampaign;
use app\models\Campaign;
use app\models\MessageTemplate;
use app\models\SearchCriteria;
use app\models\User;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var yii\web\View $this */
/* @var app\models\CampaignSearch $searchModel */
/* @var yii\data\ActiveDataProvider $dataProvider */

$this->title = Yii::t('messages', 'Sent Campaigns');
$this->titleDescription = Yii::t('messages', 'Manage your Email/SMS/Facebook/Twitter campaigns');
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Communication'), 'url' => ['#']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Sent Campaigns')];

?>
<?php
$script = <<< JS
    setInterval(function() {     
      $.pjax.reload({container:'#campaign-list-pjax', url: "/campaign/admin", timeout: false});
    }, 10000);
JS;
$this->registerJs($script);
?>
<div class="row no-gutters">
    <div class="content-panel col-md-12">
        <div class="content-inner">
            <div class="content-area">
                <div class="content-panel-sub">
                    <div class="panel-head">
                        <?php echo Yii::t('messages', 'Search by') ?>
                    </div>
                </div>

                <div class="search-form" style="display: block">
                    <?php
                    echo $this->render('_search', [
                        'model' => $searchModel
                    ]);
                    ?>
                </div>

                <?php Pjax::begin(['id' => 'campaign-list-pjax']); ?>
                <?php
                $stopConfirmMsg = Yii::t('messages', "Are you sure you want to stop the campaign?");
                $resumeConfirmMsg = Yii::t('messages', "Are you sure you want to resume the campaign?");
                $delConfirmMsg = Yii::t('messages', "Are you sure you want to delete the campaign?");
                $stopConfirmMsgAB = addslashes(Yii::t('messages', "Are you sure you want to stop the AB Test campaign? If stopped It cannot be resume."));
                echo GridView::widget([
                    'id' => 'campaign-grid',
                    'dataProvider' => $dataProvider,
                    'options' => ['class' => 'table-wrap table-custom'],
                    'tableOptions' => ['class' => 'table table-striped table-bordered'],
                    'summary' => '<div class="text-right results-count mt-4"> <div class="ajaxloading"></div>' . Yii::t('messages', 'Displaying {begin}-{end} of {totalCount} results.') . '</div>',
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
                    'layout' => '<div class="text-right  results-count">{summary}</div>
                        <div class="table-wrap table-custom">{items}</div>
                        <div class="row no-gutters d-flex flex-sm-row-reverse flex-sm-column-reverse flex-md-row"> 
                        <div class="col-md-6"></div>
                        <div class="col-md-6">
                            <div class="float-right"><nav aria-label="Page navigation">{pager}</nav></div>
                        </div></div>',
                    'headerRowOptions' => array('class' => 'table-wrap table-custom'),
                    'columns' => [
                         [
                            'attribute' => 'startDateTime',
                            'value' => function ($dataProvider) {
                                // return User::convertDBTime($dataProvider->startDateTime);
                                return $dataProvider->startDateTime;
                                // return implode(",", Yii::$app->user->getAssignedItems($model->id, true));
                            },
                        ],
                        [
                            'attribute' => 'messageTemplateId',
                            'value' => function ($dataProvider) use ($templates) {
                                return (Campaign::CAMP_TYPE_AB_TEST_EMAIL == $dataProvider->campType) ? Yii::t('messages', 'N/A') : MessageTemplate::getTemplateLabel($dataProvider->messageTemplateId, $templates);;
                            },
                        ],
                        [
                            'attribute' => 'searchCriteriaId',
                            'value' => function ($dataProvider) use ($savedSearches) {
                                return SearchCriteria::getSavedSearchLabel($dataProvider->searchCriteriaId, $savedSearches);
                            },
                        ],
                        [
                            'format' => 'html',
                            'attribute' => 'status',
                            'value' => function ($dataProvider) {
                                return Campaign::getStatusLabel($dataProvider->status, $dataProvider->startDateTime);
                            },
                        ],
                        [
                            'format' => 'html',
                            'attribute' => 'campType',
                            'value' => function ($dataProvider) {
                                return $dataProvider->getCampaignTypeLabel($dataProvider->campType);
                            },
                        ],
                        [
                            'attribute' => 'totalUsers',
                            'value' => function ($dataProvider) {
                                return $dataProvider->totalUsers;
                            },
                        ],
                        [
                            'attribute' => 'createdBy',
                            'value' => function ($dataProvider) {
                                return User::getNameById($dataProvider->createdBy);
                            },
                        ],

                        [
                            'class' => 'yii\grid\ActionColumn',
                            'headerOptions' => ['style' => 'text-align: right'],
                            'contentOptions' => ['style' => 'text-align: right'],
                            'template' => '{stop-campaign} {aBTestStop} {resume-campaign} {ab} {view-campaign-stats} {detailStat} {delete}',
                            'buttons' => [
                                'stop-campaign' => function ($url, $dataProvider, $key) {
                                    $stopConfirmMsg = Yii::t('messages', "Are you sure you want to stop the campaign?");
                                    if (($dataProvider->status == Campaign::CAMP_INPROGRESS || $dataProvider->status == Campaign::CAMP_PENDING) && Yii::$app->user->checkAccess('Campaign.StopCampaign')
                                        && Yii::$app->user->id == $dataProvider->createdBy && !Campaign::displayABStop($dataProvider->id, $dataProvider->status)) {
                                        return Html::a('<span class="fa fa-stop"></span>', false, [
                                            'pjax-container' => 'campaign-list-pjax',
                                            'onClick' => "
                                                var pjaxContainer = $(this).attr('pjax-container'); 
                                                if (confirm('$stopConfirmMsg')) {
                                                    $.ajax('$url', {
                                                        type: 'POST'
                                                    }).done(function(data) {
                                                        $('#statusMsg').html(data);
                                                        $.pjax.reload({container: '#' + $.trim(pjaxContainer)});
                                                    });
                                                }
                                                return false;
                                                ",
                                        ]);
                                    }
                                },

                                'resume-campaign' => function ($url, $dataProvider, $key) {
                                    if (($dataProvider->status == Campaign::CAMP_STOP)
                                        && Yii::$app->user->checkAccess('Campaign.ResumeCampaign')
                                        && Yii::$app->user->id == $dataProvider->createdBy
                                        && (Campaign::CAMP_TYPE_AB_TEST_EMAIL != $dataProvider->campType)
                                        || (($dataProvider->status == Campaign::CAMP_STOP)
                                            && (Campaign::CAMP_TYPE_AB_TEST_EMAIL == $dataProvider->campType)
                                            && (ABTestingCampaign::isRemainSchedule($dataProvider->aBTestId)))) {
                                        $resumeConfirmMsg = Yii::t('messages', "Are you sure you want to resume the campaign?");
                                        return Html::a('<span class="fa fa-play"></span>', false, [
                                            'pjax-container' => 'campaign-list-pjax',
                                            'onClick' => "
                                                        var pjaxContainer = $(this).attr('pjax-container'); 
                                                        if (confirm('$resumeConfirmMsg')) {
                                                            $.ajax('$url', {
                                                                type: 'POST'
                                                            }).done(function(data) {
                                                                $('#statusMsg').html(data);
                                                                $.pjax.reload({container: '#' + $.trim(pjaxContainer)});
                                                            });
                                                        }
                                                        return false;
                                                        ",
                                        ]);
                                    }
                                },
                                'ab' => function ($url, $dataProvider, $key) {
                                    if (Yii::$app->user->checkAccess('ABTesting.ViewCampaignStats') &&
                                        (Campaign::CAMP_TYPE_AB_TEST_EMAIL == $dataProvider->campType)) {
                                        $url = Yii::$app->urlManager->createUrl(['a-b-testing/view-campaign-stats', 'id' => $dataProvider->id]);
                                        return Html::a('<span class="fa fa-font fa-lg"></span>', false, [
                                            'data-toggle' => 'modal',
                                            'data-target' => '#viewStat',
                                            'onClick' => "
                                            // console.log($(this).attr(\"href\"));
                                              $(\"#iframe-stat\").attr(\"src\",'$url');
						                      return false;
                                                "
                                        ]);
                                    }
                                },
                                'view-campaign-stats' => function ($url, $dataProvider, $key) {
                                    if (Yii::$app->user->checkAccess('Campaign.ViewCampaignStats')) {
                                        return Html::a('<span class="fa fa-clock-o"></span>', false, [
                                            'data-toggle' => 'modal',
                                            'data-target' => '#viewStat',
                                            'onClick' => "
                                              $(\"#iframe-stat\").attr(\"src\",'$url');
						                      return false;
                                                "
                                        ]);
                                    }
                                },
                                'detailStat' => function ($url, $dataProvider, $key) {
                                    if (Yii::$app->user->checkAccessList(['CampaignUsers.Admin', 'CampaignUsers.SmsStat']) && ($dataProvider->campType == Campaign::CAMP_TYPE_EMAIL || $dataProvider->campType == Campaign::CAMP_TYPE_SMS || $dataProvider->campType == Campaign::CAMP_TYPE_ALL || $dataProvider->campType == Campaign::CAMP_TYPE_AB_TEST_EMAIL)) {
                                        if ($dataProvider->campType == Campaign::CAMP_TYPE_EMAIL || $dataProvider->campType == Campaign::CAMP_TYPE_ALL || $dataProvider->campType == Campaign::CAMP_TYPE_AB_TEST_EMAIL) {
                                            return Html::a('<span class="fa fa-bar-chart-o"></span>', Yii::$app->urlManager->createUrl(['campaign-users/admin', 'id' => $dataProvider->id]));
                                        } else {
                                            return Html::a('<span class="fa fa-bar-chart-o"></span>', Yii::$app->urlManager->createUrl(['campaign-users/sms-stat', 'id' => $dataProvider->id]));
                                        }
                                    }

                                },
                                'delete' => function ($url, $dataProvider, $key) {
                                    $delConfirmMsg = Yii::t('messages', "Are you sure you want to delete the campaign?");
                                    if (Yii::$app->user->checkAccess('Campaign.Delete')
                                        && Yii::$app->user->id == $dataProvider->createdBy) {
                                        return Html::a('<span class="fa fa-trash-o"></span>', false, [
                                            'pjax-container' => 'campaign-list-pjax',
                                            'onClick' => "
                                                var pjaxContainer = $(this).attr('pjax-container'); 
                                                if (confirm('$delConfirmMsg')) {
                                                    $.ajax('$url', {
                                                        type: 'POST'
                                                    }).done(function(data) {
                                                        $.pjax.reload({container: '#' + $.trim(pjaxContainer)});
                                                    });
                                                }
                                                return false;
                                                ",
                                        ]);
                                    }
                                }
                            ],
                        ],
                    ],
                ]);
                ?>
                <?php Pjax::end(); ?>
                <!-- Campaign statistics showing dialogbox -->
                <div class="modal fade" id="viewStat" tabindex="-1" role="dialog"
                     aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"
                                    id="exampleModalLongTitle"><?php echo Yii::t('messages', 'Campaign Progress') ?></h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>

                            <iframe id="iframe-stat" class="modal-body" src="" frameborder="0" scrolling="yes"
                                    width="100%" height="200px"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
