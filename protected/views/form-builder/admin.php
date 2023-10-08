<?php

use app\components\RActiveRecord;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;
use \app\models\Form;

$this->title = Yii::t('messages', 'Forms');
$this->titleDescription = Yii::t('messages', 'Forms of DigitaleBox');

$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'System'), 'url' => ['#']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Forms')];


$script = <<< JS

$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});

    $('.search-form form').submit(function(){ 
    $.fn.yiiGridView().update('formBuilder-grid',{
        data: $(this).serialize()

    });
});
JS;

$this->registerJs($script);

?>

<div class="row no-gutters">
    <div class="content-panel col-md-12">
        <div class="content-inner">
            <div class="content-area">
                <div class="form-row mb-2">
                    <div class="form-group col-md-12">
                        <?php
                        if (Yii::$app->user->checkAccess('Form.Create')) {
                            echo Html::a("<i class=\"fa fa-plus\"></i> " . Yii::t('messages', 'Add Form'), ['form-builder/create'], ['class' => 'btn-primary grid-button btn']);
                        }
                        ?>
                    </div>
                </div>

                <div class="content-panel-sub">
                    <div class="panel-head">
                        <?php echo Yii::t('messages', 'Search by') ?>
                    </div>
                </div>
                <div class="search-form" style="display:block">
                    <?php
                    echo Yii::$app->controller->renderPartial('_search', [
                        'model' => $model,
                    ]);
                    ?>
                </div>
                <?php Pjax::begin(['id' => 'pjax-list']); ?>
                <?php
                echo GridView::widget([
                    'id' => 'formBuilder-grid',
                    'dataProvider' => $dataProvider,
                    'options' => ['class' => 'table-wrap table-custom'],
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
                    'layout' => '<div class="text-right results-count">{summary}</div>
                        <div class="table-wrap">{items}</div>
                        <div class="row no-gutters d-flex flex-sm-row-reverse flex-sm-column-reverse flex-md-row">
                        <div class="col-md-6"></div>
                        <div class="col-md-6">
                            <div class="float-right"><nav aria-label="Page navigation">{pager}</nav></div>
                        </div></div>',
                    'headerRowOptions' => ['class' => 'table-wrap table-custom'],
                    'columns' => [
                        'title',
                        [
                            'attribute' => 'fieldList',
                            'value' => function ($model) {
                                return Form::getList($model->id);
                            }
                        ],
                        [
                            'attribute' => 'enabled',
                            'value' => function ($model) {
                                return RActiveRecord::getBoolList($model->enabled);
                            }
                        ],
                        [
                            'attribute' => 'createdOn',
                            'value' => function ($model) {
                                return date('Y-m-d', strtotime($model->createdAt));
                            }
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'headerOptions' => ['style' => 'text-align: center'],
                            'contentOptions' => ['style' => 'text-align: center'],
                            'template' => ' {update} {delete}',

                            'buttons' => [
                                'update' => function ($url, $model, $key) {
                                    $return = '';
                                    if ((Yii::$app->user->checkAccess('Form.Update'))
                                    ) {
                                        $return = Html::a('<span class="fa fa-edit fa-lg"></span>', $url, ['class' => 'edit', 'data-pjax' => 0]);
                                    }
                                    return $return;
                                },
                                'delete' => function ($url, $model, $key) {
                                    $return = '';
                                    if ((Yii::$app->user->checkAccess('Form.Delete'))
                                    ) {
                                        $return = Html::a(
                                            '<span class="fa fa-trash fa-lg"></span>',
                                            false,
                                            [
                                                'class' => 'ajaxDelete',
                                                'delete-url' => $url,
                                                '_csrf-frontend' => 'Yii::$app->request->csrfToken',
                                                'pjax-container' => 'pjax-list',
                                                'title' => Yii::t('app', 'Delete'),
                                            ]
                                        );
                                    }
                                    return $return;
                                }
                            ],
                        ],
                    ],
                ]);
                ?>

                <?php Pjax::end(); ?>

                <?php

                $this->registerJs("

                $(document).ready(function () { 
                  $(document).on('click', '.ajaxDelete', function (e){
                    e.preventDefault();
                    var deleteUrl     = $(this).attr('delete-url');
                    var pjaxContainer = $(this).attr('pjax-container');
                      var result = confirm('Are you sure you want to change status of this item?'); 
                               if (result) {
                                $.ajax({
                                  url:   deleteUrl,
                                  type:  'post', 
                                 data: {YII_CSRF_TOKEN:'" . Yii::$app->request->csrfToken . "'},
                                  error: function (xhr, status, error) {
                                    console.log('There was an error with your request.' 
                                          + xhr.responseText);
                                  }
                                }).done(function (data) {
                                  $.pjax.reload({container: '#' + $.trim(pjaxContainer)});
                                });
                              }
                   });
                });
                
                ");

                ?>

            </div>
        </div>
    </div>
</div>
