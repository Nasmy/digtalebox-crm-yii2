<?php
use app\models\BulkExport;
use app\models\User;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

$this->title = Yii::t('messages', 'Bulk Export List');
$this->titleDescription =  Yii::t('messages', 'Download mass people data');
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'People'), 'url' => ['admin']];
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="row no-gutters">
    <div class="content-panel col-md-12">
        <div class="content-inner">
            <div class="content-area">
                <?php
                $script = <<< JS
                 setInterval(function() 
                 {     
                     $.pjax.reload({container:'#pjax-list', url: "/advanced-search/bulk-export-view", timeout: false});
                 }, 10000);
JS;
                $this->registerJs($script);
                ?>
                <?php Pjax::begin(['id' => 'pjax-list']); ?>
                <div class="table-wrap table-custom">
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
                        'headerRowOptions' => array('class' => 'table-wrap table-custom'),
                        'columns' => [
                            [
                                'format' => 'raw',
                                'header' => Yii::t('messages', 'Export Type'),
                                'value' => function ($data, $row) {
                                    return BulkExport::getExportTypeLabel($data['exportType']);
                                },
                            ],
                            [
                                'header' => Yii::t('messages', 'Status'),
                                'format' => 'raw',
                                'value' => function ($data, $row) {
                                    return BulkExport::getStatusLabel($data['status']);
                                },
                            ],
                            [

                                'header' => Yii::t('messages', 'Created At'),
                                'value' => function ($data, $row) {
                                    return $data['createdAt'];
                                },

                            ],
                            [
                                'format' => 'raw',
                                'header' => Yii::t('messages', 'Total Records'),
                                'value' => function ($data, $row) {
                                    return $data['totalRecords'];
                                },
                                'headerOptions' => ['style' => 'text-align:left'],
                            ],
                            [
                                'format' => 'raw',
                                'header' => Yii::t('messages', 'Finished At'),
                                'value' => function ($data, $row) {
                                    return ($data['finishedAt']!="")?$data['finishedAt']:"N/A";
                                },
                                'headerOptions' => ['style' => 'text-align:left'],
                            ],
                            [
                                'format' => 'raw',
                                'header' => Yii::t('messages', 'Download Link'),
                                'value' => function ($data, $row) {
                                    return BulkExport::getDownlaodUrl($data['statusFile']);
                                },
                                'headerOptions' => ['style' => 'text-align:left'],
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'template' => '{stop}{delete}',
                                'headerOptions' =>['style' => 'text-align:right;width:100px'],
                                'buttons' => [
                                    'stop' => function ($url, $model, $key) {
                                        $return = '';
                                        if ($model['status'] == BulkExport::IN_PROGRESS && Yii::$app->user->checkAccess('People.Update'))
                                            $visibility=true;
                                        else
                                            $visibility=false;//not show this button

                                        $url = Yii::$app->urlManager->createUrl(['advanced-search/update', 'id' => $model['id']]);
                                        if (Yii::$app->user->checkAccess('People.Update')) {
                                            $return = Html::a('<i class="fa fa-stop fa-lg"></i>', $url, [
                                                'label' => Yii::t('messages', 'Stop Export'),'style'=>($visibility)?'visibility:visible;':'visibility:hidden;',
                                            ]);
                                        }
                                        return $return;
                                    },

                                    'delete' =>function ($url, $model, $key) {
                                        $return = '';
                                        if($model['status']!= BulkExport::IN_PROGRESS && Yii::$app->user->checkAccess('People.Update'))
                                            $visibility=true;
                                        else
                                            $visibility=false;//not show this button

                                        $url = Yii::$app->urlManager->createUrl(['advanced-search/delete', 'id' => $model['id']]);
                                        if (Yii::$app->user->checkAccess('People.Update')) {
                                            $delConfirmMsg = Yii::t('messages', "Are you sure you want to delete ?");
                                            $return = Html::a('<i class="fa fa-trash fa-lg"></i>', false, [
                                                'onClick' => "
                                                       if (confirm('$delConfirmMsg')) {
                                                            $.ajax('$url', {
                                                                type: 'POST'
                                                            }).done(function(data) {
                                                                $.pjax.reload({container: '#pjax-list'});
                                                            });
                                                        }
                                                        return false;
                                                        ",
                                                'label' => Yii::t('messages', 'Delete imported file'),
                                                'style'=>($visibility)?'visibility:visible;':'visibility:hidden;',
                                                'title' => Yii::t('messages', 'Delete'),
                                            ]);
                                        }
                                        return $return;
                                    },
                                ]

                            ]
                        ],
                    ]);
                    ?>
                </div>
                <?php Pjax::end(); ?>
            </div>
        </div>
    </div>
</div>
