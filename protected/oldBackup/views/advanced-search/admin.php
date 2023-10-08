<?php

use app\controllers\AdvancedSearchController;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\View;
use app\models\User;
use rmrevin\yii\fontawesome\FA;
use yii\widgets\ActiveForm;
use yii\grid\GridView;
use yii\bootstrap\Tabs;

echo Yii::$app->toolKit->registerAdvanceSearchScript();
$urlexportFile = Yii::$app->urlManager->createUrl(['advanced-search/exportFile']);
$send_email_url = Yii::$app->urlManager->createUrl(['advanced-search/sendmail']);
$advancedSearch_ShowCriteria_url = Yii::$app->urlManager->createUrl(['advanced-search/show-criteria']);

// User Type
$searchExclude = User::SEARCH_EXCLUDE;
$searchNormal = User::SEARCH_NORMAL;
$searchStrict = User::SEARCH_STRICT;

// Keyword Related Translations
$keywordNull = Yii::t('messages', 'Keywords or Exclude keywords cannot be empty');
$keywordExcludeSameValue = Yii::t('messages', 'Keywords or Exclude keywords cannot contain same value');
$keywordTwoNull = Yii::t('messages', 'Keywords 2 or Exclude keywords 2 cannot be empty');
$keywordTwoSameValue = Yii::t('messages', 'Keywords 2 or Exclude keywords 2 cannot contain same value');

// Filter Related Translations
$searchFilterEmpty = Yii::t('messages', 'Search filters cannot be empty');

// Confirmation Messages
$deleteConfirm = Yii::t('messages', 'Are you sure you want to delete?');
$exportConfirm = Yii::t('messages', 'Are you sure you want to export?');
$deleteUser = Yii::t('messages', "Are you sure you want to delete user?");

// Action Urls
$updateSavedSearch = Yii::$app->urlManager->createUrl(['advanced-search/update-search']);
$updateSearchCriteria = Yii::$app->urlManager->createUrl(['advanced-search/update-search-criteria-ajax']);
$createSaveSearch = Yii::$app->urlManager->createUrl(['advanced-search/save-search']);
$gridUpdate = Yii::$app->urlManager->createUrl(['advanced-search/grid-update']);
$sendEmail = Yii::$app->urlManager->createUrl(['advanced-search/send-mail']);

// Saved Search
$saveSearchId = null;
$isSaveSearchId =  false;
if (isset($_GET['savedSearchId'])) {
    $saveSearchId = $_GET['savedSearchId'];
} else {
    $saveSearchId = null;
}

// Filter Search
$search_array = array();
$customFields_array = array();
if (isset($_POST['filter'])) {
    $search_array = $_POST['filter'];
}


