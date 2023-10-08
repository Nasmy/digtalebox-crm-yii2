<?php

use app\models\User;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\models\FormMembershipDonationSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('messages','Donations & Memberships');
$this->titleDescription = Yii::t('messages', 'Manage Donations & Membership');
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'People'), 'url' => ['form-membership-donation/admin']];
$this->params['breadcrumbs'][] = $this->title;

$script = <<< JS
 
	$('.se/arch-button').click(function(){
        $('.search-form').toggle();
        return false;
    });
	 
	$('.search-form form').submit(function(){
        $.fn.yiiGridView.update('form-membership-donation-grid', {
            data: $(this).serialize()
        });
       // return false;
    });
	
	

JS;
$this->registerJs($script);


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
                    <?php
                    echo Yii::$app->controller->renderPartial('_search', [
                        'model' => $searchModel,
                    ]);
                    ?>
                </div>

                <?php Pjax::begin(['id' => 'pjax-list']); ?>

                <?= GridView::widget([
                    'id' => 'form-membership-donation-grid',
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
                        [
                            'attribute'=>Yii::t('messages','firstName'),
                            'format' => 'raw',
                            'value' => function ($model) {
                                return is_null($model['firstName']) ?  'N/A' : $model['firstName'];
                            }
                        ],
                        [
                            'attribute'=>Yii::t('messages','lastName'),
                            'format' => 'raw',
                            'value' => function ($model) {
                                return is_null($model['lastName']) ?  'N/A' : $model['lastName'];
                            }
                        ],
                        [
                            'attribute'=>Yii::t('messages','memberFee'),
                            'format' => 'raw',
                            'value' => function ($model) {
                                return is_null($model['memberFee']) ?  'N/A' : $model['memberFee'];
                            }
                        ],
                        [
                            'attribute'=>Yii::t('messages','donationFee'),
                            'format' => 'raw',
                            'value' => function ($model) {
                                return is_null($model['donationFee']) ?  'N/A' : $model['donationFee'];
                            }
                        ],
                        [
                            'attribute'=>Yii::t('messages','payerEmail'),
                            'format' => 'raw',
                            'value' => function ($model) {
                                return is_null($model['payerEmail']) ?  'N/A' : $model['payerEmail'];
                            }
                        ],
                    ],
                ]); ?>


                <?php Pjax::end(); ?>

            </div>
        </div>
    </div>
</div>
