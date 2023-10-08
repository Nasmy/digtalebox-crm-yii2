<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

$this->registerJs(<<<JS
 
$('.pwd-show').on('click', function(e) {
        e.preventDefault(); 
         const pwd = $(".pwd");
        if (pwd.attr('type') === 'password') {
            pwd.attr('type', 'text');
        } else {
            pwd.attr('type', 'password');
        }
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
                                <img src="<?php
                                echo Yii::$app->toolKit->getImagePath() ?>digitalebox-logo.png">
                            </div>
                            <div class="forgot-text mb-2"><?php echo Yii::t('messages','Reset Password') ?></div>
                            <div class="desc"></div>
                        </div>
                    </div>
                    <div class="bottom text-center">
                        <div class="col-md-12">
                            <div id="statusMsg"></div>
                        </div>
                        <div class="col-md-8 col-lg-10 mx-auto">
                            <?php
                            $form = ActiveForm::begin([
                                'id' => 'forgot-pw-form',
                                'layout' => 'horizontal',
                                'enableClientValidation' => true,
//                              'enableAjaxValidation' => true,
                                'validateOnSubmit' => true,
                                'fieldConfig' => [
                                    'template' => "{input} <div class=\"input-group-append\">
                                       <span class=\"input-group-text\"><a href=\"\">
                                          <i class=\"fa fa-eye-slash\" aria-hidden=\"true\"></i></a>
                                       </span>
                                    </div>\n<div class=\"error-col\">{error}</div>",
                                    'labelOptions' => ['class' => 'm-0 control-label  pwd'],

                                ],

                            ]);
                            ?>

                            <div class="form-group show_hide_password">
                                <label class="float-left"><?php echo Yii::t('messages','Password') ?>
                                </label>
                                <div class="input-group">

                                    <?php echo $form->field($model, 'password', [
                                        'template' => '{label} <div class="row">
                                            <div class="col-sm-12 d-inline-flex">{input} 
                                                <div class="input-group-append">
                                                   <span class="input-group-text"><a href="" class="pwd-show">
                                                      <i class="fa fa-eye-slash" aria-hidden="true"></i></a>
                                                   </span>
                                                </div>
                                        </div><div class="col-sm-12 text-left">{error}</div>{hint}
                                    </div>',
                                        'options'=>
                                            [
                                                'tag'=>'div',
                                                'class'=>'w-100'
                                            ]
                                    ])->passwordInput(['class' => 'form-control pwd',])->label(false);;
                                    ?>

                                </div>
                            </div>
                            <div class="clearfix"></div>
                            <div class="form-group show_hide_password">
                                <label class="float-left"><?php echo Yii::t('messages','Confirm Password') ?>
                                </label>
                                <div class="input-group">
                                    <?php echo $form->field($model, 'confPassword', [
                                        'template' => '{label} <div class="row">
                                            <div class="col-sm-12 d-inline-flex">{input} 
                                                <div class="input-group-append">
                                                   <span class="input-group-text"><a href="" class="pwd-show">
                                                      <i class="fa fa-eye-slash" aria-hidden="true"></i></a>
                                                   </span>
                                                </div>
                                        </div><div class="col-sm-12 text-left">{error}</div>{hint}
                                    </div>',
                                        'options'=>
                                            [
                                                'tag'=>'div',
                                                'class'=>'w-100'
                                            ]
                                    ])->passwordInput(['class' => 'form-control pwd',])->label(false);;
                                    ?>

                                </div>
                            </div>

                            <button type="submit" id="send" class="btn btn-primary mt-2">
                                <?php echo Yii::t('messages','Send'); ?></button>
                            <?php
                            $flash ="";
                            if (Yii::$app->session->hasFlash('success')):
                                $flash = "<div class='alert in alert-success' style='opacity: 1'>
                                            <a class='close' data-dismiss='alert'>×</a>
                                             ".Yii::$app->session->getFlash('success')."
                                            </div>";
                            elseif (Yii::$app->session->hasFlash('error')):
                                $flash = "<div class='alert in alert-error' style='opacity: 1'>
                                            <a class='close' data-dismiss='alert'>×</a>
                                             ".Yii::$app->session->getFlash('success')."
                                            </div>";

                            else: $flash ="";
                            endif;

                            echo $flash;
                            ?>
                        </div>
                        <?php ActiveForm::end() ?>
                        <div class="cprt mt-4"><?php echo Yii::$app->params['copyRight']; ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>