$js = <<<JS
    $(document).on('click', '.search-button', function () {
        $('.search-form').toggle();
        return false;
    });

    $(document).ready(function () {
        if ($('#User_isDisplayKeywords2').is(":checked"))
            $('#keywords2-div').show();
        else {
            $('#keywords2-div').hide();
        }
        
        // on check
        $('#User_isDisplayKeywords2').change(function () {
            if (this.checked) {
                $('#keywords2-div').show();
                if ($('select#User_searchType2').val() == $searchExclude) {
                    $('div.selectize-control.exclude2').show();
                } else {
                    $('div.selectize-control.exclude2').hide();
                }
            } else {
                $('#keywords2-div').hide();
                $('#User_keywords2').select()[0].selectize.clear();
                $('#User_searchType2').val("$searchNormal");
            }
        });
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

            if (searchType == $searchExclude) {
                if (keywords == '') {
                    alert('{$keywordNull}');
                    return false;
                } else if (keywordsExclude == '') {
                    alert('{$keywordNull}');
                    return false;
                } else {
                    var res = keywords.filter(function (el) {
                        return keywordsExclude.indexOf(el) != -1
                    });
                    if (res.length != 0) {
                        alert('{$keywordExcludeSameValue}');
                        return false;
                    }
                }
            }

            if($("#User_isDisplayKeywords2").prop('checked') == true) {
                if (searchType2 == $searchExclude) {
                    if (keywords == '') {
                        alert('{$keywordNull}');
                        return false;
                    } else if (keywords2 == '') {
                        alert('{$keywordTwoNull}');
                        return false;
                    } else if (keywordsExclude2 == '') {
                        alert('{$keywordTwoNull}');
                        return false;
                    } else {
                        var res = keywords2.filter(function (el) {
                            return keywordsExclude2.indexOf(el) != -1
                        });
                        if (res.length != 0) {
                            alert('{$keywordTwoSameValue}');
                            return false;
                        }
                    }
                } else if (searchType2 == $searchNormal || searchType2 == $searchStrict) {
                    if (keywords == '' && keywords2 != '') {
                        alert('{$keywordNull}');
                        return false;
                    }
                }
            }

            var filters = [];
            $(".filter:checked").each(function () {
                filters.push($(this).val());
            });
            return false;
        });
    
        function isSearchFormFilled() {
            var isFilled = $('input[type="text"]', 'form').filter(function () {
                return $.trim(this.value).length;  //text inputs have a value
            }).length;

            if (!isFilled) { //check for dropdown
                if ($('#user-countrycode').val() != '') {
                    isFilled = true;
                } else if (!isFilled && $('#user-gender').val() != '') {
                    isFilled = true;
                } else if (!isFilled && $('#user-usertype').val() != '') {
                    isFilled = true;
                } else if (!isFilled && $('#user-mapzone').val() != '') {
                    isFilled = true;
                } else if (!isFilled && $('div.selectize-input').find('div.item').length != 0) { //check auto complete inputs
                    isFilled = true;
                } else if (!isFilled && $('#user-network').val() != null) {
                    isFilled = true;
                } else if (!isFilled && $('#user-emailstatus').val() != null) {
                    isFilled = true;
                }
            }

            if (isFilled) {
                return true;
            } else {
                return false;
            }
        }
        
        jQuery('body').on('click', '#bulk-edit', function () {
            var isFilled = isSearchFormFilled();
            if (!isFilled) {
                alert('{$searchFilterEmpty}');
                return false;
            }
            // do the save
            $('#adv-search').trigger('click');

            $('#SearchCriteria_criteriaName').val($(this).attr('data'));

            $('.search-form form *').filter(':input').each(function () {
                if (typeof $(this).attr('id') !== 'undefined') {
                    value = $(this).attr('id').replace('User', 'SearchCriteria');
                    $('#' + value).val($(this).val());
                }

                if ('User_excludeFbPersonalContacts' == $(this).attr('id')) {
                    if ($('#User_excludeFbPersonalContacts').is(':checked')) {
                        $('#SearchCriteria_excludeFbPersonalContacts').val('1');
                    } else {
                        $('#SearchCriteria_excludeFbPersonalContacts').val('');
                    }
                }

                //on page load
                if ('User_isDisplayKeywords2' == $(this).attr('id')) {
                    if ($('#User_isDisplayKeywords2').is(':checked')) {
                        $('#SearchCriteria_isDisplayKeywords2').val('1');
                    } else {
                        $('#SearchCriteria_isDisplayKeywords2').val('');
                    }
                }

                // on check
                $('#User_isDisplayKeywords2').change(function () {
                    if (this.checked) {
                        $('#keywords2-div').show();
                    } else {
                        $('#keywords2-div').hide();
                    }
                });

            });

            jQuery.ajax({
                'type': 'POST',
                'url': $(this).attr('href'),
                'data': $('.search-form form').serialize(),
                'dataType': 'json',
                'success': function (data) {
                    if (data.status == 'success') {
                        window.location.replace(data.url);
                    } else {
                        $('#statusMsg').html(data.message);
                    }
                    return false;
                },
                'cache': false
            });

            return false;
        });
        
        jQuery('body').on('click', '#bulk-delete', function () {
            var isFilled = isSearchFormFilled();
            if (!isFilled) {
                alert('{$searchFilterEmpty}');
                return false;
            }

            // do the delete
            if (confirm('{$deleteConfirm}')) {
                $('#adv-search').trigger('click');
                $('#SearchCriteria_criteriaName').val($(this).attr('data'));
                $('.search-form form *').filter(':input').each(function () {
                    if (typeof $(this).attr('id') !== 'undefined') {
                        value = $(this).attr('id').replace('User', 'SearchCriteria');
                        $('#' + value).val($(this).val());
                    }

                    if ('User_excludeFbPersonalContacts' == $(this).attr('id')) {
                        if ($('#User_excludeFbPersonalContacts').is(':checked')) {
                            $('#SearchCriteria_excludeFbPersonalContacts').val('1');
                        } else {
                            $('#SearchCriteria_excludeFbPersonalContacts').val('');
                        }
                    }

                    if ('User_isDisplayKeywords2' == $(this).attr('id')) {
                        if ($('#User_isDisplayKeywords2').is(':checked')) {
                            $('#SearchCriteria_isDisplayKeywords2').val('1');
                        } else {
                            $('#SearchCriteria_isDisplayKeywords2').val('');
                        }
                    }

                });

                jQuery.ajax({
                    'type': 'POST',
                    'url': $(this).attr('href'),
                    'data': $('.search-form form').serialize(),
                    'dataType': 'json',
                    'success': function (data) {
                        if (data.status == 'success') {
                            $('#statusMsg').html(data.message);
                        } else {
                            $('#statusMsg').html(data.message);
                        }
                        return false;
                    },
                    'cache': false
                });
            }
            return false;
        });
        
        jQuery('body').on('click', '#bulk-export, #export-address', function () {
            var isFilled = isSearchFormFilled();
            if (!isFilled) {
                alert('{$searchFilterEmpty}');
                return false;
            }
            if (confirm('{$exportConfirm}')) {
                $('#adv-search').trigger('click');
                $('#SearchCriteria_criteriaName').val($(this).attr('data'));
                $('.search-form form *').filter(':input').each(function () {
                    if (typeof $(this).attr('id') !== 'undefined') {
                        value = $(this).attr('id').replace('User', 'SearchCriteria');
                        $('#' + value).val($(this).val());
                    }

                    if ('User_excludeFbPersonalContacts' == $(this).attr('id')) {
                        if ($('#User_excludeFbPersonalContacts').is(':checked')) {
                            $('#SearchCriteria_excludeFbPersonalContacts').val('1');
                        } else {
                            $('#SearchCriteria_excludeFbPersonalContacts').val('');
                        }
                    }

                    if ('User_isDisplayKeywords2' == $(this).attr('id')) {
                        if ($('#User_isDisplayKeywords2').is(':checked')) {
                            $('#SearchCriteria_isDisplayKeywords2').val('1');
                        } else {
                            $('#SearchCriteria_isDisplayKeywords2').val('');
                        }
                    }

                });

                jQuery.ajax({
                    'type': 'POST',
                    'url': $(this).attr('href'),
                    'data': $('.search-form form').serialize(),
                    'dataType': 'json',
                    'success': function (data) {
                        if (data.status == 'success') {
                            $('#statusMsg').html(data.message);
                            window.location.replace(data.url);
                        } else {
                            $('#statusMsg').html(data.message);
                        }
                        return false;
                    },
                    'cache': false
                });
            }
            return false;
        });
        
        jQuery('body').on('click', '#save-search', function () {
            var criteriaId = $("#criteriaId").val();
            if(criteriaId != '') {
                updateSaveSearch(criteriaId);
            } else {
                createSaveSearch();
            }
        });
        
        // Update Saved search
        function updateSaveSearch(criteriaId) {
            jQuery.ajax({
                'type': 'POST',
                'url': '{$updateSavedSearch}',
                'data': $('.search-form form').serialize() + '&validateOnly=true&savedSearchId=' + criteriaId,
                'dataType': 'json',
                'success': function (data) {
                    if (data.status == "success") {
                        $("#iframe-searchCriteria").attr("src", '{$updateSearchCriteria}' + '/id/' + criteriaId);
                        $('#search-criteria').modal({backdrop: 'static'});
                        $('#search-criteria').on('hidden.bs.modal', function (e) {
                            $('#iframe-searchCriteria').attr('src', '')
                        });
                        $('#searchcriteria-criterianame').val($(this).attr('data'));
                    }
                    return false;
                },
                'cache': false
            });
        }
        
        // Create Saved search
        function createSaveSearch() {
            $('#SearchCriteria_criteriaName').val($(this).attr('data'));
            jQuery.ajax({
                'type': 'POST',
                'url': '{$createSaveSearch}',
                'data': $('.search-form form').serialize() + '&validateOnly=true',
                'dataType': 'json',
                'success': function (data) {
                    if (data.status == "success") {
                        $("#iframe-searchCriteria").attr("src", '{$updateSearchCriteria}' + '/id/' + data.criteraId);
                        $('#search-criteria').modal({backdrop: 'static'});
                        $('#search-criteria').on('hidden.bs.modal', function (e) {
                            $('#iframe-searchCriteria').attr('src', '')
                        });
                    } else {
                        $('#statusMsg').html(data.message);
                    }
                    return false;
                },
                'cache': false
            });
        }
        
        // ............Advanced search filter modifications ..............................................
        // on check Display Filters
        $('#User_isDisplayFilters').change(function () {
            if (this.checked) {
                $('.search-filters').show();
            } else {
                $('.search-filters').hide();
            }
        });

        $('#advanced_srch a').click(function (e) {
            e.preventDefault();
            $.ajax({
                type: 'GET',
                url: $(this).attr('href'),
                data: {unsetFilter: '1'},
                success: function (result) {
                    window.location = '/index.php/advanced-search/admin';
                },

            });
        });
