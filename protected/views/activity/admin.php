<?php
$this->title = Yii::t('messages', 'Activities');
$this->titleDescription = Yii::t('messages', 'User activities of DigitaleBox');

$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'People'), 'url' => ['#']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Activities')];

use app\models\User;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

?>

<style type="text/css">
    div#activity-grid table tr {
        font-size: 12px !important;
    }
</style>

<div class="row no-gutters">
    <div class="content-panel col-md-12">
        <div class="content-inner">
            <div class="content-area">

                <?php Pjax::begin(['id' => 'pjax-list']); ?>

                <?= GridView::widget([
                    'id' => 'activity-grid',
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
                            'format' => 'html',
                            'attribute' => '',
                            'value' => function ($dataProvider) {
                                return User::getPic($dataProvider->profImage);
                            }
                        ],
                        [
                            'format' => 'raw',
                            'attribute' => Yii::t('messages', 'name'),
                            'value' => function ($dataProvider) {
                                $userInfo = User::findOne($dataProvider['userId']);
                                $fullName = "N/A";
                                if (!is_null($userInfo)) {
                                    $fullName = $userInfo->firstName . " " . $userInfo->lastName;
                                }
                                return $fullName;
                            }
                        ],
                        [
                            'attribute' => Yii::t('messages', 'Date Time'),
                            'value' => function ($dataProvider) {
                                $user = new User();
                                return $user->convertDBTime($dataProvider->dateTime);
                            }
                        ],
                        [
                            'format' => 'html',
                            'attribute' => Yii::t('messages', 'Activity'),
                            'value' => function ($model) {
                                return $model->getActivityMessage($model);
                            }
                        ],
                    ],
                ]); ?>

                <?php Pjax::end(); ?>

            </div>
        </div>
    </div>
</div>

