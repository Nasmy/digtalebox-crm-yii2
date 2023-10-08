<?php

use app\models\MessageTemplate;
use app\models\User;
use yii\grid\GridView;

echo GridView::widget([
    'id' => 'message-template-grid',
    'dataProvider' => $model,
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
        'name',
        'description',
        [
            'format' => 'raw',
            'attribute' => 'type',
            'value' => function ($model) {
                $messageTemplate = new MessageTemplate();
                return $messageTemplate->getTemplateTypeOptions($model['type']);
            },
        ],
        [
            'format' => 'raw',
            'attribute' => 'createdBy',
            'value' => function ($model) {
                return User::getNameById($model['createdBy']);
            },
        ],
    ]
]);


echo "</div>"
?>