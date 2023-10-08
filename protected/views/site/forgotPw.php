<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\widgets\Pjax;
use yii\web\Session;


$this->registerJs(<<<JS
   $( '#forgot-pw-form' ).ajaxComplete(function( event,data, xhr, ) {
      var notifyMsg =  $(data.responseText).find("#notify").html(); 
        jQuery("#notify").html(notifyMsg); 
    });


JS
);


?>
<div class="forgot-pass">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 mx-auto">
                <div class="mainframe">
                    <div class="row justify-content-center">
                        <div class="upper text-center">
                            <div class="mx-auto mb-4">
                                <img src="<?php echo Yii::$app->toolKit->getImagePath() ?>digitalebox-logo.png">
                            </div>
                            <div class="forgot-text mb-2"><?php echo Yii::t('messages','Forgot Password') ?></div>
                            <div class="desc"></div>
                        </div>
                    </div>
                    <div class="bottom text-center">
                        <div class="col-md-12">
                            <div id="statusMsg"></div>
                        <div class="col-md-8 col-lg-10 mx-auto">
                            <div id="notify">
                            <!-- success or error notification show by ajax-->
                                <?php echo $resp; ?>
                            </div>

                            <?php $form = ActiveForm::begin([
                                'id' => 'forgot-pw-form',
                                'layout' => 'horizontal',
                                'enableAjaxValidation' => false,
                                'validateOnSubmit' => true,
                                'fieldConfig' => [
                                    'template' => " <label class=\"float-left\">{label}\n</label><div class=\"col-md-12 p-0\">{input}</div>\n<div class=\"error-col\">{error}</div>",
                                    'labelOptions' => ['class' => 'm-0 control-label'],
                                ],
                            ]); ?>

                            <div class="form-group">
                                <?= $form->field($model, 'email')->textInput(['autofocus' => true, 'class'=>'form-control','placeholder'=>'ex:abc@company.com']); ?>

                             </div>
                             <div class="form-group">
                                <div class=" col-md-12 p-0">
                                    <?= Html::submitButton('Send', ['class' => 'btn btn-primary', 'id'=>'send','name' => 'login-button']) ?>
                                </div>
                            </div>

                            <?php ActiveForm::end(); ?>

                        </div>
                         <div class="cprt mt-4"><?php echo Yii::$app->params['copyRight']; ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
