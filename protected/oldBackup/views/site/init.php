<?php

use app\components\FacebookApi;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use app\components\TwitterApi;
?>
<?php


$twUrl = Yii::$app->urlManager->createUrl(['/signup/social-login', 'network' => TwitterApi::TWITTER]);
$likedInUrl = Yii::$app->urlManager->createUrl(['/signup/social-login', 'network' => \app\components\LinkedInApi::LINKEDIN]);
// $fbUrl = Yii::$app->urlManager->createUrl(['/signup/social-login/', 'network' => FacebookApi::FACEBOOK]);

$script = <<< JS
$('.owl-carousel').owlCarousel({
            items: 1,
            loop: true,
            margin: 10,
            nav: true,
            navText: ['<i class="fa fa-long-arrow-left"></i>', '<i class="fa fa-long-arrow-right"></i>'],
            autoplay: true,
            autoplayTimeout: 5000
        });
JS;
$this->registerJs($script);
$scriptSocial = <<< JS
    $('#tw_login').click(function(){
		window.open('$twUrl' ,'','width=500,height=400,scrollbars=no,resize=false');
		return false;
	});

	/*$('#fb_login').click(function(){
		window.open('fbUrl' ,'','width=500,height=400,scrollbars=no,resize=false');
		return false;
	});*/
	
	$('#ln_login').click(function(){
 	 	window.open('$likedInUrl' ,'','width=500,height=400,scrollbars=no,resize=false');
		return false;
	});
JS;
$this->registerJs($scriptSocial);
?>
<div class="login-page">
       <div class="container">
           <div class="row">
               <div class="col-lg-6 d-none d-lg-block">
                   <div class="login-content">
                       <div class="carousel">
                           <div class="owl-carousel owl-theme">
                               <?php foreach ($imgSliderItems as $item): ?>

                                   <div class="item">
                                       <img src="<?php echo $item['image'] ?>">
                                   </div>
                               <?php endforeach ?>
                           </div>
                       </div>
                       <div class="desc">
                           <?php echo $modelCandidateInfo->aboutText ?>
                       </div>
                   </div>
               </div>
               <div class="col-md-12 col-lg-6">
                   <div class="login-form">
                       <div class="language">
                           <a class="nav-link" href="#" id="navbarDropdown" role="button" data-toggle="dropdown"
                              aria-haspopup="true" aria-expanded="false">
                               <div class="flag"><img
                                           src="<?php echo Yii::$app->toolKit->getImagePath() . Yii::$app->params['lang'][Yii::$app->language]['flagName'] ?>">
                               </div>
                           </a>
                           <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                               <?php foreach (Yii::$app->params['lang'] as $key => $langInfo): ?>
                                   <a class="dropdown-item list-items <?php echo $key == Yii::$app->language ? "active" : ""; ?>"
                                      href="<?php echo Url::to(['site/change-lang', 'lang' => $langInfo['identifier']]); ?>">
                                       <div class="country-flag">
                                           <img src="<?php echo Yii::$app->toolKit->getImagePath() . $langInfo['flagName'] ?>">
                                       </div>
                                       <div class="message">
                                           <div class="name"><?php echo Yii::t('messages', $langInfo['name']); ?></div>
                                       </div>
                                   </a>
                               <?php endforeach; ?>
                           </div>
                       </div>

                       <div class="row justify-content-center">
                           <div class="col-10 col-md-7">
                               <div class="row justify-content-center">
                                   <a href="" class="logo">
                                       <img src="<?php echo Yii::$app->toolKit->getImagePath() ?>digitalebox-logo.png">
                                   </a>
                               </div>
                               <?php $form = ActiveForm::begin([
                                   'id' => 'login-form',
                                   'layout' => 'horizontal',
                                   'fieldConfig' => [
                                       'template' => "{label}\n<div class=\"col-lg-12\">{input}</div>\n<div class=\"col-lg-8\">{error}</div>",
                                       'labelOptions' => ['class' => 'col-lg-1 control-label'],
                                   ],
                               ]); ?>
                               <div class="form-group">
                                   <?= $form->field($model, 'username')->textInput(['autofocus' => true, 'class'=>'form-control', 'placeholder' => 'Username'])->label(false); ?>
                               </div>
                               <div class="form-group">
                                   <?= $form->field($model, 'password')->passwordInput(['class'=>'form-control', 'placeholder' => 'Password'])->label(false); ?>
                               </div>
                               <div class="form-group">
                                   <div class="col-lg-12">
                                       <?= Html::submitButton('Login', ['class' => 'btn btn-block btn-primary', 'name' => 'login-button']) ?>
                                   </div>
                               </div>
                               <div class="form-group">
                                   <div class="form-check" id="forgot-pass">
                                       <?php echo Html::a(Yii::t('messages', 'Forgot Password'),
                                           Yii::$app->urlManager->createUrl('site/forgot-password'), array(
                                               'id' => 'forgot-pw',
                                               'title' => Yii::t('messages', 'Forgot Password'),
                                               'target' => '_blank'
                                           )); ?>
                                   </div>
                               </div>
                               <?php ActiveForm::end(); ?>


                               <div class="cprt mt-4 mb-1"><?php echo Yii::$app->params['copyRight']; ?></div>
                           </div>
                       </div>
                   </div>
               </div>
           </div>
       </div>
</div>
