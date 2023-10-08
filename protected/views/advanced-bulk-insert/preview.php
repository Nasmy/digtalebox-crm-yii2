<style>
    .custom-file > .error {
        display: none;
    }
</style>
<?php

use app\components\SCustomFields;
use app\models\AdvanceBulkInsert;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'People'), 'url' => ['admin']];
$this->params['breadcrumbs'][] = Yii::t('messages', 'Advanced Bulk Insert');

$this->title = Yii::t('messages', 'File Preview');
$this->titleDescription = Yii::t('messages', 'Preview File for Mapping');
?>

<div class="row no-gutters">
    <div class="content-panel col-md-12">
        <div class="content-inner">
            <div class="content-area">
                <?php
                $form = ActiveForm::begin([
                    'id' => 'user-form',
                    'options' => ['class' => 'table-wrap'],
                ]);
                ?>
                <table class="table table-hover table-mapping">
                    <thead>
                    <tr>
                        <th scope="col"><?php echo Yii::t('messages', 'Field From File') ?></th>
                        <th width="100" class="text-center"><i class="fa fa-long-arrow-right mapping-arrow fa-lg"></i>
                        </th>
                        <th scope="col"><?php echo Yii::t('messages', 'Map to') ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td><?php echo $form->field($model, "firstName")->dropDownList($data, array('class' => 'form-control mapping-field'))->label(false); ?></td>
                        <td class="text-center"><i class="fa fa-long-arrow-right mapping-arrow fa-lg mt-3"></i></td>
                        <td>
                            <div class="mt-2 font14"><?php echo Html::activeLabel($model, 'firstName', array('class' => 'control-label', 'required' => false)) ?>
                                <span class="form-feild-info"> (<?php echo Yii::t('messages', 'Ex: John'); ?>)</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo $form->field($model, "lastName")->dropDownList($data, array('class' => 'form-control mapping-field'))->label(false); ?></td>
                        <td class="text-center"><i class="fa fa-long-arrow-right mapping-arrow fa-lg mt-3"></i></td>
                        <td>
                            <div class="mt-2 font14"><?php echo Html::activeLabel($model, 'lastName', array('class' => 'control-label', 'required' => false)) ?>
                                <span
                                        class="form-feild-info"> (<?php echo Yii::t('messages', 'Ex: Smith'); ?>)</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo $form->field($model, "email")->dropDownList($data, array('class' => 'form-control mapping-field'))->label(false); ?></td>
                        <td class="text-center"><i class="fa fa-long-arrow-right mapping-arrow fa-lg mt-3"></i></td>
                        <td>
                            <div class="mt-2 font14"><?php echo Html::activeLabel($model, 'email', array('class' => 'control-label', 'required' => false)) ?>
                                <span class="form-feild-info"> (<?php echo Yii::t('messages', 'Ex: john_smith@gmail.com'); ?>
                                    )</span></div>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo $form->field($model, "mobile")->dropDownList($data, array('class' => 'form-control mapping-field'))->label(false); ?></td>
                        <td class="text-center"><i class="fa fa-long-arrow-right mapping-arrow fa-lg mt-3"></i></td>
                        <td>
                            <div class="mt-2 font14"><?php echo Html::activeLabel($model, 'mobile', array('class' => 'control-label', 'required' => false)) ?>
                                <span class="form-feild-info"> (<?php echo Yii::t('messages', 'Ex: 00 000 0000'); ?>
                                    )</span></div>
                        </td>
                    </tr>

                    <tr>
                        <td><?php echo $form->field($model, "address1")->dropDownList($data, array('class' => 'form-control mapping-field'))->label(false); ?></td>
                        <td class="text-center"><i class="fa fa-long-arrow-right mapping-arrow fa-lg mt-3"></i></td>
                        <td>
                            <div class="mt-2 font14"><?php echo Html::activeLabel($model, 'address1', array('class' => 'control-label', 'required' => false)) ?>
                                <span class="form-feild-info"> (<?php echo Yii::t('messages', 'Ex: Main road, down town'); ?>
                                    )</span></div>
                        </td>
                    </tr>

                    <tr>
                        <td><?php echo $form->field($model, "zip")->dropDownList($data, array('class' => 'form-control mapping-field'))->label(false); ?></td>
                        <td class="text-center"><i class="fa fa-long-arrow-right mapping-arrow fa-lg mt-3"></i></td>
                        <td>
                            <div class="mt-2 font14"><?php echo Html::activeLabel($model, 'zip', array('class' => 'control-label', 'required' => false)) ?>
                                <span class="form-feild-info"> (<?php echo Yii::t('messages', 'Ex: 0000'); ?>)</span>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td><?php echo $form->field($model, "city")->dropDownList($data, array('class' => 'form-control mapping-field'))->label(false); ?></td>
                        <td class="text-center"><i class="fa fa-long-arrow-right mapping-arrow fa-lg mt-3"></i></td>
                        <td>
                            <div class="mt-2 font14"><?php echo Html::activeLabel($model, 'city', array('class' => 'control-label', 'required' => false)) ?>
                                <span class="form-feild-info"> (<?php echo Yii::t('messages', 'Ex: Paris'); ?>)</span>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td><?php echo $form->field($model, "countryCode")->dropDownList($data, array('class' => 'form-control mapping-field'))->label(false); ?></td>
                        <td class="text-center"><i class="fa fa-long-arrow-right mapping-arrow fa-lg mt-3"></i></td>
                        <td>
                            <div class="mt-2 font14"><?php echo Html::activeLabel($model, 'countryCode', array('class' => 'control-label', 'required' => false)) ?>
                                <span class="form-feild-info"> (<?php echo Yii::t('messages', 'Ex: France'); ?>)</span>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td><?php echo $form->field($model, "gender")->dropDownList($data, array('class' => 'form-control mapping-field'))->label(false); ?></td>
                        <td class="text-center"><i class="fa fa-long-arrow-right mapping-arrow fa-lg mt-3"></i></td>
                        <td>
                            <div class="mt-2 font14"><?php echo Html::activeLabel($model, 'gender', array('class' => 'control-label', 'required' => false)) ?>
                                <span class="form-feild-info"> (<?php echo Yii::t('messages', 'Ex: Male'); ?>)</span>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td><?php echo $form->field($model, "dateOfBirth")->dropDownList($data, array('class' => 'form-control mapping-field'))->label(false); ?></td>
                        <td class="text-center"><i class="fa fa-long-arrow-right mapping-arrow fa-lg mt-3"></i></td>
                        <td>
                            <div class="mt-2 font14"><?php echo Html::activeLabel($model, 'dateOfBirth', array('class' => 'control-label', 'required' => false)) ?>
                                <span class="form-feild-info"> (<?php echo Yii::t('messages', 'Ex: 1990-12-31'); ?>
                                    )</span></div>
                        </td>
                    </tr>

                    <tr>
                        <td><?php echo $form->field($model, "userType")->dropDownList($data, array('class' => 'form-control mapping-field'))->label(false); ?></td>
                        <td class="text-center"><i class="fa fa-long-arrow-right mapping-arrow fa-lg mt-3"></i></td>
                        <td>
                            <div class="mt-2 font14"><?php echo Html::activeLabel($model, 'userType', array('class' => 'control-label', 'required' => false)) ?>
                                <span class="form-feild-info"> (<?php echo Yii::t('messages', 'Ex: Supporter'); ?>
                                    )</span></div>
                        </td>
                    </tr>

                    <tr>
                        <td><?php echo $form->field($model, "notes")->dropDownList($data, array('class' => 'form-control mapping-field'))->label(false); ?></td>
                        <td class="text-center"><i class="fa fa-long-arrow-right mapping-arrow fa-lg mt-3"></i></td>
                        <td>
                            <div class="mt-2 font14"><?php echo Html::activeLabel($model, 'notes', array('class' => 'control-label', 'required' => false)) ?>
                                <span class="form-feild-info"> (<?php echo Yii::t('messages', 'Ex: Sample note'); ?>
                                    )</span></div>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo $form->field($model, "keywords")->dropDownList($data, array('class' => 'form-control mapping-field'))->label(false); ?></td>
                        <td class="text-center"><i class="fa fa-long-arrow-right mapping-arrow fa-lg mt-3"></i></td>
                        <td>
                            <div class="mt-2 font14"><?php echo Html::activeLabel($model, 'keywords', array('class' => 'control-label', 'required' => false)) ?>
                                <span class="form-feild-info"> (<?php echo Yii::t('messages', 'Ex: Electeurs,AMY,6006,C-THOUROTTE'); ?>
                                    )</span></div>
                        </td>
                    </tr>

                    <?php
                    echo SCustomFields::widget(['customFields' => $customFields,
                        'isBulkPreview' => true,
                        'previewData' => $data,
                        'template' => '{input}{label}']);
                    ?>
                    </tbody>
                </table>
                <div class="row no-gutters">
                    <div class="col-md-6">
                    </div>
                    <div class="col-md-6">
                        <div class="text-left text-md-right">
                            <?php
                            echo Html::submitButton(Yii::t('messages', 'Save'), ['class' => 'btn btn-primary']);
                            ?>

                            <?php
                            echo Html::a(Yii::t('messages', 'Cancel'), Yii::$app->urlManager->createUrl('advanced-bulk-insert/admin'), ['class' => 'btn btn-secondary']);
                            ?>
                        </div>
                    </div>
                </div>
                <?php
                ActiveForm::end();
                ?>
            </div>
        </div>
    </div>
</div>
