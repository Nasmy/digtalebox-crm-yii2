<style>
    .help-block {
        margin-top: 0px !important;
        color: #bdbdbd !important;
    }

    .radio {
        margin-right: 20px;
    }

    .control-label .required {
        display: none;
    }
</style>
<?php

use app\components\MergeCustomFields;
use app\models\CustomMerge;
use app\models\CustomType;
use app\models\Keyword;
use app\models\CustomField;
use app\components\ToolKit;
use app\models\CustomValue;
use app\models\User;
use yii\widgets\ActiveForm;
use yii\helpers\Html;

$this->params['breadcrumbs'][] = Yii::t('messages', 'People');


$this->title = Yii::t('messages', 'Merge Preview');
$this->titleDescription = Yii::t('messages', 'Displaying conflict attributes. Please select the value you require to merge.');
$attributeLabels = $model->attributeLabels();
?>

<?php
$form = ActiveForm::begin([
    'id' => 'custom-merge',
    'options' => ['class' => 'form-horizontal'],
]);
?>
<div class="row no-gutters">
    <div class="content-panel col-md-12">
        <div class="content-inner">
            <div class="content-area">

                <p class="tite-desc"><?php echo Yii::t('messages', 'Displaying conflict attributes. Please select the value you require to merge.'); ?></p>
                <div class="row no-gutters mt-2">
                    <input type="hidden" name="preview" value="preview">
                    <div class="col-12"><p><?php echo Yii::t('messages', 'Child Account') ?></label> -
                            <strong><?php
                                echo $childData['firstName'] . " " . $childData['lastName'];
                                echo " ";
                                if (isset($childData['dateOfBirth']) && $childData['dateOfBirth'] != null) {
                                    echo Yii::t('messages', 'Birthday:');
                                    echo ", ";
                                    echo $childData['dateOfBirth'];
                                }

                                if (isset($childData['email']) && $childData['dateOfBirth'] != null) {
                                    echo ", ";
                                    echo Yii::t('messages', 'Email:');
                                    echo " ";
                                    echo $childData['email'];
                                }

                                if ((isset($childData['mobile'])) && ($childData['mobile'] != '')) {
                                    echo ", ";
                                    echo Yii::t('messages', 'Mobile:');
                                    echo " ";
                                    echo $childData['mobile'];
                                }
                                ?></strong></p>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <?php foreach ($parentDatas as $parentData) {
                            $parentCustomField = $parentData['customFields'];
                            $childCustomField = $childData['customFields'];
                            ?>
                            <?php
                            $dateOfBirth = (isset($parentData['dateOfBirth']) ? $parentData['dateOfBirth'] : "");
                            $parentSelectId = "#" . $parentData['id'];
                            $label = Yii::t('messages', ' I want to Merge') .' '. '<strong>' . $parentData['firstName'] . " " . $parentData['lastName'] . " " . $dateOfBirth;
                            echo $form->field($model, 'parentId')->radio(['value' => $parentData['id'], 'name' => 'parentId', 'label' => $label, 'id' => $parentSelectId, 'uncheck' => null]);
                            ?>
                            <div id="<?php echo $parentData['id'] ?>" style="display: none;" class="hideusers">
                                <!--------------EMAIL FIELD---------------->
                                <?php
                                if (!empty($parentData['email']) || !empty($childData['email'])) {
                                    if ($parentData['email'] != $childData['email']) {
                                        ?>
                                        <p><strong><?php echo Yii::t('messages', 'Email') ?></strong></p>
                                        <div class="form-row form-row-separated mb-3 mr-5">
                                            <div class="form-group">
                                                <div class="form-check form-check-inline">
                                                    <?php
                                                    if ($parentData['email'] != null) {
                                                        $label = $parentData['email'];
                                                    } else {
                                                        $label = 'N/A';
                                                    }
                                                    echo $form->field($model, 'email')->radio(['value' => $parentData['email'], 'class' => 'form-check-input custom-icheck', 'label' => $label, 'uncheck' => null]); ?>

                                                    <?php
                                                    if ($childData['email'] != null) {
                                                        $label = $childData['email'];
                                                    } else {
                                                        $label = 'N/A';
                                                    }

                                                    echo $form->field($model, 'email')->radio(['value' => $childData['email'], 'class' => 'form-check-input custom-icheck', 'label' => $label, 'uncheck' => null]); ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php }
                                } ?>

                                <!------------------MOBILE FIELD--------------------->
                                <?php
                                if (isset($parentData['mobile']) || isset($childData['mobile'])) {
                                    if ($parentData['mobile'] != $childData['mobile']) {
                                        ?>
                                        <p><strong><?php echo Yii::t('messages', 'Mobile') ?></strong></p>
                                        <div class="form-row form-row-separated mb-3 mr-5">
                                            <div class="form-group">
                                                <div class="form-check form-check-inline">
                                                    <?php
                                                    if ($parentData['mobile'] != null) {
                                                        $label = $parentData['mobile'];
                                                    } else {
                                                        $label = 'N/A';
                                                    }
                                                    echo $form->field($model, 'mobile')->radio(['value' => $parentData['mobile'], 'class' => 'form-check-input custom-icheck', 'label' => $label, 'uncheck' => null]); ?>

                                                    <?php
                                                    if ($childData['mobile'] != null) {
                                                        $label = $childData['mobile'];
                                                    } else {
                                                        $label = 'N/A';
                                                    }

                                                    echo $form->field($model, 'mobile')->radio(['value' => $childData['mobile'], 'class' => 'form-check-input custom-icheck', 'label' => $label, 'uncheck' => null]); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                }
                                ?>

                                <!------------------ZIP FIELD--------------------->
                                <?php
                                if (isset($parentData['zip']) || isset($childData['zip'])) {
                                    if ($parentData['zip'] != $childData['zip']) {
                                        ?>
                                        <p><strong><?php echo Yii::t('messages', 'Zip code') ?></strong></p>
                                        <div class="form-row form-row-separated mb-3 mr-5">
                                            <div class="form-group">
                                                <div class="form-check form-check-inline">
                                                    <?php
                                                    if ($parentData['zip'] != null) {
                                                        $label = $parentData['zip'];
                                                    } else {
                                                        $label = 'N/A';
                                                    }
                                                    echo $form->field($model, 'zip')->radio(['value' => $parentData['zip'], 'class' => 'form-check-input custom-icheck', 'label' => $label, 'uncheck' => null]); ?>

                                                    <?php
                                                    if ($childData['zip'] != null) {
                                                        $label = $childData['zip'];
                                                    } else {
                                                        $label = 'N/A';
                                                    }

                                                    echo $form->field($model, 'zip')->radio(['value' => $childData['zip'], 'class' => 'form-check-input custom-icheck', 'label' => $label, 'uncheck' => null]); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                }
                                ?>

                                <!------------------CITY FIELD--------------------->
                                <?php
                                if (isset($parentData['city']) || isset($childData['city'])) {
                                    if ($parentData['city'] != $childData['city']) {
                                        ?>
                                        <p><strong><?php echo Yii::t('messages', 'City') ?></strong></p>
                                        <div class="form-row form-row-separated mb-3 mr-5">
                                            <div class="form-group">
                                                <div class="form-check form-check-inline">
                                                    <?php
                                                    if ($parentData['city'] != null) {
                                                        $label = $parentData['city'];
                                                    } else {
                                                        $label = 'N/A';
                                                    }
                                                    echo $form->field($model, 'city')->radio(['value' => $parentData['city'], 'class' => 'form-check-input custom-icheck', 'label' => $label, 'uncheck' => null]); ?>

                                                    <?php
                                                    if ($childData['city'] != null) {
                                                        $label = $childData['city'];
                                                    } else {
                                                        $label = 'N/A';
                                                    }

                                                    echo $form->field($model, 'city')->radio(['value' => $childData['city'], 'class' => 'form-check-input custom-icheck', 'label' => $label, 'uncheck' => null]); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                }
                                ?>

                                <!------------------Country Code--------------------->
                                <?php
                                if (isset($parentData['countryCode']) || isset($childData['countryCode'])) {
                                    if ($parentData['countryCode'] != $childData['countryCode']) {
                                        ?>
                                        <p><strong><?php echo Yii::t('messages', 'Country') ?></strong></p>
                                        <div class="form-row form-row-separated mb-3 mr-5">
                                            <div class="form-group">
                                                <div class="form-check form-check-inline">
                                                    <?php
                                                    if ($parentData['countryCode'] != null) {
                                                        $label = $parentData['countryCode'];
                                                    } else {
                                                        $label = 'N/A';
                                                    }
                                                    echo $form->field($model, 'countryCode')->radio(['value' => $parentData['countryCode'], 'class' => 'form-check-input custom-icheck', 'label' => $label, 'uncheck' => null]); ?>

                                                    <?php
                                                    if ($childData['countryCode'] != null) {
                                                        $label = $childData['countryCode'];
                                                    } else {
                                                        $label = 'N/A';
                                                    }

                                                    echo $form->field($model, 'countryCode')->radio(['value' => $childData['countryCode'], 'class' => 'form-check-input custom-icheck', 'label' => $label, 'uncheck' => null]); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                }
                                ?>

                                <!------------------Gender Field--------------------->
                                <?php
                                if (isset($parentData['gender']) || isset($childData['gender'])) {
                                    if ($parentData['gender'] != $childData['gender']) {
                                        ?>
                                        <p><strong><?php echo Yii::t('messages', 'Gender') ?></strong></p>
                                        <div class="form-row form-row-separated mb-3 mr-5">
                                            <div class="form-group">
                                                <div class="form-check form-check-inline">
                                                    <?php
                                                    if ($parentData['gender'] != null) {
                                                        $label = User::getGenderLabel($parentData['gender'], 4);
                                                    } else {
                                                        $label = 'N/A';
                                                    }
                                                    echo $form->field($model, 'gender')->radio(['value' => $parentData['gender'], 'class' => 'form-check-input custom-icheck', 'label' => $label, 'uncheck' => null]); ?>

                                                    <?php
                                                    if ($childData['gender'] != null) {
                                                        $label = User::getGenderLabel($childData['gender'],4);
                                                    } else {
                                                        $label = 'N/A';
                                                    }

                                                    echo $form->field($model, 'gender')->radio(['value' => $childData['gender'], 'class' => 'form-check-input custom-icheck', 'label' => $label, 'uncheck' => null]); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                }
                                ?>

                                <!------------------Address1 Field--------------------->
                                <?php
                                if (isset($parentData['address1']) || isset($childData['address1'])) {
                                    if ($parentData['address1'] != $childData['address1']) {
                                        ?>
                                        <p><strong><?php echo Yii::t('messages', 'Address') ?></strong></p>
                                        <div class="form-row form-row-separated mb-3 mr-5">
                                            <div class="form-group">
                                                <div class="form-check form-check-inline">
                                                    <?php
                                                    if ($parentData['address1'] != null) {
                                                        $label = $parentData['address1'];
                                                    } else {
                                                        $label = 'N/A';
                                                    }
                                                    echo $form->field($model, 'address1')->radio(['value' => $parentData['address1'], 'class' => 'form-check-input custom-icheck', 'label' => $label, 'uncheck' => null]); ?>

                                                    <?php
                                                    if ($childData['address1'] != null) {
                                                        $label = $childData['address1'];
                                                    } else {
                                                        $label = 'N/A';
                                                    }

                                                    echo $form->field($model, 'address1')->radio(['value' => $childData['address1'], 'class' => 'form-check-input custom-icheck', 'label' => $label, 'uncheck' => null]); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                }
                                ?>

                                <!------------------Notes Field--------------------->
                                <?php
                                if (isset($parentData['notes']) || isset($childData['notes'])) {
                                    if ($parentData['notes'] != $childData['notes']) {
                                        $parentNote = $parentData['notes'];
                                        $childNote = $childData['notes'];
                                        $parentChildNote = CustomMerge::combineNotes($parentNote, $childNote);
                                        ?>
                                        <p><strong><?php echo Yii::t('messages', 'Notes') ?></strong></p>
                                        <div class="form-row form-row-separated mb-3 mr-5">
                                            <div class="form-group">
                                                <div class="form-check form-check-inline">
                                                    <?php
                                                    $isNANote = false;
                                                    if ($parentNote != null) {
                                                        $label = $parentNote;
                                                    } else {
                                                        $label = 'N/A';
                                                        $isNANote = true;
                                                    }

                                                    echo $form->field($model, 'notes')->radio(['value' => $parentNote, 'class' => 'form-check-input custom-icheck', 'label' => $label, 'uncheck' => null]);

                                                    if ($childNote != null) {
                                                        $label = $childNote;
                                                    } else {
                                                        $label = 'N/A';
                                                        $isNANote = true;
                                                    }

                                                    echo $form->field($model, 'notes')->radio(['value' => $childNote, 'class' => 'form-check-input custom-icheck', 'label' => $label, 'uncheck' => null]);

                                                    if ($parentChildNote != null && !$isNANote) {
                                                        $label = $parentChildNote;
                                                        echo $form->field($model, 'notes')->radio(['value' => $parentChildNote, 'class' => 'form-check-input custom-icheck', 'label' => $label, 'uncheck' => null]);
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                }
                                ?>
                                <!------------------Dob------------------------------------->
                                <?php
                                if (!empty($parentData['dateOfBirth']) || !empty($childData['dateOfBirth'])) {
                                    if ($parentData['dateOfBirth'] != $childData['dateOfBirth']) {
                                        ?>
                                        <p><strong><?php echo Yii::t('messages', 'Birthday') ?></strong></p>
                                        <div class="form-row form-row-separated mb-3 mr-5">
                                            <div class="form-group">
                                                <div class="form-check form-check-inline">
                                                    <?php
                                                    if ($parentData['dateOfBirth'] != null) {
                                                        $label = $parentData['dateOfBirth'];
                                                    } else {
                                                        $label = 'N/A';
                                                    }
                                                    echo $form->field($model, 'dateOfBirth')->radio(['value' => $parentData['dateOfBirth'], 'class' => 'form-check-input custom-icheck', 'label' => $label, 'uncheck' => null]); ?>

                                                    <?php
                                                    if ($childData['dateOfBirth'] != null) {
                                                        $label = $childData['dateOfBirth'];
                                                    } else {
                                                        $label = 'N/A';
                                                    }

                                                    echo $form->field($model, 'dateOfBirth')->radio(['value' => $childData['dateOfBirth'], 'class' => 'form-check-input custom-icheck', 'label' => $label, 'uncheck' => null]); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                }
                                ?>
                                <!------------------Keywords Field--------------------->
                                <?php
                                if (!isset($parentData['keywords']) || isset($childData['keywords'])) {
                                    if ($parentData['keywords'] != $childData['keywords']) {
                                        $parentKeyword = explode(',', $parentData['keywords']);
                                        $childKeyword = explode(',', $childData['keywords']);
                                        $parentChildMerge = CustomMerge::combineKeywords($parentKeyword, $childKeyword);

                                        // get label as parent , child And Parent child combinations
                                        $parentKeywordLabel = Keyword::getKeywordLabelList($parentKeyword);
                                        $childKeywordLabel = Keyword::getKeywordLabelList($childKeyword);
                                        $parentChildLabel = Keyword::getKeywordLabelList($parentChildMerge);
                                        ?>
                                        <p><strong><?php echo Yii::t('messages', 'Keywords') ?></strong></p>
                                        <div class="form-row form-row-separated mb-3 mr-5">
                                            <div class="form-group">
                                                <div class="form-check form-check-inline">
                                                    <?php
                                                    $isNA = false;
                                                    if ($parentData['keywords'] != null) {
                                                        $label = $parentKeywordLabel;
                                                    } else {
                                                        $label = 'N/A';
                                                        $isNA = true;
                                                    }
                                                    echo $form->field($model, 'keywords')->radio(['value' => $parentData['keywords'], 'class' => 'form-check-input custom-icheck', 'label' => $label, 'uncheck' => null]); ?>

                                                    <?php
                                                    if ($childData['keywords'] != null) {
                                                        $label = $childKeywordLabel;
                                                    } else {
                                                        $label = 'N/A';
                                                        $isNA = true;
                                                    }

                                                    echo $form->field($model, 'keywords')->radio(['value' => $childData['keywords'], 'class' => 'form-check-input custom-icheck', 'label' => $label, 'uncheck' => null]); ?>

                                                    <?php
                                                    if ($parentChildMerge != null && !$isNA) {
                                                        $label = $parentChildLabel;
                                                        echo $form->field($model, 'keywords')->radio(['value' => implode(',', $parentChildMerge), 'class' => 'form-check-input custom-icheck', 'label' => $label, 'uncheck' => null]);
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                }
                                ?>

                                <!-----------------Custom Field------------------->
                                <?php
                                MergeCustomFields::previewCustomField($parentCustomField, $childCustomField);
                                ?>

                            </div>
                        <?php } ?>
                        <div class="form-group">
                            <?= Html::submitButton(Yii::t('messages', 'Save'), ['class' => 'btn btn-success']) ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>
<script>

    $("input[name=\"parentId\"]").click(function () {
        $('.custom-icheck').prop('checked', false);
        $('.hideusers').attr("style", "display: none;");
        $("input:checked").each(function () {
            var ids = "#" + $(this).val();
            console.log(ids);
            $(ids).attr("style", "display: block;");
        });
    });
</script>