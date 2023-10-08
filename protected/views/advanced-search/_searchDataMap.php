<?php
use app\models\Country;
use app\models\User;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\components\WebUser;
$form = ActiveForm::begin([
        'id' => 'map-form',
        'class' => 'form-vertical',
        'method' => 'post'
]);
?>
<script type="text/javascript">
    $(document).ready(function () {
        $('#user-gender option[value=""]').attr("selected",true);
        $('#user-usertype option[value=""]').attr("selected",true);
    });

</script>
<?php $attributeLabels = $model->attributeLabels(); ?>
<?php $isRegional = Yii::$app->user->CheckUserType(WebUser::REGIONAL_ADMIN_NAME) && !Yii::$app->session->get('is_super_admin'); ?>
<div class="form-row">
    <div class="form-group col-sm-6 col-md-4 col-lg-4 col-xl-2">
        <?php echo $form->field($model, 'firstName')->textInput(['class'=>'form-control','maxlength'=>45,'placeholder' => $attributeLabels['firstName']])->label(false) ?>
    </div>
    <div class="form-group col-sm-6 col-md-4 col-lg-4 col-xl-2">
        <?php echo $form->field($model, 'lastName')->textInput(['class'=>'form-control','maxlength'=>45,'placeholder' => $attributeLabels['lastName']])->label(false) ?>
    </div>
    <div class="form-group col-sm-6 col-md-4 col-lg-4 col-xl-2">
        <?php echo $form->field($model, 'city')->textInput(['class'=>'form-control','maxlength'=>45,'placeholder' => $attributeLabels['city']])->label(false) ?>
    </div>
    <div class="form-group col-sm-6 col-md-4 col-lg-4 col-xl-2">
        <?php $Country = new Country();?>
        <?php echo $form->field($model, 'countryCode')->dropDownList($Country->getCountryDropdown(), ['class' => 'form-control'])->label(false) ?>
    </div>
    <div class="form-group col-sm-6 col-md-4 col-lg-4 col-xl-2">
        <?php echo $form->field($model, 'gender')->dropDownList(array('' => Yii::t('messages', '- Gender -'), User::MALE => Yii::t('messages', 'Male'), User::FEMALE => Yii::t('messages', 'Female'), User::ASEXUAL => Yii::t('messages', 'Other')), ['class' => 'form-control'])->label(false) ?>

    </div>
    <div class="form-group col-sm-6 col-md-4 col-lg-4 col-xl-2">
        <?php $User = new User();?>
        <?php echo $form->field($model, 'userType')->dropDownList($User->getUserTypes(), ['class' => 'form-control','prompt' => Yii::t('messages', '- Category -')])->label(false) ?>
    </div>
    <div class="form-group col-sm-6 col-md-4 col-lg-4 col-xl-2">
        <?php if (!$isRegional) echo $form->field($model, 'zip')->textInput(['class'=>'form-control','maxlength'=>45,'placeholder' => $attributeLabels['zip']])->label(false); ?>
    </div>
    <div class="form-group col-sm-6 col-md-4 col-lg-4 col-xl-2">
        <?php echo $form->field($model, 'age')->textInput(['class' => 'form-control', 'maxlength' => 45,'placeholder' => $attributeLabels['age']])->label(false);?>
        <?php echo $form->field($model, 'CustomValue')->hiddenInput(['value' =>''])->label(false);?>
    </div>
</div>
<div class="form-row">
    <div class="form-group col-md-12">
        <?php echo $form->field($model, 'fullAddress')->textInput(['class' => 'form-control', 'maxlength' => 128,  'placeholder' => $attributeLabels['fullAddress']])->label(false);?>
    </div>
</div>
<div class="form-row">
    <div class="form-control no-border normal-padding">
        <div class="input-group no-gutters">
            <div class="form-control no-border p-0 col-sm-9 col-md-8 col-lg-9">
                <?php
                echo $form->field($model, 'keywords')->widget(Select2::className(), [
                    'name' => 'keywords',
                    'data' => $tagList,
                    'size' => Select2::MEDIUM,
                    'options' => [
                        'class' => 'form-control form-control-selectize',
                        'multiple' => true,
                        'create' => false,
                        'fullWidth' => false,
                        'useWithBootstrap' => true,
                        'placeholder' => Yii::t('messages', 'Keywords'),
                    ],
                ])->label(false);
                ?>
            </div>
            <div class="input-group-append col-sm-3 col-md-4 col-lg-3">
                <?php echo $form->field($model, "searchType")->dropDownList($User->getSearchType(),['class' =>'keyword-select form-control'])->label(false); ?>
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
                        'class' => 'form-control form-control-selectize exclude-hide exclude',
                        'multiple' => true,
                        'fullWidth' => false,
                        'placeholder' => Yii::t('messages', 'Exclude keywords'),
                        'useWithBootstrap' => true,
                    ],

                ])->label(false);
                ?>
            </div>
        </div>
    </div>
</div>
<div class="form-row mt-3">
    <div class="form-group col-md-8"></div>
    <div class="form-group col-md-4 text-left text-md-right">
        <?= Html::submitButton('<i class="fa fa-search"></i> '.Yii::t('messages', 'Search'), ['type'=>'submit','encodeLabel' => false,'size' => 'small','class' => 'btn btn-primary','id' => 'map-search']); ?>
        <button type="button" class="btn btn-secondary" data-toggle="button" aria-pressed="false" autocomplete="off"
                id="dm-toggle">
            <?php echo Yii::t('messages', 'Enable OpenData'); ?>
        </button>
    </div>
</div>
<?php ActiveForm::end(); ?>
<script>
    $(document).ready(function () {
        $(".chosen-select").chosen();
        $('div.exclude-keyword').hide();
        $('div.selectize-control.exclude').hide();
        $("select#user-searchtype").on('change', function () {
            if ($(this).val() == <?php echo User::SEARCH_EXCLUDE ?>) {
                $('div.exclude-keyword').show();
                $('div.selectize-control.exclude').show();
            } else {
                $('div.exclude-keyword').hide();
                $('div.selectize-control.exclude').hide();
            }
        });

        function initExcludeKeywords(){
            $('#User_keywordsExclude').hide();
            if ($("select#user-searchtype").val() == <?php echo User::SEARCH_EXCLUDE ?>) {
                $('#User_keywordsExclude').removeAttr("class");
                $('#User_keywordsExclude').attr("class","exclude");
                $('div.exclude-keyword').show();
                $('div.selectize-control.exclude').show();
            } else {
                $('div.exclude-keyword').hide();
                $('div.selectize-control.exclude').hide();
            }
        }
    });
</script>
