<?php

use app\components\WebUser;
use app\models\AuthItem;
use yii\bootstrap\Button;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\models\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'System'), 'url' => ['user/admin']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'System')];
$this->title = Yii::t('messages', 'Manage Users');
$this->titleDescription = Yii::t('messages', 'Manage users who are using this system');

?>

<div class="row no-gutters">
    <div class="content-panel col-md-12">
        <div class="content-inner">
            <div class="content-area">

                <div class="form-row mb-2">
                    <div class="form-group col-md-12">
                        <?php
                        if (Yii::$app->user->checkAccess('User.Create')) {
                            echo Html::a("<i class=\"fa fa-plus\"></i> " . Yii::t('messages', 'Add User'), ['/user/create'], ['class' => 'btn btn-primary']);
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
                    echo $this->render('_search', array(
                        'model' => $searchModel
                    ));
                    ?>
                    <?php Pjax::begin(['id' => 'pjax-list']); ?>
                    <div class="table-wrap table-custom">
                        <?= GridView::widget([
                            'id' => 'user-grid',
                            'dataProvider' => $dataProvider,
                            'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => ''],
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
                                'firstName',
                                'lastName',
                                'username',
                                'email',
                                [
                                    'label' => 'Role',
                                    'value' => function ($model) {
                                        return implode(",", Yii::$app->user->getAssignedItems($model->id, true));
                                    }
                                ],
                                [
                                    'class' => 'yii\grid\ActionColumn',
                                    'headerOptions' => ['style' => 'text-align: center'],
                                    'contentOptions' => ['style' => 'text-align: center'],
                                    'template' => '{update} {delete}',
                                    'buttons' => [
                                        'update' => function ($url, $data, $key) {
                                            $return = '';
                                            if ((Yii::$app->user->checkAccess("User.Update"))
                                                || (Yii::$app->user->checkAccess("User.Update") && Yii::$app->session->get("is_super_admin") && !AuthItem::isDefaultRole(implode(",", WebUser::getAssignedItems($data->id, true))))
                                                || (Yii::$app->session->get("is_super_admin") && !AuthItem::isDefaultRole(Yii::$app->session->get("login_user_role")))) {
                                                $return = Html::a('<span class="fa fa-edit fa-lg"></span>', $url, ['class' => 'edit', 'data-pjax' => 0]);
                                            }
                                            return $return;

                                        },
                                        'delete' => function ($url, $data, $key) {
                                            $return = '';
                                            if (
                                                AuthItem::isDefaultRole(Yii::$app->session->get("login_user_role")) && (
                                                    (Yii::$app->user->checkAccess("User.Delete"))
                                                    || (Yii::$app->user->checkAccess("User.Delete") && Yii::$app->session->get("is_super_admin") && !AuthItem::isDefaultRole(implode(",", WebUser::getAssignedItems($data->id, true))))
                                                    || (Yii::$app->session->get("is_super_admin") && !AuthItem::isDefaultRole(Yii::$app->session->get("login_user_role"))))
                                            ) {
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
                                                return $return;

                                            }
                                            return $return;
                                        }
                                    ],
                                ],
                            ],
                        ]); ?>
                    </div>
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
</div>

