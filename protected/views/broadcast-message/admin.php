<?php

use app\models\BroadcastMessage;
use app\models\User;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\models\BroadcastMessageSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('messages', 'Social Media');
$this->titleDescription = Yii::t('messages', 'Publish message Twitter/LinkedIn');
$this->params['breadcrumbs'][] = $this->title;

?>

    <script type="text/javascript">


        function showModel(data) { // Showing model
            var j = jQuery(data);


            $('#viewFrame').attr('src', jQuery(j[0]).attr('url'));
            $('#viewDetails').modal('show');

            return false;
        }

        function editModel(data) { // Showing model
            var j = jQuery(data);
            $('#viewFrame').attr('src', jQuery(j[0]).attr('url'));
            $('#updateKeyword').modal('show');

            return false;
        }

    </script>
    <div class="row no-gutters">
        <div class="content-panel col-md-12">
            <div class="content-inner">
                <div class="content-area">
                    <div class="form-row mb-2">
                        <div class="col-md-12 text-left" style="margin-top: 15px">
                            <?php
                            if (Yii::$app->user->checkAccess('BroadcastMessage.Create')) {
                                echo Html::a('<i class="fa fa-plus"></i> ' . Yii::t('messages', 'Create'), 'create', ['class' => 'btn btn-primary mt-1',]);
                            }
                            ?>

                            <?php
                            if (Yii::$app->user->checkAccess('BroadcastMessage.Create')) {
                                echo Html::a('<i class="fa fa-cubes"></i> ' . Yii::t('messages', 'Authenticate Social Networks'), Yii::$app->urlManager->createUrl('site/index'), ['class' => 'btn btn-secondary mt-1',]);
                            }
                            ?>
                        </div>
                    </div>
                    <div class="content-panel-sub" style="margin-top: 20px">
                        <div class="panel-head">
                            <?php echo Yii::t('messages', 'Search by') ?>
                        </div>
                    </div>
                    <div class="search-form" style="display:block">
                        <?php echo $this->render('_search', [
                            'model' => $searchModel,
                            'attributeLabels' => $attributeLabels
                        ]); ?>
                    </div><!-- search-form -->
                    <?php Pjax::begin(['id' => 'pjax-list']); ?>

                    <?= GridView::widget([
                        'id' => 'broadcast-message-grid',
                        'dataProvider' => $dataProvider,
                        'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => ''],
                        'options' => ['class' => 'table-wrap table-custom'],
                        'tableOptions' => ['class' => 'table table-striped table-bordered'],
                        'summary' => '<div class="text-right results-count mt-4">' . Yii::t('messages', 'Displaying {begin}-{end} of {totalCount} results') . '</div>',
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
                            [
                                'format' => 'html',
                                'attribute' => Yii::t('messages', 'publishDate'),
                                'label' => Yii::t('messages', 'Publish Date'),
                                'value' => function ($data) {
                                    $user = new User();
                                    return $user->convertDBTime($data['publishDate']);
                                }
                            ],
                            [
                                'format' => 'html',
                                'attribute' => Yii::t('messages', 'lnPostStatus'),
                                'value' => function ($data) {
                                    $BMessage = new BroadcastMessage();
                                    $BMessage->lnPost = $data['lnPost'];
                                    $BMessage->lnPostStatus = $data['lnPostStatus'];
                                    return $BMessage->getLnStatusLabel();
                                }
                            ],
                            [
                                'format' => 'html',
                                'attribute' => Yii::t('messages', 'lnPagePostStatus'),
                                'value' => function ($data) {
                                    $BMessage = new BroadcastMessage();
                                    $BMessage->lnPagePost = $data['lnPagePost'];
                                    $BMessage->lnPagePostStatus = $data['lnPagePostStatus'];
                                    return $BMessage->getLnPageStatusLabel();
                                }
                            ],
                            [
                                'format' => 'html',
                                'attribute' => Yii::t('messages', 'twPostStatus'),
                                'value' => function ($data) {
                                    $BMessage = new BroadcastMessage();
                                    $BMessage->twPost = $data['twPost'];
                                    $BMessage->twPostStatus = $data['twPostStatus'];
                                    return $BMessage->getTwStatusLabel();
                                }
                            ],
                            [
                                'format' => 'html',
                                'attribute' => Yii::t('messages', 'fbPostStatus'),
                                'value' => function ($data) {
                                    $BMessage = new BroadcastMessage();
                                    return $BMessage->getFbStatusLabel();
                                }
                            ],
                            [
                                'format' => 'html',
                                'attribute' => Yii::t('messages', 'fbProfPostStatus'),
                                'value' => function ($data) {
                                    $BMessage = new BroadcastMessage($data);
                                    return $BMessage->getFbProfStatusLabel();
                                }
                            ],
                            [
                                'format' => 'html',
                                'value' => function ($data) {
                                    return (BroadcastMessage::REC_STATUS_DRAFT == $data['recordStatus']) ? Yii::$app->toolKit->getBootLabel("warning", Yii::t("messages", "Draft")) : "";
                                }
                            ],
                            [
                                'format' => 'html',
                                'attribute' => Yii::t('messages', 'createdBy'),
                                'label' => Yii::t('messages', 'Created By'),
                                'value' => function ($data) {
                                    return User::getNameById($data['createdBy']);
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
                                        if (Yii::$app->user->checkAccess('BroadcastMessage.View')) {
                                            $return = Html::a('<span class="fa fa-eye  fa-lg"></span>', 'javascript:void(0);', [
                                                'id' => 'activity-view-link',
                                                'url' => $url,
                                                'title' => Yii::t('yii', 'View'),
                                                'data-toggle' => 'modal',
                                                'data-target' => '#viewDetails',
                                                'data-id' => $key,
                                                'data-pjax' => '0',
                                                'data-backdrop' => 'static',
                                                'onclick' => 'showModel(this)']);
                                        }

                                        return $return;

                                    },
                                    'update' => function ($url, $model, $key) {
                                        $return = '';
                                        if (Yii::$app->user->checkAccess('BroadcastMessage.Update') && $model['recordStatus'] != BroadcastMessage::REC_STATUS_PROCESSED) {
                                            $return = Html::a('<span class="fa fa-edit fa-lg "></span>', $url);

                                        }
                                        return $return;
                                    },
                                    'delete' => function ($url, $model, $key) {
                                        $return = '';
                                        if (Yii::$app->user->checkAccess('BroadcastMessage.Delete')) {
                                            $return = Html::a(
                                                '<span class="fa fa-trash  fa-lg"></span>',
                                                false,
                                                [
                                                    'class' => 'ajaxDelete',
                                                    'delete-url' => $url,
                                                    '_csrf-frontend' => Yii::$app->request->csrfToken,
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
                                        $url = 'view?id=' . $model['id'];
                                        return $url;
                                    }
                                    if ($action === 'update') {
                                        $url = 'update?id=' . $model['id'];
                                        return $url;
                                    }
                                    if ($action === 'delete') {
                                        $url = 'delete?id=' . $model['id'];
                                        return $url;
                                    }

                                }
                            ],
                        ],
                    ]); ?>

                    <?php Pjax::end(); ?>
                </div>
            </div>
        </div>

        <!-- START View Modal -->
        <div class="modal fade" id="viewDetails" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
             aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">

                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title" id=""> <?php echo Yii::t('messages', 'View Details'); ?> </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <iframe id="viewFrame" src="" frameborder="0" scrolling="auto" width="100%"
                            height="700px"></iframe>
                </div>
            </div>
        </div>

    </div>

    <script>
        $(document).ready(function () {
            $('.datetimepicker-input')
                .datetimepicker({
                    format: 'YYYY-MM-DD',
                    ignoreReadonly: true
                });
        });
    </script>
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
                                    jQuery('.alert-message').html(xhr.responseText);
                                  }
                                }).done(function (data) {
                                console.log(data);
                                  $.pjax.reload({container: '#' + $.trim(pjaxContainer)});
                                });
                              }
                   });
                });
                
                ");

?>