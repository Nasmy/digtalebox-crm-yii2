<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Event */

$this->title = Yii::t('messages', 'Update Event');
$this->titleDescription = Yii::t('messages', 'Update event in the system');

$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Manage Events'), 'url' => ['admin']];
$this->params['breadcrumbs'][] = Yii::t('messages', 'Update');
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
                        <?php $advanceKeywordHint = Yii::t('messages', 'When update this; will not affected to old users.'); ?>
                        <?php echo Yii::$app->controller->renderPartial('_form', [
                            'model' => $model,
                            'clientFbProfile' => $clientFbProfile,
                            'advanceKeywordHint' => $advanceKeywordHint,
                            'osmMaxLimit' => $osmMaxLimit,
                            'osmCanProceed' => $osmCanProceed
                        ]); ?>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>