<?php

// use yii\grid\GridView;
use app\models\AuthItem;
use app\models\AuthItemChild;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $model app\models\AuthItemChild */
/* @var $form yii\widgets\ActiveForm */

if (isset($itemName)) {
    $parentName = $itemName;
} else {
    $parentName = '';
}
$url = Yii::$app->urlManager->createUrl('/auth-item/get-parent/');
$setChildrenUrl = Yii::$app->urlManager->createUrl('/auth-item/get-children');


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

<style type="text/css">
    .theme-green .app-body .green-th th {
         font-size: 12px !important;
    }

    .theme-green .app-body  .green-th td.kv-grid-group.kv-grouped-row {
        background: initial !important;
        font-size: inherit !important;
    }


</style>
<div>
    <div class="row no-gutters">
        <div class="content-panel col-md-12">
            <div class="content-inner">
                <div class="content-area">
                    <div class="row">
                        <div class="col-md-12">
                            <?php $form = ActiveForm::begin([
                                'id' => 'user-form'
                            ]); ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="FormMembershipType_name"><?php echo $attributeLabels['name']; ?></label>
                                        <?php
                                        if ($model->isNewRecord) {
                                            echo $form->field($model, 'name')->textInput(['class' => 'form-control', 'maxlength' => 64])->label(false);
                                        } else {
                                            echo $form->field($model, 'name')->textInput(['class' => 'form-control', 'maxlength' => 64, 'readonly' => true])->label(false);
                                        }
                                        ?>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="FormMembershipType_fee"><?php echo $attributeLabels['description']; ?></label>
                                        <?php echo $form->field($model, 'description')->textInput(['class' => 'form-control', 'maxlength' => 64])->label(false); ?>
                                    </div>
                                </div>

                            </div>
                            <?php
                            if ($dataProvider->totalCount > 0) {
                                echo GridView::widget([
                                    'dataProvider' => $dataProvider,
                                    'id' => 'permissions',
                                    'tableOptions' => ['class' => 'table table-striped'],
                                    'summary' => '
                                        <div class="summery">
                                        ' . Yii::t('messages', 'Total {count} results.') .
                                        '</div>
                                        <input type="hidden" name="total-count" id="total-count" value="{count}"> 
                                        ',
                                    'tableOptions' => ['class' => 'table green-th table-striped table-bordered'],
                                    'columns' => [
                                        [
                                            'attribute' => 'category',
                                            'width' => '310px',
                                            'value' => function ($model, $key, $index, $widget) {
                                                $category = str_replace(' /  ', ' / ', $model['category']);

                                                    return Yii::t('messages', $category);
                                            },
                                            'filterType' => GridView::FILTER_SELECT2,
                                            'filter' => ArrayHelper::map(AuthItem::find()->orderBy('category')->asArray()->all(), 'id', 'category'),
                                            'filterWidgetOptions' => [
                                                'pluginOptions' => ['allowClear' => true],
                                            ],
                                            'group' => true,
                                            'groupedRow' => true,
                                            'groupOddCssClass' => 'kv-grouped-row',  // configure odd group cell css class
                                            'groupEvenCssClass' => 'kv-grouped-row',
                                        ],
                                        [
                                            'class' => '\kartik\grid\CheckboxColumn',
                                            'checkboxOptions' => function ($model, $key, $index, $column) use ($parentName) {

                                                if ($parentName != null) {
                                                    $updatedPermissions = AuthItemChild::find()->where(['parent' => $parentName, 'child' => $model['name']])->one();
                                                    if ($updatedPermissions) {
                                                        $checked = true;
                                                    } else {
                                                        $checked = false;
                                                    }
                                                } else {
                                                    $checked = false;
                                                }
                                                return ['value' => $model['name'], 'checked' => $checked];
                                            },
                                        ],
                                        array(
                                            'attribute' => Yii::t('messages', 'Permission'),
                                            'value' => function ($model) {
                                                return Yii::t('messages', $model['description']);
                                            }
                                            // 'value' => Yii::t('messages', $dataProvider->description),
                                        ),

                                        // ['class' => 'yii\grid\ActionColumn'],
                                    ],
                                ]);
                            }
                            ?>
                            <div class="form-row text-left text-md-right">
                                <div class="form-group col-md-12">
                                    <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Save', ['class' => 'btn btn-success']) ?>
                                    <?= Html::a('Cancel', ['auth-item/admin', 'type' => AuthItem::TYPE_ROLE], ['class' => 'btn btn-secondary', 'data-pjax' => 0]); ?>
                                </div>
                            </div>
                            <?php ActiveForm::end(); ?>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
