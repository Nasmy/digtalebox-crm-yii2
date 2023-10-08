<?php

use app\models\CampaignUsers;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\CampaignUsersSearch */
/* @var $form yii\widgets\ActiveForm */

if(isset($_GET['id'])) {
    $campaignId = $_GET['id'];
}
?>

<div class="campaign-users-search">
     <?php $id = Yii::$app->controller->actionParams['id'];
     $url = Yii::$app->controller->route.'?id='.$id;
      $form = ActiveForm::begin([
        'action' => [$url] ,
        'method' => 'get',
          'options' => [
              'data-pjax' => 1
          ],
    ]); ?>

    <div class="form-row">
        <?php
        if (!$isSmsStat) { ?>
            <div class="form-group col-md-4">
                <?php echo $form->field($model, 'emailStatus')->dropDownList(array('' => Yii::t('messages', '- Status -'), CampaignUsers::EMAIL_SENT => Yii::t('messages', 'Sent'), CampaignUsers::EMAIL_CLICKED => Yii::t('messages', 'Clicked'), CampaignUsers::EMAIL_OPENED => Yii::t('messages', 'Opened'), CampaignUsers::EMAIL_BOUNCED => Yii::t('messages', 'Bounced'), CampaignUsers::EMAIL_BLOCKED => Yii::t('messages', 'Blocked'), CampaignUsers::EMAIL_SPAM => Yii::t('messages', 'Spam'), CampaignUsers::EMAIL_FAILED => Yii::t('messages', 'Failed'), CampaignUsers::EMAIL_UNSUBSCRIBED => Yii::t('messages', 'Unsubscribed'),), array('class' => 'form-control'))->label(false) ?>
            </div>
        <?php } ?>

        <?php if ($isSmsStat) { ?>
            <div class="form-group col-md-4 m-0">
                <?php echo $form->field($model, 'smsStatus')->dropDownList(array('' => Yii::t('messages', '- Status -'), CampaignUsers::SMS_PENDING => Yii::t('messages', 'Pending'), CampaignUsers::SMS_DELIVERED => Yii::t('messages', 'Delivered'), CampaignUsers::SMS_FAILED => Yii::t('messages', 'Failed')), array('class' => 'form-control'))->label(false) ?>
            </div>
        <?php } ?>

        <div class="form-group col-md-4 m-0">
            <?php echo $form->field($model, 'name')->textInput(array('class' => 'form-control', 'placeholder' => Yii::t('messages', "Name")))->label(false)?>
        </div>
        <?php if ($isAdmin) { ?>
            <div class="form-group col-md-4 m-0">
                <?php echo $form->field($model, 'email')->textInput(array('class' => 'form-control', 'placeholder' => Yii::t('messages', "Email")))->label(false)?>
            </div>
        <?php } ?>
        <div class="form-group col-md-4 m-0">
            <?php echo $form->field($model, 'mobile')->textInput(array('class' => 'form-control', 'placeholder' => Yii::t('messages', "Mobile")))->label(false)?>
        </div>

    </div>

    <div class="form-group text-right">
        <?php echo Html::submitButton(Yii::t('messages', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?php echo Html::submitButton('<i class="fa fa-save"></i> '.Yii::t('messages', 'Export'), ['class' => 'btn btn-secondary',  'id' => !$isSmsStat ? 'export-button' : 'export-button-sms']) ?>

        <?php
        if(!$isSmsStat) {
            echo Html::a('<i class="fa fa-refresh"></i> ' . Yii::t('messages', 'Refresh Results'), ['campaign-users/update-statistic', 'id' => $campaignId], ['class' => 'btn btn-secondary', 'id' => 'update-statistic']);
        }
        ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
