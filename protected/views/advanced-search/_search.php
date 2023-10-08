<?php

use app\components\SCustomFields;
use app\components\WebUser;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use app\models\Country;
use app\models\MapZone;
use app\models\User;
use app\models\BulkExport;
use yii\helpers\ArrayHelper;
use kartik\select2\Select2;
use app\components\TwitterApi;
use app\components\LinkedInApi;
use wbraganca\multiselect\MultiSelectWidget;

$attributeLabels = $model->attributeLabels();
$form = ActiveForm::begin(['class' => 'form-vertical', 'method' => 'get', 'id' => 'searchFrom']);
$msgNoOption = Yii::t('messages', '- Networks -');
$msgMoreOptions = Yii::t('messages', 'More than 3 options selected');
$msgNoOptionType = Yii::t('messages', '- Type -'); ?>

<script type="text/javascript">
    $(document).ready(function () {
        $('#user-network').multiselect({
            nonSelectedText: '<?php echo $msgNoOption;?>'
        });
        $('#user-gender option[value="0"]').attr("selected", false);
    });
    $('.search-form form').submit(function (e) {
        e.preventDefault();
        var inputs = $('.search-form form :input');
        var values = {};
        inputs.each(function () {
            values[this.name] = $(this).val();
        });

        var searchType = values["User[searchType]"];
        var keywords = values["User[keywords][]"];
        var keywordsExclude = values["User[keywordsExclude][]"];

        var searchType2 = values["User[searchType2]"];
        var keywords2 = values["User[keywords2][]"];
        var keywordsExclude2 = values["User[keywordsExclude2][]"];

        if (searchType == " . User::SEARCH_EXCLUDE . ") {
            if (keywords == null) {
                alert('<?=Yii::t('messages', 'Keywords or Exclude keywords cannot be empty');?>');
                return false;
            } else if (keywordsExclude == null) {
                alert('<?=Yii::t('messages', 'Keywords or Exclude keywords cannot be empty');?>');
                return false;
            } else {
                var res = keywords.filter(function (el) {
                    return keywordsExclude.indexOf(el) != -1
                });
                if (res.length != 0) {
                    alert('<?=Yii::t('messages', 'Keywords or Exclude keywords cannot contain same value') ?>"');
                    return false;
                }
            }
        }

        if (searchType2 == " . User::SEARCH_EXCLUDE . ") {
            if (keywords == null) {
                alert('<?=Yii::t('messages', 'Keywords cannot be empty');?>');
                return false;
            } else if (keywords2 == null) {
                alert('<?=Yii::t('messages', 'Keywords 2 or Exclude keywords 2 cannot be empty');?>');
                return false;
            } else if (keywordsExclude2 == null) {
                alert('<?=Yii::t('messages', 'Keywords 2 or Exclude keywords 2 cannot be empty');?>');
                return false;
            } else {
                var res = keywords2.filter(function (el) {
                    return keywordsExclude2.indexOf(el) != -1
                });
                if (res.length != 0) {
                    alert('<?=Yii::t('messages', 'Keywords 2 or Exclude keywords 2 cannot contain same value');?>');
                    return false;
                }
            }
        } else if (searchType2 == " . User::SEARCH_NORMAL . " || searchType2 == " . User::SEARCH_STRICT . ") {
            if (keywords == null && keywords2 != null) {
                alert('<?=Yii::t('messages', 'Keywords cannot be empty');?>');
                return false;
            }
        }


        var filters = [];
        $(".filter:checked").each(function () {
            filters.push($(this).val());
        });
        var criteriaId = $("#criteriaId option:selected").val();
        var url = '<?=Yii::$app->urlManager->createUrl(['advanced-search/grid-update'])?>';
        var dataarray = $('.search-form form').serialize();

        $.pjax.reload({
            type: 'POST',
            url: url,
            replace: false,
            container: '#people-grid-update',
            data: {filters: filters, data: dataarray, criteriaId: criteriaId}
        });
        /*$.post(url, {filters: filters, data: dataarray, criteriaId: criteriaId},
            function (returnedData) {
                if (returnedData) {
                    $('.search-grid').html(returnedData);
                } else {
                }
                return false;
            });*/
        return false;
    });
</script>

