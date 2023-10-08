<?php

use app\components\Bitly;
use app\components\LinkedInApi;
use app\components\TwitterApi;
use Mailchimp\MailChimpApi;

Yii::$app->toolKit->registerFancyboxScripts();
?>
<?php
$pages_ln = $userLnP;
//$pages = $userP;
$twUrl = Yii::$app->urlManager->createUrl(['/signup/sign-up-client/', 'network' => TwitterApi::TWITTER]);
$lnUrl = Yii::$app->urlManager->createUrl(['/signup/sign-up-client/', 'network' => LinkedInApi::LINKEDIN]);
$bitlyUrl = Yii::$app->urlManager->createUrl(['/signup/sign-up-client/', 'network' => Bitly::BITLY]);
$fbUrl = "";
$gpUrl = "";
$maUrl = Yii::$app->urlManager->createUrl(['/signup/sign-up-client/', 'network' => MailChimpApi::MAILCHIMP]);
$instaUrl = "";
$initScript = <<< JS
    function popupwindow(url, title, w, h) {
		var left = (screen.width/2)-(w/2);
		var top = (screen.height/2)-(h/2);
		return window.open(url, title, 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, copyhistory=no, width='+w+', height='+h+', top='+top+', left='+left);
	} 

	// TODO
	$('#tw_login').click(function(){
	    window.open('$twUrl','','width=500,height=270,scrollbars=no,resize=false');
		return false;
	});
	// TODO
	/*$('#fb_login').click(function(){
	    window.open('$fbUrl','','width=500,height=270,scrollbars=no,resize=false');
		return false;
	});*/
	
	$('#ln_login').click(function(){
	    window.open('$lnUrl','','width=500,height=270,scrollbars=no,resize=false');
		return false;
	});
	
	// TODO
	/*$('#gp_login').click(function(){
	    window.open('$gpUrl','','width=500,height=270,scrollbars=no,resize=false');
		return false;
	});*/
	
	// TODO
	/*$('#bly_login').click(function(){
	    window.open('$bitlyUrl','','width=500,height=270,scrollbars=no,resize=false');
		return false;
	});*/
	
	$('#mc_login').click(function(){
	    window.open('$maUrl','','width=500,height=570,scrollbars=no,resize=false');
		return false;
	});
	/*
	$('#ig_login').click(function(){
	    window.open('$instaUrl','','width=500,height=270,scrollbars=no,resize=false');
		return false;
	});	*/

	$('.soc_page').click(function() {
		$.fancybox.open({
			padding : 10,
			href:$(this).attr('href'),
			type: 'iframe',
			width: 500,
			height: 420,
			transitionIn: 'elastic',
			transitionOut: 'elastic',
			autoSize: false
		});
		return false;
	});
JS;
$this->registerJs($initScript);
?>

<div class="connection">

    <div class="connection-popup">
        <ul>
            <!--TODO-->
           <!-- <?php /*if ($hasTwProfile): */?>
                <li class="connected"><?php /*echo Yii::t('messages', 'Twitter') */?>
                    - <?php /*echo $Profiles['Twitter']['name']; */?></li>
            <?php /*else: */?>
                <li><?php /*echo Yii::t('messages', 'Twitter') */?>
                    <?php /*if ($isPolitician): */?>
                        <button type="button" class="btn btn-primary float-right"
                                id="tw_login"><?php /*echo Yii::t('messages', 'Connect'); */?></button>
                    <?php /*endif; */?>
                </li>
            --><?php /*endif; */?>
            <?php /*if($hasFbProfile): */ ?><!--
                <li class="connected"><?php /*echo Yii::t('messages', 'Facebook') */ ?>
            <?php /*endif;*/ ?>-->
            <?php /*if($hasFbProfile): */ ?><!--
                <li class="connected"><?php /*echo Yii::t('messages', 'Facebook') */ ?>
                    <div class="profile">
                        <div class="pic">
                            <img width="50px" src="<?php /*echo $Profiles['Facebook']['profileImageUrl'] */ ?>">
                        </div>
                        <div class="text">
                            <div class="title"><?php /*echo $Profiles['Facebook']['name'] */ ?></div>
                            <?php /*if (!empty($pages)): */ ?>
                                <div class="pageName">
                                    <i class="fa fa-file-text-o" aria-hidden="true"></i>
                                    <?php /*echo $pages['name'] */ ?></div>
                            <?php /*else: */ ?>
                                <?php /*if($isPolitician):*/ ?>
                                    <div class="addpage">
                                        <a id="fb_page" class="btn btn-secondary soc_page" href="<?php /*echo Yii::$app->createUrl('site/fbPages') */ ?>">
                                            <?php /*echo Yii::t('messages','Add Page') */ ?></a>
                                    </div>
                                <?php /*endif; */ ?>
                            <?php /*endif; */ ?>
                        </div>
                    </div>
                </li>
            <?php /*else: */ ?>
                <li><?php /*echo Yii::t('messages', 'Facebook') */ ?>
                    <?php /*if($isPolitician):*/ ?>
                        <button type="button" class="btn btn-primary float-right" id="fb_login"><?php /*echo Yii::t('messages', 'Connect') */ ?></button>
                    <?php /*endif;*/ ?>
                </li>
            --><?php /*endif; */ ?>
            <?php if ($hasLnProfile): ?>
                <li class="connected"><?php echo Yii::t('messages', 'Linkedin') ?> -
                    <div class="profile">
                        <div class="pic">
                            <img style="
                            width: 40px;
                            display: block;
                            margin: auto;" src="<?php echo $Profiles['LinkedIn']['pictureUrl'] ?>">
                        </div>
                        <div class="text">
                            <div class="title"><?php echo implode(' ', array($Profiles['LinkedIn']['firstName'], $Profiles['LinkedIn']['lastName'])) ?></div>
                            <?php if (is_array($pages_ln)): ?>
                                <div class="pageName"><i class="fa fa-file-text-o"
                                                         aria-hidden="true"></i> <?php echo $pages_ln['name']; ?></div>
                            <?php else: ?>
                                <?php if ($isPolitician): ?>
                                    <div class="addpage">
                                        <a id="ln_page" class="btn btn-secondary soc_page"
                                           href="<?php echo Yii::$app->urlManager->createUrl('site/ln-pages') ?>"><?php echo Yii::t('messages', 'Add Page') ?></a>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </li>
            <?php else: ?>
                <li><?php echo Yii::t('messages', 'Linkedin') ?>
                    <?php if ($isPolitician): ?>
                        <button type="button" class="btn btn-primary float-right"
                                id="ln_login"><?php echo Yii::t('messages', 'Connect') ?></button>
                    <?php endif; ?>
                </li>
            <?php endif; ?>
            <!--TODO-->
            <?php  if($hasMcProfile):
                    $mcProfile = $Profiles['MailChimp'];
             ?>
                <li class="connected"><?php echo Yii::t('messages', 'MailChimp') ?> - <?php  echo $mcProfile[0]['loginName'] ?></li>
            <?php  else:  ?>
                <li><?php echo Yii::t('messages', 'MailChimp') ?>
                    <?php  if($isPolitician): ?>
                        <button type="button" class="btn btn-primary float-right" id="mc_login"><?php  echo Yii::t('messages', 'Connect') ?></button>
                    <?php  endif; ?>
                </li>
            <?php  endif;   ?>

            <?php /* if($hasBlyProfile): ?>
                <li class="connected"><?php  echo Yii::t('messages', 'Bitly') ?> - <?php  echo $Profiles['Bitly'][0]['fullName']  ?></li>
            <?php  else: ?>
                <li><?php echo Yii::t('messages', 'Bitly') ?>
                    <?php if($isPolitician): ?>
                        <button type="button" class="btn btn-primary float-right" id="bly_login"><?php echo Yii::t('messages', 'Connect') ?></button>
                    <?php  endif; ?>
                </li>
            <?php endif; */ ?>


        </ul>
    </div>

    <div class="title">
        <?php echo Yii::t('messages', 'Connections') ?>
    </div>
    <div class="social-btns">

        <div class="btn-group btn-group-toggle" data-toggle="buttons">
           <!-- <label class="btn-connections <?php /*echo $hasTwProfile ? "active" : "" */?>">
                <img src="<?php /*echo Yii::$app->toolKit->getImagePath() */?>social-twitter.svg">
            </label>-->
            <!--<label class="btn-connections <?php /*echo $hasFbProfile ? "active" : "" */ ?>">
                <img src="<?php /*echo Yii::$app->toolKit->getImagePath() */ ?>social-fb.svg">
            </label>-->
            <label class="btn-connections <?php echo $hasLnProfile ? "active" : "" ?>">
                <img src="<?php echo Yii::$app->toolKit->getImagePath() ?>social-in.svg">
            </label>
           <label class="btn-connections <?php echo $hasMcProfile ? "active" : "" ?>">
                <img src="<?php echo Yii::$app->toolKit->getImagePath() ?>social-mailchimp.svg">
            </label>
            <!-- <label class="btn-connections <?php /*echo $hasBlyProfile ? "active" : "" */?>">
                <img src="<?php /*echo Yii::$app->toolKit->getImagePath() */?>social-bitly.svg">
            </label>-->
        </div>

    </div>
</div>
