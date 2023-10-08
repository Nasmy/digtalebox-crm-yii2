<!doctype html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang=""> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8" lang=""> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9" lang=""> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang=""> <!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="language" content="en">
    <title><?php echo CHtml::encode($this->pageTitle); ?></title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!--favicon-->
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="<?php echo Yii::app()->toolKit->getImagePath() ?>favicon/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
    <link rel="apple-touch-icon" href="<?php echo Yii::app()->toolKit->getImagePath() ?>favicon/apple-icon-114x114.png">
    <link rel="icon" type="image/png" href="<?php echo Yii::app()->toolKit->getImagePath() ?>favicon/favicon-96x96.png">
    <link rel="manifest" href="<?php echo Yii::app()->toolKit->getImagePath() ?>favicon/manifest.json">
    <link rel="icon" href="<?php echo Yii::app()->toolKit->getImagePath() ?>favicon/favicon.ico" type="image/x-icon">
    <script src="<?php echo Yii::app()->theme->baseUrl; ?>/js/vendor/jquery-3.3.1.min.js"></script>
    <?php Yii::app()->toolKit->registerMainScripts(); ?>
    <style>
        .permissionError {
            width: 98%; height: 100%
        }
        .app-main {
            height: 100%;
        }
    </style>
</head>
<body class="<?php echo Yii::app()->session['themeStyle'] ?>">
<!--[if lt IE 8]>
<p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade
    your browser</a> to improve your experience.</p>
