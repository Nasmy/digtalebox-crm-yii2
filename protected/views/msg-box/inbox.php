<?php

use app\models\User;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\widgets\Pjax;
use app\assets\ImageAsset;
use app\components\WebUser;
use yii\web\JqueryAsset;
JqueryAsset::register($this);
//ImageAsset::register($this);

$this->title = Yii::t('messages', 'Inbox');
$this->titleDescription = Yii::t('messages', 'Messages received by you');

$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Communication'), 'url' => ['#']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Inbox')];

?>

<?php echo  Yii::$app->controller->renderPartial('_tabMenu'); ?>

     <div class="content-inner">
        <div class="content-area">
             <?php Pjax::begin(['id' => 'pjax-list']); ?>
            <div class="content-panel-sub">
                <div class="panel-head">
                    <?php echo Yii::t('messages','Search by') ?>
                </div>
            </div>
            <div class="search-form" style="display:block">
                <?php   echo $this->render('_searchInbox', ['model'=>$model, 'attributeLabels'=>$attributeLabels]); ?>
            </div>


            <?= GridView::widget([
                'id' => 'in-box-grid',
                'dataProvider' => $dataProvider,
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
//                'filterRowOptions' => '$data->status == "1" ? "unread" : ""',
                'columns' => [
                    [
                        'format'=>'raw',
                        'label' => '',
                        'value' => function ($data) {
                             $User = new User();
                             return $User->getPic(null,30,30,null,$data['senderUserId']);
                        }
                    ],
                    [
                        'format'=>'raw',
                        'label' => Yii::t('messages', 'Name'),
                        'value' => function ($data) {
                            $User = new User();
                            return $User->getNameById($data['senderUserId']);
                        }
                    ],
                    'dateTime',
                    'subject',
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'headerOptions' => ['style' => 'text-align: center'],
                        'contentOptions' => ['style' => 'text-align: center'],
                        'template'=>'{reply} {info} {del}',

                        'buttons' => [
                            'reply' => function ($url, $model, $key) {
                                $return = '';
                                if ((Yii::$app->user->checkAccess('MsgBox.Reply'))
                                ) {
                                    $return = Html::a('<span class="fa fa-reply"></span>', $url, ['class' => 'edit', 'data-pjax' => 0]);
                                }
                                return $return;
                            },
                            'info' => function ($url, $model, $key) {
                                $return = '';
                                if ((Yii::$app->user->checkAccess('MsgBox.ViewInboxMsg'))
                                ) {
                                    $return = Html::a(
                                        '<span class="fa fa-eye "></span>',
                                        Url::to(["msg-box/view-inbox-msg","id"=>$model->id]),
                                        [
                                              'title' => Yii::t('app', 'View Message'),
                                        ]
                                    );
                                }
                                return $return;
                            } ,
                            'del' => function ($url, $model, $key) {
                                $url = '/index.php/msg-box/delete-inbox-msg?id='.$key;
                                $return = '';
                                if ((Yii::$app->user->checkAccess('MsgBox.DeleteInboxMsg'))
                                ) {
                                    $return = Html::a(
                                        '<span class="fa fa-trash"></span>',
                                        false,
                                        [
                                            'class' => 'ajaxDelete',
                                            'delete-url' => $url,
                                            'key-id' => $key,
                                            '_csrf-frontend'=>'Yii::$app->request->csrfToken',
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
                $('.search-form form').submit(function(){
                    $.fn.yiiGridView.update('in-box-grid', {
                        data: $(this).serialize()
                    });
                    return false;
                });

                $(document).ready(function () { 
                  $(document).on('click', '.ajaxDelete', function (e){
                    e.preventDefault();
                    var deleteUrl     = $(this).attr('delete-url');
                    var pjaxContainer = $(this).attr('pjax-container');
                    var keyId = $(this).attr('key-id');
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
