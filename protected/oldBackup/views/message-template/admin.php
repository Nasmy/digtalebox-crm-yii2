<?php
/* @var $this yii\web\View */

use app\models\User;
use yii\grid\GridView;
use yii\helpers\Html;
use app\models\MessageTemplate;

/* @var $searchModel app\models\CampaignSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('messages', 'Message Templates');
$this->titleDescription = Yii::t('messages', 'Templates for mass Email/SMS campaigns');
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Communication'), 'url' => ['admin']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Message Templates')];

?>

<div class="row no-gutters">
    <div class="content-panel col-md-12">
        <div class="content-inner">
            <div class="content-area">
                <div class="form-row mb-2">
                    <div class="form-group col-md-12">
                        <?php
                        if (Yii::$app->user->checkAccess('Resource.Create')) {
                            echo Html::a("<i class=\"fa fa-plus\"></i> " . Yii::t('messages', 'Add Email Template'), ['message-template/create', 'templateCategory' => MessageTemplate::MSG_CAT_EMAIL], ['class' => 'mr-4 btn-primary grid-button btn']);
                            echo Html::a("<i class=\"fa fa-plus\"></i> " . Yii::t('messages', 'Add SMS Template'), ['message-template/create', 'templateCategory' => MessageTemplate::MSG_CAT_SMS], ['class' => 'btn-primary grid-button btn']);
                        }
                        ?>
                    </div>
                </div>

                <?php
                echo GridView::widget([
                    'id' => 'campaign-users-grid',
                    'dataProvider' => $dataProvider,
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
                    'options' => ['class' => 'table-wrap table-custom'],
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
                            'format' => 'raw',
                            'attribute' => 'dateTime',
                            'value' => function ($dataProvider) {
                                // return User::convertDBTime($dataProvider->dateTime);
                                return $dataProvider->dateTime;
                            }
                        ],
                        'name',
                        'description',
                        [
                            'attribute' => 'type',
                            'value' => function ($dataProvider) {
                                return $dataProvider->getTemplateTypeOptions($dataProvider->type);
                            }
                        ],
                        [
                            'attribute' => 'createdBy',
                            'value' => function ($dataProvider) {
                                return User::getNameById($dataProvider->createdBy);
                            }
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'headerOptions' => ['style' => 'text-align: center'],
                            'contentOptions' => ['style' => 'text-align: center'],
                            'template' => '{view} {duplicate} {update} {delete} {updateSms} {updateEmail}',
                            'buttons' => [
                                'view' => function ($url, $model, $key) {
                                    $return = Html::a(
                                        '<span class="fa fa-eye fa-lg"></span>',
                                        $url,
                                        [
                                            'title' => Yii::t('app', 'Update')
                                        ]
                                    );
                                    return $return;

                                },
                                // TODO
                                'duplicate' => function ($url, $model, $key) {
                                    $return = '';
                                    if ((Yii::$app->user->checkAccess('MessageTemplate.Update') && $model->createdBy == Yii::$app->user->id)) {
                                        $return = MessageTemplate::getRenderIcon($model->templateCategory, $model->id, $url, true);
                                    }
                                    return $return;
                                },
                                'update' => function ($url, $model, $key) {
                                    $return = '';
                                    if ((Yii::$app->user->checkAccess('MessageTemplate.Update') && $model->createdBy == Yii::$app->user->id && ($model->templateCategory === MessageTemplate::MSG_CAT_EMAIL || $model->templateCategory == MessageTemplate::MSG_CAT_SMS))) {
                                        $return = MessageTemplate::getRenderIcon($model->templateCategory, $model->id, $url);
                                    }
                                    return $return;
                                },
                                'updateSms' => function ($url, $model, $key) {
                                    $return = '';
                                    $url = Yii::$app->urlManager->createUrl(['message-template/update', 'id' => $model->id]);
                                    if ((Yii::$app->user->checkAccess('MessageTemplate.Update') && $model->createdBy == Yii::$app->user->id && ($model->templateCategory === MessageTemplate::MSG_CAT_BOTH || $model->templateCategory == null || $model->templateCategory == ''))) {
                                        $return = MessageTemplate::getRenderIcon(MessageTemplate::MSG_CAT_SMS, $model->id, $url);
                                    }
                                    return $return;

                                },
                                'updateEmail' => function ($url, $model, $key) {
                                    $return = '';
                                    $url = Yii::$app->urlManager->createUrl(['message-template/update', 'id' => $model->id]);
                                    if ((Yii::$app->user->checkAccess('MessageTemplate.Update') && $model->createdBy == Yii::$app->user->id && ($model->templateCategory === MessageTemplate::MSG_CAT_BOTH || $model->templateCategory == null || $model->templateCategory == ''))) {
                                        $return = MessageTemplate::getRenderIcon(MessageTemplate::MSG_CAT_EMAIL, $model->id, $url);
                                    }
                                    return $return;
                                },
                                'delete' => function ($url, $model, $key) {
                                    $return = '';
                                    if ((Yii::$app->user->checkAccess('MessageTemplate.Delete') && $model->createdBy == Yii::$app->user->id)) {
                                        $return = Html::a(
                                            '<span class="fa fa-trash fa-lg"></span>',
                                            false,
                                            [
                                                'class' => 'ajaxDelete',
                                                'delete-url' => $url,
                                                'pjax-container' => 'pjax-list',
                                                'title' => Yii::t('app', 'Delete')
                                            ]
                                        );
                                    }
                                    return $return;
                                }
                            ],
                        ],
                    ]
                ]);
                ?>
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
