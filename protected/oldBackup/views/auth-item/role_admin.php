<?php

use app\models\AuthItem;
use http\Url;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

$this->title = Yii::t('messages', 'Manage Roles');
$this->titleDescription = Yii::t('messages', 'Manage system roles');
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'System'), 'url' => ['admin', ['type' => AuthItem::TYPE_ROLE]]];
$this->params['breadcrumbs'][] = Yii::t('messages', 'Manage Roles');
$this->registerJs("

$(function() {
	$(document).on('click', '.ajaxDelete', function() {
    	var deleteUrl = $(this).attr('href');
    	var pjaxContainer = $(this).attr('pjax-container');  	
            	$.ajax({
                	url: deleteUrl,
                	type: \"post\",
                	dataType: 'json',
                	error: function(xhr, status, error) {
                    	alert('There was an error with your request.' + xhr.responseText);
                	}
            	}).done(function(data) {
                    $.pjax.reload({container: '#pjax-list'});
            	});        	
    	});
});
");
?>

 <div class="row no-gutters">
    <div class="content-panel col-md-12">
        <div class="content-inner">
            <div class="content-area">
                <div class="form-row mb-2">
                    <div class="form-group col-md-12">
                        <?php
                        if (Yii::$app->user->checkAccess('Erbac.AuthItem.Create')) {
                            echo Html::a('<i class="fa fa-plus"> </i> '.Yii::t('auth', 'Add Role'), ['/auth-item/create', 'type' => AuthItem::TYPE_ROLE], ['class' => 'btn btn-primary']);
                        }
                        ?>
                    </div>
                </div>
                <div class="content-panel-sub">
                </div>
                <div class="table-wrap table-custom">
                    <?php Pjax::begin(['id' => 'pjax-list']); ?>

                    <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                        'summary' => '<div class="text-right results-count mt-4">' . Yii::t('messages', 'Displaying {begin}-{end} of {totalCount} results') . '</div>',
                    'tableOptions' => ['class' => 'table table-striped table-bordered'],
                    'layout' => '<div class="text-right results-count">{summary}</div>
                        <div class="table-wrap">{items}</div>
                        <div class="row no-gutters d-flex flex-sm-row-reverse flex-sm-column-reverse flex-md-row"> 
                        <div class="col-md-6"></div>
                        <div class="col-md-6">
                            <div class="float-right"><nav aria-label="Page navigation">{pager}</nav></div>
                        </div></div>',
                    'headerRowOptions' => array('class' => 'table-wrap table-custom'),
                    'columns' => [
                        'name',
                        'description',
                        [
                            'format' => 'raw',
                            'value' => function ($searchModel) {
                                return $searchModel->getRoleTypeLabel($searchModel->name);
                            },
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'headerOptions' => ['style' => 'text-align: center'],
                            'contentOptions' => ['style' => 'text-align: center'],
                            'template' => '{update} {delete} {view}',
                            'buttons' => [
                                'update' => function ($url, $searchModel, $key) {
                                    $return = '';
                                    if($searchModel->checkAccess($searchModel)) {
                                        $return = Html::a('', ['auth-item/update','itemName'=> $searchModel['name'],'type'=>AuthItem::TYPE_ROLE], ['class' => 'fa fa-edit fa-lg']);
                                    }
                                    return $return;

                                },
                                'view' => function ($url,$searchModel, $key) {
                                    $return = '';
                                    $return = Html::a('', ['auth-item/view','itemName'=> $searchModel['name'],'type'=>AuthItem::TYPE_ROLE], ['class' => 'fa fa-eye fa-lg']);

                                    return $return;
                                },
                                'delete' => function ($url,$searchModel, $key) {
                                     if($searchModel->checkAccess($searchModel)){
                                        $return = '';
                                        $return = Html::a('', ['auth-item/delete','itemName'=> $searchModel['name'],'type'=>AuthItem::TYPE_ROLE], ['class' => 'fa ajaxDelete fa-trash fa-lg']);

                                        return $return;
                                    }
                                },
                            ]
                        ],
                    ],
                ]); ?>
                <?php Pjax::end(); ?>

                </div>
            </div>
        </div>
    </div>
</div>
