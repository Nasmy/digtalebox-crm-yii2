<?php

use app\components\RbacAuthManager;
use app\components\SocialAuths;
use app\components\ThresholdChecker;
use app\components\ToolKit;
use app\models\AuthItem;
use app\widgets\Alert;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;
use yii\helpers\Url;
use yii\widgets\Menu;
use app\models\App;
use app\components\View;


$js = <<<JS
 
    $(document).on("pjax:beforeSend",function(e){
        jQuery('.grid-view').addClass('grid-view-loading');
    }).on("pjax:end",function(){
        jQuery('.grid-view').removeClass('grid-view-loading');
    }).on("pjax:complete",function() {
        jQuery('.grid-view').addClass('grid-view-loading');
    });
 
 
JS;
$this->registerJs($js, View::POS_READY);

?>
<?php $this->beginContent('@app/web/themes/bootstrap_spacelab/views/layouts/dashboard.php'); ?>
<style type="text/css">
    div.ajaxloading {
        width: 40px;
        height: 25px;
        background-color: #fff;
        background-image: url('/themes/bootstrap_spacelab/img/loading.gif');
        background-position: left;
        background-repeat: no-repeat;
        display: none;
        margin-right: 0 !important;
        float: left;
    }
</style>
<div id="side-bar" class="side-bar">
    <nav class="sidebar-nav" id="slimscrolldiv">
        <?php $portalSettingsLandingAction = '';
        if (Yii::$app->user->checkAccess('CandidateInfo.ManageImagesf')) {
            $portalSettingsLandingAction = array('/candidate-info/manage-images/');
        } else if (Yii::$app->user->checkAccess('CandidateInfo.Theme')) {
            $portalSettingsLandingAction = array('/candidate-info/theme/');
        } else if (Yii::$app->user->checkAccess('CandidateInfo.UpdateTexts')) {
            $portalSettingsLandingAction = array('/candidate-info/update-texts/');
        } else if (Yii::$app->user->checkAccess('CandidateInfo.ChangeBgImage')) {
            $portalSettingsLandingAction = array('/candidate-info/change-bg-image/');
        }
        ?>

        <?php $action = Yii::$app->controller->id . '.' . Yii::$app->controller->action->id; ?>
        <?php
        // Get current selected language for create help center url by language selection
        $language = explode('-', Yii::$app->language);
        $language = $language[0];
        ?>
        <?=
        Menu::widget([
            'encodeLabels' => false,
            'items' => [
                // Important: you need to specify url as 'controller/action',
                // not just as 'controller' even if default action is used.
                ['label' => '<span class="fa fa-fw fa-th-large"></span> ' . Yii::t('messages', 'Dashboard'), 'url' => ['dashboard/dashboard']],
                // 'Products' menu item will be selected as long as the route is ' roduct/index'
                [
                    'label' => '<span class="fa fa-fw fa-users"></span> ' . Yii::t('messages', 'People'),
                    'url' => array('#'),
                    'visible' => Yii::$app->user->checkAccessList(
                        array('People.Create', 'AdvancedSearch.Admin', 'SearchCriteria.Admin', 'Keyword.Admin', 'AdvancedBulkInsert.Admin', 'People.Statistics', 'Feed.SocialFeed', 'People.Volunteers', 'Resource.Admin', 'FormMembershipDonation.Admin', 'FriendFind.Admin')),
                    'active' => in_array(Yii::$app->controller->id, array(
                        'people',
                        'advancedSearch',
                        'potentialMatches',
                        'searchCriteria',
                        'keyword',
                        'feed',
                        'team',
                        'teamMember',
                        'teamZone',
                        'resource',
                        'activity',
                        'donation',
                        'donationUser',
                        'formMembershipDonation',
                        'friendFind',
                        'broadcastMessage',
                        'advancedBulkInsert',
                    )),
                    'options' => array('id' => 'people', 'class' => 'has-arrow'),
                    'items' => array(
                        array(
                            'label' => Yii::t('messages', 'Add People'),
                            'visible' => Yii::$app->user->checkAccess('People.Create'),
                            'url' => array('/people/create'),
                            'options' => array('id' => 'people_create')
                        ),
                        array(
                            'label' => Yii::t('messages', 'Advanced Search'),
                            'active' => in_array(Yii::$app->controller->id, array('advancedSearch')),
                            'visible' => Yii::$app->user->checkAccess('AdvancedSearch.Admin'),
                            'url' => array('/advanced-search/admin'),
                            'options' => array('id' => 'advanced_srch')
                        ),
                        array(
                            'label' => Yii::t('messages', 'Account Merge'),
                            'visible' => Yii::$app->user->checkAccess('PotentialMatches.Admin') && Yii::$app->user->checkPackageAccess('PotentialMatches.Admin'),
                            'url' => array('/potential-matches/admin'),
                            'options' => array('id' => 'potential_match')
                        ),
                        array(
                            'label' => Yii::t('messages', 'Saved Search'),
                            'visible' => Yii::$app->user->checkAccess('SearchCriteria.Admin'),
                            'url' => array('/search-criteria/admin'),
                            'options' => array('id' => 'saved_srch')
                        ),
                        array(
                            'label' => Yii::t('messages', 'Manage Keywords'),
                            'active' => in_array(Yii::$app->controller->id, array('keyword')),
                            'visible' => Yii::$app->user->checkAccess('Keyword.Admin'),
                            'url' => array('/keyword/admin'),
                            'options' => array('id' => 'keywords')
                        ),
                        array(
                            'label' => Yii::t('messages', 'Advance Bulk Insert'),
                            'active' => in_array(Yii::$app->controller->id, array('advancedBulkInsert')),
                            'visible' => Yii::$app->user->checkAccess("AdvancedBulkInsert.Admin"),
                            'url' => array('/advanced-bulk-insert/admin'),
                            'options' => array('id' => 'advance_bulk_insert')
                        ),
//                        array(
//                            'label' => Yii::t('messages', 'Statistics'),
//                            'active' => in_array(Yii::$app->controller->action->id,
//                                array('Statistics', 'Analytics', 'Posts', 'Population')),
//                            'visible' => Yii::$app->user->checkAccess("People.Statistics"),
//                            'url' => array('/people/statistics'),
//                            'options' => array('id' => 'stats')
//                        ),
                        /*
                        Hided due to client request  16-12-2020
                        DBYII2-88-Communciation-Social-Activites-hide
**//*
                            array(
                                 'label' => Yii::t('messages', 'Social Activities'),
                                 'active' => in_array(Yii::$app->controller->id, array('feed', 'broadcastMessage')),
                                 'visible' => Yii::$app->user->checkAccessList(array('Feed.SocialFeed')),
                                 'url' => array('/feed/social-feed'),
                                 'options' => array('id' => 'social_activity')
                             ),*/

                        /**    Hided due to client request  06-10-2020
                         * array(
                         * 'label' => Yii::t('messages', 'Volunteers'),
                         * 'visible' => Yii::$app->user->checkAccess('People.Volunteers'),
                         * 'url' => array('/people/volunteers'),
                         * 'options' => array('id' => 'volunteers')
                         * ),*/
                        array(
                            'label' => Yii::t('messages', 'Resource'),
                            'active' => in_array(Yii::$app->controller->id, array('resource')),
                            'visible' => Yii::$app->user->checkAccess('Resource.Admin') && Yii::$app->user->checkPackageAccess('Resource.Admin'),
                            'url' => array('/resource/admin'),
                            'options' => array('id' => 'resource')
                        ),
                        array(
                            'label' => Yii::t('messages', 'Activities'),
                            'active' => in_array(Yii::$app->controller->id, array('Activity.Admin')),
                            'visible' => Yii::$app->user->checkAccess('Activity.Admin') && Yii::$app->user->checkPackageAccess('Activity.Admin'),
                            'url' => array('/activity/admin'),
                            'options' => array('id' => 'activity')
                        ),
                        array(
                            'label' => Yii::t('messages', 'Donations & Membership'),
                            'active' => in_array(Yii::$app->controller->id, array('formMembershipDonation')),
                            'visible' => Yii::$app->user->checkAccess('FormMembershipDonation.Admin'),
                            'url' => array('/form-membership-donation/admin'),
                            'options' => array('id' => 'donation')
                        ),
                        array(
                            'label' => Yii::t('messages', 'Membership'),
                            'active' => in_array(Yii::$app->controller->id, array('membership')),
                            'visible' => false
                            /*Yii::$app->user->checkAccess('Membership.Admin')*/,
                            'url' => array('/Membership/Admin'),
                            'options' => array('id' => 'membership')
                        )
                    ),
                ],
                [
                    'options' => array('id' => 'communication', 'class' => 'has-arrow'),
                    'label' => '<span class="fa fa-fw fa-bullhorn"></span> ' . Yii::t('messages', 'Communication'),
                    'active' => in_array(Yii::$app->controller->id,
                        array(
                            'messageTemplate',
                            'campaign',
                            'msgBox',
                            'campaignUsers',
                            'keywordUrl',
                            'aBTesting',
                            'event',
                        )),
                    'url' => array('#'),
                    'visible' => Yii::$app->user->checkAccessList(
                        array('MessageTemplate.Admin', 'SendBulkMessages', 'aBTesting', 'Campaign.Admin', 'msgBox.InBox')),
                    'items' => array(
                        array(
                            'label' => Yii::t('messages', 'Manage Events'),
                            'active' => in_array(Yii::$app->controller->id, array('event')),
                            'visible' => Yii::$app->user->checkAccess('Event.Admin'),
                            'url' => array('/event/admin'),
                            'options' => array('id' => 'events')
                        ),
                        array(
                            'label' => Yii::t('messages', 'Message Templates'),
                            'active' => in_array(Yii::$app->controller->id, array('messageTemplate')),
                            'visible' => Yii::$app->user->checkAccess('MessageTemplate.Admin'),
                            'url' => array('/message-template/admin'),
                            'options' => array('id' => 'msg-template')
                        ),
                        array(
                            'label' => Yii::t('messages', 'New Campaign'),
                            'visible' => Yii::$app->user->checkAccess('SendBulkMessages'),
                            'active' => in_array($action, array('campaign.CreateCamp')),
                            'url' => array('/campaign/create-camp'),
                            'options' => array('id' => 'newCampaign')
                        ),
                        /*   Hided due to client request  06-10-2020
                         array(
                            'label' => Yii::t('messages', 'Create A/B Testing'),
                            'visible' => Yii::$app->user->checkAccess('aBTesting'),
                            'active' => in_array($action, array('aBTesting.CreateCamp')),
                            'url' => array('/a-b-testing/create-camp'),
                            'options' => array('id' => 'aBCampaign')
                        ),*/
                        array(
                            'label' => Yii::t('messages', 'Sent Campaigns'),
                            'visible' => Yii::$app->user->checkAccess('Campaign.Admin'),
                            'active' => in_array($action, array('campaign.Admin', 'campaignUsers.Admin')),
                            'url' => array('/campaign/admin'),
                            'options' => array('id' => 'campaigns')
                        ),
                        array(
                            'label' => Yii::t('messages', 'Social Media'),
                            'active' => in_array(Yii::$app->controller->id, array('feed', 'broadcastMessage')),
                            'visible' => Yii::$app->user->checkAccessList(array('Feed.SocialFeed')),
                            'url' => array('/broadcast-message/admin'),
                            'options' => array('id' => 'social_media')
                        ),

                        /* Hided due to client request  06-10-2020
                         array(
                             'label' => Yii::t('messages', 'Message Box'),
                             'active' => in_array(Yii::$app->controller->id, array('msgBox')),
                             'visible' => Yii::$app->user->checkAccess('MsgBox.InBox'),
                             'url' => array('/msg-box/inbox'),
                             'options' => array('id' => 'msg-box')
                         ),*/
                        array(
                            'label' => Yii::t('messages', 'Keyword Url'),
                            'active' => in_array(Yii::$app->controller->id, array('keywordUrl')),
                            'visible' => Yii::$app->user->checkAccess('KeywordUrl.Admin'),
                            'url' => array('/keyword-url/admin'),
                            'options' => array('id' => 'KeywordUrl')
                        ),
                    )
                ],
                [
                    'options' => array('id' => 'system', 'class' => 'has-arrow'),
                    'label' => '<span class="fa fa-fw fa-cogs"></span> ' . Yii::t('messages', 'System'),
                    'visible' => Yii::$app->user->checkAccessList(
                        array('Erbac.Authitem.Admin', 'User.Admin', 'CandidateInfo.Update', 'FeedSearchKeyword.Admin', 'Configuration.Update', 'CandidateInfo.Theme', 'CandidateInfo.UpdateTexts', 'CandidateInfo.ManageImages', 'CandidateInfo.ChangeBgImage', 'CustomField.Admin', 'Form.Admin', 'MembershipType.Admin')),
                    'active' => in_array(Yii::$app->controller->id, array(
                        'authItem',
                        'user',
                        'candidateInfo',
                        'adBannerData',
                        'feedSearchKeyword',
                        'configuration',
                        'customField',
                        'form'
                    )),
                    'url' => array('#'),
                    'items' => array(
                        array(
                            'label' => Yii::t('messages', 'Manage Permissions'),
                            'visible' => Yii::$app->session->get('is_super_admin'),
                            'url' => ['/auth-item/admin', 'type' => AuthItem::TYPE_OPERATION]
                        ),
                        array(
                            'label' => Yii::t('messages', 'Manage Roles'),
                            'visible' => Yii::$app->user->checkAccess('Erbac.Authitem.Admin'),
                            'url' => ['/auth-item/admin', 'type' => AuthItem::TYPE_ROLE],
                            'options' => array('id' => 'mng-roles')
                        ),
                        array(
                            'label' => Yii::t('messages', 'Manage System Users'),
                            'active' => in_array(Yii::$app->controller->id, array('user')),
                            'visible' => Yii::$app->user->checkAccess('User.Admin'),
                            'url' => array('/user/admin'),
                            'options' => array('id' => 'mng-sys-users')
                        ),
                        /*array(
                            'label' => Yii::t('messages', 'Volunteer Portal'),
                            'active' => in_array(Yii::$app->controller->id . '.' . Yii::$app->controller->action->id,
                                array('candidateInfo')),
                            'visible' => Yii::$app->user->checkAccess("CandidateInfo.Update") && Yii::$app->user->checkPackageAccess('CandidateInfo.Update'),
                            'url' => array('/candidate-info/check-candidate-info'),
                            'options' => array('class' => 'visible-desktop', 'id' => 'org-info')
                        ),*/
                        /*  Hided due to client request  06-10-2020

                         array(
                              'label' => Yii::t('messages', 'Feed Keywords'),
                              'active' => in_array(Yii::$app->controller->id, array('feedSearchKeyword')),
                              // 'visible' => Yii::$app->user->checkAccess("FeedSearchKeyword.Admin"),
                              'url' => array('/feed-search-keyword/admin'),
                              'options' => array('id' => 'feed-keywords')
                          ),*/
                        array(
                            'label' => Yii::t('messages', 'Configurations'),
                            'visible' => Yii::$app->user->checkAccess("Configuration.Update"),
                            'url' => array('/configuration/update'),
                            'options' => array('id' => 'config')
                        ),
                        array(
                            'label' => Yii::t('messages', 'Portal Settings'),
                            'visible' => Yii::$app->user->checkAccessList(array(
                                "CandidateInfo.Theme",
                                "CandidateInfo.UpdateTexts",
                                "CandidateInfo.ManageImages",
                                "CandidateInfo.ChangeBgImage"
                            )),
                            // 'visible' => Yii::$app->user->checkAccess("CandidateInfo.Update") && Yii::$app->user->checkPackageAccess('CandidateInfo.Update'),
                            'active' => in_array(Yii::$app->controller->id . '.' . Yii::$app->controller->action->id,
                                array(
                                    'candidateInfo.ManageImages',
                                    'candidateInfo.UpdateTexts',
                                    'candidateInfo.Theme',
                                    'candidateInfo.ChangeBgImage'
                                )),
                            'url' => $portalSettingsLandingAction,
                            'options' => array('id' => 'portal-settings')
                        ),
                        array(
                            'label' => Yii::t('messages', 'Custom Fields'),
                            'visible' => Yii::$app->user->checkAccess("CustomField.Admin"),
                            'url' => array('/custom-field/admin'),
                            'options' => array('id' => 'custom-fields')
                        ),
                        array(
                            'label' => Yii::t('messages', 'Form Builder'),
                            'visible' => Yii::$app->user->checkAccessList(array("Form.Admin","FormBuilder.Admin")),
                            'url' => array('/form-builder/admin'),
                            'options' => array('id' => 'form-builder')
                        ),

                        array(
                            'label' => Yii::t('messages', 'Membership Types'),
                            'visible' => Yii::$app->user->checkAccess("MembershipType.Admin"),
                            'url' => array('/membership-type/admin'),
                            'options' => array('id' => 'form-builder')
                        ),

                    )
                ],
                [
                    'label' => Yii::t('messages', 'Help center'),
                    'options' => array('class' => 'enable', 'onclick' => 'return true;'),
                    'template' => '<a href="https://digitalebox.info/doc/' . $language . '" target="_blank"><span class="fa fa-fw fa-compass"></span>' . Yii::t("messages", "Help center") . '</a>',
                ],

            ],
            'options' => [
                'class' => 'metismenu',
                'id' => 'menu1',
                'style' => 'font-size: 14px;',
                'data-tag' => 'yii2-menu',
            ],
        ]);
        ?>
    </nav>
    <?php
    /*NavBar::begin([
        'brandLabel' => Yii::$app->name,
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => [
            ['label' => 'Home', 'url' => ['/site/index']],
            ['label' => 'About', 'url' => ['/site/about']],
            ['label' => 'Contact', 'url' => ['/site/contact']],
            Yii::$app->user->isGuest ? (
            ['label' => 'Login', 'url' => ['/site/login']]
            ) : (
                '<li>'
                . Html::beginForm(['/site/logout'], 'post')
                . Html::submitButton(
                    'Logout (' . Yii::$app->user->identity->username . ')',
                    ['class' => 'btn btn-link logout']
                )
                . Html::endForm()
                . '</li>'
            )
        ],
    ]);
    NavBar::end();*/
    ?>
    <div class="account-section">

        <?php
        $tc = new ThresholdChecker(Yii::$app->session->get('packageType'), Yii::$app->session->get('smsPackageType'));
        $tc->renewDate = (isset(Yii::$app->session)) ? (!ToolKit::isEmpty(Yii::$app->session->get('renewDate'))) ? Yii::$app->session->get('renewDate') : '' : '';
        $tc->renewedDate = (isset(Yii::$app->session)) ? (!ToolKit::isEmpty(Yii::$app->session->get('renewedDate'))) ? Yii::$app->session->get('renewedDate') : '' : '';
        $usage = array(
            'emailContacts' => array(
                'max' => isset($tc->packageInfo['totalEmailContacts']) ? $tc->packageInfo['totalEmailContacts'] : 1,
                'used' => $tc->getCount(ThresholdChecker::EMAIL_CONTACTS)
            ),
            'socialContacts' => array(
                'max' => isset($tc->packageInfo['totalSocialContacts']) ? $tc->packageInfo['totalSocialContacts'] : 1,
                'used' => $tc->getCount(ThresholdChecker::SOCIAL_CONTACTS)
            ),
            'smsLimit' => array(
                'max' => isset($tc->packageInfo['monthlySmsLimit']) ? $tc->packageInfo['monthlySmsLimit'] : 1,
                'used' => $tc->getCount(ThresholdChecker::MONTH_SMS_LIMIT)
            )
        );

        $package = array(
            'packageTypeId' => isset($tc->packageInfo['PackageTypeId']) ? $tc->packageInfo['PackageTypeId'] : 1,
            'packageName' => isset($tc->packageInfo['name']) ? $tc->packageInfo['name'] : ''
        );

        echo yii\base\View::render("@app/views/site/_usagePanel", array(
            'usage' => $usage,
            'package' => $package,
        ));
        ?>

        <!-- display connection widgets. -->
        <?php echo SocialAuths::widget(); ?>

    </div>