JS;
$this->registerJs($js);
?>

<script>
    // on load Display Filters
    $(window).on('load', function () {
        if ($('#User_isDisplayFilters').is(':checked')) {
            $('.search-filters').show();
        } else {
            $('.search-filters').hide();
        }
    });
</script>

<?php
$this->title = Yii::t('messages', 'Advanced Search');
$this->titleDescription = Yii::t('messages', 'Search people by various criteria');
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'People'), 'url' => ['admin']];
$this->params['breadcrumbs'][] = $this->title;
$saveSearch = Yii::$app->user->checkAccess("AdvancedSearch.SaveSearch");
$sendBulk = Yii::$app->user->checkAccess('SendBulkMessages');
?>
<div id="statusMsg"></div>
<?php echo Yii::$app->controller->renderPartial('_tabMenu'); ?>
<div class="row no-gutters">
    <div class="content-panel col-md-12">
        <div class="content-inner">
            <div class="content-area">
                <div class="form-row mb-2">
                    <div class="form-group col-md-12">
                        <?php if ($saveSearch || $sendBulk): ?>
                        <?php if ($saveSearch): ?>
                            <div class="content-panel-sub">
                                <div class="panel-head">
                                    <?php echo Yii::t('messages', 'Saved Searches') ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <form>
                            <?php
                            $form = ActiveForm::begin(['id' => 'Send-email', 'enableClientValidation' => true, 'options' => ['enctype' => 'multipart/form-data']]);
                            ?>
                            <div class="form-row">
                                <div class="form-group col-md-6 col-xl-5">
                                    <?php if (isset($_GET['savedSearchId'])) { ?>
                                        <script>
                                            $(document).ready(function () {
                                                $.post('<?=Yii::$app->urlManager->createUrl(['advanced-search/show-criteria'])?>', {criteriaId: <?=$_GET['savedSearchId']?>},
                                                    function (data) {
                                                        $('.search-form').html(data);
                                                        // on page load
                                                        if ($('#User_isDisplayKeywords2').is(":checked"))
                                                            $('#keywords2-div').show();
                                                        else {
                                                            $('#keywords2-div').hide();
                                                        }
                                                        // on check
                                                        $('#User_isDisplayKeywords2').change(function () {
                                                            if (this.checked) {
                                                                $('#keywords2-div').show();
                                                                if ($('select#User_searchType2').val() == "<?=User::SEARCH_EXCLUDE?>") {
                                                                    $('div.selectize-control.exclude2').show();
                                                                } else {
                                                                    $('div.selectize-control.exclude2').hide();
                                                                }
                                                            } else {
                                                                $('#keywords2-div').hide();
                                                                $('#User_keywords2').select()[0].selectize.clear();
                                                                $('#User_searchType2').val("<?=User::SEARCH_NORMAL?>");
                                                            }
                                                        });
                                                        var filters = [];
                                                        $(".filter:checked").each(function () {
                                                            filters.push($(this).val());
                                                        });
                                                        var url = '<?=Yii::$app->urlManager->createUrl(['advanced-search/grid-update'])?>';
                                                        var criteriaId = $("#criteriaId option:selected").val();
                                                        $('#searchFrom').attr('action', "/index.php/advanced-search/admin");
                                                        var dataArray = $('.search-form form').serialize();
                                                        $.post(url, {
                                                                filters: filters,
                                                                data: dataArray,
                                                                criteriaId: criteriaId
                                                            },
                                                            function (returnedData) {
                                                                if (returnedData) {
                                                                    $('.search-grid').html(returnedData);
                                                                } else {
                                                                }
                                                                return false;
                                                            });
                                                    });
                                            });
                                        </script>
                                    <?php } ?>
                                    <script>
                                        $(document).ready(function () {
                                            $('select[name=criteriaId]').on('change', function () {
                                                $.post('<?=Yii::$app->urlManager->createUrl(['advanced-search/show-criteria'])?>', {criteriaId: $(this).val()},
                                                    function (data) {
                                                        $('.search-form').html(data);
                                                        // on page load
                                                        if ($('#User_isDisplayKeywords2').is(":checked"))
                                                            $('#keywords2-div').show();
                                                        else {
                                                            $('#keywords2-div').hide();
                                                        }
                                                        // on check
                                                        $('#User_isDisplayKeywords2').change(function () {
                                                            if (this.checked) {
                                                                $('#keywords2-div').show();
                                                                if ($('select#User_searchType2').val() == "<?=User::SEARCH_EXCLUDE?>") {
                                                                    $('div.selectize-control.exclude2').show();
                                                                } else {
                                                                    $('div.selectize-control.exclude2').hide();
                                                                }
                                                            } else {
                                                                $('#keywords2-div').hide();
                                                                $('#User_keywords2').select()[0].selectize.clear();
                                                                $('#User_searchType2').val("<?=User::SEARCH_NORMAL?>");
                                                            }
                                                        });
                                                        var filters = [];
                                                        $(".filter:checked").each(function () {
                                                            filters.push($(this).val());
                                                        });
                                                        var url = '<?=Yii::$app->urlManager->createUrl(['advanced-search/grid-update'])?>';
                                                        var criteriaId = $("#criteriaId option:selected").val();

                                                        if (criteriaId != '') {
                                                            $('#save-search').show();
                                                        }
                                                        $('#searchFrom').attr('action', "/index.php/advanced-search/admin");
                                                        var dataarray = $('.search-form form').serialize();
                                                        $.pjax.reload({
                                                            type: 'POST',
                                                            url: url,
                                                            replace: false,
                                                            container: '#people-grid-update',
                                                            data: {filters: filters, data: dataarray, criteriaId: criteriaId}
                                                        });

                                                    });
                                            });
                                        });
                                    </script>
                                    <?php
                                    if (Yii::$app->user->checkAccess("AdvancedSearch.SaveSearch")) {
                                        echo $form->field($modelSearchCriteria, 'id')->dropDownList($searchCriteria, ['class' => 'form-control', 'name' =>
                                            'criteriaId', 'id' => 'criteriaId'])->label(false);
                                    }
                                    ?>
                                </div>

                                <div class="form-group col-md-6 col-xl-5">
                                    <?php
                                    if (Yii::$app->user->checkAccess('SendBulkMessages')) {
                                        echo Html::a('<i class="fa fa-envelope fa-1x"></i> ' . Yii::t('messages', 'Send Emails'), ['campaign/create-camp'], ['class' => 'btn btn-primary send-email-campaign']);
                                    } ?>
                                </div>
                                <?php ActiveForm::end(); ?>

                                <?php endif; ?>
                            </div>
                    </div>

                    <div class="content-panel-sub">
                        <div class="panel-head">
                            <?php echo Yii::t('messages', 'Search by') ?>
                        </div>
                    </div>
                    <div class="search-form col-md-12" style="display:block">
                        <?= $this->render('_search', array(
                            'customFields' => $customFields,
                            'model' => $model, 'tagList' => $keywords, 'teams' => $teams,
                            'data' => $modelSearchCriteria->criteriaName, 'isOwner' => $isOwner, 'modelConfig' => $modelConfig
                        )); ?>
                    </div>
                    <br/>

                    <!----------------Advanced search filter modifications--------------------->
                    <div class="col-md-12 form-row mt-3">
                        <div class="form-group col-md-8">
                            <div class="form-check form-check-inline">
                                <label class="checkbox" for="User_isDisplayFilters">
                                    <input id="User_isDisplayFilters" class="form-check-input checkbox"
                                           name="User[isDisplayFilters]" <?php if (count($search_array) > 3) {
                                        echo 'checked="checked"';
                                    } ?> value="1"
                                           type="checkbox"><?php echo Yii::t('messages', 'Enable Filter Options') ?>
                                </label><br/>
                            </div>
                        </div>
                    </div>


                    <div class="ms-options col-md-12">
                        <div class="search-filters" style="display:block">
                            <form class="search-filters-form form-vertical" method="POST" action="">
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-2">
                                            <?php
                                            $filterOptions = array('Update', 'Profile', 'First Name', 'Last Name', 'Email', 'Mobile', 'Keywords', 'Kedit', 'City',
                                                'Joined Date', 'Age', 'Category', 'Ucategory', 'Created At', 'Updated At');

                                            foreach ($customFields as $val) {
                                                $customFields_array[] = $val['fieldName'];
                                            }
                                            $fullOptions = array_merge($filterOptions, $customFields_array);

                                            foreach ($fullOptions

                                            as $key => $value)
                                            { ?>

                                            <?php if ($key == 0 OR $key == 7 OR $key == 12) { ?>
                                                <input type="checkbox" class="filter" name="filter[]"
                                                       value="<?php echo $key; ?>" style="display:none;"
                                                       checked="checked">
                                            <?php } ?>
                                            <?php if (0 < $key && $key != 7 && $key <= 8) { ?>
                                                <input type="checkbox" class="filter" name="filter[]"
                                                       style="margin-right: 5px;" value="<?php echo $key; ?>"
                                                    <?php if ($search_array) {
                                                        if (array_search($key, $search_array)) {
                                                            echo 'checked="checked"';
                                                        }
                                                    } ?>><label><?php echo Yii::t('messages', $value); ?></label>
                                                <br/> <?php } ?>
                                            <?php if ($key == 9){ ?></div>
                                        <div class="col-md-3"><?php } ?>
                                            <?php if (9 <= $key && $key != 12 && $key <= 16) { ?>
                                                <input type="checkbox" class="filter" name="filter[]"
                                                       style="margin-right: 5px;" value="<?php echo $key; ?>"
                                                    <?php if ($search_array) {
                                                        if (array_search($key, $search_array)) {
                                                            echo 'checked="checked"';
                                                        }
                                                    } ?>><label><?php echo Yii::t('messages', $value); ?></label>
                                                <br/> <?php } ?>
                                            <?php if ($key == 17){ ?></div>
                                        <div class="col-md-3"><?php } ?>
                                            <?php if (17 <= $key && $key <= 23) { ?>
                                                <input type="checkbox" class="filter" name="filter[]"
                                                       style="margin-right: 5px;" value="<?php echo $key; ?>"
                                                    <?php if ($search_array) {
                                                        if (array_search($key, $search_array)) {
                                                            echo 'checked="checked"';
                                                        }
                                                    } ?>><label><?php echo Yii::t('messages', $value); ?></label>
                                                <br/> <?php } ?>
                                            <?php if ($key == 24){ ?></div>
                                        <div class="col-md-3"><?php } ?>
                                            <?php if (24 <= $key && $key <= 30) { ?>
                                                <input type="checkbox" class="filter" name="filter[]"
                                                       style="margin-right: 5px;" value="<?php echo $key; ?>"
                                                    <?php if ($search_array) {
                                                        if (array_search($key, $search_array)) {
                                                            echo 'checked="checked"';
                                                        }
                                                    } ?>><label><?php echo Yii::t('messages', $value); ?></label>
                                                <br/> <?php } ?>

                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-8"></div>
                                        <div class="col-md-4">
                                            <button id="adv-filter" class="btn btn-info btn btn-info btn-small"
                                                    type="submit">
                                                <i class="fa fa-filter fa-1x "></i>&nbsp;<?php echo Yii::t('messages', 'Filter'); ?>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>

                        </div>
                    </div>
                     <?php
                     $filterCheckBox = <<<JS
                        limit = 0; //set limit
                        var checkboxes = document.querySelectorAll('.search-filters input[type="checkbox"]'); //select all checkboxes
                        function checker(elem) {
                            if (elem.checked) { //if checked, increment counter
                                limit++;
                            } else {
                                limit--; //else, decrement counter
                            }
                            for (i = 0; i < checkboxes.length; i++) { // loop through all
                                if (limit == 10) {
                                    if (!checkboxes[i].checked) {
                                        checkboxes[i].disabled = true; // and disable unchecked checkboxes
                                    }
                                } else { //if limit is less than two
                                    if (!checkboxes[i].checked) {
                                        checkboxes[i].disabled = false; // enable unchecked checkboxes
                                    }
                                }
                            }
                        }
                        for (i = 0; i < checkboxes.length; i++) {
                            checkboxes[i].onclick = function () { //call function on click and send current element as param
                                checker(this);
                            }
                        }
                     JS;

                     $this->registerJs($filterCheckBox);
                     ?>

                    <!-- .............. End of Advanced search filter modifications ............................... -->
                    <div class="search-grid col-md-12" style="display:block">
                        <?= $this->render('_grid', array('model' => $model, 'mapView' => false, 'customFields' => $customFields, 'filters' => $filters, 'gridId' => 'people-grid', 'filtersFields' => $fullOptions, 'dataProvider' => $dataProvider, 'searchModel' => $searchModel)); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--Send Email-->
    <div class="modal fade" id="sendEmail" tabindex="-1" role="dialog"
         aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered model-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"
                        id="exampleModalLongTitle"><?php echo Yii::t('messages', 'Start Campaign') ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <iframe id="iframe-message" class="modal-body" src="" frameborder="0" scrolling="no" width="100%"
                        height="330px"></iframe>
            </div>
        </div>
    </div>
    <!--End-->

    <div class="modal fade" id="search-criteria" tabindex="-1" role="dialog"
         aria-labelledby="exampleModalCenterTitle"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"
                        id="exampleModalLongTitle"> <?php echo Yii::t('messages', 'Save New Search Criteria'); ?> </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <iframe id="iframe-searchCriteria" class="modal-body" src="" frameborder="0" scrolling="no"
                        width="100%"
                        height="260px"></iframe>
            </div>
        </div>
    </div>

    <?php
        $token = Yii::$app->request->csrfToken;
        $footerJs = <<<JS
             $(document).on("click", ".ajaxDelete", function (e) {
                e.preventDefault();
                var deleteUrl = $(this).attr('delete-url');
                var pjaxContainer = $(this).attr('pjax-container');
                var result = confirm("$deleteUser");
                if (result) {
                    /*$.ajax({
                        url: deleteUrl,
                        type: "post",
                        data: {
                            YII_CSRF_TOKEN: '{$token}'
                        },
                        success: function (data) {
                            $("#statusMsg").html(data);
                            refreshDeleteSearchGrid(deleteUrl);
                            return false;
                        }
                    });*/
                    $.ajax({
                        url: deleteUrl,
                        type: 'post',
                        data: {YII_CSRF_TOKEN: '{$token}'},
                        success: function(response) {
                            $.pjax.reload({container:'#people-grid-update',async: false});
                        }
                    });
                }
            });
            function refreshDeleteSearchGrid(url) {
                console.log(url);
                var prameters = url.split("?");
                
                var filters = [];
                $(".filter:checked").each(function () {
                    filters.push($(this).val());
                });
                var criteriaId = $("#criteriaId option:selected").val();
                var url = '{$gridUpdate}?' + prameters[1];
                var dataarray = $('.search-form form').serialize();
                $.post(url, {filters: filters, data: dataarray, criteriaId: criteriaId},
                    function (returnedData) {
                        if (returnedData) {
                            $('.search-grid', window.parent.document).html(returnedData);
                            return false;
                        }
                    });
            }
            /*$('.send-email-campaign').on('click', function () {
                $.ajax({
                    type: "POST",
                    url: '{$sendEmail}',
                    data: $('.search-form form').serialize(),
                    dataType: "json",
                    success: function (data) {
                        if (data.status == "success") {
                            $("#iframe-message").attr("src", data.url);
                            $("#sendEmail").modal({backdrop: 'static'});
                        } else {
                            $("#statusMsg").html(data.message);
                        }
                        return false;
                    } 
                });
            });*/
        JS;
        $this->registerJs($footerJs);
    ?>
