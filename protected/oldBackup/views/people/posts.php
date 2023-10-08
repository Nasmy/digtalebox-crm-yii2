<?php

use app\models\BroadcastMessage;
use app\models\User;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'People'), 'url' => '#'];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Posts')];
$this->title = Yii::t('messages', 'Posts ');

?>

<?php echo  Yii::$app->controller->renderPartial('_tabMenu'); ?>

<div class="content-inner">
    <div class="content-area">
        <?php Pjax::begin(); ?>

        <?= GridView::widget([
            'id' => 'broadcast-message-grid',
            'dataProvider' => $model->search(),
            'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => ''],
            'tableOptions' => ['class' => 'table table-striped table-bordered'],
            'summary' => '<div class="text-right results-count mt-4">' . Yii::t('messages', 'Displaying {begin}-{end} of {count} imports') . '</div>',
            'pager' => [
                'firstPageLabel' => '',
                'firstPageCssClass' => 'first',
                'activePageCssClass' => 'selected active',
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
            'headerRowOptions' => ['class' => 'table-wrap table-custom'],

            'columns' => [
                [
                    'format'=>'html',
                    'label' => 'publishDate',
                    'attribute' => 'publishDate',
                    'value' => function ($data) {
                        $user = new User();
                        return $user->convertDBTime($data['publishDate']);
                    }
                ],
                [
                    'format'=>'html',
                    'label' => 'Ln',
                    'attribute' => 'Ln',
                    'value' => function ($data) {
                        return   BroadcastMessage::getStat(3,$data);
                    }
                ],
                [
                    'format'=>'html',
                    'label' => 'Ln Page',
                    'attribute' => 'LnPage',
                    'value' => function ($data) {
                        return    BroadcastMessage::getStat(4,$data);
                    }
                ],
                [
                    'format'=>'html',
                    'label' => 'Tw',
                    'attribute' => 'Tw',
                    'value' => function ($data) {
                        return  BroadcastMessage::getStat(1,$data);
                    }
                ],
                [
                    'format'=>'html',
                    'label' => 'Fb Page',
                    'attribute' => 'FbPage',
                    'value' => function ($data) {
                        return BroadcastMessage::getStat(2,$data);
                    }
                ],
                [
                    'format'=>'html',
                    'label' => 'Fb',
                    'attribute' => 'Fb',
                    'value' => function ($data) {
                        return  BroadcastMessage::getStat(2,$data);
                    }
                ],
                [
                    'format'=>'html',
                    'label' => 'Post',
                    'attribute' => 'Post',
                    'value' => function ($data) {
                        return  BroadcastMessage::getPostText($data);
                    }
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'headerOptions' => ['style' => 'text-align: center'],
                    'contentOptions' => ['style' => 'text-align: center'],
                    'template'=>'{view}',

                    'buttons' => [
                        'view' => function ($url, $model, $key) {
                            $return = '';
                            if ((Yii::$app->user->checkAccess('BroadcastMessage.View'))) {
                                $url_new = Yii::$app->urlManager->createUrl(['broadcast-message/view-pop', 'id' => $model['id']]);
                                $return = Html::a('<span class="fa fa-eye " style="cursor: pointer;"></span>', false, [
                                    'title' => Yii::t('app', 'Details'),
                                    'class' => 'view',
                                    'data-target' => '#viewDetails',
                                    'data-backdrop' => 'static',
                                    'onClick' => "
                                                  $.ajax('$url_new', {
                                                    type: 'POST',
                                                   }).done(function(data) {
                                                    $('#iframe-viewDetails').html(data);
                                                    $('#viewDetails').modal('show');
                                                      return false;
                                                    });
                                                    $('#viewDetails').on('hidden.bs.modal', function (e) {
                                                        $('#iframe-viewDetails').html('');
                                                    });
                                                    return false;
                                        ",
                                ]);
                            }
                            return $return;
                        } ,

                    ]
                ]
            ],
        ]); ?>

        <?php Pjax::end(); ?>

    </div>
</div>

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

            <div class="row">
                <div class="col-md-12 no-gutters">
                    <div id="iframe-viewDetails" style="vertical-align: center;" class="div-min-height">
                        <div class="progress loader themed-progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated"
                                 role="progressbar" style="width: 100%"
                                 aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END View Modal -->
<script>
    $(document).ready(function () {
        $(document).on("hidden.bs.modal", function () {
            $("#iframe-viewDetails").html('<div class="progress loader themed-progress">' +
                '<div class="progress-bar progress-bar-striped progress-bar-animated"' +
                'role="progressbar" style="width: 100%"' +
                'aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>' +
                '</div>');
        });
    });
</script>
