<?php

use app\models\AuthItem;
use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = Yii::t('messages', 'Role Details');
$this->titleDescription = Yii::t('messages', 'View specific role details');
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Manage Roles'), 'url' => ['admin', ['type' => AuthItem::TYPE_ROLE]]];
// $this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'System')];
$this->params['breadcrumbs'][] = Yii::t('messages', 'View Role');
?>

<div class="row no-gutters">
    <div class="content-panel col-md-12">
        <div class="content-inner">
            <div class="content-area">
                <div class="form-row mb-2">
                    <div class="form-group col-md-12"></div>
                </div>
                <div class="content-panel-sub">
                </div>
                <?php /*$this->widget('bootstrap.widgets.TbDetailView', array(
                    'data'=>$model,
                    'attributes'=>array(
                        array(
                            'name'=>'name',
                            'value'=>Erbac::t('messages', $model->name),
                        ),
                        array(
                            'name'=>'description',
                            'value'=>ErbacModule::t('messages', $model->description),
                        ),
                        array(
                            'name'=>'permissions',
                            'type'=>'html',
                            'value'=>$permissionDes)
                    ),
                ));*/ ?>
                <?php
                echo DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                         'name',
                         [
                                 'label' => 'Descriptions',
                                 'value' => $model->description
                         ],
                        [
                            'label' => 'permissions',
                            'format' =>'html',
                            'value' => $permissionDes
                        ]
                        // 'Description',
                    ],
                ]);
                ?>

                <div class="form-row text-left text-md-right">
                    <div class="form-group col-md-12">
                        <?= Html::a('Cancel', ['auth-item/admin','type'=>AuthItem::TYPE_ROLE], ['class' => 'btn btn-secondary', 'data-pjax' => 0]); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>