<?php

use app\models\FeedSearchKeyword;
use app\models\Keyword;
use app\models\User;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\UrlManager;
use yii\widgets\Pjax;

$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'People'), 'url' => ['#']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Manage Keywords\'')];
$url = Yii::$app->urlManager->createUrl('keyword/create');

$script = <<< JS
	$('.update').on('click', function() {
		$.fancybox.open({
			padding : 10,
			href:$(this).attr('href'),
			type: 'iframe',
			width: 780,
			height: 450,
			transitionIn: 'elastic',
			transitionOut: 'elastic',
			autoSize: false
		});
		return false;
	});

	$('#keywordCreate').on('click', function() {
	      var url = '{$url}';
	      $(target).find(".modal-body").load(url);
	}); 
    
	$('.search-button').click(function(){
        $('.search-form').toggle();
        return false;
    });
	  
	$('.search-form form').submit(function(){
        $.fn.yiiGridView.update('keyword-grid', {
            data: $(this).serialize()
        });
       // return false;
    });
	
	

JS;
$this->registerJs($script);

$this->title = Yii::t('messages', 'Manage Keywords');
$this->titleDescription = Yii::t('messages', 'Keywords for tagging people');
?>
<script type="text/javascript">
    function showModel(data) { // Showing model
        var j = jQuery(data);

        console.log(jQuery(j[0]).attr('url'))

        $('#viewFrame').attr('src', jQuery(j[0]).attr('url'));
        return false;
    }

    function editModel(data) { // Edit model
        var j = jQuery(data);

        console.log(jQuery(j[0]).attr('url'))

        $('#updateFrame').attr('src', jQuery(j[0]).attr('url'));
        return false;
    }
</script>
<style>
    @media (min-width: 576px) {
        #updateKeyword .modal-dialog {
            max-width: 600px;
            margin: 1.75rem auto;
        }

        .page-item.next.disabled {
            padding: 5px;
            border: 1px solid #dee2e6 !important;
            border-left: none !important;
        }

    }
</style>

<div class="row no-gutters">
    <div class="content-panel col-md-12">
        <div class="content-inner">
            <div class="content-area">
                <div class="form-row mb-2">
                    <div class="form-group col-md-12">
                        <?php
                        if (Yii::$app->user->checkAccess('Keyword.Create')) {
                            echo Html::a("<i class=\"fa fa-plus\"></i> " . Yii::t('messages', 'Add Keyword'), ['keyword/create'], ['class' => 'btn-primary grid-button btn']);
                        }
                        echo " ";
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
                    'id' => 'keyword-grid',
                    'dataProvider' => $dataProvider,
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
                    'headerRowOptions' => ['class' => 'table-wrap table-custom'],
                    'columns' => [
                        'name',
                        [
                            'attribute' => 'behaviour',
                            'value' => function ($dataProvider) {
                                $status = Keyword::find()->where(['=', 'id', $dataProvider->id])->one();
                                return $dataProvider->getBehaviourOptions($status->behaviour);
                            }
                        ],
                        [
                            'format' => 'raw',
                            'attribute' => 'status',
                            'value' => function ($data) {
                                return $data->getKeywordLable($data->status);
                            }
                        ],
                        [
                            'attribute' => 'createdAt',
                            'value' => function ($data) {
                                return User::convertSystemTime($data->createdAt);
                            }
                        ],
                        [
                            'attribute' => 'createdBy',
                            'value' => function ($data) {
                                return User::getNameById($data->createdBy);
                            }
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'headerOptions' => ['style' => 'text-align: center'],
                            'contentOptions' => ['style' => 'text-align: center'],
                            'template' => ' {view} {update} {delete}',

                            'buttons' => [
                                'view' => function ($url, $model, $key) {
                                    $return = '';
                                    if ((Yii::$app->user->checkAccess('Keyword.View'))) {
                                        $return = Html::a('', '#', [
                                            'id' => 'activity-view-link',
                                            'url' => $url,
                                            'title' => Yii::t('yii', 'View'),
                                            'data-toggle' => 'modal',
                                            'data-target' => '#viewKeyword',
                                            'data-id' => $key,
                                            'data-pjax' => '0',
                                            'onclick' => 'showModel(this)',
                                            'class' => 'fa fa-eye fa-lg'
                                        ]);

                                    }

                                    return $return;

                                },
                                'update' => function ($url, $model, $key) {
                                    $return = '';
                                    if ((Yii::$app->user->checkAccess('Keyword.Update') || Yii::$app->user->id == $model->createdBy
                                        && Keyword::KEY_DELETED != $model->status && count(FeedSearchKeyword::find()->where(['keyword' => $model->name])->all()) === 0
                                        && count(FeedSearchKeyword::find()->where(['keyword' => $model->name])->all()) === 0
                                        && Keyword::KEY_DELETED != $model->status)
                                    ) {
                                        $return = Html::a('', '#',
                                            [
                                                'id' => 'keyword-edit',
                                                'url' => $url,
                                                'title' => Yii::t('yii', 'Edit'),
                                                'data-toggle' => 'modal',
                                                'data-target' => '#updateKeyword',
                                                'data-id' => $key,
                                                'data-pjax' => '0',
                                                'onclick' => 'editModel(this)',
                                                'class' => 'fa fa-edit fa-lg'
                                            ]);

                                    }
                                    return $return;
                                },
                                'delete' => function ($url, $model, $key) {
                                    $return = '';
                                    if (Keyword::isAllowed($model, 'Keyword.Delete') || Yii::$app->user->checkAccess('Keyword.Delete') && Keyword::KEY_DELETED != $model->status
                                        && count(FeedSearchKeyword::find()->where(['keyword' => $model->name])->all()) === 0) {
                                        $return = Html::a(
                                            '',
                                            false,
                                            [
                                                'class' => 'ajaxDelete fa fa-trash fa-lg',
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
                            'urlCreator' => function ($action, $model, $key, $index) {
                                if ($action === 'view') {
                                    $url = 'view?id=' . $model->id;
                                    return $url;
                                }
                                if ($action === 'update') {
                                    $url = 'update?id=' . $model->id;
                                    return $url;
                                }
                                if ($action === 'delete') {
                                    $url = 'delete?id=' . $model->id;
                                    return $url;
                                }

                            }
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
<div class="modal fade" id="viewKeyword" tabindex="-1" role="dialog"
     aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered model-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"
                    id="exampleModalLongTitle"><?php echo Yii::t('messages', 'View') ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <iframe id="viewFrame" frameborder="0" scrolling="no" width="100%" height="300px"></iframe>
            </div>

        </div>
    </div>
</div>
<div class="modal" id="updateKeyword" tabindex="-1" role="dialog"
     aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered model-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"
                    id="exampleModalLongTitle"><?php echo Yii::t('messages', 'Update Keywords') ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <iframe id="updateFrame" frameborder="0" scrolling="no" width="100%" height="400px"></iframe>
            </div>

        </div>
    </div>
</div>
