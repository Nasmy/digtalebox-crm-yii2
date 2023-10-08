<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;
$attributeLabels = $model->attributeLabels();
$this->title = Yii::t('messages', 'Change Password');
$this->titleDescription = Yii::t('messages', 'Change your account password');
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Dashboard'), 'url' => ['dashboard/dashboard']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Change Password')];

$this->registerCss("
.form-group {
     width: 100%;
}
.form-group input {
    width: 90%;
    float: left;
}

");

?>
<div>
    <div class="row no-gutters">
        <div class="content-panel col-md-12">
            <div class="content-inner">
                <!--<div class="panel-head">Enter New Password</div>-->
                <div class="content-area pt-4">

                    <?php
                    $form = ActiveForm::begin([
                        'id' => 'change-password-form',
                        'options' => ['class' => 'form-horizontal user-form'],
                        'validateOnSubmit' => true,
                        'validateOnType' => true,
//                        'action' => Yii::$app->urlManager->createUrl('/user/change-password'),
//                        'method'=>'post',
                        'fieldConfig' => [
                            'template' => "{input} <div class=\"input-group-append\">
                                       <span class=\"input-group-text\"><a href=\"\">
                                          <i class=\"fa fa-eye-slash\" aria-hidden=\"true\"></i></a>
                                       </span>
                                    </div>\n<div class=\"error-col\">{error}</div>",
                            'labelOptions' => ['class' => 'm-0 control-label  pwd'],

                        ],
//                        'enableAjaxValidation' => true,
                    ]) ?>

                    <div class="form-row">
                        <div class="form-group col-md-6 show_hide_password">
                            <label for="opass"> <?php echo $attributeLabels['oldPassword']; ?></label>
                            <div class="input-group">

                                <?php echo $form->field($model, 'oldPassword')
                                    ->passwordInput(['class' => 'form-control', 'maxlength' => 45, 'placeholder'=> $attributeLabels['oldPassword']])
                                    ->label(false);
                                ?>
                            </div>
                         </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6 show_hide_password">
                            <label for="npass"><?php echo $attributeLabels['password']; ?></label>
                            <div class="input-group">
                                <?php echo $form->field($model, 'password')
                                    ->passwordInput(['class' => 'form-control', 'maxlength' => 45, 'placeholder'=> $attributeLabels['password']])
                                    ->label(false);
                                ?>
                            </div>
                         </div>
                        <div class="form-group col-md-6 show_hide_password">
                            <label for="cpass"><?php echo $attributeLabels['confPassword']; ?></label>
                            <div class="input-group">
                                <?php echo $form->field($model, 'confPassword')
                                    ->passwordInput(['class' => 'form-control', 'maxlength' => 45, 'placeholder'=> $attributeLabels['confPassword']])
                                    ->label(false);
                                ?>
                            </div>
                         </div>
                    </div>


                    <div class="text-left text-md-right">
                        <?= Html::submitButton('Submit', ['class'=> 'btn btn-primary']); ?>
                    </div>
                    <?php ActiveForm::end(); ?>

                </div>
            </div>
        </div>
    </div>
</div>