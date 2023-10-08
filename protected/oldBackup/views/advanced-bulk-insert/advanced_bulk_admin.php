<?php

use app\models\AdvanceBulkInsert;
use app\models\User;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\models\advanceBulkInsertSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */


$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'People'), 'url' => ['admin']];
$this->params['breadcrumbs'][] = Yii::t('messages', 'Advanced Bulk Insert');

$this->title = Yii::t('messages', 'Advanced Bulk Insert');
$this->titleDescription = Yii::t('messages', 'Import mass people data');

$delConfirmMsg = Yii::t('messages', "Are you sure you want to delete ?");
$errorDownload = <<<JS

  $('.download-file').click(function(){
    var url = $(this).attr("href");
    window.open(url);
    return false;
  });

JS;
$this->registerJs($errorDownload);
?>

<?php
$script = <<< JS
    setInterval(function() {     
      $.pjax.reload({container:'#bulk-insert-pjax', url: "/advanced-bulk-insert/admin", timeout: false});
    }, 10000);
JS;
$this->registerJs($script);
?>

<div class="row no-gutters">
    <div class="content-panel col-md-12">
        <div class="content-inner">
            <div class="content-area">
                <?php
                echo Html::a("<i class='fa fa-plus'></i> " . Yii::t('messages', 'Add Zip File'), Yii::$app->urlManager->createUrl('advanced-bulk-insert/create'), ['class' => 'btn btn-primary ']);
                ?>
                <br><br>

                <?php Pjax::begin(['id' => 'bulk-insert-pjax', 'timeout' => false]); ?>

                <?= GridView::widget([
                    'id' => 'advanced-bulk-grid',
                    'dataProvider' => $dataProvider,
                    'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => ''],
                    'tableOptions' => ['class' => 'table table-striped table-bordered'],
                    'summary' => '<div class="text-right results-count mt-4">' . Yii::t('messages', 'Displaying {begin}-{end} of {totalCount} results.') . '</div>',
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
                    'layout' => '<div class="text-right results-count" style="margin: 0 !important;">{summary}</div>
                        <div class="table-wrap table-custom">{items}</div>
                        <div class="row no-gutters d-flex flex-sm-row-reverse flex-sm-column-reverse flex-md-row"> 
                        <div class="col-md-6"></div>
                        <div class="col-md-6">
                            <div class="float-right"><nav aria-label="Page navigation">{pager}</nav></div>
                        </div></div>',
                    'headerRowOptions' => array('class' => 'table-wrap table-custom'),
                    'columns' => [
                        'source',
                        [
                            'attribute' => 'size',
                            'value' => function ($data) {
                                return AdvanceBulkInsert::getFormattedText(AdvanceBulkInsert::FORMAT_SIZE, $data->size);
                            }
                        ],
                        [
                            'attribute' => 'progress',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return AdvanceBulkInsert::getFormattedProgress($data->id);
                            }
                        ],
                        [
                            'format' => 'raw',
                            'attribute' => 'errors',
                            'contentOptions' => ['style' => 'text-align:center'],
                            'value' => function ($data) {
                                return AdvanceBulkInsert::getErrorFile($data->errors);
                            }

                        ],
                        [
                            'attribute' => 'timeSpent',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return AdvanceBulkInsert::getFormattedSpentTime($data->id);
                            }
                        ],
                        [
                            'attribute' => 'status',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return AdvanceBulkInsert::getStatusLabel($data->status);
                            }
                        ],
                        [
                            'attribute' => 'createdAt',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return User::convertDBTime($data->createdAt);
                            }
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'headerOptions' => ['style' => 'text-align: center'],
                            'contentOptions' => ['style' => 'text-align: right'],
                            'template' => '{stop}{delete}{map}',
                            'buttons' => [
                                'stop' => function ($url, $dataProvider, $key) {
                                    if ($dataProvider->status == AdvanceBulkInsert::IN_PROGRESS && Yii::$app->user->checkAccess("AdvancedBulkInsert.Update")) {
                                        return Html::a('<i class="fa fa-stop"></i>', $url, [
                                            'pjax-container' => 'pjax-list',
                                            'onClick' => "
                                                var pjaxContainer = $(this).attr('pjax-container'); 
                         
                                                    $.ajax('$url', {
                                                        type: 'POST'
                                                    }).done(function(data) {
                                                        $.pjax.reload({container: '#' + $.trim(pjaxContainer)});
                                                    });
                                           
                                                ",
                                        ]);
                                    } else {
                                        return false;
                                    }
                                },
                                'map' => function ($url, $dataProvider, $key) {
                                    if ($dataProvider->status == AdvanceBulkInsert::PENDING && Yii::$app->user->checkAccess("AdvancedBulkInsert.Update")) {
                                        $url = Yii::$app->urlManager->createUrl(['advanced-bulk-insert/preview', 'id' => $dataProvider->id]);
                                        return Html::a('<i class="fa fa-file"></i>', $url);
                                    } else {
                                        return false;
                                    }
                                },
                                'delete' => function ($url, $dataProvider, $key) {
                                    $delConfirmMsg = Yii::t('messages', "Are you sure you want to delete the Bulk insert?");
                                    if ($dataProvider->status != AdvanceBulkInsert::IN_PROGRESS && Yii::$app->user->checkAccess("AdvancedBulkInsert.Delete")) {
                                        return Html::a('<i class="fa fa-trash-o"></i>', $url, [
                                            'pjax-container' => 'pjax-list',
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
                                    } else {
                                        return false;
                                    }
                                }

                            ],
                        ],
                    ],
                ]); ?>

                <?php Pjax::end(); ?>
            </div>
        </div>
    </div>
</div>
