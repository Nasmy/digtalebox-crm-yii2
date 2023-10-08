<?php

use app\components\WebUser;
use app\models\AuthItem;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$regionalAdmin = WebUser::REGIONAL_ADMIN_NAME;
$this->registerJs("
$('#user-role').on('change', function() {
    showZipField();
});

function showZipField() {
    if ($('#user-role').val() == '{$regionalAdmin}') {
        $('#user-zip').parents('.col-md-6').show();
    } else {
        $('#user-zip').parents('.col-md-6').hide();
    }
}

showZipField();
");

/* @var $this yii\web\View */
/* @var $model app\models\User */
/* @var $form yii\widgets\ActiveForm */
?>

<div>
    <div class="row no-gutters">
        <div class="content-panel col-md-12">
            <div class="content-inner">
                <div class="content-area">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="content-panel-sub">
                                <div class="panel-head"><?php Yii::t('messages', 'User Details') ?></div>
                            </div>
                            <?php $form = ActiveForm::begin([
                                'options' => [
                                    'class' => 'form-horizontal',
                                    'method' => 'post',
                                    'enableAjaxValidation' => true,
                                    'validateOnSubmit' => true,
                                ],
                            ]); ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="FormMembershipType_email"><?php echo $attributeLabels['firstName']; ?></label>
                                        <?php echo $form->field($model, 'firstName')->textInput(['class' => 'form-control', 'size' => 45, 'maxlength' => 45, 'placeholder' => 'First Name'])->label(false); ?>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="FormMembershipType_email"><?php echo $attributeLabels['lastName']; ?></label>
                                        <?php echo $form->field($model, 'lastName')->textInput(['class' => 'form-control', 'size' => 45, 'maxlength' => 45, 'placeholder' => 'Last Name'])->label(false); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="FormMembershipType_email"><?php echo $attributeLabels['email']; ?></label>
                                        <?php echo $form->field($model, 'email')->textInput(['class' => 'form-control', 'size' => 45, 'maxlength' => 45, 'placeholder' => 'email'])->label(false); ?>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <?php
                                        $selectedRole = null;
                                        if(isset($currentRole)){
                                            $selectedRole = $currentRole;
                                        }
                                        echo $form->field($model, 'role')
                                            ->dropDownList($roles,
                                                ['options' => [$selectedRole => ['Selected' => 'selected']],     'prompt'=>Yii::t('messages','-- Select a Role --')]
                                            );
                                        ?>

                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="FormMembershipType_email"><?php echo $attributeLabels['username']; ?></label>
                                        <?php echo $form->field($model, 'username')->textInput(['class' => 'form-control', 'size' => 45, 'maxlength' => 45, 'placeholder' => 'User Name'])->label(false); ?>

                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="FormMembershipType_password"><?php echo $attributeLabels['password']; ?></label>
                                        <?= $form->field($model, 'password')->passwordInput(['class' => 'form-control', 'placeholder' => 'Password'])->label(false); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="FormMembershipType_confPassword"><?php echo $attributeLabels['confPassword']; ?></label>
                                        <?= $form->field($model, 'confPassword')->passwordInput(['class' => 'form-control', 'placeholder' => 'Confirm New Password'])->label(false); ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="FormMembershipType_zip"><?php echo $attributeLabels['zip']; ?></label>
                                        <?php echo $form->field($model, 'zip')->textInput(['class' => 'form-control', 'size' => 45, 'maxlength' => 45])->label(false); ?>
                                    </div>
                                </div>

                            </div>
                            <div class="form-group">
                                <?= Html::submitButton(Yii::t('messages', 'Save'), ['class' => 'btn btn-success']) ?>
                            </div>

                            <?php ActiveForm::end(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
