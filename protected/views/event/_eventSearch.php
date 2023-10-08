<?php

use app\components\WebUser;
use kartik\datetime\DateTimePicker;
use yii\bootstrap\Button;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\jui\AutoComplete;
use yii\jui\DatePicker;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\EventSearch */
/* @var $form yii\widgets\ActiveForm */
?>
<?php
$search = <<<JS
 	$('.search-form form').submit(function(){
		$.fn.yiiGridView.update('people-grid', {
			data: $(this).serialize()
		});
		//return false;
	});
JS;
$this->registerJs($search);

$attributeLabels = $userModel->attributeLabels();
$id = Yii::$app->controller->actionParams['id'];
$key = Yii::$app->controller->actionParams['key'];

$url = Url::to(['event/view/', 'id' => $id, 'key' => $key]);
$form = ActiveForm::begin([
    'action' => $url,
    'method' => 'get',
])

?>


<div class="form-row ">
    <?php $isRegional = Yii::$app->user->checkAccess(WebUser::REGIONAL_ADMIN_NAME) && !Yii::$app->session->get('is_super_admin'); ?>
    <div class="form-row">
        <div class="form-group  col-sm-4 col-md-4 col-lg-4 col-xl-4">
            <?php echo $form->field($userModel, 'firstName')->textInput(
                ['class' => 'form-control',
                    'maxlength' => 45,
                    'label' => false,
                    'placeholder' => $attributeLabels['firstName']
                ]
            )->label(false);
            ?>
        </div>
        <div class="form-group col-sm-4 col-md-4 col-lg-4 col-xl-4">
            <?php echo $form->field($userModel, 'lastName')->textInput(
                ['class' => 'form-control',
                    'maxlength' => 45,
                    'label' => false,
                    'placeholder' => $attributeLabels['lastName']
                ]
            )->label(false);
            ?>
        </div>
        <div class="form-group col-sm-4 col-md-4 col-lg-4 col-xl-4">
            <?php echo $form->field($userModel, 'email')->textInput(
                array('class' => 'form-control',
                    'maxlength' => 45,
                    'label' => false,
                    'placeholder' => $attributeLabels['email'])
            )->label(false);
            ?>
        </div>
    </div>


    <p></p>

    <div class="form-row text-left text-md-left">
        <div class="form-group col-md-12 event-management-filter">
            <?php
            echo Html::submitButton(Yii::t('messages', 'Search'), ['class' => 'btn btn-primary']);
            ?>
        </div>
    </div>
</div>
<?php
$form->end();
?>
