<?php
use app\components\ToolKit;
use app\models\CampaignUsers;
use app\models\User;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

$this->title = Yii::t('messages', 'Detail statistics of the campaign');
$this->titleDescription = Yii::t('messages', 'Status of each SMS (Pending, Delivered, Failed)');
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Sent Campaigns'), 'url' => ['campaign/admin']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Detailed Stats')];

//checking access
$isAdmin = ( Yii::$app->user->checkAccess("superadmin") == false ) ? true :false;
$isSmsStat = true;



$getExportFile = Url::to(['get-export-file', 'id' => $_GET['id']]);
$exportCSV = <<< JS
$('#export-button-sms').on('click', function(e) {
    e.preventDefault();
    var smsStatus='';
    var searchName='';
    var searchEmail='';
    var searchMobile='';
    var type='sms';
    if($('#campaignuserssearch-smsstatus').val()!=''){
        smsStatus=$('#campaignuserssearch-smsstatus').val();
    }
    if($('#campaignuserssearch-name').val()!=''){
        searchName=$('#campaignuserssearch-name').val();
    }
    if($('#campaignuserssearch-email').val()!=''){
        searchEmail=$('#campaignuserssearch-email').val();
    }
    
    
       $.ajax({
            url:'$getExportFile &smsStatus='+smsStatus+'&name='+searchName+'&email='+searchEmail+'&type='+type,
            type:"post",
            data:'&export=true',
            success: function(data){ 
                $('#campaign-users-grid').removeClass('grid-view-loading');
                 window.location = '$getExportFile &smsStatus='+smsStatus+'&name='+searchName;                
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
                <div class="row" style="margin-bottom: 10px">
                    <div class="col-md-12">
                        <div class="progress-keys">
                            <span><?php echo Yii::t('messages', 'Pending') ?> <span
                                        class="badge badge-pill bg-not-opened"><?php echo $pendingCount; ?></span></span>
                        </div>
                        <div class="progress-keys">
                            <span><?php echo Yii::t('messages', 'Delivered'); ?><span
                                        class="badge badge-pill bg-clicked"><?php echo $deliveredCount; ?></span></span>
                        </div>
                        <div class="progress-keys">
                            <span><?php echo Yii::t('messages', 'Failed'); ?><span
                                        class="badge badge-pill bg-failed"><?php echo $failedCount; ?></span></span>
                        </div>
                    </div>
                </div>

                <div class="search-form">
                    <?php
                    echo   Yii::$app->controller->renderPartial('_search', array(
                        'model' => $model,
                        'isAdmin' => $isAdmin,
                        'isSmsStat' => $isSmsStat
                    )); ?>
                </div><!-- search-form -->
                <?php Pjax::begin(); ?>

                <?= GridView::widget([
                    'id' => 'broadcast-message-grid',
                    'dataProvider' => $dataProvider,
                    'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => ''],
                    'tableOptions' => ['class' => 'table table-striped table-bordered'],
                    'summary' => '<div class="text-right results-count mt-4">' . Yii::t('messages', 'Displaying {begin}-{end} of {totalCount} imports') . '</div>',
                    'pager' => [
                        'firstPageLabel' => '',
                        'firstPageCssClass' => 'first',
                        'activePageCssClass' => 'selected',
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
                        <div class="table-wrap table-custom">{items}</div>
                        <div class="row no-gutters d-flex flex-sm-row-reverse flex-sm-column-reverse flex-md-row">
                        <div class="col-md-6"></div>
                        <div class="col-md-6">
                            <div class="float-right"><nav aria-label="Page navigation">{pager}</nav></div>
                        </div></div>',
                    'headerRowOptions' => ['class' => 'table-wrap table-custom'],
                    'columns' => [
                        [
                            'format' => 'raw',
                            'attribute' => Yii::t('messages','Name'),
                            'value' => function ($data) {
                                 return ToolKit::isEmpty($data['name']) ? "N/A" : $data['name'];
                            }
                        ],
                        [
                            'format' => 'raw',
                            'attribute' => 'mobile',
                            'value' => function ($data) {
                                 return  CampaignUsers::getMobile($data['userId']);
                            }
                        ],
                        [
                            'format' => 'html',
                            'attribute' => 'createdAt',
                            'value' => function ($data) {
                                return User::convertDBTime($data['createdAt']);
                            }

                        ],
                        [
                            'format' => 'raw',
                            'value' => function ($data) {
                                   return $data->getSmsStatusLabel($data->smsStatus);
                            }
                        ],
                    ],
                ]); ?>

                <?php Pjax::end(); ?>
            </div>
        </div>
    </div>
</div>

