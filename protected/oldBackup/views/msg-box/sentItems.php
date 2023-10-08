<?php

$this->title =  Yii::t('messages','Sent Items');
$this->titleDescription = Yii::t('messages','Messages sent by you');

$this->params['breadcrumbs'][] = ['label' =>  Yii::t('messages','Communication'), 'url' => ['#']];
$this->params['breadcrumbs'][] = ['label' =>  Yii::t('messages','Messages'), 'url' => ['msg-box/inbox']];
$this->params['breadcrumbs'][] = Yii::t('messages','Sent Items');

use app\assets\ImageAsset;
use app\components\WebUser;
use app\models\User;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JqueryAsset;
use yii\widgets\Pjax;
$User = new User();
JqueryAsset::register($this);
ImageAsset::register($this);
?>

<?php
$delConfirm = Yii::t('messages','Are you sure you want to delete the Message?');
?>
<script type="text/javascript">

    $('.search-form form').submit(function(){
        $.fn.yiiGridView.update('msg-box-grid', {
            data: $(this).serialize()
        });
        return false;
    });

    function progressShowModel(data) { // Showing model
        var j = jQuery(data);
        $('#iframe-progress').attr('src',jQuery(j[0]).attr('url'));
        return false;
    }

    function editModel(data) { // Edit model
        var j = jQuery(data);
        $('#updateFrame').attr('src',jQuery(j[0]).attr('url'));
        return false;
    }
</script>

<?php echo  Yii::$app->controller->renderPartial('_tabMenu'); ?>

<div class="row no-gutters">
    <div class="content-panel col-md-12">
        <div class="content-inner">
            <div class="content-area">

                <?php Pjax::begin(['id' => 'pjax-list']); ?>
                <div class="content-panel-sub">
                    <div class="panel-head">
                        <?php echo Yii::t('messages','Search by') ?>
                    </div>
                </div>
                <div class="search-form" style="display:block">
                    <?php   echo $this->render('_searchSentItems', ['model'=>$model, 'attributeLabels'=>$attributeLabels]); ?>
                </div>


                <?= GridView::widget([
                    'id' => 'msg-box-grid',
                    'dataProvider' => $dataProvider,
                    'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => ''],
                    'tableOptions' => ['class' => 'table table-striped table-bordered'],
                    'summary' => '<div class="text-right results-count mt-4">' . Yii::t('messages', 'Displaying {begin}-{end} of {count} imports') . '</div>',
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
                    'headerRowOptions' => ['class' => 'table-wrap table-custom'],
                    'columns' => [
                        [
                            'format'=>'raw',
                            'label' => Yii::t('messages','Date & Time'),
                            'value' => function ($data) {
                                $User = new User();
                                return $User->convertDBTime($data->dateTime);
                            }
                        ],
                         'subject',
                        [
                            'attribute' => 'criteriaId',
                            'visible' => !Yii::$app->user->checkAccess(WebUser::SUPPORTER_ROLE_NAME),
                            'value' => function ($data) {
                                return $data->getCriteriaName($data->criteriaId);
                            }
                        ],
                        [
                            'format'=>'raw',
                            'attribute' => 'userlist',
                            'value' => function ($data) {
                                $User = new User();
                                return '<div class="wrapword">' . $data->getUserNames($data->userlist) . '</div>';
                            }
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'headerOptions' => ['style' => 'text-align: center'],
                            'contentOptions' => ['style' => 'text-align: center'],
                            'template' => '{progress} {info} {del}',

                            'buttons' => [
                                'progress' => function ($url, $model, $key) {
                                    return  Html::a('<span class="fa fa-clock-o"></span>','#', [
                                        'id' => 'activity-view-link',
                                        'url' => $url,
                                        'title' => Yii::t('yii', 'Message(s) sending progress'),
                                        'data-toggle' => 'modal',
                                        'data-target' => '#showCode',
                                        'data-id' => $key,
                                        'data-pjax' => '0',
                                        'onclick'=>'progressShowModel(this)' ]);
                                 },
                                'info' => function ($url, $model, $key) {
                                    $return = '';
                                    if ((Yii::$app->user->checkAccess('MsgBox.ViewSentItems'))
                                    ) {
                                        $return = Html::a(
                                            '<span class="fa fa-eye "></span>',
                                            Url::to(["msg-box/view-sent-items","id"=>$model->id]),
                                            [
                                                'title' => Yii::t('app', 'View Message'),
                                            ]
                                        );
                                    }
                                    return $return;
                                } ,
                                'del' => function ($url, $model, $key) {
                                    $url = '/index.php/msg-box/delete-sent-msg?id='.$key;
                                    $return = '';
                                    if ((Yii::$app->user->checkAccess('MsgBox.DeleteSentMsg'))
                                    ) {
                                        $return = Html::a(
                                            '<span class="fa fa-trash"></span>',
                                            false,
                                            [
                                                'class' => 'ajaxDelete',
                                                'delete-url' => $url,
                                                'key-id' => $key,
                                                '_csrf-frontend'=>Yii::$app->request->csrfToken,
                                                'pjax-container' => 'pjax-list',
                                                'title' => Yii::t('app', 'Delete'),
                                            ]
                                        );
                                    }
                                    return $return;
                                }
                            ]
                        ]
                    ],
                ]); ?>

                <?php Pjax::end(); ?>

                <?php

                $this->registerJs("

                $(document).ready(function () { 
                  $(document).on('click', '.ajaxDelete', function (e){
                    e.preventDefault();
                    var deleteUrl     = $(this).attr('delete-url');
                    var keyId     = $(this).attr('key-id');
                    var pjaxContainer = $(this).attr('pjax-container');
                      var result = confirm('Are you sure you want to change status of this item?'); 
                               if (result) {
                                $.ajax({
                                  url:   deleteUrl,
                                  type:  'post', 
                                  data: {
                                  _csrf:'".Yii::$app->request->csrfToken."',
                                  id:keyId,
                                  },
                                  error: function (xhr, status, error) {
                                    console.log('There was an error with your request.' 
                                          + xhr.responseText);
                                  }
                                }).done(function (data) {
                                    $('#statusMsg').html(data); 
                                    $.pjax.reload({container: '#' + $.trim(pjaxContainer)});
                                });
                              }
                   });
                });
                
                ");

                ?>

            </div>
        </div>

        <!-- Add Modal -->
        <div class="modal fade" id="showCode" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered model-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle"><?php echo Yii::t('messages', 'Progress') ?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="row">
                        <div class="col-md-12 no-gutters">
                        <iframe id="iframe-progress" src="" frameborder="0" scrolling="no" width="100%" height="100%"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>