<?php

use app\components\ToolKit;
use app\models\ABTestingCampaign;
use app\models\Campaign;
use app\models\CampaignUsersSearch;
use app\models\User;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = Yii::t('messages', 'Detail statistics of the campaign');
$this->titleDescription = Yii::t('messages', 'Status of each email (bounced, opened, spam, sent)');
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Sent Campaigns'), 'url' => ['campaign/admin']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Detailed Stats')];
$page = null;
$sort = null;

if (isset($_GET['page'])) {
    $page = $_GET["page"];
}

if (isset($_GET['sort'])) {
    $sort = $_GET["sort"];
}

$getExportFile = Url::to(['get-export-file', 'id' => $_GET['id']]);
$isSmsStat = false;
$exportCSV = <<< JS
$('#export-button').on('click', function(e) {
    e.preventDefault();
   
    var emailStatus='';
    var searchName='';
    var searchEmail='';
    var searchMobile='';
    var type = 'email';
    var sort = '{$sort}';
    
    var url_string = window.location; 
    var url = new URL(url_string);
    
    if($('#campaignuserssearch-emailstatus').val()!=''){
        emailStatus=$('#campaignuserssearch-emailstatus').val();
    }
    if($('#campaignuserssearch-name').val()!=''){
        searchName=$('#campaignuserssearch-name').val();
    }
    if($('#campaignuserssearch-email').val()!=''){
        searchEmail=$('#campaignuserssearch-email').val();
    }
    if($('#campaignuserssearch-mobile').val()!=''){
        searchMobile=$('#campaignuserssearch-mobile').val();
    }
    
    if(url.searchParams.get("sort")){
         sort = url.searchParams.get("sort"); 
    }
      
    $.ajax({
            url:'$getExportFile &emailStatus='+emailStatus+'&name='+searchName+'&email='+searchEmail+'&mobile='+searchMobile+'&type='+type+'&sort='+sort+'&export=true',
            type:"post",
            data:'&export=true',
            success: function(data){ 
                $('#campaign-users-grid').removeClass('grid-view-loading');
                 window.location = '$getExportFile &emailStatus='+emailStatus+'&name='+searchName+'&email='+searchEmail+'&mobile='+searchMobile+'&sort='+sort+'&export=true';                
            } 
        });
    });
JS;

$this->registerJs($exportCSV);
?>

