<?php

use app\models\Campaign;
use app\models\User;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\web\View;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\models\SearchCriteriaSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('messages', 'Saved Search');
$this->titleDescription = Yii::t('messages', 'Different search criterias saved by you.');

$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'People'), 'url' => ['#']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Manage saved searches')];

$delConfirm = Yii::t('messages', 'Are you sure you want to delete the saved search?');
$script = <<< JS
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
	$('.editBtn').click(function(e) {
	     console.log(1);
	});
	 
JS;
$this->registerJs($script,\yii\web\VIEW::POS_HEAD);
$this->registerJs(' 

        function AjaxReq(s,e) {
            e.preventDefault();
            var Url = $(s).attr("href");
            $.ajax({
            url: Url, 
            type:"get", 
            success: function(data){
                var res = jQuery.parseJSON(data);
                if (res.status == 0) {
                    location.href = res.message;
                } else {
                    $("#statusMsg").html(res.message);
                }
            }}); 
        }
        
        $(document).on("click",".sendEmail", function (e){
            $("#iframe-campaign").attr("src",$(this).attr("href"));
        });
        
        $(document).on("click",".viewCriteria", function (e){
            $("#iframe-viewCriteria").attr("src",$(this).attr("href")); 
         });
        
         $(document).on("click",".ajaxDelete", function (e){
                        e.preventDefault();
                        var deleteUrl     = $(this).attr(\'delete-url\');
                        var pjaxContainer = $(this).attr(\'pjax-container\');
                          var result = confirm("' . $delConfirm . '"); 
                           if (result) {
                                $.ajax({
                                url:   deleteUrl,
                                type:  "post", 
                                data: {
                                YII_CSRF_TOKEN:\'".Yii::$app->request->csrfToken."\'},
                                success: function(data){
                                 $("#statusMsg").html(data);
                                  $.pjax.reload({container: \'#saved-search-grid\'});
                                   }}); 
                            }
                });
            
    
    ', View::POS_READY);
?>




<div class="row no-gutters">
    <div class="content-panel col-md-12">
        <div class="content-inner">
            <div class="content-area">
                <div class="content-panel-sub">
                    <div class="panel-head">
                        <?php echo Yii::t('messages', 'Search by') ?>
                    </div>
                </div>
                <div class="search-form" style="display:block">
                  <?php  echo yii::$app->controller->renderPartial('_search', ['model' => $model]); ?>
                <?php  Pjax::begin(['id' => 'saved-search-grid']); ?>
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => ''],
                    'options' => ['class' => 'table-wrap table-custom'],
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
                            'criteriaName',
                            [
                                'attribute' => 'createdAt',
                                'format' => 'html',
                                'value' => function ($model) {
                                     return User::convertSystemTime($model->createdAt);
                                }
                            ],
                            [
                                'attribute' => 'createdBy',
                                'format' => 'html',
                                'value' => function ($model) {
                                    return User::getNameById($model->createdBy);
                                }
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'headerOptions' => ['style' => 'text-align: center'],
                                'contentOptions' => ['style' => 'text-align: right'],
                                'template' => '{all} {sms} {emailMessage} {viewCriteria} {edit} {del}',

                                'buttons' => [
//                                    'all' => function ($url, $model, $key) {
//                                    $url =  Yii::$app->urlManager->createUrl(['search-criteria/show-messages-dialog','id'=>$model->id,'type'=>Campaign::CAMP_TYPE_ALL]);
//                                        $return = '';
//                                        if (Yii::$app->user->checkAccess('SendBulkMessages')){
//                                            $return = Html::a(
//                                                    '',
//                                                    $url,
//                                                    [
//                                                    'data-pjax' => 0,
//                                                    'title' => Yii::t('messages', 'Campaign cover all channels(Email,Twitter)'),
//                                                    'data-target' => '#sendEmail',
//                                                    'data-toggle'=>"modal",
//                                                    'class'=>"fa fa-star fa-lg sendEmail"
//                                                    ]);
//                                        }
//                                        return $return;
//                                    },
                                    /*'sms' => function ($url, $model, $key) {
                                    $url =  Yii::$app->urlManager->createUrl(['search-criteria/show-messages-dialog','id'=>$model->id,'type'=>Campaign::CAMP_TYPE_SMS]);
                                        $return = '';
                                        if (Yii::$app->user->checkAccess('SendBulkMessages')){
                                            $return = Html::a(
                                                    '',
                                                    $url,
                                                    [
                                                    'title' => Yii::t('messages', 'SMS campaign'),
                                                    'data-pjax' => 0,
                                                    'data-target' => '#sendEmail',
                                                    'data-toggle'=>"modal",
                                                    'class'=>"fa fa-mobile-phone fa-lg sendEmail"

                                                    ]);
                                        }
                                        return $return;
                                    },
                                    'emailMessage' => function ($url, $model, $key) {
                                    $url =  Yii::$app->urlManager->createUrl(['search-criteria/show-messages-dialog','id'=>$model->id,'type'=>Campaign::CAMP_TYPE_EMAIL]);
                                        $return = '';
                                        if (Yii::$app->user->checkAccess('SendBulkMessages')){
                                            $return = Html::a(
                                                    '',
                                                    $url,
                                                    [
                                                    'title' => Yii::t('messages', 'Email campaign'),
                                                    'data-pjax' => 0,
                                                    'data-target' => '#sendEmail',
                                                    'data-toggle'=>"modal",
                                                    'class'=>"fa fa-envelope fa-lg sendEmail"
                                                    ]);
                                        }
                                        return $return;
                                    },*/
//                                    'twitterMessage' => function ($url, $model, $key) {
//                                    $url =  Yii::$app->urlManager->createUrl(['search-criteria/show-messages-dialog','id'=>$model->id,'type'=>Campaign::CAMP_TYPE_TWITTER]);
//                                        $return = '';
//                                        if (Yii::$app->user->checkAccess('SendBulkMessages')){
//                                            $return = Html::a(
//                                                    '',
//                                                    $url,
//                                                    [
//                                                    'onClick'=>'sendEmail(this);',
//                                                    'title' => Yii::t('messages', 'Email campaign'),
//                                                    'data-pjax' => 0,
//                                                    'data-target' => '#sendEmail',
//                                                    'data-toggle'=>"modal",
//                                                    'class'=>"fa fa-twitter fa-lg"
//                                                    ]);
//                                        }
//                                        return $return;
//                                    },
                                    'viewCriteria' => function ($url, $model, $key) {
                                    $url =  Yii::$app->urlManager->createUrl(['search-criteria/view','id'=>$model->id]);
                                        $return = '';
                                        if (Yii::$app->user->checkAccess('SearchCriteria.View')) {
                                            $return = Html::a(
                                            '',
                                            $url,
                                            [
                                            'title' => Yii::t('messages', 'View Criteria'),
                                            'data-pjax' => 0,
                                            'data-target' => '#viewCriteria',
                                            'data-toggle'=>"modal",
                                            'class'=>"fa fa-eye fa-lg viewCriteria"

                                            ]);
                                        }
                                        return $return;
                                    },
                                    'edit' => function ($url, $model, $key) {
                                        $url =  Yii::$app->urlManager->createUrl(['search-criteria/is-criteria-in-use','savedSearchId'=>$model->id]);
                                        $return = '';
                                        if (Yii::$app->user->checkAccess('AdvancedSearch.Admin') OR Yii::$app->user->id == $model->createdBy) {
                                            $return = Html::a(
                                            '',
                                            $url,
                                            [
                                                'title' => Yii::t('messages', 'Edit search'),
                                                'class' => 'editBtn fa fa-edit fa-lg',
                                                'pjax-container' => 'saved-search-grid',
                                                'onClick' => "
                                                 var pjaxContainer = $(this).attr('pjax-container'); 
                                                
                                                    $.ajax('$url', {
                                                        type: 'POST'
                                                    }).done(function(data) {
                                                        $('#statusMsg').html(data);
                                                        $.pjax.reload({container: '#' + $.trim(pjaxContainer)});
                                                    });
                                               
                                                return false;
                                                ",


                                            ]);
                                        }
                                        return $return;
                                    },
                                    'del' => function ($url, $model, $key) {
                                         $url =  Yii::$app->urlManager->createUrl(['search-criteria/delete','savedSearchId'=>$model->id]);
                                         $return = '';
                                        if (Yii::$app->user->checkAccess("AdvancedSearch.Admin") OR Yii::$app->user->id == $model->createdBy) {
                                            $return = Html::a(
                                                '',
                                                'javascript:void(0);',
                                                [
                                                    'delete-url' => $url,
                                                    '_csrf-frontend'=>Yii::$app->request->csrfToken,
                                                    'pjax-container' => 'saved-search-grid',
                                                    'title' => Yii::t('app', 'Delete'),
                                                    'class'=>"fa fa-trash fa-lg ajaxDelete",
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

                </div>

            </div>
        </div>
    </div>
</div>

<!-- campaign modal --->
<div class="modal fade" id="sendEmail" tabindex="-1" role="dialog"
     aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered model-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"
                    id="exampleModalLongTitle"><?php echo Yii::t('messages', 'Start Campaign') ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <iframe id="iframe-campaign" class="modal-body" src="" frameborder="0" scrolling="no"
                    width="100%" height="380px"></iframe>
        </div>
    </div>
</div>

<!--- End campaign modal --->

<!--View search criteria Modal`s -->
<div class="modal fade" id="viewCriteria" tabindex="-1" role="dialog"
     aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered model-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"
                    id="exampleModalLongTitle"><?php echo Yii::t('messages', 'View Criteria') ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <iframe id="iframe-viewCriteria" class="modal-body" src="" frameborder="0" scrolling="yes"
                    width="100%"  height="380px"></iframe>
        </div>
    </div>
</div>
<!-- End search criteria modal --->

