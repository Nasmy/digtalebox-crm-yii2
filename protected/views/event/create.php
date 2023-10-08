<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Event */


$this->title = Yii::t('messages', 'Create Event');
$this->titleDescription = Yii::t('messages', 'Add new event to the system');


$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Manage Events'), 'url' => ['admin']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Create')];


?>

<div class="row no-gutters">
    <div class="content-panel col-md-12">
        <div class="content-inner">
            <div class="content-area">

                <div class="row">
                    <div class="col-md-12">

                        <div class="content-panel-sub">
                            <div class="panel-head">
                                <?php echo Yii::t('messages', 'Event Details'); ?>
                            </div>
                        </div>
                        <?php $advanceKeywordHint = ''; ?>
                        <?php
                       echo $this->render('_form', [
                            'model' => $model,
                            'clientFbProfile'=>$clientFbProfile,
                            'advanceKeywordHint'=>$advanceKeywordHint,
                            'erbacModule'=>$erbacModule,
                            'osmMaxLimit' => $osmMaxLimit,
                            'osmCanProceed' => $osmCanProceed
                        ]); ?>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>