<?php
$isRegional = Yii::$app->user->CheckUserType(WebUser::REGIONAL_ADMIN_NAME) && !Yii::$app->session->get('is_super_admin');
?>
<div class="form-row">
    <div class="form-group col-sm-6 col-md-4 col-lg-4 col-xl-2">
        <?php echo $form->field($model, 'firstName')->textInput(['class' => 'form-control', 'maxlength' => 45, 'placeholder' => $attributeLabels['firstName']])->label(false) ?>
    </div>
    <div class="form-group col-sm-6 col-md-4 col-lg-4 col-xl-2">
        <?php echo $form->field($model, 'lastName')->textInput(['class' => 'form-control', 'maxlength' => 45, 'placeholder' => $attributeLabels['lastName']])->label(false) ?>
    </div>
    <div class="form-group col-sm-6 col-md-4 col-lg-4 col-xl-2">
        <?php echo $form->field($model, 'city')->textInput(['class' => 'form-control', 'maxlength' => 45, 'placeholder' => $attributeLabels['city']])->label(false) ?>
    </div>
    <div class="form-group col-sm-6 col-md-4 col-lg-4 col-xl-2">
        <?php $Country = new Country(); ?>
        <?php echo $form->field($model, 'countryCode')->dropDownList($Country->getCountryDropdown(), ['class' => 'form-control'])->label(false) ?>
    </div>
    <div class="form-group col-sm-6 col-md-4 col-lg-4 col-xl-2">
        <?php $User = new User(); ?>
        <?php echo $form->field($model, 'gender')->dropDownList($User->getUserGender(), ['class' => 'form-control'])->label(false) ?>
    </div>
    <div class="form-group col-sm-6 col-md-4 col-lg-4 col-xl-2">
        <?php echo $form->field($model, 'userType')->dropDownList($User->getUserTypes(), ['class' => 'form-control', 'prompt' => Yii::t('messages', '- Category -')])->label(false) ?>
    </div>
</div>

<div class="form-row">
    <div class="form-group col-sm-6 col-md-4 col-lg-4 col-xl-2">
        <?php

        if (!$isRegional) {
            echo $form->field($model, 'zip')->textInput(['class' => 'form-control', 'maxlength' => 45, 'placeholder' => $attributeLabels['zip']])->label(false);
        }

        ?>
    </div>
    <div class="form-group col-sm-6 col-md-4 col-lg-4 col-xl-2">
        <?php echo $form->field($model, 'email')->textInput(['class' => 'form-control', 'maxlength' => 45, 'placeholder' => $attributeLabels['email']])->label(false); ?>
    </div>
    <div class="form-group col-sm-6 col-md-4 col-lg-4 col-xl-2">
        <?php echo $form->field($model, 'mobile')->textInput(['class' => 'form-control', 'maxlength' => 15, 'placeholder' => $attributeLabels['mobile']])->label(false); ?>
    </div>
    <div class="form-group col-sm-6 col-md-4 col-lg-4 col-xl-2">
        <?php echo $form->field($model, 'age')->textInput(['class' => 'form-control', 'maxlength' => 45, 'placeholder' => $attributeLabels['age']])->label(false); ?>
    </div>
    <div class="form-group col-sm-6 col-md-4 col-lg-4 col-xl-2">
        <?php $MapZone = new MapZone(); ?>
        <?php echo $form->field($model, "mapZone")->dropDownList($MapZone->getMapZoneDropdown(),
            ['class' => 'form-control',
            ])->label(false); ?>
    </div>
</div>
<div class="form-row">
    <div class="form-group col-md-12">
        <?php echo $form->field($model, 'fullAddress')->textInput(['class' => 'form-control', 'maxlength' => 128, 'placeholder' => $attributeLabels['fullAddress']])->label(false); ?>
    </div>
</div>
<div class="form-row">
    <div class="form-group col-md-3">
        <?php echo $form->field($model, "emailStatus")->dropDownList($User->getEmailStatus(), ['class' => 'form-control'])->label(false); ?>
    </div>
    <div class="form-group col-sm-6 col-md-auto">
        <?php
        $selected = array();
        if (!empty($model->network)) {
            foreach ($model->network as $selectedNetwork) {
                $selected[$selectedNetwork] = array('selected' => 'selected');
            }
        }
        ?>
        <script type="text/javascript">
            $(document).ready(function () {
                $(".ms-options-wrap button").text("<?=Yii::t('messages', '- Networks -') ?>");
            });
        </script>
        <?= MultiSelectWidget::widget([
            'options' => [
                'multiple' => 'multiple',
                'placeholder' => 'placeholder',
            ],
            'clientOptions' => [
                'nonSelectedText' => 'Check an option!',
                'nSelectedText' => ' - Options selected!',
                'allSelectedText' => 'All',
                'selectAllText' => 'Check all!',
                'numberDisplayed' => 1,
                'enableCaseInsensitiveFiltering' => true,
                'maxHeight' => 200, // The maximum height of the dropdown. This is useful when using the plugin with plenty of options.
                'includeSelectAllOption' => true,
            ],
            'data' => ArrayHelper::map($User->getMultiSelectNetworkTypes(), 'id', 'network'),
            'model' => $model,
            'attribute' => 'network',
        ]) ?>
    </div>
    <div class="form-group col-md-3">
        <?php echo $form->field($model, "formId")->dropDownList($User->getFormsDetails(), ['class' => 'form-control'])->label(false); ?>
    </div>

    <?php
    echo SCustomFields::widget(['customFields' => $customFields, 'template' => '<div class="form-group col-md-3">{label}{input}</div>', 'isSearchView' => true, 'rowClass' => 'form-control']);
    ?>
