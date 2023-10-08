<?php

use yii\helpers\Html;

$this->title = Yii::t('messages', 'Upgrade');
$this->titleDescription = Yii::t('messages', 'Upgrade your plan');

$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'People'), 'url' => ['#']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Upgrade')];

?>

<?php if (Yii::$app->user->checkAccess('UpgradePlan')): ?>
    <div class="row-fluid">
        <div class="span12">
            <div id="statusMsg"></div>
            <?php if (Yii::$app->session->hasFlash('success')): ?>
                <div class="alert alert-success alert-dismissable">
                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                    <?= Yii::$app->session->getFlash('success') ?>
                </div>
            <?php endif; ?>

            <?php if (Yii::$app->session->hasFlash('error')): ?>
                <div class="alert alert-danger alert-dismissable">
                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                    <?= Yii::$app->session->getFlash('error') ?>
                </div>
            <?php endif; ?>

               <?php if (Yii::$app->session->hasFlash('warning')): ?>
                <div class="alert alert-warning alert-dismissable">
                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                    <?= Yii::$app->session->getFlash('warning') ?>
                </div>
            <?php endif; ?>

            <?php
            echo Html::a("<i class=\"fa fa-arrow-up\"></i> " . Yii::t('messages', 'Upgrade Plan'), Yii::$app->toolKit->getUpgradeUrlToSales(), ['class' => 'btn-primary grid-button btn']);

            ?>
        </div>
    </div>
<?php endif; ?>

<?php if (!Yii::$app->user->checkAccess('UpgradePlan')): ?>
    <?php echo Yii::t('messages', 'Please contact account owner to upgrade the plan.'); ?>
<?php endif; ?>