<div class="row no-gutters">
    <div class="content-panel col-md-12">
        <div class="content-inner">
            <div class="content-area">
                <div class="row">
                    <div class="col-md-12 mb-4">
                        <div class="progress">
                            <div class="progress-bar bg-not-opened"
                                 title="<?php echo Yii::t('messages', 'Sent') . " " . $sentCount; ?>"
                                 data-toggle="tooltip" role="progressbar" style="width: <?php echo $sentCount; ?>"
                                 aria-valuenow="<?php echo $sentCount; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            <div class="progress-bar bg-clicked"
                                 title="<?php echo Yii::t('messages', 'Clicked') . " " . $clickedCount; ?>"
                                 data-toggle="tooltip" role="progressbar" style="width: <?php echo $clickedCount; ?>"
                                 aria-valuenow="<?php echo $clickedCount; ?>" aria-valuemin="0"
                                 aria-valuemax="100"></div>
                            <div class="progress-bar bg-opened"
                                 title="<?php echo Yii::t('messages', 'Opened') . " " . $openedCount; ?>"
                                 data-toggle="tooltip" role="progressbar" style="width: <?php echo $openedCount; ?>"
                                 aria-valuenow="<?php echo $openedCount; ?>" aria-valuemin="0"
                                 aria-valuemax="100"></div>
                            <div class="progress-bar bg-bounced"
                                 title="<?php echo Yii::t('messages', 'Bounced') . " " . $bouncedCount; ?>"
                                 data-toggle="tooltip" role="progressbar" style="width: <?php echo $bouncedCount; ?>"
                                 aria-valuenow="<?php echo $bouncedCount; ?>" aria-valuemin="0"
                                 aria-valuemax="100"></div>
                            <div class="progress-bar bg-blocked"
                                 title="<?php echo Yii::t('messages', 'Blocked') . " " . $blockedCount; ?>"
                                 data-toggle="tooltip" role="progressbar" style="width: <?php echo $blockedCount; ?>"
                                 aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                            <div class="progress-bar bg-spam"
                                 title="<?php echo Yii::t('messages', 'Spam') . " " . $spamCount; ?>"
                                 data-toggle="tooltip" role="progressbar" style="width: <?php echo $spamCount; ?>"
                                 aria-valuenow="<?php echo $spamCount; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            <div class="progress-bar bg-failed"
                                 title="<?php echo Yii::t('messages', 'Failed') . " " . $failedCount; ?>"
                                 data-toggle="tooltip" role="progressbar" style="width: <?php echo $failedCount; ?>"
                                 aria-valuenow="<?php echo $failedCount; ?>" aria-valuemin="0"
                                 aria-valuemax="100"></div>

                            <div class="progress-bar bg-unsubs"
                                 title="<?php echo Yii::t('messages', 'Unsubs') . " " . $unsubscribedCount; ?>"
                                 data-toggle="tooltip" role="progressbar"
                                 style="width: <?php echo $unsubscribedCount; ?>"
                                 aria-valuenow="<?php echo $unsubscribedCount; ?>" aria-valuemin="0"
                                 aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="progress-keys">
                            <span><?php echo Yii::t('messages', 'Sent') ?> <span
                                        class="badge badge-pill bg-not-opened"><?php echo $sentCount . " - " . "(" . $sentCountTotal . ")"; ?></span></span>
                        </div>
                        <div class="progress-keys">
                            <span><?php echo Yii::t('messages', 'Clicked'); ?><span
                                        class="badge badge-pill bg-clicked"><?php echo $clickedCount . " - " . "(" . $clickedCountTotal . ")"; ?></span></span>
                        </div>
                        <div class="progress-keys">
                            <span><?php echo Yii::t('messages', 'Opened'); ?><span
                                        class="badge badge-pill bg-opened"><?php echo $openedCount . " - " . "(" . $openedCountTotal . ")"; ?></span></span>
                        </div>
                        <div class="progress-keys">
                            <span><?php echo Yii::t('messages', 'Bounced') ?><span
                                        class="badge badge-pill bg-bounced"><?php echo $bouncedCount . " - " . "(" . $bouncedCountTotal . ")"; ?></span></span>
                        </div>

                        <div class="progress-keys">
                            <span><?php echo Yii::t('messages', 'Blocked'); ?> <span
                                        class="badge badge-pill bg-blocked"><?php echo $blockedCount . " - " . "(" . $blockedCountTotal . ")"; ?></span></span>
                        </div>
                        <div class="progress-keys">
                            <span><?php echo Yii::t('messages', 'Spam'); ?><span
                                        class="badge badge-pill bg-spam"><?php echo $spamCount . " - " . "(" . $spamCountTotal . ")"; ?></span></span>
                        </div>
                        <div class="progress-keys">
                            <span><?php echo Yii::t('messages', 'Failed'); ?><span
                                        class="badge badge-pill bg-failed"><?php echo $failedCount . " - " . "(" . $failedCountTotal . ")"; ?></span></span>
                        </div>
                        <div class="progress-keys">
                            <span><?php echo Yii::t('messages', 'Unsubs'); ?> <span
                                        class="badge badge-pill bg-unsubs"><?php echo $unsubscribedCount . " - " . "(" . $unsubscribedCountTotal . ")"; ?></span></span>
                        </div>
                    </div>
                </div>

                <div class="content-panel-sub mt-5">
                    <div class="panel-head">
                        <?php echo Yii::t('messages', 'Search by') ?>
                    </div>
                </div>

                <div class="search-form" style="display:block">
                    <?php echo $this->render('_search', array(
                        'model' => $model,
                        'isAdmin' => true,
                        'isSmsStat' => $isSmsStat
                    )); ?>
                </div>

                <?php
                \yii\widgets\Pjax::begin();
                echo GridView::widget([
                    'id' => 'campaign-users-grid',
                    'dataProvider' => $dataProvider,
                    'summary' => '<div class="text-right results-count mt-4">' . Yii::t('messages', 'Displaying {begin}-{end} of {totalCount} results.') . '</div>',
                    'pager' => [
                        'firstPageLabel' => '',
                        'firstPageCssClass' => 'first',
                        'activePageCssClass' => 'selected active-page',
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
                    'tableOptions' => ['class' => 'table table-striped table-bordered'],
                    'headerRowOptions' => array('class' => 'table-wrap table-custom'),
                    'columns' => [
                        [
                            'format' => 'html',
                            'attribute' => Yii::t('messages','nom'),
                            'value' => function ($dataProvider) {
                                return ToolKit::isEmpty($dataProvider['name']) ? "N/A" : $dataProvider['name'];
                            },
                        ],
                        [
                            'format' => 'raw',
                            'attribute' => Yii::t('messages','email'),
                            'value' => function ($model) {
                                return $model->getAttributeValue($model->userId, 'email');
                            },
                        ],
                        [
                            'format' => 'raw',
                            'attribute' => Yii::t('messages','mobile'),
                            'value' => function ($model) {
                                return $model->getAttributeValue($model->userId, 'mobile');
                            },
                        ],
                        [
                            'format' => 'raw',
                            'attribute' => Yii::t('messages','Clicked Urls'),
                            'value' => function ($model) {
                                return $model->getClickUrl($model->clickedUrls);
                            },
                        ],
                        [
                            'format' => 'raw',
                            'attribute' => Yii::t('messages','keywords'),
                            'value' => function ($model) {
                                return User::getUserKeywordNames($model->keywords);
                            },
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'headerOptions' => ['style' => 'text-align: center'],
                            'contentOptions' => ['style' => 'text-align: center'],
                            'template' => '{keywordUpdate}',
                            'buttons' => [
                                'keywordUpdate' => function ($url, $dataProvider, $key) {
                                    if (Yii::$app->user->checkAccess('People.Update')) {
                                        $url = Yii::$app->urlManager->createUrl(["people/update-people-ajax", "id" => $dataProvider->userId, "check" => 1]);
                                        return Html::a('<span class="fa fa-edit fa-lg"></span>', $url, [
                                            'data-toggle' => 'modal',
                                            'data-target' => '#editKeywords',
                                            'onClick' => "
                                            $(\"#iframe-editKeywords\").attr(\"src\",'$url');
                                            $(\"#editKeywords\").on(\"hidden.bs.modal\", function (e) {
                                                $(\"#iframe-editKeywords\").attr(\"src\", \"\");
                                                $.pjax.reload({container: \"#campaign-users-grid\"});  
                                            });
                                            return false;
                                            "
                                        ]);
                                    }
                                }
                            ],
                        ],
                        [
                            'attribute' => 'createdAt',
                            'format' => 'html',
                            'value' => function ($model) {
                                return User::convertSystemTime($model->createdAt);
                            }
                        ],
                        [
                            'format' => 'raw',
                            'value' => function ($dataProvider) {
                                $campaignModel = new \app\models\CampaignUsers();
                                return $campaignModel->getEmailStatusLabel($dataProvider['emailStatus'], $dataProvider['isUnsubEmail']);
                            }
                        ]
                    ]
                ]);
                \yii\widgets\Pjax::end();
                ?>
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
                <h5 class="modal-title"
                    id="exampleModalLongTitle"> <?php echo Yii::t('messages', 'Edit Keywords'); ?> </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <iframe id="iframe-editKeywords" src="" frameborder="0" scrolling="yes" width="100%"
                    height="200px"></iframe>
        </div>
    </div>
</div>
<!-- END // Edit Keywords Modal -->