</div>
<div class="form-row">
    <div class="form-control no-border normal-padding">
        <div class="input-group no-gutters">
            <div class="form-control no-border col-md-8">
                <?php
                echo $form->field($model, 'keywords')->widget(Select2::className(), [
                    'name' => 'keywords',
                    'data' => $tagList,
                    'size' => Select2::MEDIUM,
                    'options' => [
                        'class' => 'form-control col-md-8 form-control-selectize',
                        'multiple' => true,
                        'create' => false,
                        'fullWidth' => false,
                        'useWithBootstrap' => true,
                        'placeholder' => Yii::t('messages', 'Keywords'),
                    ],

                ])->label(false);
                ?>
            </div>

            <div class="form-control no-border col-md-4">
                <?php echo $form->field($model, "searchType")->dropDownList($User->getSearchType(), ['class' => 'keyword-select form-control'])->label(false); ?>
            </div>
        </div>
    </div>
</div>
<div class="form-row exclude-keyword">
    <div class="form-control no-border normal-padding">
        <div class="input-group no-gutters">
            <div class="form-control no-border p-0 col-sm-9 col-md-8 col-lg-9">
                <?php
                echo $form->field($model, 'keywordsExclude')->widget(Select2::className(), [
                    'name' => 'keywordsExclude',
                    'data' => $tagList,
                    'size' => Select2::MEDIUM,
                    'options' => [
                        'class' => 'form-control',
                        'multiple' => true,
                        'placeholder' => Yii::t('messages', 'Exclude keywords'),
                    ],

                ])->label(false);
                ?>
            </div>
        </div>
    </div>
</div>
<!-- Keyword 2 search START -->
<div id="keywords2-div">
    <div class="form-row">
        <div class="form-control no-border normal-padding">
            <div class="input-group no-gutters">
                <div class="form-control no-border col-md-8">
                    <?php
                    echo $form->field($model, 'keywords2')->widget(Select2::className(), [
                        'name' => 'keywords2',
                        'data' => $tagList,
                        'size' => Select2::MEDIUM,
                        'options' => [
                            'class' => 'form-control col-md-8',
                            'multiple' => true,
                            'placeholder' => Yii::t('messages', 'Keywords 2'),
                        ],

                    ])->label(false);
                    ?>

                </div>
                <div class="form-control no-border col-md-4">
                    <?php echo $form->field($model, "searchType2")->dropDownList($User->getSearchType(), ['class' => 'keyword-select form-control'])->label(false); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="form-row exclude-keyword2">
        <div class="form-control no-border normal-padding">
            <div class="input-group no-gutters">
                <div class="form-control no-border col-md-8">
                    <?php
                    echo $form->field($model, 'keywordsExclude2')->widget(Select2::className(), [
                        'name' => 'keywordsExclude2',
                        'data' => $tagList,
                        'size' => Select2::MEDIUM,
                        'options' => [
                            'class' => 'form-control',
                            'multiple' => true,
                            'placeholder' => Yii::t('messages', 'Exclude keywords 2'),
                        ],

                    ])->label(false);
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Keyword 2 search END -->
<?php
echo $form->field($model, 'id')->hiddenInput()->label(false);
echo Html::hiddenInput('ajax', 1);
?>
<div class="form-row mt-3">
    <div class="form-group col-md-8">
        <div class="form-check form-check-inline">
            <?php echo $form->field($model, 'isDisplayKeywords2')->checkbox(['class' => 'form-check-input checkbox', 'id' => 'User_isDisplayKeywords2']); ?>
        </div>
    </div>
    <div class="form-group col-md-4 text-left text-md-right">
        <?= Html::submitButton('<i class="fa fa-search"></i> ' . Yii::t('messages', 'Search'), ['type' => 'submit', 'encodeLabel' => false, 'size' => 'small', 'class' => 'btn btn-primary', 'id' => 'adv-search']); ?>
        <?= Html::submitButton('<i class="fa fa-floppy-o"></i> ' . Yii::t('messages', 'Save Search'), ["class" => "btn btn-secondary", 'encodeLabel' => false, 'size' => 'small', 'type' => 'primary', 'id' => 'save-search', "style" => 'word-wrap: break-word;']); ?>
    </div>
