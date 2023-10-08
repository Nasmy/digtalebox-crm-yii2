<?php

use app\models\AuthItem;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\AuthItem */
/* @var $form yii\widgets\ActiveForm */
?>

<div>
    <div class="row no-gutters">
        <div class="content-panel col-md-12">
            <div class="content-inner">
                <div class="content-area">
                    <div class="row">
                        <div class="col-md-12">
                            <?php $form = ActiveForm::begin(); ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="AuthItem_name"><?php echo $attributeLabels['name']; ?></label>
                                        <?php
                                        if($model->isNewRecord) {
                                            echo $form->field($model, 'name')->textInput(['maxlength' => true])->label(false);
                                        } else {
                                            echo $form->field($model, 'name')->textInput(['maxlength' => true, 'readonly'=>true])->label(false);
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="AuthItem_category"><?php echo $attributeLabels['category']; ?></label>
                                        <?= $form->field($model, 'category')->textInput(['rows' => 6])->label(false) ?>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="AuthItem_fee"><?php echo $attributeLabels['description']; ?></label>
                                        <?= $form->field($model, 'description')->textInput(['rows' => 6])->label(false)?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="AuthItem_fee"><?php echo $attributeLabels['child']; ?></label>
                                        <?= $form->field($model, 'child')->dropDownList(AuthItem::getItemOptions(AuthItem::TYPE_OPERATION),['class' => 'form-control', 'label' => false, 'prompt'=>Yii::t('messages','--- Select Child ---')])->label(false)?>
                                    </div>
                                </div>
                            </div>

                            <div class="text-left text-md-right">
                                <input type="hidden" name="ajax" value="user-form">
                                <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
                                <?= Html::a(Yii::t('messages', 'Cancel'),Yii::$app->urlManager->createUrl(['auth-item/admin','type'=>AuthItem::TYPE_OPERATION]), ['class' => 'btn btn-secondary']) ?>
                            </div>
                             <?php ActiveForm::end(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
