<?php
use app\components\SCustomFields;
use app\models\Country;
use app\models\User;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
$this->title = Yii::t('messages', 'Edit Preview');
$this->titleDescription = Yii::t('messages', 'Preview for Bulk Edit');
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'People'), 'url' => ['admin']];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="row no-gutters">
    <div class="content-panel col-md-12">
        <div class="content-inner">
            <div class="content-area">
                <div class="row">
                    <div class="col-md-12">
                        <p class="alert alert-primary"><?php echo Yii::t('messages','You have chosen <strong>{userCount}</strong> records
                            to bulk edit. Please fill the only values you need to edit and leave others empty.',['userCount'=>$userCount]); ?></p>
                    </div>
                </div>
                <?php
                $form = ActiveForm::begin(['class' => 'form-horizontal','id' => 'user-form']);
                ?>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <?php echo $form->field($model, 'zip')->textInput(['class' => 'form-control']); ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <?php echo $form->field($model, 'gender')->dropDownList(array(
                                User::MALE => Yii::t('messages', 'Male'), User::FEMALE => Yii::t('messages', 'Female'),
                                User::ASEXUAL => Yii::t('messages', 'Unknown')),['class' => 'form-control','prompt' => Yii::t('messages', '- Gender -')]); ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <?php echo $form->field($model, 'city')->textInput(['class' => 'form-control']); ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <?php echo $form->field($model, 'countryCode')->dropDownList(Country::getCountryDropdown(), ['class' => 'form-control']); ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <?php echo $form->field($model, 'notes')->textInput(['class' => 'form-control']); ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <?php echo $form->field($model, 'userType')->dropDownList(User::getUserTypes(),['class' => 'form-control','prompt' => Yii::t('messages', '- Category -')]); ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <?php echo $form->field($model, 'emailStatus')->dropDownList(User::changeEmailStatus(),['class' => 'form-control']); ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <?php $attributeLabels = $model->attributeLabels(); ?>
                            <label class="control-label" for="User_keywords"><?php echo $attributeLabels['keywords']; ?></label>
                            <?php
                            echo $form->field($model, 'keywords')->widget(Select2::className(), [
                                'name' => 'keywords',
                                'data' => $keywords,
                                'size' => Select2::MEDIUM,
                                'options' => [
                                    'class' => 'form-control col-md-8 form-control-selectize',
                                    'multiple' => true,
                                    'create' => false,
                                    'fullWidth' => false,
                                    'useWithBootstrap' => true,
                                ],

                            ])->label(false);
                            ?>
                        </div>
                    </div>
                    <?php
                    echo SCustomFields::widget(['customFields' => $customFields, 'template' => '<div class="form-group col-md-6">{label}{input}</div>', 'isEditPreview' => true, 'enableAjaxValidation' => true, 'rowClass' => 'form-control']);
                    ?>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-row text-left text-md-right">
                            <div class="form-group col-md-12">
                                <?= Html::submitButton(Yii::t('messages', 'Save'), [
                                    'type' => 'submit',
                                    'encodeLabel' => false,
                                    'size' => 'small',
                                    'class' => 'btn btn-primary',
                                    'id' => 'saveBulkPreview'
                                ]); ?>
                                <?php $bulkEditUrl=Yii::$app->urlManager->createUrl(['advanced-search/cancel-bulk-edit/id/'.$id]);?>
                                <?=Html::a(Yii::t('messages', 'Cancel'),$bulkEditUrl,["class" => "btn btn-secondary btn-small", 'id' => 'bulkexport','encodeLabel' => false,"style" => 'word-wrap: break-word;']); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>

<?php
$msg1 = Yii::t('messages', 'At least one value is required');
$msg2 = Yii::t('messages', 'Are you sure you want to proceed?');

$bulkEditJs = <<<JS
function isSearchFormFilled(){
	var isFilled = $('input[type="text"]', 'form').filter(function() {
		return $.trim(this.value).length;  //text inputs have a value
	}).length;

	if(!isFilled) { //check for dropdown
    if(!isFilled && $('#BulkEditPreview_gender').val() != ''){
	isFilled = true;
	}
	else if(!isFilled && $('#BulkEditPreview_userType').val() != ''){
	isFilled = true;
	}
	else if(!isFilled && $('#BulkEditPreview_countryCode').val() != ''){
	isFilled = true;
	}
	else if(!isFilled && $('div.selectize-input').find('div.item').length != 0){ //check auto complete inputs
	isFilled = true;
	}
	else if(!isFilled && $('#BulkEditPreview_emailStatus').val() != ''){
	isFilled = true;
	}
	}

	if(isFilled){
	return true;
	}
	else {
	return false;
	}
}

	$('#saveBulkPreview').live('click',function(e) {
    var isFilled = isSearchFormFilled();
    if(!isFilled){
    var customDropdown = $("select option:selected" ).index();
    var customCheckbox = $('.customcheckboxes input:checked').length > 0;
    if(customCheckbox == 0 && customDropdown == 0){ //no custom dropdowns selected
        bootbox.alert("{$msg1}");
        return false;
    }
    }

    bootbox.confirm("{$msg2}", function(result) {
			if (result) {
				$('#user-form').submit();
			}
		});
		return false;
	});
JS;
$this->registerJs($bulkEditJs);