</div>
<div class="app-content">
    <!--Config panel-->
    <div class="config-panel">
        <div class="panel-toggle">
            <i class="fa fa-cogs"></i>
        </div>
        <div class="panel">
            <div class="section-head mb-3">Themes</div>
            <div class="themes" id="configPanel">
                <?php foreach (Yii::$app->params['themes'] as $key => $theme) { ?>
                    <div class="title"><?php echo $theme['themeName']; ?></div>
                    <a class="portal-setting-images theme" data-theme="<?php echo $theme['configTheme'] ?>" href="">
                        <img class="object-fit_cover config-theme" data-theme="<?php echo $key ?>"
                             href="<?php echo Yii::$app->urlManager->createUrl('CandidateInfo/ApplyTheme', array('id' => $key)); ?>"
                             src="<?php echo Yii::$app->toolKit->getImagePath() . $theme['thumbnail'] ?>"/></a>
                <?php } ?>
            </div>
        </div>
    </div>
    <?php

    if (null != $this->title): ?>
        <div class="page-header">
            <div class="row">
                <div class="col-md-6">
                    <div class="title">
                        <?php echo $this->title ?>
                    </div>
                    <div class="desc">
                        <?php echo $this->titleDescription ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="main-breadcrumb pt-3 text-left text-md-right">
                        <?= Breadcrumbs::widget([
                            'links' => [
                                'template' => "<a href='{link}'>{link}</a>\n",
                            ],
                            'activeItemTemplate' => "<span>{link}</span>\n",
                            'itemTemplate' => "{link}<span class=\"divider\">/</span>\n",
                            'options' => ['class' => 'breadcrumbs'],
                            'tag' => 'div',
                            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
                        ]);

                        ?>

                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <div id="statusMsg"></div>
    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success alert-dismissable">
            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
            <?= Yii::$app->session->getFlash('success') ?>
        </div>
    <?php endif; ?>

    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger alert-dismissable">
            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
            <?= Yii::$app->session->getFlash('error') ?>
        </div>
    <?php endif; ?>
    <div>
        <div class="row no-gutters">

            <div class="content-panel col-md-12">
                <?= $content; ?>
            </div>
        </div>
    </div>

</div>

<?php
/*$this->registerJs(<<<JS

 setInterval(function() {
    scrollPos=$('#pjax-list').scrollTop();
     $.pjax.reload({container: '#pjax-list'});
 },10000);


$(document).on('pjax:send', function() {
   $('.ajaxloading').show();
});
$(document).on('pjax:complete', function() {
  $('.ajaxloading').hide();
});
JS
);*/
?>
<?php $this->endContent(); ?>
