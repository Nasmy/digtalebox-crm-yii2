<?php
/* @var $this yii\web\View */

use app\models\User;
use yii\grid\GridView;
use yii\helpers\Html;

/* @var $searchModel app\models\searchVolunteers */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('messages', 'Volunteers');
$this->titleDescription = Yii::t('messages', 'Users those who were registered with the system');
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'People '), 'url' => ['#']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Volunteers')];

?>

<div class="row no-gutters">
    <div class="content-panel col-md-12">
        <div class="content-inner">
            <div class="content-area">

                <?php
                \yii\widgets\Pjax::begin();
                echo GridView::widget([
                    'id' => 'campaign-users-grid',
                    'dataProvider' => $model->searchVolunteers(),
                    'summary' => '<div class="text-right results-count mt-4">' . Yii::t('messages', 'Displaying {begin}-{end} of {count} imports') . '</div>',
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
                    'tableOptions' => ['class' => 'table table-striped table-bordered'],
                    'headerRowOptions' => array('class' => 'table-wrap table-custom'),
                    'columns' => [
                        [
                            'format' => 'raw',
                            'attribute' => '',
                            'value' => function ($data) {
                                  return str_replace("http","https",User::getPic(null,30,30,null,$data['id']));
                            }
                        ],
                        [
                            'format' => 'raw',
                            'attribute' => Yii::t('messages', 'Name'),
                            'value' => function ($data) {
                                  return User::getNameById($data['id']);
                            }
                        ],
                        'email',
                        'teamName',
                        'reqruiteCount',
                        [
                            'format' => 'raw',
                            'attribute' =>Yii::t('messages', 'Total Donations ({symbol})', array('symbol' => $currencySymbol)),
                            'value' => function ($data) {
                                return $data['totalDonations'];
                            }
                        ],
                    ]
                ]);
                \yii\widgets\Pjax::end();
                ?>

            </div>
        </div>
    </div>
</div>