<![endif]-->
<div class="app-main">
    <div class="frame">
        <!-- app header php start -->
        <div class="app-header">
            <nav class="navbar fixed-top navbar-expand-lg navbar-light bg-light">

                <div class="menu-btn d-xl-none d-lg-none">
                    <div class="menu">
                        <i class="fa fa-bars fa-lg"></i>
                    </div>
                </div>
                <a class="navbar-brand" href="<?php echo Yii::app()->createUrl('Dashboard/Dashboard') ?>">
                    <img src="<?php echo Yii::app()->toolKit->getImagePath() ?>digitalebox-logo@2x.png">
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <div class="pic"><?php echo User::model()->getPic(null,30,30,"",Yii::app()->user->id) ?></div>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ml-auto">

                        <li class="nav-item dropdown header-dropdown desktop-hide">
                            <a class="nav-link" href="<?php echo Yii::app()->createUrl('User/MyAccount') ?>" id="navbarDropdown" role="button" aria-haspopup="true"
                               aria-expanded="false">
                                <i class="fa fa-user fa-2x"></i>
                                <div class="text"><?php echo Yii::t('messages','My Account') ?></div>
                            </a>
                        </li>

                        <li class="nav-item dropdown header-dropdown desktop-hide">
                            <a class="nav-link" href="<?php echo Yii::app()->createUrl('User/ChangePassword') ?>" id="navbarDropdown" role="button"
                               aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-lock fa-2x"></i>
                                <div class="text"><?php echo Yii::t('messages','Change Password') ?></div>
                            </a>
                        </li>

                        <li class="nav-item dropdown languages header-dropdown">
                            <a class="nav-link" href="#" title="Language" id="navbarDropdown" role="button"
                               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <div class="flag"><img src="<?php echo Yii::app()->toolKit->getImagePath() . Yii::app()->params['lang'][Yii::app()->language]['flagName'] ?>">
                                </div><div class="text desktop-hide"><?php echo Yii::t('messages','Languages') ?></div>
                                <div class="desktop-hide float-right"><i class="fa fa-angle-down"></i></div>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                <?php foreach (Yii::app()->params['lang'] as $key => $langInfo): ?>
                                    <a class="dropdown-item list-items <?php echo $key == Yii::app()->language? "active" : ""; ?>"
                                       href="<?php echo Yii::app()->createUrl('Site/ChangeLang',
                                           array('lang' => $langInfo['identifier'])); ?>">
                                        <div class="country-flag">
                                            <img src="<?php echo Yii::app()->toolKit->getImagePath() . $langInfo['flagName'] ?>">
                                        </div>
                                        <div class="message">
                                            <div class="name"><?php echo Yii::t('messages',$langInfo['name']); ?></div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </li>

                        <li class="nav-item dropdown header-dropdown">
                            <a class="nav-link" href="#" title="Messages" id="navbarDropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                <span class="badge msg"><?php echo App::getNewMessageCount() ?></span> <i class="fa fa-envelope
                        fa-2x"></i>
                                <div class="text desktop-hide"><?php echo Yii::t('messages','Messages') ?></div>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right mobile-hide" aria-labelledby="navbarDropdown">
                                <?php  $msgBoxModels = App::getMessageSummary();
                                if (null != $msgBoxModels):
                                    foreach ($msgBoxModels as $msgBoxModel): ?>
                                        <a class="dropdown-item messages list-items" href="<?php echo Yii::app()->createUrl('MsgBox/ViewInboxMsg',
                                            array('id' => $msgBoxModel->id)) ?>">
                                            <?php $modelUser = User::model()->findByPk($msgBoxModel->senderUserId);?>
                                            <div class="profile-pic"><?php echo User::model()->getPic($modelUser->profImage) ?></div>
                                            <div class="message">
                                                <div class="name"><?php echo implode(' ', array($modelUser->firstName,
                                                        $modelUser->lastName)) ?></div>
                                                <div class="text"><?php echo substr(strip_tags($msgBoxModel->message), 0, 15); ?></div>
                                            </div>
                                            <div class="date"><?php echo Feed::model()->getTimeElapsed($msgBoxModel->dateTime); ?></div>
                                        </a>
                                    <?php endforeach;?>
                                <?php endif;?>
                                <?php if(App::getNewMessageCount() > App::NOTIFICATION_MESSAGE_COUNT): ?>
                                    <a class="dropdown-item messages list-items see-all" href="<?php echo Yii::app()->createUrl('MsgBox/InBox') ?>">
                                        <div class="text-center"><?php echo Yii::t('messages','See All') ?></div>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </li>

                        <li class="nav-item dropdown header-dropdown desktop-hide">
                            <a class="nav-link" href="<?php echo Yii::app()->createUrl('Site/Logout') ?>" id="navbarDropdown" role="button"
                               aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-power-off fa-lg"></i>
                                <div class="text"><?php echo Yii::t('messages','Logout') ?></div>
                            </a>
                        </li>

                        <li class="nav-item dropdown header-dropdown mobile-hide">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <?php echo User::model()->getPic(null,30,30,"",Yii::app()->user->id) ?>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item user-profile" href="<?php echo Yii::app()->createUrl('User/MyAccount') ?>">
                                    <div class="pic"><?php echo User::model()->getPic(null,30,30,"",Yii::app()->user->id) ?></div>
                                    <div class="details">
                                        <div class="name"><?php echo implode(' ', array(Yii::app()->user->getState('firstName'), Yii::app()->user->getState('lastName'))) ?></div>
                                        <?php if(Yii::app()->user->getState('is_super_admin')): ?>
                                            <div class="role"><?php echo Yii::t('messages','superadmin') ?></div>
                                        <?php else: ?>
                                            <div class="role"><?php echo implode(',',Yii::app()->getModule("erbac")->getAssignedItems(Yii::app()->user->id,true)) ?></div>
                                        <?php endif;?>

                                    </div>
                                </a>
                                <a class="dropdown-item" href="<?php echo Yii::app()->createUrl('User/MyAccount') ?>"><i class="fa fa-user fa-lg"></i>
                                    <?php echo Yii::t('messages','My Account') ?></a>
                                <a class="dropdown-item" href="<?php echo Yii::app()->createUrl('User/ChangePassword') ?>"><i class="fa fa-lock fa-lg"></i>
                                    <?php echo Yii::t('messages','Change Password') ?></a>
                                <a class="dropdown-item" href="<?php echo Yii::app()->createUrl('Site/Logout') ?>"><i class="fa fa-power-off fa-lg"></i> <?php echo Yii::t('messages','Logout') ?></a>
                            </div>
                        </li>

                    </ul>
                </div>
            </nav>
        </div>
        <!-- app header php end -->
        <div class="app-body">
            <div class="permissionError">
                <div class="row mt-5">
                    <div class="container">
                        <?php echo $content; ?>
                    </div>
                </div>
            </div>


            <div class="app-footer"><?php echo Yii::t('messages','Copyright DigitaleBox © 2018') ?></div>
        </div>
    </div>
</div>
</body>
</html>