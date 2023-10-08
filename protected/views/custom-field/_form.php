<?php
//Set default type
use app\models\CustomType;
use yii\helpers\Html;
use yii\helpers\StringHelper;
use yii\widgets\ActiveForm;

$customTypes = new CustomType();
$customTypes = $customTypes->getTypes();
$modelName = StringHelper::basename(get_class($model));

?>
<div>

    <div class="row no-gutters">
        <div class="content-panel col-md-12">
            <div class="content-inner">
                <div class="content-area">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="content-panel-sub">
                                <div class="panel-head"><?php Yii::t('messages', 'Custom Field Details') ?></div>
                            </div>
                            <?php
                            $form = \yii\bootstrap\ActiveForm::begin(
                                [
                                    'id' => 'custom-field-form',
                                    'options' => [
                                        'enableAjaxValidation' => true,
                                        'validateOnSubmit' => true,
                                    ],
                                ]
                            );
                            ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="CustomField_label"><?php echo $attributeLabels['label']; ?></label>
                                        <?php echo $form->field($model, 'label')->textInput(['class' => 'form-control'])->label(false); ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="CustomField_fieldName"><?php echo $attributeLabels['fieldName']; ?></label>
                                        <?php echo $form->field($model, 'fieldName')->textInput(['id' => 'CustomField_fieldName', 'class' => 'form-control'])->label(false); ?>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="CustomField_relatedTable"><?php echo $attributeLabels['relatedTable']; ?></label>
                                        <?php
                                        $url = yii::$app->urlManager->createUrl('/custom-field/load-custom-type');
                                        echo $form->field($model, 'relatedTable')->dropDownList(
                                            CustomType::getAreaList(true),
                                            [
                                                'class' => 'form-control',
                                                'id' => 'CustomField_relatedTable',
                                                'onchange' => '
                                                    var url = "' . $url . '"
                                                    var thisVal  = this.value;
                                                    var data = {
                                                        model:"' . $modelName . '",
                                                        emptyOption: 1,
                                                        areaId:thisVal,
                                                        parentAttribute:"areaId",
                                                        childAttribute1:"customTypeId"
                                                    }
                                                       $.post(url, data , function(result){
                                                           var res = $(result).find("#customTypeId").html(); 
                                                           $("#CustomField_customTypeId").html(res); 
                                                      });
                                                    ',
                                            ])->label(false); ?>

                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="CustomField_customTypeId"><?php echo $attributeLabels['customTypeId']; ?></label>
                                        <?php echo $form->field($model, 'customTypeId')->dropDownList(CustomType::getListByArea(true, $model->relatedTable), ['id' => 'CustomField_customTypeId', 'class' => 'form-control'])->label(false); ?>

                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="CustomField_listValues"><?php echo $attributeLabels['listValues']; ?></label>
                                        <?php echo $form->field($model, 'listValues')->textarea(['cols' => 30, 'rows' => 5, 'class' => 'span8', 'class' => 'form-control', 'id' => 'CustomField_listValues'])->label(false); ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="CustomField_defaultValue"><?php echo $attributeLabels['defaultValue']; ?></label>
                                        <?php echo $form->field($model, 'defaultValue')->textInput(['id' => 'CustomField_defaultValue', 'class' => 'form-control'])->label(false); ?>

                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="CustomField_sortOrder"><?php echo $attributeLabels['sortOrder']; ?></label>
                                        <?php echo $form->field($model, 'sortOrder')->textInput(['class' => 'form-control','value' => 0, 'id' => 'CustomField_sortOrder'])->label(false); ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mt-3">
                                        <label for="CustomField_subarea"
                                               class="d-block"><?php echo $attributeLabels['subarea']; ?></label>
                                        <div class="rows">

                                            <?php
                                            echo $form->field($model, 'subarea')->checkboxList(CustomType::getSubAreas(CustomType::CF_PEOPLE), [
                                                'class' => 'row',
                                                'item' => function ($index, $label, $name, $checked, $value) {
                                                    if ($checked == 1) {
                                                        $checked = 'checked';
                                                    } else {
                                                        $checked = '';
                                                    }
                                                    return "
                                                           
                                                            <div class='col-xl-6 col-lg-12 displayOption'>
                                                            <label class=''>
                                                            <input type='checkbox' id='CustomField_subarea_{$index}'   $checked   name='{$name}' value='{$value}' tabindex='3' class='form-check-input custom-icheck'>
                                                            &nbsp;{$label}
                                                            </label></div>";
                                                }

                                            ])->label(false);

                                            ?>

                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mt-3">
                                        <label for="form-check" class="d-block">Options</label>
                                        <div class="form-check form-check-inline">
                                            <?php
                                            if($model->isNewRecord) {
                                                echo $form->field($model, 'enabled')->checkbox(['checked' => 'checked', 'class' => 'form-check-input custom-icheck']);
                                            } else {
                                                echo $form->field($model, 'enabled')->checkbox(['class' => 'form-check-input custom-icheck']);
                                            }
                                            ?>

                                        </div>
                                        <div class="form-check form-check-inline">
                                            <?php echo $form->field($model, 'required')->checkbox(['class' => 'form-check-input custom-icheck']); ?>

                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="form-row text-left text-md-right">
                                <div class="form-group col-md-12">
                                    <?php
                                    echo Html::submitButton($model->isNewRecord ? Yii::t('messages', 'Create') : Yii::t
                                    ('messages', 'Save'), ['class' => 'btn btn-primary']);
                                    ?>
                                    <?php
                                    echo Html::a(Yii::t('messages','Cancel'), ['custom-field/admin'], ["class" => "btn btn-secondary"]);
                                    ?>

                                </div>
                            </div>

                            <?php \yii\bootstrap\ActiveForm::end(); ?>

                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<script type="text/javascript">
    /*<![CDATA[*/
    $(function () {

        function initCustomFieldPage(types) {
            $('#CustomField_relatedTable').change(function () {
                var selected = $(this).val();
            });

            $('#CustomField_customTypeId').change(function () {
                var selected = types[$(this).val()];
                if (selected == 'list') {
                    hideSubArea('<?php echo CustomType::CF_PEOPLE ?>', '<?php echo CustomType::CF_SUB_PEOPLE_BULK_INSERT ?>');
                    hideSubArea('<?php echo CustomType::CF_PEOPLE ?>', '<?php echo CustomType::CF_SUB_PEOPLE_ADVANCED_SEARCH ?>');
                    hideSubArea('<?php echo CustomType::CF_PEOPLE ?>', '<?php echo CustomType::CF_SUB_PEOPLE_BASIC_SEARCH ?>');
                    hideSubArea('<?php echo CustomType::CF_PEOPLE ?>', '<?php echo CustomType::CF_SUB_PEOPLE_MAP_VIEW ?>');
                    $("#CustomField_defaultValue").attr("placeholder", "").blur();
                    $('#CustomField_listValues').closest('.col-md-6').hide();
                } else if (selected == 'dropdown' || selected == 'radiobutton' || selected == 'checkbox') {
                    hideSubArea('<?php echo CustomType::CF_PEOPLE ?>', '<?php echo CustomType::CF_SUB_PEOPLE_BULK_INSERT ?>');
                    hideSubArea('<?php echo CustomType::CF_PEOPLE ?>', '<?php echo CustomType::CF_SUB_PEOPLE_ADVANCED_SEARCH ?>');
                    hideSubArea('<?php echo CustomType::CF_PEOPLE ?>', '<?php echo CustomType::CF_SUB_PEOPLE_BASIC_SEARCH ?>');
                    hideSubArea('<?php echo CustomType::CF_PEOPLE ?>', '<?php echo CustomType::CF_SUB_PEOPLE_MAP_VIEW ?>');
                    $("#CustomField_defaultValue").attr("placeholder", "").blur();
                    $('#CustomField_listValues').closest('.col-md-6').show();
                } else if (selected == 'textarea') {
                    showSubArea('<?php echo CustomType::CF_PEOPLE ?>', '<?php echo CustomType::CF_SUB_PEOPLE_BULK_INSERT ?>');
                    showSubArea('<?php echo CustomType::CF_PEOPLE ?>', '<?php echo CustomType::CF_SUB_PEOPLE_ADVANCED_SEARCH ?>');
                    showSubArea('<?php echo CustomType::CF_PEOPLE ?>', '<?php echo CustomType::CF_SUB_PEOPLE_BASIC_SEARCH ?>');
                    showSubArea('<?php echo CustomType::CF_PEOPLE ?>', '<?php echo CustomType::CF_SUB_PEOPLE_MAP_VIEW ?>');
                    $("#CustomField_defaultValue").attr("placeholder", "").blur();
                    $('#CustomField_listValues').closest('.col-md-6').hide();
                } else if (selected == 'text') {
                    showSubArea('<?php echo CustomType::CF_PEOPLE ?>', '<?php echo CustomType::CF_SUB_PEOPLE_BULK_INSERT ?>');
                    showSubArea('<?php echo CustomType::CF_PEOPLE ?>', '<?php echo CustomType::CF_SUB_PEOPLE_ADVANCED_SEARCH ?>');
                    showSubArea('<?php echo CustomType::CF_PEOPLE ?>', '<?php echo CustomType::CF_SUB_PEOPLE_BASIC_SEARCH ?>');
                    showSubArea('<?php echo CustomType::CF_PEOPLE ?>', '<?php echo CustomType::CF_SUB_PEOPLE_MAP_VIEW ?>');
                    $("#CustomField_defaultValue").attr("placeholder", "").blur();
                    $('#CustomField_listValues').closest('.col-md-6').hide();
                } else if (selected == 'date') {
                    showSubArea('<?php echo CustomType::CF_PEOPLE ?>', '<?php echo CustomType::CF_SUB_PEOPLE_BULK_INSERT ?>');
                    showSubArea('<?php echo CustomType::CF_PEOPLE ?>', '<?php echo CustomType::CF_SUB_PEOPLE_ADVANCED_SEARCH ?>');
                    showSubArea('<?php echo CustomType::CF_PEOPLE ?>', '<?php echo CustomType::CF_SUB_PEOPLE_BASIC_SEARCH ?>');
                    showSubArea('<?php echo CustomType::CF_PEOPLE ?>', '<?php echo CustomType::CF_SUB_PEOPLE_MAP_VIEW ?>');
                    $("#CustomField_defaultValue").attr("placeholder", "YYYY-MM-DD").blur();
                    $('#CustomField_listValues').closest('.col-md-6').hide();
                } else {
                    showSubArea('<?php echo CustomType::CF_PEOPLE ?>', '<?php echo CustomType::CF_SUB_PEOPLE_BULK_INSERT ?>');
                    showSubArea('<?php echo CustomType::CF_PEOPLE ?>', '<?php echo CustomType::CF_SUB_PEOPLE_ADVANCED_SEARCH ?>');
                    showSubArea('<?php echo CustomType::CF_PEOPLE ?>', '<?php echo CustomType::CF_SUB_PEOPLE_BASIC_SEARCH ?>');
                    showSubArea('<?php echo CustomType::CF_PEOPLE ?>', '<?php echo CustomType::CF_SUB_PEOPLE_MAP_VIEW ?>');
                    $("#CustomField_defaultValue").attr("placeholder", "").blur();
                    $('#CustomField_listValues').closest('.col-md-6').hide();
                }
            });

            $('#customfield-label').blur(function () {
                if ($('#CustomField_fieldName').val() == "")
                    $('#CustomField_fieldName').val((($(this).val()).replace(/ /g, '')).toUpperCase());
            });

            $('#CustomField_fieldName').blur(function () {
                $(this).val((($(this).val()).replace(/ /g, '')).toUpperCase());
            });
        }

        function showSubArea(area, subArea) {
            if ($('#CustomField_relatedTable').val() == area) {
                $(":checkbox[value=" + subArea + "]").closest(".displayOption").show();
            }
        }

        function hideSubArea(area, subArea) {
            if ($('#CustomField_relatedTable').val() == area) {
                $(":checkbox[value=" + subArea + "]").prop("checked", false);
                $(":checkbox[value=" + subArea + "]").closest(".displayOption").hide();
            }
        }

        initCustomFieldPage(<?php echo json_encode($customTypesCodes); ?>);
        $('#CustomField_customTypeId').trigger("change");

    });

    /*]]>*/
</script>
