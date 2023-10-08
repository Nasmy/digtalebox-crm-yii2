<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Form;

/* @var $this yii\web\View */
/* @var $model app\models\FormMembershipDonationSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="form-membership-donation-search">

    <?php $form = ActiveForm::begin([
//        'id' =>'search-form',
        'action' => ['form-membership-donation/admin'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <div class="form-row">
        <div class="form-group m-0 col-md-4">
            <?= $form->field($model, 'firstName')->textInput([
                'class' => 'form-control ',
                'maxlength' => 45,
                'placeholder' => Yii::t('messages', 'First Name'),
                'value' => isset($_GET['FormMembershipDonationSearch']['firstName']) ? $_GET['FormMembershipDonationSearch']['firstName'] : ''
            ])->label(false); ?>
        </div>
        <div class="form-group m-0 col-md-4">
            <?= $form->field($model, 'lastName')->textInput([
                'class' => 'form-control ',
                'maxlength' => 45,
                'placeholder' => Yii::t('messages', 'Last Name'),
                'value' => isset($_GET['FormMembershipDonationSearch']['lastName']) ? $_GET['FormMembershipDonationSearch']['lastName'] : ''
            ])->label(false); ?>
        </div>
        <div class="form-group m-0 col-md-4">
            <?= $form->field($model, 'payerEmail')->textInput([
                'class' => 'form-control ',
                'maxlength' => 127,
                'placeholder' => Yii::t('messages', 'Payer Email'),
                'value' => isset($_GET['FormMembershipDonationSearch']['payerEmail']) ? $_GET['FormMembershipDonationSearch']['payerEmail'] : ''
            ])->label(false); ?>
        </div>
        <div class="form-group  col-md-4">
            <?= $form->field($model, 'memberType')->dropDownList(Form::getPaymentTypes(), [
                'class' => 'form-control',
                'style' => 'margin-top:1px;',
                'prompt' => Yii::t('messages', '- Membership -'),
                'options' => [isset($_GET['FormMembershipDonationSearch']['memberType']) ? $_GET['FormMembershipDonationSearch']['memberType'] : '' => ['selected' => true]],
            ])->label(false);
            ?>
        </div>
        <div class="form-group col-md-4">
            <?= $form->field($model, 'memberDonationType')->dropDownList(Form::getMemberDonationTypes(), [
                'class' => 'form-control',
                'style' => 'margin-top:1px;',
                'prompt' => Yii::t('messages', '- Donation / Membership -'),
                'options' => [isset($_GET['FormMembershipDonationSearch']['memberDonationType']) ? $_GET['FormMembershipDonationSearch']['memberDonationType'] : '' => ['selected' => true]],
            ])->label(false);
            ?>
        </div>
    </div>
    <div class="form-row text-left text-md-right">
        <div class="form-group col-md-12">
            <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
