<?php

use app\models\AuthItem;
use kartik\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;use yii\widgets\Pjax;

$this->title = 'Generate Permissions';
$this->titleDescription = Yii::t('messages', 'Generate permissions for controller actions');
$this->params['breadcrumbs'][] = ['label' => 'System', 'url' => ['admin', 'type' => AuthItem::TYPE_OPERATION]];
$this->params['breadcrumbs'][] = ['label' => 'Manage Permission', 'url' => ['admin', 'type' => AuthItem::TYPE_OPERATION]];
$this->params['breadcrumbs'][] = 'Generate Permissions';

$this->registerJs("
$(document).ready(function(){

     var inputCheckboxes = $('#permissions .permissions input[type=\"checkbox\"]');
        
        var countCheckedCheckboxes =  inputCheckboxes.filter(':checked').length;
        var checkboxesTotal = $('#total-count').val();
        if(countCheckedCheckboxes == checkboxesTotal){
                $('.select-on-check-all').attr('checked','checked');
        }
 });
");

?>


<?php $form=ActiveForm::begin([
    'id'=>'permission-form',
    'enableAjaxValidation'=>false,
    'method'=>'post',
]); ?>


    <style type="text/css">
        .theme-green .app-body .green-th th {
            background: #60A649 !important;
            font-size: 12px !important;
        }

        .green-th td.kv-grid-group.kv-grouped-row {
            background: initial !important;
        }


    </style>

 <div class="row no-gutters">
    <div class="content-panel col-md-12">
        <div class="content-inner">
            <div class="content-area">
                <div class="table-wrap table-custom">
                    <?php Pjax::begin(['id' => 'pjax-list']); ?>

                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'id' => 'permissions',
                        'striped' => true,
                        'dataProvider' => $dataProvider,
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
                        'summary' => '<div class="text-right results-count mt-4">' . Yii::t('messages', 'Displaying {begin}-{end} of {totalCount} results') . '</div>',
                        'options' => ['class' => 'table-wrap table-custom'],
                        'layout' => '<div class="text-right results-count">{summary}</div>
                        <div class="table-wrap table-custom">{items}</div>
                        <div class="row no-gutters d-flex flex-sm-row-reverse flex-sm-column-reverse flex-md-row"> 
                        <div class="col-md-6"></div>
                        <div class="col-md-6">
                            <div class="float-right"><nav aria-label="Page navigation">{pager}</nav></div>
                        </div></div>',
                        'tableOptions' => ['class' => 'table green-th table-striped table-bordered'],
                        'columns' => [
                            [
                                'class' => 'yii\grid\CheckboxColumn',
                                'checkboxOptions' => function($model, $key, $index, $column) {
                                      return[
                                        'value'=>$model["name"].",".$model["category"],
                                        'checked'=>AuthItem::isPermissionExists($model["name"]),
                                        'name'=>'permissions_c0[]',
                                     ];
                                }
                            ],
                            [
                                'attribute' => 'category',
                                'width' => '310px',
                                'value' => function ($model, $key, $index, $widget) {
                                    return $model['category'];
                                },
                                'filterType' => \kartik\grid\GridView::FILTER_SELECT2,
                                'filter' => ArrayHelper::map(AuthItem::find()->orderBy('category')->asArray()->all(), 'id', 'category'),
                                'filterWidgetOptions' => [
                                    'pluginOptions' => ['allowClear' => true],
                                ],
                                'group' => true,
                                'groupedRow' => true,
                                'groupOddCssClass' => 'kv-grouped-row',  // configure odd group cell css class
                                'groupEvenCssClass' => 'kv-grouped-row',
                                'contentOptions' => ['class'=>'grid-bold font-weight-bold']
                            ],
                            [
                                'label' => 'Permission Name',
                                'format' => 'raw',
                                'value' => function ($data) {
                                     return $data["name"];
                                },

                            ]
                        ],
                    ]); ?>
                    <?php Pjax::end(); ?>


                </div>
            </div>
        </div>
    </div>
</div>
<div class="form-actions">
    <?php
        echo  Html::submitButton(Yii::t('messages', 'Save'),['class'=>'btn btn-primary','name'=>'save']);
     ?>

    <?= Html::a(Yii::t('messages', 'Cancel'),Yii::$app->urlManager->createUrl(['auth-item/admin','type'=>AuthItem::TYPE_OPERATION]), ['class' => 'btn btn-secondary']) ?>
</div>

<?php
ActiveForm::end();