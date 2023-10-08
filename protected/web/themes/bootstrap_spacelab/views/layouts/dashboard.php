<?php
/* @var $this \yii\web\View */
/* @var $content string */

use app\models\CandidateInfo;
use app\models\Feed;
use app\widgets\Alert;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;
use yii\helpers\Url;
use app\models\User;
use app\models\AuthItem;
use app\models\App;
use app\components\View;
AppAsset::register($this);

$js = <<<JS
 
    $(document).on("pjax:beforeSend",function(e){
        jQuery('.grid-view').addClass('grid-view-loading');
    }).on("pjax:end",function(){
                jQuery('.grid-view').removeClass('grid-view-loading');
    });
JS;
$this->registerJs($js, View::POS_READY);

?>
<?php $this->beginPage() ?>
    <!doctype html>
    <!--[if lt IE 7]>
    <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang=""> <![endif]-->
    <!--[if IE 7]>
    <html class="no-js lt-ie9 lt-ie8" lang=""> <![endif]-->
    <!--[if IE 8]>
    <html class="no-js lt-ie9" lang=""> <![endif]-->
    <!--[if gt IE 8]><!-->
    <html class="no-js" lang=""> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta name="language" content="en">
        <title><?php echo Html::encode($this->title); ?></title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Html::csrfMetaTags() ?>

        <!--favicon-->
        <meta name="msapplication-TileColor" content="#ffffff">
        <meta name="msapplication-TileImage"
              content="<?php echo Yii::$app->toolKit->getImagePath() ?>favicon/ms-icon-144x144.png">
        <meta name="theme-color" content="#ffffff">
        <link rel="apple-touch-icon"
              href="<?php echo Yii::$app->toolKit->getImagePath() ?>favicon/apple-icon-114x114.png">
        <link rel="icon" type="image/png"
              href="<?php echo Yii::$app->toolKit->getImagePath() ?>favicon/favicon-96x96.png">
        <link rel="manifest" href="<?php echo Yii::$app->toolKit->getImagePath() ?>favicon/manifest.json">
        <link rel="icon" href="<?php echo Yii::$app->toolKit->getImagePath() ?>favicon/favicon.ico" type="image/x-icon">
        <script type="text/javascript" src="/themes/bootstrap_spacelab/js/jquery.min.js"></script>

        <?php Yii::$app->toolKit->registerMainScripts(); ?>
        <?php $this->head() ?>
    </head>
    <body class="<?php echo Yii::$app->session->get('themeStyle') ?>">
    <?php $this->beginBody() ?>
    <!--[if lt IE 8]>
    <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a
            href="http://browsehappy.com/">upgrade
        your browser</a> to improve your experience.</p>
    <![endif]-->
    <div class="app-main" id="app-main">
        <div class="frame">
            <!-- app header php start -->
            <div class="app-header">
                <nav class="navbar fixed-top navbar-expand-lg navbar-light bg-light">
                    <div class="menu-btn d-xl-none d-lg-none">
                        <div class="menu">
                            <i class="fa fa-bars fa-lg"></i>
                        </div>
                    </div>
                    <a class="navbar-brand"
                       href="<?php echo Yii::$app->urlManager->createUrl('dashboard/dashboard') ?>">
                        <img src="<?php echo Yii::$app->toolKit->getImagePath() ?>digitalebox-logo@2x.png">
                    </a>
                    <button class="navbar-toggler" type="button" data-toggle="collapse"
                            data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                            aria-expanded="false" aria-label="Toggle navigation">
                        <div class="pic"><?php echo User::getPic(null, 30, 30, "", Yii::$app->user->id) ?></div>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul class="navbar-nav ml-auto">

                            <li class="nav-item dropdown header-dropdown desktop-hide">
                                <a class="nav-link" href="<?php echo Yii::$app->urlManager->createUrl('user/my-account') ?>"
                                   id="navbarDropdown" role="button" aria-haspopup="true"
                                   aria-expanded="false">
                                    <i class="fa fa-user fa-2x"></i>
                                    <div class="text"><?php echo Yii::t('messages', 'My Account') ?></div>
                                </a>
                            </li>
                            <li class="nav-item dropdown header-dropdown desktop-hide">
                                <a class="nav-link" href="<?php echo Yii::$app->urlManager->createUrl('user/change-password') ?>" id="navbarDropdown" role="button"
                                   aria-haspopup="true" aria-expanded="false">
                                    <i class="fa fa-lock fa-2x"></i>
                                    <div class="text"><?php echo Yii::t('messages', 'Change Password') ?></div>
                                </a>
                            </li>
                            <li class="nav-item dropdown languages header-dropdown">
                                <a class="nav-link" href="#" title="Language" id="navbarDropdown" role="button"
                                   data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <div class="flag"><img src="<?php echo Yii::$app->toolKit->getImagePath() . Yii::$app->params['lang'][Yii::$app->language]['flagName'] ?>">
                                    </div><div class="text desktop-hide"><?php echo Yii::t('messages', 'Languages') ?></div>
                                    <div class="desktop-hide float-right"><i class="fa fa-angle-down"></i></div>
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
                            </li>
                            <!--<li class="nav-item dropdown header-dropdown">-->
                                <!--for mobile-->
                                <!--<a class="nav-link desktop-hide" href="<?php // echo Yii::$app->urlManager->createUrl('MsgBox/InBox'); ?>"
                                   title="Messages" id="navbarDropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                    <span class="badge msg"><?php // echo App::getNewMessageCount() ?></span>
                                    <i class="fa fa-envelope fa-lg"></i>
                                    <div class="text"><?php // echo Yii::t('messages', 'Messages') ?></div>
                                </a>-->

                                <!--for desktop-->
                                <!--<a class="nav-link mobile-hide" href="#" title="Messages" id="navbarDropdown" data-toggle="dropdown"
                                   role="button" aria-haspopup="true" aria-expanded="false">
                                    <span class="badge msg"><?php // echo App::getNewMessageCount() ?></span>
                                    <i class="fa fa-envelope fa-lg"></i>
                                </a>-->

                                <!--<div class="dropdown-menu dropdown-menu-right mobile-hide" aria-labelledby="navbarDropdown">
                                    <?php
                                    /*$msgBoxModels = App::getMessageSummary();
                                    $feed = new Feed();
                                    if (null != $msgBoxModels):
                                        foreach ($msgBoxModels as $msgBoxModel):
                                            ?>
                                            <a class="dropdown-item messages list-items" href="<?php echo Yii::$app->urlManager->createUrl(['msg-box/view-inbox-msg','id' => $msgBoxModel['id']])
                                            ?>">
                                                <?php $modelUser = User::findOne($msgBoxModel['senderUserId']); ?>
                                                <div class="profile-pic"><?php echo User::getPic($modelUser->profImage) ?></div>
                                                <div class="message">
                                                    <div class="name"><?php echo implode(' ', array($modelUser->firstName,
                                                            $modelUser->lastName))
                                                        ?></div>
                                                    <div class="text"><?php echo substr(strip_tags($msgBoxModel['message']), 0, 15); ?></div>
                                                </div>
                                                <div class="date mt-1" style="white-space: normal;line-height:12px"><?php echo $feed->getTimeElapsed($msgBoxModel['dateTime']); ?></div>
                                            </a>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    <?php if (App::getNewMessageCount() > App::NOTIFICATION_MESSAGE_COUNT): ?>
                                        <a class="dropdown-item messages list-items see-all" href="<?php echo Yii::$app->urlManager->createUrl('msg-box/inbox') ?>">
                                            <div class="text-center"><?php echo Yii::t('messages', 'See All') ?></div>
                                        </a>
                                    <?php endif; */?>
                                </div>
                            </li>-->
                            <li class="nav-item dropdown header-dropdown desktop-hide">
                                <a class="nav-link" href="<?php echo Yii::$app->urlManager->createUrl('site/logout') ?>" id="navbarDropdown" role="button"
                                   aria-haspopup="true" aria-expanded="false">
                                    <i class="fa fa-power-off fa-lg"></i>
                                    <div class="text"><?php echo Yii::t('messages', 'Logout') ?></div>
                                </a>
                            </li>

                            <li class="nav-item dropdown header-dropdown mobile-hide">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                                   data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <?php echo User::getPic(null, 30, 30, "", Yii::$app->user->id) ?>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item user-profile" href="<?php if (Yii::$app->user->checkAccess('User.Admin')) {
                                        echo Yii::$app->urlManager->createUrl('user/my-account');
                                    } else {
                                        echo "#";
                                    } ?>">
                                        <div class="pic"><?php echo User::getPic(null, 30, 30, "", Yii::$app->user->id) ?></div>
                                        <div class="details">
                                            <div class="name"><?php echo implode(' ', array(Yii::$app->user->identity->firstName, Yii::$app->user->identity->lastName)) ?></div>
                                            <?php if (Yii::$app->session->get('is_super_admin')): ?>
                                                <div class="role"><?php echo Yii::t('messages', 'superadmin') ?></div>
                                            <?php else: ?>
                                                <div class="role"><?php echo implode(',', Yii::$app->user->getAssignedItems(Yii::$app->user->id, true)) ?></div>
                                            <?php endif; ?>

                                        </div>
                                    </a>
                                    <?php if (Yii::$app->user->checkAccess('User.Admin')) { ?>
                                        <a class="dropdown-item" href="<?php echo Yii::$app->urlManager->createUrl('user/my-account') ?>"><i class="fa fa-user fa-lg"></i>
                                            <?php echo Yii::t('messages', 'My Account') ?></a>

                                        <a class="dropdown-item" href="<?php echo Yii::$app->urlManager->createUrl('user/change-password') ?>"><i class="fa fa-lock fa-lg"></i>
                                            <?php echo Yii::t('messages', 'Change Password') ?></a>
                                    <?php } ?>
                                    <a class="dropdown-item" href="<?php echo Yii::$app->urlManager->createUrl('site/logout') ?>"><i class="fa fa-power-off fa-lg"></i> <?php echo Yii::t('messages', 'Logout') ?></a>
                                </div>
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>
            <!-- app header php end -->
            <div class="app-body">
                <?= $content; ?>
                <div class="app-footer"><?php echo Yii::t('messages', 'Copyright DigitaleBox © 2021') ?></div>
            </div>
        </div>
    </div>
    <?php $this->endBody() ?>
    </body>
    </html>
<?php $this->endPage() ?>