</div>
<p></p>

<?php
if (Yii::$app->user->checkAccess("AdvancedSearch.BulkEdit")) {
    echo Html::a('<i class="fa fa-floppy-o"></i> ' . Yii::t('messages', 'Bulk Edit'), ['advanced-search/bulk-edit'], ["class" => "btn btn-warning btn-small", 'id' => 'bulk-edit'], ['encodeLabel' => false], ['type' => 'warning'], ["data" => $data], ["style" => 'word-wrap: break-word;']);
}
?>

<?php
if (Yii::$app->user->checkAccess("AdvancedSearch.BulkDelete")) {
    echo Html::a('<i class="fa fa-floppy-o"></i> ' . Yii::t('messages', 'Bulk Delete'), ['advanced-search/bulk-delete'], ["class" => "btn btn-danger btn-small", 'id' => 'bulk-delete'], ['encodeLabel' => false], ['type' => 'danger'], ["data" => $data], ["style" => 'word-wrap: break-word;']);
}
?>

<?php
if (Yii::$app->user->checkAccess("AdvancedSearch.BulkExport")) {
    echo Html::a('<i class="fa fa-floppy-o"></i> ' . Yii::t('messages', 'Bulk Export'), ['advanced-search/bulk-export'], ["class" => "btn btn-success btn-small", 'id' => 'bulk-export'], ['encodeLabel' => false], ['type' => 'success'], ["data" => $data], ["style" => 'word-wrap: break-word;']);
}
?>

<?php
if (Yii::$app->user->checkAccess("AdvancedSearch.BulkExport")) {
    echo Html::a('<i class="fa fa-floppy-o"></i> ' . Yii::t('messages', 'Address Export'), ['advanced-search/bulk-export', 'exportType' => BulkExport::ADDRESS_EXPORT_TYPE], ["class" => "btn btn-info btn-small", 'id' => 'export-address'], ['encodeLabel' => false], ['type' => 'info'], ["data" => $data], ["style" => 'word-wrap: break-word;']);
}
?>

<?php
if (Yii::$app->user->checkAccess("AdvancedSearch.BulkExport")) {
    echo Html::a('<i class="fa fa-list"></i> ' . Yii::t('messages', 'Export List'), ['advanced-search/bulk-export-view', 'exportType' => BulkExport::ADDRESS_EXPORT_TYPE], ["class" => "btn btn-secondary btn-small", 'id' => 'bulkexport'], ['encodeLabel' => false], ['type' => 'info'], ["data" => $data], ["style" => 'word-wrap: break-word;']);
}
?>
<?php ActiveForm::end(); ?>
<?php $SEARCH_EXCLUDE = User::SEARCH_EXCLUDE; ?>
<script>
    $(document).ready(function () {
        initExcludeKeywords();
        $("select#user-searchtype").on('change', function () {
            if ($(this).val() == <?=$SEARCH_EXCLUDE?>) {
                $('div.exclude-keyword').show();
                $('div.selectize-control.exclude').show();
                $('#User_keywordsExclude').select()[0].selectize.clear();
            } else {
                $('div.exclude-keyword').hide();
                $('div.selectize-control.exclude').hide();
            }
        });

        $("select#user-searchtype2").on('change', function () {
            if ($(this).val() == <?=$SEARCH_EXCLUDE?>) {
                $('div.exclude-keyword2').show();
                $('div.selectize-control.exclude2').show();
                $('#User_keywordsExclude2').select()[0].selectize.clear();
            } else {
                $('div.exclude-keyword2').hide();
                $('div.selectize-control.exclude2').hide();
            }
        });

        function initExcludeKeywords() {
            if ($("select#user-searchtype").val() == <?=$SEARCH_EXCLUDE?>) {
                $('#User_keywordsExclude').removeAttr("class");
                $('#User_keywordsExclude').attr("class", "exclude");
                $('div.exclude-keyword').show();
                $('div.selectize-control.exclude').show();
            } else {
                $('div.exclude-keyword').hide();
                $('div.selectize-control.exclude').hide();
            }

            if ($("select#user-searchtype2").val() == <?=$SEARCH_EXCLUDE?>) {
                $('#User_keywordsExclude2').removeAttr("class");
                $('#User_keywordsExclude2').attr("class", "exclude2");
                $('div.exclude-keyword2').show();
                $('div.selectize-control.exclude2').show();
            } else {
                $('div.exclude-keyword2').hide();
                $('div.selectize-control.exclude2').hide();
            }
        }
    });
</script>