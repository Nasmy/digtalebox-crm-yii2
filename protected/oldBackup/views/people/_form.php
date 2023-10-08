<?php

use app\models\Country;
use app\models\User;
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use app\components\SCustomFields;
use yii\jui\DatePicker;
use yii\helpers\file;
use kartik\select2\Select2;

?>
<script type="text/javascript" language="javascript">
    var customFieldList = new Array();
    <?php if(isset($customFields)) {
    foreach($customFields as $k => $customField){ ?>
    customFieldList.push('<?php echo $customField->customFieldId; ?>');
    <?php }
    }
    ?>
</script>

<div>
    <div class="row no-gutters">
        <div class="content-panel col-md-12">
            <div class="content-inner">
                <div class="panel-head"><?php
                    Yii::t('messages', 'Person Details') ?></div>
                <div class="content-area">
                    <?php
                    $form = ActiveForm::begin([
                        'options' => ['class' => 'form-horizontal', 'enableAjaxValidation' => false,'validateOnChange' => false],
                        'id' => 'user-form'
                    ])
                    ?>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="fname"><?php echo $attributeLabels['firstName']; ?></label>
                            <?php echo $form->field($model, 'firstName')->textInput(['class' => 'form-control', 'id' => 'firstName', 'size' => 45, 'maxlength' => 45, 'placeholder' => Yii::t('messages', 'First Name')])->label(false); ?>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="lname"><?php echo $attributeLabels['lastName']; ?></label>
                            <?php echo $form->field($model, 'lastName')->textInput(['class' => 'form-control', 'size' => 45, 'maxlength' => 45, 'id' => 'lastName', 'placeholder' => Yii::t('messages', 'Last Name')])->label(false); ?>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="email"><?php echo $attributeLabels['email']; ?></label>
                            <?php echo $form->field($model, 'email')->textInput(['class' => 'form-control', 'placeholder' => 'abc@digitalebox.com'])->label(false); ?>
                        </div>
                        <div class="form-group col-md-6">
                            <div class="control-group" id="internationalMobileNo">
                                <label for="mobile"><?php echo $attributeLabels['mobile']; ?></label>
                                <?php echo $form->field($model, 'mobile')->textInput(['class' => 'form-control', 'id' => 'mobile-inputs', 'maxlength' => 15, 'placeholder' => '+330000000000'])->label(false); ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="zip"><?php echo $attributeLabels['zip']; ?></label>
                            <?php
                            echo $form->field($model, 'zip')->textInput(['class' => 'form-control', 'maxlength' => 15, 'placeholder' => 'ex: 14390'])->label(false); ?>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="address1"><?php echo $attributeLabels['address1']; ?></label>
                            <?php echo $form->field($model, 'address1')->textInput(['class' => 'form-control', 'maxlength' => 45, 'placeholder' => 'ex: 27 Avenue Pasteur'])->label(false); ?>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="gender"><?php echo $attributeLabels['gender']; ?></label>
                            <?php
                            echo $form->field($model, 'gender')->dropdownList([
                                User::MALE => Yii::t('messages', 'Male'),
                                User::FEMALE => Yii::t('messages', 'Female'),
                                User::ASEXUAL => Yii::t('messages', 'Other'),
                            ],
                                ['prompt' => '--- Select Gender ---']
                            )->label(false); ?>

                        </div>
                        <div class="form-group col-md-6">
                            <label for="userType"><?php echo $attributeLabels['userType']; ?></label>
                            <?php
                            echo $form->field($model, 'userType')->dropdownList(User::getUserTypes(),
                                ['class' => 'form-control', 'prompt' => Yii::t('messages', '--- Select Category ---')]
                            )->label(false); ?>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="city"><?php echo $attributeLabels['city']; ?></label>
                            <?php echo $form->field($model, 'city')->textInput(
                                ['class' => 'form-control', 'maxlength' => 50, 'placeholder' => 'ex: Cabourg']
                            )->label(false); ?>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="category"><?php echo $attributeLabels['countryCode']; ?></label>
                            <?php
                            echo $form->field($model, 'countryCode')->dropdownList(Country::getCountryDropdown()
                            )->label(false); ?>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="control-label"
                                   for="User_gender"><?php echo $attributeLabels['dateOfBirth']; ?></label>
                            <div class="controls">
                                <?= DatePicker::widget([
                                    'model' => $model,
                                    'attribute' => 'dateOfBirth',
                                    'dateFormat' => 'yyyy-MM-dd',
                                    'language' => '',
                                    'clientOptions' => [
                                        'format' => 'yyyy-mm-dd',
                                        'maxDate' => 'js:new Date(' . date('Y-10,m,d,H,i') . ')',
                                        'changeYear' => true,
                                        'yearRange' => "1900:" . date("Y"),
                                        'type' => 'date',
                                    ],
                                    'options' => array('readonly' => true, 'placeholder' => 'YYYY-MM-DD',
                                        'class' => 'form-control datetimepicker-input'),

                                ]) ?>

                            </div>
                        </div>
                        <div class="form-group col-md-6">
                            <div class="controls">
                                <?php
                                echo $form->field($model, 'keywords')->widget(Select2::className(), [
                                    'name' => 'keywords',
                                    'data' => $keywords,
                                    'size' => Select2::MEDIUM,
                                    'options' => [
                                        'placeholder' => Yii::t('messages', 'Select a Keyword'),
                                        'class' => 'form-control form-control-selectize',
                                        'multiple' => true

                                    ],

                                ]);
                                ?>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="User_notes"><?php echo $attributeLabels['notes']; ?></label>
                            <?= $form->field($model, 'notes')->textarea(['rows' => '2', 'class' => 'form-control'])->label(false); ?>
                        </div>


                        <?php
                        echo SCustomFields::widget(['customFields' => $customFields,
                            'template' => '<div class="form-group col-md-6">{label}{input}</div>',
                            'hideLabel' => false,
                            'enableAjaxValidation' => false,
                            'rowClass' => 'form-control']);
                        ?>
                    </div>

                    <div class="text-left text-md-right">
                        <input type="hidden" name="ajax" value="user-form">
                        <?php
                        echo Html::submitButton($model->isNewRecord ? Yii::t('messages', 'Create') : Yii::t
                        ('messages', 'Save'), ['class' => 'btn btn-primary']);
                        ?>

                        <?php
                        if ($model->isNewRecord) {
                            echo Html::a(Yii::t('messages', 'Cancel'), $closeUrl, ['class' => 'btn btn-secondary']);
                        } ?>
                    </div>
                    <?php ActiveForm::end(); ?>

                </div>
            </div>
        </div>
    </div>
</div>


