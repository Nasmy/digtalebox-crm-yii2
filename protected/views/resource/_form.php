<?php

use app\models\Resource;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Resource */
/* @var $form yii\widgets\ActiveForm */

$typeImage = Resource::IMAGE;
$typeDocument = Resource::DOCUMENT;
$typeVideo = Resource::VIDEO;

$script = <<<JS
    
    $('#type').on('change',function(){
		showUploadOptions();
	});
	
	function showUploadOptions()
	{
		if ($('#type').val() == '{$typeImage}' || $('#type').val() == '{$typeDocument}') {
			$('#divFile').show();
			$('#divUrl').hide();
		} else if ($('#type').val() == '{$typeVideo}') {
			$('#divFile').hide();
			$('#divUrl').show();
		} else {
			$('#divFile').hide();
			$('#divUrl').hide();
		}
	}
	
	showUploadOptions();
JS;
$this->registerJs($script);
?>

<div class="row no-gutters mt-4">
    <div class="content-panel col-md-12">
        <div class="content-inner">
            <div class="content-area">

                <?php $form = ActiveForm::begin([
                    'id' => 'resource-form',
                    'options' => [
                        'enctype' => 'multipart/form-data',
                        'validateOnSubmit' => true,
                        'validateOnChange' => true,
                        'method' => 'post',
                    ],
                    'fieldConfig' => [
                        'template' => "{label}\n<div class=\"col-lg-4\">{input}</div>\n<div class=\"col-lg-6\">{error}</div>",
                        'labelOptions' => ['class' => 'col-lg-2 control-label'],
                    ],
                ]); ?>

            <?= $form->field($model, 'title')->textInput(['maxlength' => 64,'placeholder'=> 'Title'])  ?>

            <?= $form->field($model, 'description')->textInput(['maxlength' => true,'maxlength' => 128,'placeholder'=> 'description']) ?>

            <?= $form->field($model, 'tag')->textInput(['placeholder'=> 'tag'])->hint(Yii::t('messages', 'Comma separated list of search keywords'))  ?>

            <?= $form->field($model, 'type')->dropDownList( $model->getResourceTypeOptions(true),['id' => 'type','placeholder'=> 'type'])  ?>

            <div id="divFile" class="form-group col-md-4">

                <?=
                $form->field($model, 'file')->fileInput()->hint(Yii::t('messages', 'Allowed image types:{imageTypes}. Allowed document types:{documentTypes}', ['imageTypes'=>implode(',', $model->imageTypes),'documentTypes'=>implode(',', $model->documentTypes)]))  ?>
            </div>

            <div id="divUrl" class="form-group col-md-4">
                <?= $form->field($model, 'url')->textInput(['placeholder'=> 'Url'])->hint( Yii::t('messages', 'YouTube video URL'))  ?>
             </div>
            <div class="form-group">
                <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
                <?= Html::a('Cancel',Yii::$app->urlManager->createUrl('resource/admin'), ['class' => 'btn btn-secondary']) ?>
            </div>

            <?php ActiveForm::end(); ?>

        </div>
    </div>
</div>
