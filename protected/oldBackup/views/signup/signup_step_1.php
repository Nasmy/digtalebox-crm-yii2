<?php

use app\components\LinkedInApi;
use app\components\TwitterApi;
use yii\helpers\Html;
use yii\helpers\Url;

$altPic = Yii::$app->toolKit->getAltProfPic();
$profImage = Html::img($profImageUrl, array('onerror' => 'this.src="' . $altPic . '"'));
// echo $profImage;
$url = Yii::$app->urlManager->createUrl('/signup/share-by-email/');
$urlStepTwo = Yii::$app->urlManager->createUrl('/signup/Step2/');
$shareByeEmailSubject = $shareByEmailDetails['subject'];
$shareByeEmailBody = $shareByEmailDetails['body'];
$shareByEmail = <<<JS
    jQuery('#shareByEmail').click(function() { console.log('sad'); });


    jQuery('#shareByEmail').click(function() {
      
      var email = $('#appendedInputButton').val();
     
     if(email == '') {
         return;
     }
     $.ajax({
            url: "$url",
            type: 'POST',
			data:'email=' + email + '&subject={$shareByeEmailSubject}&body={$shareByeEmailSubject}',
	    	success: function(data) {
				var res = $.parseJSON(data);
				if (res.status == '1') {
				    $('#appendedInputButton').val('');
				    alert(res.message);
				} else if (res.status == '0') {
				    alert(res.message);
				}
	    	}
		});
    
    });
JS;
$this->registerJs($shareByEmail);
$twUrl = Yii::$app->urlManager->createUrl(['/signup/step2/', 'network' => TwitterApi::TWITTER]);
//$fbUrl = Yii::$app->urlManager->createUrl('/signup/step2/', array('network' => FacebookApi::FACEBOOK));
$linUrl = Yii::$app->urlManager->createUrl(['/signup/step2/', 'network' => LinkedInApi::LINKEDIN]);
$socialLogin = <<< JS
   $('#tw_login').click(function(){
		window.open('$twUrl','','width=500,height=400,scrollbars=no,resize=false');
		return false;
	});

	$('#fb_login').click(function(){
	 	// window.open($twUrl . "','','width=500,height=270,scrollbars=yes');
	});

	$('#lin_login').click(function(){
	 	 window.open('$linUrl','','width=500,height=270,scrollbars=yes');
	 	 return false;
	});
JS;
$this->registerJs($socialLogin);

?>
<style type="text/css">
    span.share-btn-text {
        font-size: 10px;
    }

    a.twitter-share-button.twitter-share-button-rendered.twitter-tweet-button {
        background: #1b95e0;
        color: white !important;
        padding: 0px 6px;
        border-radius: 3px;
    }
</style>
<div class="register-promo">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 mx-auto">
                <div class="mainframe">
                    <div class="row">
                        <div class="upper mx-auto">
                            <img src="<?php echo Yii::$app->toolKit->getImagePath() ?>digitalebox-logo.png">
                        </div>
                    </div>

                    <div class="bottom text-center">
                        <div class="profile-pic"><?php echo $profImage;?></div>
                        <div class="slogan"><?php echo $model[0]->headerText;?></div>
                        <div class="promo-text"><?php echo $model[0]->promoText;?></div>

                        <div class="desc mb-3">
                            <?php echo $model[0]->introduction;?>
                        </div>

                        <?php if (empty(Yii::$app->session['signupOther'])): ?>
                            <div class="mb-4">
                                <a href="<?php echo Url::to(['signup/step-three','network'=>'']); ?>"
                                   class="btn btn-primary" id="signup"><?php echo Yii::t('messages','Sign Up with email');?></a>
                            </div>
                        <?php endif; ?>
                        <div class="social-links mb-5">
                            <ul>
                                <?php  if (empty(Yii::$app->session['signupOther']['networks']) || in_array(TwitterApi::TWITTER, Yii::$app->session['signupOther']['networks'])): ?>
                                    <li><a id="tw_login" class="tw" href=""><i class="fa fa-twitter"></i></a></li>
                                <?php  endif; ?>
                                <?php  if (empty(Yii::$app->session['signupOther']['networks']) || in_array(FacebookApi::FACEBOOK, Yii::$app->session['signupOther']['networks'])): ?>
                                    <li><a id="fb_login" class="fb" href=""><i class="fa fa-facebook"></i></a></li>
                                <?php  endif; ?>
                                <?php  if (empty(Yii::$app->session['signupOther']['networks']) || in_array(LinkedInApi::LINKEDIN, Yii::$app->session['signupOther']['networks'])): ?>
                                    <li><a id="lin_login" class="in" href=""><i class="fa fa-linkedin"></i></a></li>
                                <?php endif; ?>
                            </ul>
                        </div>

                        <div class="share-this">
                            <?php echo Yii::t('messages','Share this page on'); ?>

                            <div class="row mt-2">
                                <div class="col-sm-12 col-md-8 mx-auto">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="d-inline-flex">
                                                <?php  echo Yii::$app->toolKit->getTwitterShareButton($shareUri, 'Volunteer Sign Up', 'none'); ?>
                                            </div>
                                            <div class="d-inline-flex">
                                                    <?php // echo Yii::$app->toolKit->getFacebookShareButton($shareUri, 'button'); ?>
                                            </div>
                                            <div class="d-inline-flex align-top">
                                                <button class="btn align-top btn-primary btn-share-by-email btn-sm" type="button" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
                                                    <?php  echo Yii::t('messages','Share by email'); ?>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="collapse mt-3" id="collapseExample">
                            <div class="row">
                                <div class="col-md-10 mx-auto">
                                    <div class="form-group">
                                        <div class="input-group">
                                            <input type="text" id="appendedInputButton" class="form-control" placeholder="Email Address">
                                            <div class="input-group-append">
                                                <button class="btn btn-primary" id="shareByEmail" type="button">
                                                    <?php
                                                        echo  Yii::t('messages','Share');
                                                    ?>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="cprt mt-4 mb-1"><?php echo Yii::$app->params['copyRight']; ?></div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
