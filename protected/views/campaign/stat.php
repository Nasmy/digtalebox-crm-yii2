<?php
use yii\widgets\DetailView;
?>
<style>
    @media screen and (max-width: 570px) {
        .total-stat-user {
            margin-left: 10px !important;
        }
    }
</style>
<div class="row">
    <div class="col-12 col-sm-12 mb-1">
        <div class="progress progress-striped">
            <div class="progress-bar" style="width: <?php echo $progress ?>%;"><?php echo $progress ?>%</div>
        </div>
    </div>
    <div class="col-12 col-sm-12 mt-2">
        <?php  echo DetailView::widget([
            'model'=>$model,
            'attributes'=>array(
                array(
                    'label'=> Yii::t('messages', 'Success'),
                    'format' =>'html',
                    'value'=> '<p class="badge badge-pill badge-success total-stat-user">'.$successCount.'</p>',
                ),
                array(
                    'label'=> Yii::t('messages', 'Failed'),
                    'format' =>'html',
                    'value'=> '<p class="badge badge-pill badge-danger total-stat-user">'.$failedCount.'</p>',
                ),
                array(
                    'label'=> Yii::t('messages', 'Pending'),
                    'format' =>'html',
                    'value'=> '<p class="badge badge-pill badge-warning total-stat-user">'.$pendingCount.'</p>',
                ),
                array(
                    'label'=> Yii::t('messages', 'Total'),
                    'format' =>'html',
                    'value'=> '<p class="badge badge-pill badge-info total-stat-user">'.$totalUsers.'</p>',
                ),
            ),
        ]); ?>
    </div>
</div>


<?php
if ($isRescheduled && $model->campType == Campaign::CAMP_TYPE_SMS) {
    ?>
    <p class="help-block"><?php echo Yii::t('messages', 'Monthly message limit reached. Campaign rescheduled.') ?></p>
    <?php
} else if ($isRescheduled) {
    ?>
    <p class="help-block"><?php echo Yii::t('messages', 'Daily message limit reached. Campaign rescheduled.') ?></p>
    <?php
}
?>