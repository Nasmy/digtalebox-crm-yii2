<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Event */


$this->title = Yii::t('messages', 'Preview Event');
$this->titleDescription = Yii::t('messages', 'Preview event before add to the system.');


$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Manage Events'), 'url' => ['admin']];
$this->params['breadcrumbs'][] = Yii::t('messages', 'Preview');
?>

<div class="row no-gutters">
    <div class="content-panel col-md-12">
        <div class="content-inner">
            <div class="content-area">

                <div class="row">
                    <div class="col-md-12">
                        <?php $advanceKeywordHint = ''; ?>
                        <?php
                          echo Yii::$app->controller->renderPartial('_eventEmailView',
                            [
                                'model'=>$model,
                                'appId'=>$appId,
                                'erbacModule'=>$erbacModule,
                            ]);
                        ?>
                    </div>
                </div>

            </div>
        </div>

    </div>
