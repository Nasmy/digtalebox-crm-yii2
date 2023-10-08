<?php
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

$script = <<< JS

$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});

$('.search-form form').submit(function(){ 
	$.fn.yiigridview.update('membershipType-grid', {
		data: $(this).serialize()
	}); 
});
JS;

$this->registerJs($script);
$this->title = Yii::t('messages', 'Manage Membership Types');
$this->titleDescription = Yii::t('messages', 'Manage membership types for Form Builder');
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'System'), 'url' => ['#']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Manage Membership Types')];

?>

<div class="row no-gutters">
    <div class="content-panel col-md-12">
        <div class="content-inner">
            <div class="content-area">
                <div class="form-row mb-2">
                    <div class="form-group col-md-12">

                        <?php
                        if (Yii::$app->user->checkAccess('MembershipType.Create')) {
                            echo Html::a("<i class=\"fa fa-plus\"></i> " . Yii::t('messages', 'Add Membership Type'), ['membership-type/create'], ['class' => 'btn-primary grid-button btn']);
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
                    echo Yii::$app->controller->renderPartial('_search', array(
                        'model' => $model,
                    ));
                    ?>
                </div>

                <?php Pjax::begin(['id' => 'pjax-list']); ?>

                <?= GridView::widget([
                    'id' => 'membershipType-grid',
                    'dataProvider' => $model->search(),
                    'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => ''],
                    'options' => ['class' => 'table-wrap table-custom'],
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
                    'headerRowOptions' => array('class' => 'table-wrap table-custom'),
                    'columns' => [
                        'title',
                        'fee',
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'headerOptions' => ['style' => 'text-align: center'],
                            'contentOptions' => ['style' => 'text-align: center'],
                            'template' => ' {update} {delete}',

                            'buttons' => [
                                'update' => function ($url, $model, $key) {
                                    $return = '';
                                    if ((Yii::$app->user->checkAccess('MembershipType.Update'))
                                    ) {
                                        $return = Html::a('<span class="fa fa-edit fa-lg"></span>', $url, ['class' => 'edit', 'data-pjax' => 0]);
                                    }
                                    return $return;
                                },
                                'delete' => function ($url, $model, $key) {
                                    $return = '';
                                    if ((Yii::$app->user->checkAccess('MembershipType.Delete'))
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
//                                                'data-confirm' => Yii::t('app', 'Are you sure you want to delete this Record?')
                                            ]
                                        );
                                    }
                                    return $return;
                                }
                            ],
                        ],
                    ],
                ]); ?>

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
