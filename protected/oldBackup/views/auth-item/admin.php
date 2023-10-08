<?php

use app\models\AuthItem;
use kartik\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\Pjax;

$this->title = 'Manage Permissions';
$this->titleDescription = Yii::t('messages', 'Manage all system permissions');
$this->params['breadcrumbs'][] = ['label' => 'System', 'url' => ['admin', 'type' => AuthItem::TYPE_OPERATION]];
$this->params['breadcrumbs'][] = ['label' => 'Manage Permission', 'url' => ['admin', 'type' => AuthItem::TYPE_OPERATION]];
?>

<style type="text/css">
    .theme-green .app-body .green-th th {
        background: #60A649 !important;
        font-size: 12px !important;
    }

    .green-th td.kv-grid-group.kv-grouped-row {
        background: initial !important;
    }

</style>
<div class="row mt-3"></div>
<div class="row no-gutters">
    <div class="content-panel col-md-12">
        <div class="content-inner">
            <div class="content-area">
                <?php
                if (Yii::$app->user->checkAccess('Erbac.AuthItem.Create')) {
                    echo Html::a('<i class="fa fa-plus"> </i> '.Yii::t('messages', 'Add Permission'), ['/auth-item/create', 'type' => AuthItem::TYPE_OPERATION], ['class' => 'btn btn-primary']);
                }
                ?>
                <?php
                if (Yii::$app->user->checkAccess('Erbac.AuthItem.GeneratePermissions')) {
                    echo Html::a(Yii::t('messages', 'Generate Permissions'), ['/auth-item/generate-permissions'], ['class' => 'btn btn-primary']);
                }
                ?>

                <div class="table-wrap table-custom">
                    <?php Pjax::begin(['id' => 'pjax-list']); ?>
                     <?= GridView::widget([
                        'dataProvider' => $dataProvider,
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
                        'summary' => '<div class="text-right results-count mt-4">' . Yii::t('messages', 'Displaying {begin}-{end} of {totalCount} results') . '</div>',
                        'options' => ['class' => 'table-wrap table-custom'],
                        'layout' => '<div class="text-right results-count">{summary}</div>
                        <div class="table-wrap table-custom">{items}</div>
                        <div class="row no-gutters d-flex flex-sm-row-reverse flex-sm-column-reverse flex-md-row"> 
                        <div class="col-md-6"></div>
                        <div class="col-md-6">
                            <div class="float-right"><nav aria-label="Page navigation">{pager}</nav></div>
                        </div></div>',
                        'tableOptions' => ['class' => 'table green-th table-striped table-bordered'],
                        'columns' => [
                             [
                                'attribute' => 'category',
                                'width' => '310px',
                                'value' => function ($model, $key, $index, $widget) {
                                    return $model['category'];
                                },
                                // 'filterType' => GridView::FILTER_SELECT2,
                                'filterType' => \kartik\grid\GridView::FILTER_SELECT2,
                                'filter' => ArrayHelper::map(AuthItem::find()->orderBy('category')->asArray()->all(), 'id', 'category'),
                                'filterWidgetOptions' => [
                                    'pluginOptions' => ['allowClear' => true],
                                ],
                                'group' => true,
                                'groupedRow' => true,
                                'groupOddCssClass' => 'kv-grouped-row',  // configure odd group cell css class
                                'groupEvenCssClass' => 'kv-grouped-row',
                            ],
                            'name',
                            'description',
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'headerOptions' => ['style' => 'text-align: center'],
                                'contentOptions' => ['style' => 'text-align: center'],
                                'template' => '{update} {delete}',
                                'buttons' => [
                                    'update' => function ($url, $model, $key) {
                                        $return = '';
                                        if ((Yii::$app->user->checkAccess('Erbac.AuthItem.Update'))) {
                                            $return = Html::a('<span class="fa fa-edit fa-lg"></span>', ['auth-item/update', 'itemName' => $model['name'], 'type' => $model['type']]);
                                        }
                                        return $return;
                                    },
                                    'delete' => function ($url, $model, $key) {
                                        $return = '';
                                        if ((Yii::$app->user->checkAccess('Erbac.AuthItem.Delete'))) {
                                            $return = Html::a('<span class="fa fa-trash fa-lg"></span>', ['auth-item/delete', 'itemName' => $model['name'], 'type' => $model['type']],['class'=>'ajaxDelete']);
                                        }
                                        return $return;
                                    }
                                ]
                            ],
                        ],
                    ]); ?>
                    <?php Pjax::end(); ?>

                    <?php

                    $this->registerJs("

                $(document).ready(function () { 
                  $(document).on('click', '.ajaxDelete', function (e){
                    e.preventDefault();
                    var deleteUrl = $(this).attr('href');
                        console.log(deleteUrl);
            
                    var pjaxContainer = $(this).attr('pjax-container');
                      var result = confirm('Are you sure you want to change status of this item?'); 
                               if (result) {
                              
                              }
                   });
                });
                
                ");

                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
