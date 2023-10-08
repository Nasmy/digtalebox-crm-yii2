<?php
/**
 * Created by PhpStorm.
 * User: nasmy
 * Date: 7/22/2019
 * Time: 4:17 PM
 */

namespace app\components;

use app\models\Country;
use Mailjet\Client;
use weluse\mailjet\Mailer;
use yii\base\ErrorException;
use yii\db\Exception;
use yii\helpers\Url;

use app\assets\AppAsset;
use app\models\Activity;
use app\models\App;
use app\models\CandidateInfo;
use app\models\Configuration;
use Yii;
use yii\base\Component;
use app\models\User;
use yii\db\Query;
use yii\helpers\Html;
use yii\web\HttpException;
use yii\web\ServerErrorHttpException;
use yii\web\YiiAsset;
use yii\web\View;


class ToolKit extends Component
{
    const OS_WINDOWS = 1;
    const OS_LINUX = 2;

    /**
     * Domain of the appliation ex:mahinda.digitalebox.com, ranil.digitalebox.com
     */
    public $domain = '';

    /**
     * Politician wise resource maintain folder path.Ex:images, email templates etc..
     */
    public $resourcePathUrl = '';

    /**
     * Application id
     */
    public $appId = null;

    /**
     * Resource path relative
     */
    public $resourcePathRelative = null;

    /**
     * Resource path absolute
     */
    public $resourcePathAbsolute = null;

    /**
     * Operating system which this application run
     */
    public $osType = self::OS_WINDOWS;

    /**
     * Whether to retrieve email campaign id from Mailjet API
     */
    public $retrieveEmailCampaignId = false;

    /**
     * Email campaign id that return from Mailjet API
     */
    public $emailCampaignId = null;

    /**
     * Email message id that return from Mailjet API
     */
    public $emailTransactionId = null;

    public $campaignId = null;

    /**
     * Mailjet username
     */
    public $mailJetUsername = null;

    /**
     * Mailjet password
     */
    public $mailJetPassword = null;


    public function init()
    {
        $domain = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : Yii::$app->params['masterDomain'];
        $this->domain = $domain;
        $this->setOsType();
    }

    /**
     * Register main css and javascripts requred by the system.
     */
    public function registerMainScripts()
    {
        return $this->registerThemeStyle();

    }

    /**
     * Register theme specific style
     */
    public function registerThemeStyle()
    {
        // Register theme style
        if (!Yii::$app->session->get('themeStyle')) {
            $model = CandidateInfo::find()->one();
            $themeInfo = Yii::$app->params['themes'][$model->themeStyle];
            Yii::$app->session->set('themeStyle', $themeInfo['class']);
        }
    }

    /**
     * Register background image style
     */
    public function registerBackgroundImage()
    {
        if (!isset(Yii::$app->session['bgImage'])) {
            $model = new CandidateInfo();
            Yii::$app->session->set('bgImage', $model->find()->one()->bgImage);
            $bgImage = $model->find()->one()->bgImage;
        } else {
            $bgImage = Yii::$app->session['bgImage'];
        }
        if (null != $bgImage) {
            $this->setResourceInfo();

            $defBgImagePath = "{$this->getImagePath()}def_bg.jpg";
            $customBgImagePathAbs = "{$this->resourcePathAbsolute}{$bgImage}";
            $customBgImagePathRel = '/' . $this->resourcePathRelative . "{$bgImage}";

            $bgImagePath = $defBgImagePath;

            if (is_file($customBgImagePathAbs)) {
                $bgImagePath = $customBgImagePathRel;
            }

            $bgImagePath = "{$bgImagePath}";
            echo Yii::$app->view->registerCss('body{
            
               background-image:url(' . $bgImagePath . ') !important;
            }');
        }
    }

    /**
     * Register volunteer portal a specific theme style
     */
    public function registerVolunteerThemeStyle()
    {
        $model = CandidateInfo::find()->one();
        if (!is_null($model->volunteerBgImageName)) {
            $path = Yii::$app->toolKit->resourcePathRelative;
            $imagePath = "{$path}$model->volunteerBgImageName";
            $bgImagePath = "/{$imagePath}?rand=" . rand(100, 200);
            Yii::$app->view->registerCss("
				body {
					background-image:url({$bgImagePath}) !important;
				}
			");
        }
    }

    /**
     * Register donation page a specific theme style
     */
    public function registerDonationPageThemeStyle($id)
    {
        $model = Donation::model()->findByPk($id);
        if (!is_null($model->bgImageName)) {
            $path = Yii::$app->toolKit->resourcePathRelative;
            $imagePath = "{$path}$model->bgImageName";
            $bgImagePath = "/{$imagePath}?rand=" . rand(100, 200);
            Yii::$app->getClientScript()->registerCssFile('bgDonationCss', "
				body {
					background-image:url({$bgImagePath}) !important;
					background-repeat: no-repeat !important;
					background-size: cover !important;
				}
			");
        }
    }

    /**
     * Retrieve system features to be shown on tour guide and freemium user
     */
    public function getSystemFeatureList()
    {
        $featureList = array(
            // Home
            // array('mmName'=>'main', 'id'=>'home', 'image'=>'home.jpg',
            // 'message'=>Yii::t('messages', 'Connect your social networks with DigitaleBox. Most of the system features depend on the networks you have connected.'),
            // 'freemium'=>array('show'), 'cm'=>array('show'), 'showTip'=>false, 'title'=>Yii::t('messages', 'Home')
            // ),

            // Dashboard
            array('mmName' => 'main', 'id' => 'dash', 'image' => 'dashboard.jpg',
                'message' => Yii::t('messages', 'DigitaleBox at a glance, highlights features such as Social activities, social feeds, statistics, etc...'),
                'freemium' => array('show'), 'cm' => array('show'), 'showTip' => true, 'title' => Yii::t('messages', 'Dashboard')
            ),

            // People
            array('mmName' => 'main', 'id' => 'people', 'image' => 'people.jpg',
                'message' => Yii::t('messages', 'Publish and Schedule your Social Media content, Manage all social media interactions, Organize your data & contacts.'),
                'freemium' => array('show'), 'cm' => array('show'), 'title' => Yii::t('messages', 'People'), 'showTip' => false,
                'link' => Html::a(Yii::t('messages', 'See more about People...'), '#', array('id' => 'peopleMore'))
            ),
            array('mmName' => 'people', 'id' => 'basic_srch', 'image' => 'basic-search.jpg',
                'message' => Yii::t('messages', 'Launch a quick search in your database'), 'showTip' => true, 'freemium' => array('show', 'disable'), 'cm' => array('show'),
                'title' => Yii::t('messages', 'People -> Basic Search')
            ),
            array('mmName' => 'people', 'id' => 'advanced_srch', 'image' => 'advncd-srch.jpg',
                'message' => Yii::t('messages', 'Launch a search in your database with some advanced filters.'),
                'freemium' => array('show', 'disable'), 'cm' => array('show'), 'showTip' => true, 'title' => Yii::t('messages', 'People -> Advanced Search')
            ),
            array('mmName' => 'people', 'id' => 'user_match', 'image' => 'matching.jpg',
                'message' => Yii::t('messages', 'Match emails, social contacts that related to same user.'),
                'freemium' => array('show', 'disable'), 'cm' => array('show'), 'showTip' => true, 'title' => Yii::t('messages', 'People -> User Match')
            ),
            array('mmName' => 'people', 'id' => 'saved_srch', 'image' => 'saved-srch.jpg',
                'message' => Yii::t('messages', 'Use your search results in your data base as a contact list for Communications with Emailing, social networks or SMS campaign'),
                'freemium' => array('show', 'disable'), 'cm' => array('show'), 'showTip' => true, 'title' => Yii::t('messages', 'People -> Saved Search')
            ),
            array('mmName' => 'people', 'id' => 'keywords', 'image' => 'keywords.jpg',
                'message' => Yii::t('messages', 'All contacts can be tagged with keywords to better organize your database.'),
                'freemium' => array('hide'), 'cm' => array('show'), 'showTip' => true, 'title' => Yii::t('messages', 'People -> Keywords')
            ),
            array('mmName' => 'people', 'id' => 'bulk_insert', 'image' => 'bulk-upload.jpg',
                'message' => Yii::t('messages', 'Do not seat on your data, use them for victory! Import all your contacts files in the system, emails, cell phone, address, use the data you already have in DigitaleBox.'),
                'freemium' => array('show', 'disable'), 'cm' => array('show'), 'showTip' => true, 'title' => Yii::t('messages', 'People -> Bulk Insert')
            ),
            array('mmName' => 'people', 'id' => 'advance_bulk_insert', 'image' => 'bulk-upload.jpg',
                'message' => Yii::t('messages', 'Do not seat on your data, use them for victory! Import all your contacts files in the system, emails, cell phone, address, use the data you already have in DigitaleBox.'),
                'freemium' => array('show', 'disable'), 'cm' => array('show'), 'showTip' => true, 'title' => Yii::t('messages', 'People -> Advanced Bulk Insert')
            ),
            array('mmName' => 'people', 'id' => 'stats', 'image' => 'statistical.jpg',
                'message' => Yii::t('messages', 'Shows latest stystem statistics such as daily user engagements, daily prospect count, overall prospect, supporter, non supporter statistics.'),
                'freemium' => array('show', 'disable'), 'cm' => array('show'), 'showTip' => true, 'title' => Yii::t('messages', 'People -> Statistics')
            ),
            array('mmName' => 'people', 'id' => 'teams', 'image' => 'teams.jpg',
                'message' => Yii::t('messages', 'Ideal solution to organize your supporters, fans, volunteers according to their geographical location. Interactive map shows up locations of your supporters.'),
                'freemium' => array('hide'), 'cm' => array('show', 'disable'), 'showTip' => true, 'title' => Yii::t('messages', 'People -> Teams')
            ),
            array('mmName' => 'people', 'id' => 'social_activity', 'image' => 'social-activity.jpg',
                'message' => Yii::t('messages', 'Publish & Schedule all your contents on social networks. Identify new contacts that share common interests, engage with them and grow your community.'),
                'freemium' => array('show'), 'cm' => array('show'), 'showTip' => true, 'title' => Yii::t('messages', 'People -> Social Activities')
            ),
            array('mmName' => 'people', 'id' => 'volunteers', 'image' => 'Volunteers.jpg',
                'message' => Yii::t('messages', 'Give access of your platform to your community and organize them for action'),
                'freemium' => array('hide'), 'cm' => array('show', 'disable'), 'showTip' => true, 'title' => Yii::t('messages', 'People -> Volunteers')
            ),
            array('mmName' => 'people', 'id' => 'events', 'image' => 'events.jpg',
                'message' => Yii::t('messages', 'Create and share your events with your communitiy, discover the advanced Event Management with DigitaleBox'),
                'freemium' => array('hide'), 'cm' => array('show'), 'showTip' => true, 'title' => Yii::t('messages', 'People -> Events')
            ),
            array('mmName' => 'people', 'id' => 'resource', 'image' => 'Resource.jpg',
                'message' => Yii::t('messages', 'Share important documents, images, videos with your community'),
                'freemium' => array('hide'), 'cm' => array('show', 'disable'), 'showTip' => true, 'title' => Yii::t('messages', 'People -> Resource')
            ),
            array('mmName' => 'people', 'id' => 'activity', 'image' => 'Activities.jpg',
                'message' => Yii::t('messages', 'Have a look at your history activity on DigitaleBox'),
                'freemium' => array('hide'), 'cm' => array('show', 'disable'), 'showTip' => true, 'title' => Yii::t('messages', 'People -> Activities')
            ),
            array('mmName' => 'people', 'id' => 'donation', 'image' => 'donation.jpg',
                'message' => Yii::t('messages', 'Start a donation campaign but do no collect alone, your community can create interactive donation page and share it with social networks.'),
                'freemium' => array('hide'), 'cm' => array('show'), 'showTip' => false, 'title' => Yii::t('messages', 'People -> Donations & Membership')
            ),
            array('mmName' => 'people', 'id' => 'friend_find', 'image' => 'friend-find.jpg',
                'message' => Yii::t('messages', 'Find friends on your social networks(Facebook, Twitter) and invite them to join with your community.'),
                'freemium' => array('hide'), 'cm' => array('show', 'disable'), 'showTip' => true, 'title' => Yii::t('messages', 'People -> Friend Finder')
            ),
            array('mmName' => 'people', 'id' => 'petition', 'image' => 'petition.jpg',
                'message' => Yii::t('messages', 'Create petitions with Change.org.'),
                'freemium' => array('show', 'disable'), 'cm' => array('show', 'disable'), 'showTip' => true, 'title' => Yii::t('messages', 'People -> Petition')
            ),
            array('mmName' => 'people', 'id' => 'membership', 'image' => 'membership.jpg',
                'message' => Yii::t('messages', 'Start a membership campaign'),
                'freemium' => array('hide'), 'cm' => array('show', 'disable'), 'showTip' => true, 'title' => Yii::t('messages', 'People -> Membership')
            ),
            // Communication
            array('mmName' => 'main', 'id' => 'communication', 'image' => 'communication.jpg',
                'message' => Yii::t('messages', 'Launch targeted or mass communication campaign by using the database with Emailing, Direct message on twitter & Linkedin or SMS. Build your Newsletter with easy to use templates, Follow each campaign with in depth statistics. '),
                'freemium' => array('show', 'disable'), 'cm' => array('show'), 'showTip' => false, 'title' => Yii::t('messages', 'Communication'), 'link' => Html::a(Yii::t('messages', 'See more about Communication..'),
                '#', array('id' => 'communicationMore'))
            ),
            array('mmName' => 'communication', 'id' => 'msg-template', 'image' => 'msg-templates.jpg',
                'message' => Yii::t('messages', 'Create and Manage message templates for your campaigns. Use and modify the predefined templates or create your own for your newsletter.'),
                'freemium' => array('show', 'disable'), 'cm' => array('show'), 'showTip' => true, 'title' => Yii::t('messages', 'Communication -> Message Templates')
            ),
            array('mmName' => 'communication', 'id' => 'newCampaign', 'image' => 'new-campaigns.jpg',
                'message' => Yii::t('messages', 'Create new Email/Twitter/SMS campaign'),
                'freemium' => array('show', 'disable'), 'cm' => array('show'), 'showTip' => true, 'title' => Yii::t('messages', 'Communication -> New Campaign')
            ),
            array('mmName' => 'communication', 'id' => 'campaigns', 'image' => 'sent-campaigns.jpg',
                'message' => Yii::t('messages', 'Monitor your ongoing Email, social, & SMS campaign progress with in depth statistics'),
                'freemium' => array('show', 'disable'), 'cm' => array('show'), 'showTip' => true, 'title' => Yii::t('messages', 'Communication -> Reports')
            ),
            array('mmName' => 'communication', 'id' => 'msg-box', 'image' => 'message-box.jpg',
                'message' => Yii::t('messages', 'Internal messaging system which allow your community members to send and receive messages.'),
                'freemium' => array('show', 'disable'), 'cm' => array('show'), 'showTip' => true, 'title' => Yii::t('messages', 'Communication -> Message Box')
            ),

            // System
            array('mmName' => 'main', 'id' => 'system', 'image' => 'system.jpg',
                'message' => Yii::t('messages', 'System enables you to manage all admin accounts, rights & permissions, to parameter your DigitaleBox account, your Dashboard design and Feed keywords. '),
                'freemium' => array('show'), 'cm' => array('show'), 'title' => Yii::t('messages', 'System'), 'showTip' => false,
                'link' => Html::a(Yii::t('messages', 'See more about System...'), '#', array('id' => 'systemMore'))
            ),
            array('mmName' => 'system', 'id' => 'mng-roles', 'image' => 'manage-roles.jpg',
                'message' => Yii::t('messages', 'Create user roles with different features. It help you to grant access to the system with limited featues. Ex:secretary, clerk'),
                'freemium' => array('show', 'disable'), 'cm' => array('show'), 'showTip' => true, 'title' => Yii::t('messages', 'System -> Manage Roles')
            ),
            array('mmName' => 'system', 'id' => 'mng-sys-users', 'image' => 'managFe-sys-users.jpg',
                'message' => Yii::t('messages', 'Create and manage admins with different roles.'),
                'freemium' => array('show', 'disable'), 'cm' => array('show'), 'showTip' => true, 'title' => Yii::t('messages', 'System -> Manage Users')
            ),
            array('mmName' => 'system', 'id' => 'org-info', 'image' => 'org-info.jpg',
                'message' => Yii::t('messages', 'You chosed to give a limited access to your platform to your volunteer? Then you can customize and brand your volunteer sign up features. Add you pictures, texts, form fields that associated with signup process.'),
                'freemium' => array('hide'), 'cm' => array('show', 'disable'), 'showTip' => true, 'title' => Yii::t('messages', 'System -> Volunteer Portal')
            ),
            array('mmName' => 'system', 'id' => 'ad-banner', 'image' => 'ad-banner.jpg',
                'message' => Yii::t('messages', 'Create and share banner with social networks.'),
                'freemium' => array('hide'), 'cm' => array('show', 'disable'), 'showTip' => true, 'title' => Yii::t('messages', 'System -> Add Banner')
            ),
            array('mmName' => 'system', 'id' => 'feed-keywords', 'image' => 'feed-keywords.jpg',
                'message' => Yii::t('messages', 'Configure keywords the system will use to find more contacts on social networks (LinkedIn, Twitter, Facebook). It will also help you monitor and analyze what people say about you or your organization.'),
                'freemium' => array('hide'), 'cm' => array('show'), 'showTip' => true, 'title' => Yii::t('messages', 'System -> Feed Keywords')
            ),
            array('mmName' => 'system', 'id' => 'config', 'image' => 'configuration.jpg',
                'message' => Yii::t('messages', 'Configure various system settings. Such as language, email, Facebook page, LinkedIn page etc..'),
                'freemium' => array('show'), 'cm' => array('show'), 'showTip' => true, 'title' => Yii::t('messages', 'System -> Configuration')
            ),
            array('mmName' => 'system', 'id' => 'portal-settings', 'image' => 'portal-settings.jpg',
                'message' => Yii::t('messages', 'Customize your DigitaleBox. Add new pictures to the landing page, change color, change background, texts, etc..'),
                'freemium' => array('hide'), 'cm' => array('show'), 'showTip' => true, 'title' => Yii::t('messages', 'System -> Portal Settings')
            ),
            array('mmName' => 'system', 'id' => 'custom-fields', 'image' => 'custom-fields.jpg',
                'message' => Yii::t('messages', 'Customize forms with fields like Text, Checkbox, Date, Dropdown List, Option List and etc. Add new fields to the sign up page, bulk insert, people create, etc..'),
                'freemium' => array('show', 'disable'), 'cm' => array('show'), 'showTip' => true, 'title' => Yii::t('messages', 'System -> Custom Fields')
            ),
            array('mmName' => 'system', 'id' => 'form-builder', 'image' => 'form-builder.jpg',
                'message' => Yii::t('messages', 'Build rich looking custom forms with selected fields to use it in your website as a widget.'),
                'freemium' => array('show', 'disable'), 'cm' => array('show'), 'showTip' => true, 'title' => Yii::t('messages', 'System -> Form Builder')
            ),
            // Site Guide
            array('mmName' => 'main', 'id' => 'site_guide', 'image' => 'home.jpg',
                'message' => Yii::t('messages', 'Take a Tour to discover DigitaleBox features'),
                'freemium' => array('show'), 'cm' => array('show'), 'showTip' => false, 'title' => Yii::t('messages', 'Guide')
            ),

            // Invite
            array('mmName' => 'main', 'id' => 'invite', 'image' => 'invite.jpg',
                'message' => Yii::t('messages', 'Send invitation to your friends & colleagues to discover DigitaleBox and receives benefits everytime one of them actives, 1 more post per day in Broadcast.'),
                'freemium' => array('show'), 'cm' => array('show', 'disable'), 'showTip' => false, 'title' => Yii::t('messages', 'Invite')
            ),

            // Account
            array('mmName' => 'main', 'id' => 'account', 'image' => 'account.jpg',
                'message' => Yii::t('messages', 'Connect your social networks with DigitaleBox'),
                'freemium' => array('show'), 'cm' => array('show'), 'title' => Yii::t('messages', 'Profile'), 'showTip' => false,
                'link' => Html::a(Yii::t('messages', 'See more about Account...'), '#', array('id' => 'accountMore'))
            ),
            array('mmName' => 'account', 'id' => 'my-account', 'image' => 'my-account.jpg',
                'message' => Yii::t('messages', 'Modify your profile details.'),
                'freemium' => array('show'), 'cm' => array('show'), 'showTip' => true, 'title' => Yii::t('messages', 'My Account')
            ),
            array('mmName' => 'account', 'id' => 'chng-pass', 'image' => 'change-pass.jpg',
                'message' => Yii::t('messages', 'Change your account password.'),
                'freemium' => array('show'), 'cm' => array('show'), 'showTip' => true, 'title' => Yii::t('messages', 'Change Password')
            ),
            array('mmName' => 'account', 'id' => 'lang', 'image' => 'language.jpg',
                'message' => Yii::t('messages', 'Choose your preferred language'),
                'freemium' => array('show'), 'cm' => array('show'), 'showTip' => true, 'title' => Yii::t('messages', 'Language')
            ),
            array('mmName' => 'account', 'id' => 'Logout', 'image' => 'logout.jpg',
                'message' => Yii::t('messages', 'Logout from the system.'),
                'freemium' => array('show'), 'cm' => array('show'), 'showTip' => true, 'title' => Yii::t('messages', 'Logout')
            ),

            // People->Social Activities & Followups
            array('id' => 'ppl-safolup-feed', 'image' => '', 'message' => Yii::t('messages', 'Desactivated'), 'freemium' => array('show', 'disable'), 'showTip' => false, 'cm' => array('show'), 'title' => ''),
            array('id' => 'ppl-safolup-folup', 'image' => '', 'message' => Yii::t('messages', 'Desactivated'), 'freemium' => array('show', 'disable'), 'showTip' => false, 'cm' => array('show'), 'title' => ''),
        );

        return $featureList;
    }

    /**
     * Retrieve system features to be shown on tour guide
     * @param string $mmName Mainmenu item name
     * @return array feature list matched for given criteria
     */
    public function getSiteGuideFeatures($mmName = '')
    {
        $allFeatures = $this->getSystemFeatureList();
        $featureList = array();
        foreach ($allFeatures as $feature) {

            if (App::FREEMIUM == Yii::$app->user->getState('packageTypeId') && in_array('hide', $feature['freemium'])) {
                continue;
            }

            if (App::CM == Yii::$app->user->getState('packageTypeId') && in_array('hide', $feature['cm'])) {
                continue;
            }

            if ('' != $mmName && $mmName != @$feature['mmName']) {
                continue;
            }

            $featureList[] = $feature;
        }
        return $featureList;
    }

    /**
     * Return upgrade URL.
     */
    public function getUpgradeUrl()
    {
        $url = Url::to(['site/sales-auto-login', 'redUrl' => Yii::$app->params['salesAppUrl'] . 'app/myDigitaleBox']);
        return $url;
    }

    /**
     * Return upgrade URL.
     */
    public function getUpgradeUrlToSales()
    {

        //getUpgradeUrl method didnt work so we created new method for fetch user from masterDB
        $url = Url::to(['site/sales-auto-login', 'redUrl' => Yii::$app->params['salesAppUrl'] . 'app/myDigitaleBox']);
        return $url;
    }

    /**
     * Register script for disabling features depending on package type
     */
    public function registerFeatureDisableScript()
    {
        $this->registerToolTipsterScripts();
        $upgradeUrl = $this->getUpgradeUrl();
        $upgradeLink = Html::a(Yii::t('messages', 'UPGRADE'), $upgradeUrl);
        $curPkgType = Yii::$app->user->getState('packageTypeId');
        $allFeatures = json_encode($this->getSystemFeatureList());
        Yii::$app->clientScript->registerScript('feature-disable', "
			var allFeatures = {$allFeatures};
			var upgradeLink = '{$upgradeLink}';
			var ids = '';
			$.each(allFeatures, function(idx, rec){
				
				switch({$curPkgType}) {
					case " . App::FREEMIUM . ":
						if (rec.freemium.indexOf('disable') > -1) {
							$('#' + rec.id + ' > a').attr('href','#');
							$('#' + rec.id + ' > a').attr('class', 'disabled_main');
							ids += '#' + rec.id + ',';
						}
						if (rec.freemium.indexOf('hide') > -1) {
							$('#' + rec.id).remove();
						}
						break;
						
					case " . App::CM . ":
						if (rec.cm.indexOf('disable') > -1) {
							$('#' + rec.id + ' > a').attr('href','#');
							$('#' + rec.id + ' > a').attr('class', 'disabled_main');
							ids += '#' + rec.id + ',';
						}
						if (rec.cm.indexOf('hide') > -1) {
							$('#' + rec.id).remove();
						}
						break;
				}
			});

			$('#infotip').tooltipster({
				autoClose:false,
				timer:10000,
				position:'bottom',
				offsetX:'-150px',
				offsetY:'50px',
				arrow:false,
				maxWidth:'200',
				contentAsHTML:true,
				interactive: true
			});
			
			function getFeatureInfo(id) {
				var info = '';
				$.each(allFeatures, function(idx, rec){
					if (id == rec.id) {
						info = rec;
					}
				});
				
				return info;
			}
			
			ids = ids.replace(/(^,)|(,$)/g, '');
			
			$(ids).mouseover(function() {
				var info = getFeatureInfo($(this).attr('id'));
				if (info.showTip) {
					$('#infotip').tooltipster('content', '<p style=\'text-align: justify;\'>' + info.message + '</p><p><strong>' + upgradeLink + '</strong></p>');
					$('#infotip').tooltipster('show');
				}
			});
		", CClientScript::POS_READY);
    }

    /**
     * Register Google+ button script
     */
    public function registerGplusButtonScript()
    {
        echo '<script src="https://apis.google.com/js/platform.js" async defer></script>';
    }

    /**
     * Retrieve base URI of a folder inside extension directory
     */
    public function getExtensionBaseUri($folder)
    {
        $basePath = Yii::$app->basePath . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . $folder;
        // $baseUrl = Yii::$app->getAssetManager()->publish($basePath, false, 1, YII_DEBUG);
        $baseUrl = Yii::$app->getAssetManager()->publish($basePath);
        return $baseUrl;
        // return $basePath;
    }

    /**
     * Retrieve base URI of a folder inside theme directory
     */
    public function getThemeBaseUri($folder)
    {
        $basePath = Yii::getAlias('@app') . DIRECTORY_SEPARATOR . 'web' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'bootstrap_spacelab' . DIRECTORY_SEPARATOR . $folder;
        $baseUrl = Yii::$app->getAssetManager()->publish($basePath);

        return $baseUrl;
    }

    /**
     * Register bootstrap alert box plugin. http://nakupanda.github.io/bootstrap3-dialog/
     */
    public function registerBootstrapDialogPlugin()
    {
        $baseUrl = $this->getExtensionBaseUri('bootstrap3-dialog-master');
        $cs = Yii::$app->clientScript;

        $cs->registerScriptFile($baseUrl . '/js/bootstrap-dialog.js');
        $cs->registerCssFile($baseUrl . '/css/bootstrap-dialog.css');
    }

    /**
     * Register main css and javascripts requred by the system.
     */
    public function registerSignupScripts()
    {
        // Register bootstap extension related scripts
        Yii::$app->bootstrap->register();

        // Unregister some original scripts comes with bootstrap extension
        // Since I want to overide these scripts with theme specific
        Yii::$app->clientScript->scriptMap = array(
            'bootstrap.css' => false,
            'bootstrap-responsive.css' => false,
        );

        // Registering theme sepecific scripts
        Yii::$app->getClientScript()->registerCssFile(Yii::$app->theme->baseUrl . '/css/bootstrap-responsive.min.css');
        Yii::$app->getClientScript()->registerCssFile(Yii::$app->theme->baseUrl . '/css/bootstrap.min.css');
        Yii::$app->getClientScript()->registerCssFile(Yii::$app->theme->baseUrl . '/css/styles.css');
        Yii::$app->getClientScript()->registerCssFile(Yii::$app->theme->baseUrl . '/css/yii.css');
        Yii::$app->getClientScript()->registerCssFile(Yii::$app->theme->baseUrl . '/font-awesome-4.0.1/css/font-awesome.css');
        Yii::$app->getClientScript()->registerCssFile(Yii::$app->theme->baseUrl . '/css/social-buttons.css');
    }

    /**
     * Register admin login page related css and js scripts.
     */
    public function registerLoginScripts()
    {
        Yii::$app->getClientScript()->registerCssFile(Yii::$app->theme->baseUrl . '/css/login.css');
    }

    /**
     * Register popup dialogbox related scripts
     */
    public function registerDialogScripts()
    {
        //Yii::$app->getClientScript()->registerCssFile(Yii::$app->theme->baseUrl . '/css/dialog.css');
    }

    public function registerMultiSelet()
    {
        // echo '<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js" async defer></script>';
        // Yii::$app->getClientScript()->registerCssFile(Yii::$app->theme->baseUrl . '/css/jquery.multiselect.css');
        // Yii::$app->getClientScript()->registerCssFile(Yii::$app->theme->baseUrl . '/js/jquery.multiselect.js');
    }

    /**
     * Register Jqplot chart library related scripts.
     */
    public function registerJqplotScripts()
    {
        $baseUrl = $this->getExtensionBaseUri('jqplot');

        $cs = Yii::$app->clientScript;
        $cs->registerScriptFile($baseUrl . '/jquery.jqplot.min.js');
        $cs->registerScriptFile($baseUrl . '/plugins/jqplot.barRenderer.min.js');
        $cs->registerScriptFile($baseUrl . '/plugins/jqplot.categoryAxisRenderer.min.js');
        $cs->registerScriptFile($baseUrl . '/plugins/jqplot.pointLabels.min.js');
        $cs->registerScriptFile($baseUrl . '/plugins/jqplot.canvasTextRenderer.min.js');
        $cs->registerScriptFile($baseUrl . '/plugins/jqplot.canvasAxisLabelRenderer.min.js');
        $cs->registerScriptFile($baseUrl . '/plugins/jqplot.canvasAxisTickRenderer.min.js');
        $cs->registerScriptFile($baseUrl . '/plugins/jqplot.pieRenderer.min.js');
        $cs->registerScriptFile($baseUrl . '/plugins/jqplot.donutRenderer.min.js');
        $cs->registerScriptFile($baseUrl . '/plugins/jqplot.highlighter.min.js');
        $cs->registerScriptFile($baseUrl . '/plugins/jqplot.dateAxisRenderer.min.js');

        $cs->registerCssFile($baseUrl . '/jquery.jqplot.min.css');
    }

    /**
     * Register Tinymce(http://www.tinymce.com/) rlated scripts.
     * http://justboil.me/
     */
    public function registerTinyMceScripts()
    {
        $basePath = Yii::$app->getAssetManager()->publish(Yii::$app->basePath . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'tinymce');
        $cs = Yii::$app->view->registerJsFile('@web' . $basePath[1] . '/js/tinymce/tinymce.min.js');
        return $cs;
    }

    /**
     * Register Imagearea select scripts.
     */
    public function registerImageAreaSelectScripts()
    {
        $baseUrl = $this->getExtensionBaseUri('imgAreaSelect');

        $cs = Yii::$app->clientScript;
        $cs->registerScriptFile($baseUrl . '/scripts/jquery.imgareaselect.pack.js');
        $cs->registerCssFile($baseUrl . '/css/imgareaselect-default.css');
    }

    /**
     * Register tooltipster scripts
     * http://iamceege.github.io/tooltipster/#demos
     */
    public function registerToolTipsterScripts()
    {
        $baseUrl = $this->getExtensionBaseUri('tooltipster');

        $cs = Yii::$app->clientScript;
        $cs->registerScriptFile($baseUrl . '/js/jquery.tooltipster.min.js');
        $cs->registerCssFile($baseUrl . '/css/tooltipster.css');
    }

    /**
     * Register owl carousel script
     * http://owlgraphic.com/owlcarousel
     */
    public function registerOwlCarouselScripts()
    {
        $baseUrl = $this->getExtensionBaseUri('jqueryOwlCarousel');

        $cs = Yii::$app->clientScript;
        $cs->registerScriptFile($baseUrl . '/js/owl.carousel.min.js');
        $cs->registerCssFile($baseUrl . '/css/owl.carousel.css');
        $cs->registerCssFile($baseUrl . '/css/owl.theme.css');
        //$cs->registerCssFile($baseUrl . '/css/owl.transitions.css');
        $cs->registerCssFile($baseUrl . '/css/owl.custom.css');
    }

    /**
     * Register JQuery UI autocomplete related scripts
     */
    public function registerAutoCompleteScripts()
    {
        $baseUrl = $this->getExtensionBaseUri('jquery-ui-1.10.3');

        $cs = Yii::$app->clientScript;
        $cs->registerScriptFile($baseUrl . '/ui/jquery.ui.core.js');
        $cs->registerScriptFile($baseUrl . '/ui/jquery.ui.widget.js');
        $cs->registerScriptFile($baseUrl . '/ui/jquery.ui.position.js');
        $cs->registerScriptFile($baseUrl . '/ui/jquery.ui.autocomplete.js');
    }

    public function registerUIScripts()
    {
        $baseUrl = $this->getExtensionBaseUri('jquery-ui-1.11.4.custom');

        $cs = Yii::$app->clientScript;
        $cs->registerScriptFile($baseUrl . '/jquery-ui.js');
        $cs->registerCssFile($baseUrl . '/jquery-ui.css');
    }

    public function registerHighchartsScripts()
    {
        $baseUrl = $this->getExtensionBaseUri('highcharts');
        $cs = Yii::$app->view->registerJsFile('@web' . $baseUrl[1] . '/highcharts.js');
        return $cs;
    }

    public function registerGeo($filename)
    {
        $baseUrl = $this->getExtensionBaseUri('geojson');
        echo $baseUrl . $filename;
    }

    /**
     * Register Google map API related scripts
     * Tutorial:http://google-maps-utility-library-v3.googlecode.com/svn/tags/markerwithlabel/1.1.8/docs/examples.html
     */
    public function registerGoogleMapScripts($sensor = 'false', $libraries = '', $marker = true, $callback = '')
    {
        $apiKey = Yii::$app->params['google']['apiKey'];

        $scripts = "<script type='text/javascript' src='https://maps.googleapis.com/maps/api/js?v=3.exp&key={$apiKey}&libraries={$libraries}'></script>";
        if ($callback)
            $scripts = "<script type='text/javascript' src='https://maps.googleapis.com/maps/api/js?v=3.exp&key={$apiKey}&callback=initMap&libraries={$libraries}'></script>";
        //$scripts .= "<script type='text/javascript' src='http://google-maps-utility-library-v3.googlecode.com/svn/tags/markerwithlabel/1.1.9/src/markerwithlabel.js'></script>";

        echo $scripts;
    }

    /**
     * Register jquery token input related scripts.
     * Url: http://loopj.com/jquery-tokeninput/
     */
    public function registerTokenInputScripts()
    {
        $baseUrl = $this->getExtensionBaseUri('tokenInput');
        Yii::$app->view->registerJsFile('@web' . $baseUrl[1] . '/src/jquery.tokeninput.js');
        Yii::$app->view->registerCssFile('@web' . $baseUrl[1] . '/styles/token-input.css');
        Yii::$app->view->registerCssFile('@web' . $baseUrl[1] . '/styles/token-input-facebook.css');

    }

    /**
     * Register Qtip related scripts.
     */
    public function registerQtip2Scripts()
    {
        $baseUrl = $this->getExtensionBaseUri('qtip2' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'qtip2');

        $cs = Yii::$app->clientScript;
        $cs->registerScriptFile($baseUrl . '/jquery.qtip.min.js');
        $cs->registerCssFile($baseUrl . '/jquery.qtip.min.css');
    }

    /**
     * Register Bootstrap multiselect scripts.
     */
    public function registerBootstrapMultiselctScripts()
    {
        $baseUrl = $this->getExtensionBaseUri('bootstrapMultiselect');

        $cs = Yii::$app->clientScript;
        $cs->registerScriptFile($baseUrl . '/js/bootstrap-multiselect.js');
        $cs->registerCssFile($baseUrl . '/css/bootstrap-multiselect.css');
    }

    /**
     * Register nice scrol related scripts.
     */
    public function registerNiceScrolScripts()
    {
        $baseUrl = $this->getExtensionBaseUri('niceScrol');

        $cs = Yii::$app->clientScript;
        $cs->registerScriptFile($baseUrl . '/jquery.nicescroll.min.js');
    }

    /**
     * Register bootsrap file input styling script.
     * http://gregpike.net/demos/bootstrap-file-input/demo.html
     */
    public function registerBootstrapFileInputStyleScripts()
    {
        $baseUrl = $this->getExtensionBaseUri('bootstrapFileInput');

        $cs = Yii::$app->clientScript;
        $cs->registerScriptFile($baseUrl . '/bootstrapFileInput.js');
    }

    /**
     * Register jquery fancybox related scripts
     * http://fancybox.net/howto
     */
    public function registerFancyboxScripts()
    {
        Yii::$app->view->registerCssFile('@web' . '/js/fancybox/source/jquery.fancybox.css');
        Yii::$app->view->registerJsFile('@web' . '/js/fancybox/source/jquery.fancybox.pack.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
    }

    /**
     * Register jquery Peitychart related scripts
     * http://benpickles.github.io/peity/
     */
    public function registerPeityChartScripts()
    {
        $baseUrl = $this->getExtensionBaseUri('jqpiety');

        $cs = Yii::$app->clientScript;
        $cs->registerScriptFile($baseUrl . '/jquery.peity.min.js');
    }

    /**
     * Register bootbox alert related scripts
     * http://bootboxjs.com/
     */
    public function registerBootboxScripts()
    {
        $baseUrl = $this->getExtensionBaseUri('bootBox');

        $cs = Yii::$app->clientScript;
        $cs->registerScriptFile($baseUrl . '/bootbox.min.js');
    }

    /**
     * Register Jquery steps script(Wizard)
     * http://www.jquery-steps.com
     * @throws \yii\base\InvalidConfigException
     */
    public function registerJqueryStepsScripts()
    {
        $baseUrl = $this->getExtensionBaseUri('jquerySteps');
        Yii::$app->view->registerJsFile('@web' . $baseUrl[1] . '/jquery.steps.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
        Yii::$app->view->registerCssFile('@web' . $baseUrl[1] . '/jquery.steps.css');
    }

    /**
     * Register script for handling character countdown for FB/TW/LI messages.
     */
    public function registerCharcterCountdownScript()
    {
        return Yii::$app->view->registerJs(
            "function updateCountDown(maxLength, inputId, labelId, e) {
			var remaining = maxLength - $(inputId).val().length;
			$(labelId).text(remaining);
			
			if (e != undefined) {
				if (e.which < 0x20) {
					return;
				}
				if ($(inputId).val().length == maxLength) {
					e.preventDefault();
				} else if ($(inputId).val().length > maxLength) {
					var value = $(inputId).val().substring(0, maxLength);
					$(inputId).val(value);
				}
			}
		}", View::POS_HEAD);
    }

    /**
     * Print flash message when performing ajax requests.
     */
    public static function setAjaxFlash($type, $message, $return = false)
    {
        $flash = "<div class='alert in alert-{$type}' style='opacity: 1'>
<a class='close' data-dismiss='alert'>×</a>
				{$message}
                </div>";

        if ($return) {
            return $flash;
        }

        echo $flash;
    }

    /**
     * Print flash message for small dialog boxes.
     * @param string $type Message type. Ex:error,info,success
     * @param string $message Message to be displayed
     * @return string $id Unique id for this alert box
     */
    public function setDialogFlash($type = 'info', $message = '')
    {
        $id = mt_rand(0, 9999);
        $containerId = "alertcontainer{$id}";
        $msgId = "alertmsg{$id}";

        echo "<div id='{$containerId}' class='alert alert-{$type}'>
				<span id='{$msgId}'>{$message}</span>
			  </div>";

        Yii::$app->clientScript->registerScript('dialog-flash',
            "function changeDialogFlash(type, message, id) {
			if (id == undefined) {
				id = {$id};
			}
			$('#alertmsg' + id).text(message);
			$('#alertcontainer' + id).attr('class', 'alert alert-' + type);
		}",
            CClientScript::POS_READY);

        return $id;
    }

    /**
     * Register javascript to set javascript falsh messages
     */
    public function setJsFlash()
    {
        $script = <<< JS

			function setJsFlash(type, message) {
				type = 'alert alert-' + type;
				var msgStr  = '<div id=\"flash-inner\" class=\"' + type +'\">';
					msgStr += '<button class=\"close\" data-dismiss=\"alert\" type=\"button\">×</button>';
					msgStr += message;
					msgStr += '</div>';
					
				$('#statusMsg').html(msgStr);
				jQuery('html, body').animate({scrollTop:0}, 'slow');
			}
			
			function clearJsFlash() {
				$('#flash-inner').remove();
			}
		
            
        
        function setJsFlash(type, message) {
                        type = 'alert alert-' + type;
                        var msgStr  = '<div id=\"flash-inner\" class=\"' + type +'\">';
                            msgStr += '<button class=\"close\" data-dismiss=\"alert\" type=\"button\">×</button>';
                            msgStr += message;
                            msgStr += '</div>';
                            
                        $('#statusMsg').html(msgStr);
                        jQuery('html, body').animate({scrollTop:0}, 'slow');
                    }
                    
                    function clearJsFlash() {
                        $('#flash-inner').remove();
                    }
JS;
        Yii::$app->view->registerJs($script);

    }

    /**
     * Register popup window script
     */
    public function registerPopupWindowScript()
    {
        $script = <<< JS
			function popupwindow(url, title, w, h) {
			  var left = (screen.width/2)-(w/2);
			  var top = (screen.height/2)-(h/2);
			  //toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, 
			  return window.open(url, title, 'scrollbars=yes, resizable=yes, width='+w+', height='+h+', top='+top+', left='+left);
			} 
		JS;

    }


    /**
     * Handle popup window close.
     * @param string $parentRedirectUrl Parent page redirect URL on popup close
     * @param boolean $parentRefresh Whether to refresh parent page on popup close
     */
    public function closePopupWindow($parentRedirectUrl = null, $parentRefresh = false)
    {
        if ($parentRefresh) {
            $script = "window.opener.location.reload();window.close();";
        } else if (null == $parentRedirectUrl && !$parentRefresh) {
            $script = "window.close();";
        } else {
            $script = "window.opener.location.href = '{$parentRedirectUrl}';window.close();";
        }
        //echo $script;
        echo "<script>{$script}</script>";
    }

    /**
     * Dynamically change the database connection
     * @param string $dbName Database name
     * @param string $host Database server Ip
     * @param string $username Database username
     * @param string $password Database password
     * @param string $driver Driver whether mysql,mongodb etc.. need to enhace code to support this feature
     * @return bool|\yii\db\Connection
     */
    public function changeDbConnection($dbName, $host, $username, $password, $driver = 'mysql')
    {
        try {
            $connectionString = "mysql:host={$host};dbname={$dbName}";
            Yii::$app->db->close();
            Yii::$app->db->dsn = $connectionString;
            Yii::$app->db->username = $username;
            Yii::$app->db->password = $password;
            Yii::$app->db->open();
            return Yii::$app->db;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Dynamically change the database connection for web request
     * @param return database connection object
     * @return bool|\yii\db\Connection
     */
    public function changeDbConnectionWeb()
    {
        // Recreate database connection specific to application
        $domain = $this->domain;
        $apps = array();
        $apps[$domain] = Yii::$app->dbMaster->createCommand('SELECT * FROM App WHERE domain=:domain ')
            ->bindValue(':domain', $this->domain)
            ->queryOne();

        if (isset($apps[$domain])) {
            return $this->changeDbConnection($apps[$domain]['dbName'],
                $apps[$domain]['host'],
                $apps[$domain]['username'],
                $apps[$domain]['password']
            );
        } else {
            return false;
        }
    }

    /**
     * Check whether applicatin is active or not
     */
    public function isAppActive()
    {
        $app = $this->getAppData();

        if ($app['status'] == App::APP_ACTIVE) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check whether application is freemium
     */
    public function isAppFreemium()
    {
        $app = $this->getAppData();

        if ($app['packageType'] == App::FREEMIUM_PLAN_TYPE) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Retrieve application data from the master table depending on the domain
     */
    public function getAppData()
    {
        $query = new Query();

        $app = Yii::$app->dbMaster->createCommand('SELECT * FROM App WHERE domain=:domain')
            ->bindValue(':domain', $this->domain)
            ->queryOne();
        return $app;
    }

    /**
     * Retrieve user data from the master table depending on the masterID
     */
    public function getAppUserData($masterId = null)
    {

        $app = Yii::$app->dbMaster->createCommand('SELECT * FROM User WHERE id=:id')
            ->bindValue(':id', $masterId)
            ->queryOne();
        return $app;
    }


    /**
     * Retrieve application data from id
     */
    public function getAppIdByDomain($domain)
    {

        $app = Yii::$app->dbMaster->createCommand('SELECT appId FROM App WHERE domain=:domain ')
            ->bindValue(':domain', $this->domain)
            ->queryOne();

        return $app;
    }

    /**
     * Retrieve package data from the master database
     * @param integer $packageType Type of the package
     * @return
     */
    public function getPackageData($packageType)
    {
        $packageData = Yii::$app->dbMaster->createCommand('SELECT * FROM Package WHERE type=:packageType')
            ->bindValue(':packageType', $packageType)
            ->queryOne();

        return $packageData;
    }

    /**
     * Set resouce informations.ie:application wise resource path etc..
     */
    public function setResourceInfo()
    {
        if (null == $this->resourcePathRelative) {
            $appId = null;
            if (!Yii::$app->params['isConsole']) {
                if (isset(Yii::$app->session['appId'])) {
                    $appId = Yii::$app->session['appId'];
                }
            }

            if (null == $appId) {
                $app = Yii::$app->dbMaster->createCommand('SELECT * FROM App WHERE domain=:domain ')
                    ->bindValue(':domain', $this->domain)
                    ->queryOne();

                $appId = $app['appId'];
                if (!Yii::$app->params['isConsole']) {
                    Yii::$app->session->set('appId', $appId);
                }
            }

            $this->appId = $appId;
            if (!Yii::$app->params['isConsole']) {
                $this->resourcePathUrl = Yii::$app->getHomeUrl() . "/{$this->appId}/" . $this->domain;
            }
            $this->resourcePathRelative = "resources/{$this->appId}/";
            $this->resourcePathAbsolute = Yii::$app->params['resourcePath'] . "{$this->appId}/";

            if (!is_dir($this->resourcePathRelative)) {
                @mkdir($this->resourcePathRelative);
                exec("chmod 777 {$this->resourcePathRelative}");
            }
        }
    }

    /**
     * Check whether previous cron process is still running.
     *
     * @param string $command Command name
     * @param string $functionName Function name
     * @return bool
     */
    public function isProcessExists(string $command, $functionName = ''): bool
    {
        $command = strtolower(str_replace('Command', '', $command));

        $php_path = exec('which php');
        $file_path = $_SERVER['PHP_SELF'];

        if ("" == $functionName) {
            $grep_info = "{$php_path} {$file_path} {$command}";
        } else {
            $grep_info = "{$php_path} {$file_path} {$command} {$functionName}";
        }

        $cmd = "ps -ef | grep -w \"{$grep_info}\"| grep -v grep";

        $output = shell_exec($cmd);

        if (substr_count($output, "{$grep_info}") > 1) {
            return true;
        }

        return false;
    }


    /**
     * @return string
     */
    public function getSignupUrl()
    {
        return Yii::$app->urlManager->createAbsoluteUrl('/signup/step1/');
    }

    /**
     * Retrieve email unsubscribe information
     * @param integer $userId Id of the user to be unsubscribed
     * @param string $domain For console applications we cant use "createAbsoluteUrl" method so pass the domain
     * and prepare the unsubscribe URL manually
     * @param string $clientName Client name
     * @return string $message Unsubscribe message.This will append to the main message
     */
    public function getUnSubInformation($userId, $domain = null, $clientName = null)
    {
        if (!is_null($domain)) {
            $seperator = $this->osType == self::OS_WINDOWS ? "\\" : "/";
            $pathData = array_reverse(explode($seperator, Yii::$app->basePath));
            $unSubUrl = "https://{$domain}/index.php/signup/unsubscribe?userId={$userId}";
        } else {
            $unSubUrl = Url::to(['/signup/unsubscribe/', 'userId' => $userId]);
        }

        $message = '<br/><br/> ' . Yii::t('messages', 'You are receiving this email because you are subscribed newsletter {name}. Under the Data Protection Act of 6 January 1978, you have a right to access, rectify and delete data concerning you. To unsubscribe,', ['name' => $clientName]);
        $message .= "<a href='{$unSubUrl}' >" . Yii::t('messages', 'Click here') . "</a>";

        return $message;
    }

    /**
     * Set operating system type
     */
    public function setOsType()
    {
        if (stristr(php_uname('s'), 'win')) {
            $this->osType = self::OS_WINDOWS;
        } else {
            $this->osType = self::OS_LINUX;
        }
    }

    /**
     * For some components they use different identifiers for language ex:fr_Fr instead of fr
     * So we have to get correct identifier.
     * @param string $component Component name
     * @return string Correct identifier
     */
    public function getComponenetSpecificLangIdentifier($component)
    {
        switch ($component) {
            case 'tinyMce':

                if ('fr-FR' == Yii::$app->language) {
                    $identifier = 'fr_FR';
                } else if ('pt' == Yii::$app->language) {
                    $identifier = 'pt_PT';
                } else if ('en-US' == Yii::$app->language) {
                    $identifier = '';
                } else {
                    $identifier = Yii::$app->language;
                }

                break;

            case 'juiDateTimePicker':

                $identifier = 'en' == Yii::$app->language ? '' : Yii::$app->language;

                break;

            case 'FullCalendar':
                $identifier = 'en' == Yii::$app->language ? '' : Yii::$app->language;
                break;

            case 'paypal':

                switch (Yii::$app->language) {
                    case 'fr':
                        $identifier = 'fr_FR';
                        break;

                    case 'pt':
                        $identifier = 'pt_PT';
                        break;

                    default:
                        $identifier = 'en_GB';
                        break;
                }

                break;
        }

        return $identifier;
    }

    /**
     * Retrieve alternative profile picture to display when original image link is broken
     * @return string Alternative picture path
     */
    public function getAltProfPic()
    {
        return Yii::$app->view->theme->baseUrl . '/img/user-profile.png';
    }

    /**
     * Retrieve alternative team picture to display when original image link is broken
     * @return string Alternative picture path
     */
    public function getAltTeamPic()
    {
        return Yii::$app->theme->baseUrl . '/img/defaultTeam.png';
    }

    /**
     * Retrieve google map marker image path
     * @return string picture path
     */
    public function getMarkerImage()
    {
        return Yii::$app->view->theme->baseUrl . '/img/markerman.png';
    }

    public function getImagePath()
    {
        return Yii::$app->view->theme->baseUrl . '/img/';
    }

    public function getImageUrlPath()
    {
        if (YII_ENV != 'dev') {
            return Url::base(false) . Yii::$app->view->theme->baseUrl . '/img';
        }
        
        return Url::base(true) . Yii::$app->view->theme->baseUrl . '/img';
    }

    public function getAltContentPic()
    {
        return Yii::$app->theme->baseUrl . '/img/blank_content.png';
    }

    /**
     * Make HTTP request.
     *
     * @param string $url Request URL
     * @param return mixed array Error number and response
     */
    public function httpRequest($url, $timeout = 1)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        $res = curl_exec($ch);
        $error_no = curl_errno($ch);
        curl_close($ch);

        return array($error_no, $res);
    }

    /**
     * Get location details by ip. For this use ipinfo service(http://ipinfo.io)
     * @return Object Json object of the response or null
     */
    public function getLocationByIp()
    {
        $ip = (('172.18.0.1' == $_SERVER['REMOTE_ADDR'] || '172.19.0.1' == $_SERVER['REMOTE_ADDR']) ? '220.247.236.99' : $_SERVER['REMOTE_ADDR']); // This workaround is just to work with local and live.
        //TODO: $url = Yii::$app->params['ipInfoUrl'] . "{$ip}/json";
        $url = Yii::$app->params['ipInfoUrl'] . "220.247.236.99/json";

        $response = $this->httpRequest($url, 50);
        $ipinfo = @json_decode($response[1]);

        if (is_object($ipinfo)) {
            return $ipinfo;
        }

        return null;
    }

    /**
     * Send email notifications to moderators(Client and client admins).
     * @param string $message Message to be sent
     * @param array $otherEmails Any other email addresses
     * @param string $subject Email subject
     */
    public function sendModeratorEmails($message, $otherEmails = array(), $subject = "DigitaleBox")
    {
        // Send email alert to moderators
        $user = new User();
        $moderatorEmails = array_merge($user->getModeratorEmails(), $otherEmails);
        $subjet = Yii::t('messages', $subject);
        $message = Yii::$app->controller->renderPartial('@app/views/emailTemplates/notificationTemplate',
            ['content' => $message], true);
        if (!empty($moderatorEmails)) {
            if ($this->sendEmail($moderatorEmails, $subjet, $message)) {
                Yii::$app->appLog->writeLog("Notification message sent to moderators. Emails:" . implode(',', $moderatorEmails));
            } else {
                Yii::$app->appLog->writeLog("Notification message sent failed to moderators. Emails:" . implode(',', $moderatorEmails));
            }
        } else {
            Yii::$app->appLog->writeLog("moderators emails empty. Emails:" . implode(',', $moderatorEmails));
        }

    }

    /**
     * System sends mails via this function. Used PHP mailer wrapper class
     * @param array $toAddress to Addresses
     * @param string $subject Subject of the email
     * @param string $body Message body
     * @param array $unsubInfo Contains details that required to prepare unsubscribe message.ie: userId, domain, clientName (These are array elements)
     * If this is null then do not append unsub message.
     * @param array $attachments Paths of attachements.Not implemented
     * @param string $fromName Sender name. If this is null take from system configuration
     * @param string $fromEmail Sender email address. If this is null take from system configuration
     * @return boolean $sendStatus true if email sent otherwise false
     */
    public function sendEmail($toAddress, $subject, $body, $unsubInfo = null, $attachments = null, $fromName = null, $fromEmail = null)
    {
        $ConfigModel = new Configuration();

        $config = $ConfigModel->getConfigurations();
        if (null == $this->mailJetUsername) {
            $this->mailJetUsername = '' == $config[Configuration::MAILJET_USERNAME] ? Yii::$app->params['smtp']['username'] : $config[Configuration::MAILJET_USERNAME];
            $this->mailJetPassword = '' == $config[Configuration::MAILJET_PASSWORD] ? Yii::$app->params['smtp']['password'] : $config[Configuration::MAILJET_PASSWORD];
        }
        Yii::$app->appLog->writeLog("Mailjet:{$this->mailJetPassword} / $this->mailJetPassword");
        $sendStatus = true;

        if (null == $fromEmail) {
            if (!isset($config['FROM_EMAIL'])) {
                $config = Configuration::getConfigurations();
            }
            $fromEmail = $config['FROM_EMAIL'];
            $fromName = $config['FROM_NAME'];
        } else if ($fromEmail == Yii::$app->params['smtp']['senderEmail']) { // system generated emails
            $this->mailJetUsername = Yii::$app->params['smtp']['username'];
            $this->mailJetPassword = Yii::$app->params['smtp']['password'];
        }

        Yii::$app->appLog->writeLog("Final Mailjet:{$this->mailJetPassword} / $this->mailJetPassword");
        $mj = new MailjetApi($this->mailJetUsername, $this->mailJetPassword);

        $unsubMessage = '';

        if (null != $unsubInfo) {
            $unsubMessage = $this->getUnSubInformation($unsubInfo['userId'], $unsubInfo['domain'], $unsubInfo['clientName']);
        }

        $params = array(
            "method" => "POST",
            "from" => $fromName . " <$fromEmail>",
            "sender" => Yii::$app->params['smtp']['senderEmail'],
            "subject" => $subject,
            "text" => '',
            "html" => urlencode($body . $unsubMessage)
        );

        $toStr = "";
        if (count($toAddress) > 1) {
            foreach ($toAddress as $toAdd) {
                $toStr .= "to={$toAdd}&";
            }
            $toStr = rtrim($toStr, '&');
            $params['to'] = array('multiple', $toStr);
        } else {
            $params['to'] = $toAddress[0];
        }

        $mj->sendRequest('send/message', $params, 'POST');
        Yii::$app->appLog->writeLog("Mailjet API response:{$mj->response}");

        if (!empty($mj->response)) {
            $resJson = $this->cleanMailjetResponse($mj->response, 'MSG_TEXT_BODY');
            $msgId = @$resJson['Data'][0]['ID'];
            if ('' != $msgId) {
                $this->emailTransactionId = $msgId;
                Yii::$app->appLog->writeLog("Email sent.Id:{$msgId}, To:" . json_encode($toAddress));
            } else {
                $sendStatus = false;
                Yii::$app->appLog->writeLog("Email sent failed. No transaction id");
            }
        } else {
            $sendStatus = false;
            Yii::$app->appLog->writeLog("Email sent failed. Response is empty. Error code:{$mj->errorCode}");
        }
        return $sendStatus;
    }


    /**
     * Clean MailJet api response. In windows 7 machine I get wierd output from
     * Maijet response. It may due to issue with php curl library
     * @param string $response Api response
     * @param
     * @return mixed
     */
    public function cleanMailjetResponse($response, $filter = 'MSG_HTML_BODY')
    {
        switch ($filter) {
            case 'MSG_HTML_BODY': // Response for HTML content
                //$resCleaned = '{' . preg_replace('/^[^,]*{\s*/', '', $response);
                $resCleaned = preg_replace('/:\s*(\-?\d+(\.\d+)?([e|E][\-|\+]\d+)?)/', ': "$1"', $response);
                //Yii::$app->appLog->writeLog("1:{$resCleaned}");
                $resCleaned = preg_replace('/HTTP(.*)close/s', "", $resCleaned);
                //Yii::$app->appLog->writeLog("2:{$resCleaned}");
                $resCleaned = '{' . preg_replace('/^[^,]*{\s*/', '', $resCleaned);
                //Yii::$app->appLog->writeLog("3:{$resCleaned}");
                $resCleaned = substr($resCleaned, 0, -1);
                //Yii::$app->appLog->writeLog("4:{$resCleaned}");
                $resJson = json_decode($resCleaned, true);
                //Yii::$app->appLog->writeLog("5:".print_r($resJson,true));
                break;

            case 'MSG_TEXT_BODY':
                $resCleaned = '{' . preg_replace('/^[^,]*{\s*/', '', $response);
                $resCleaned = preg_replace('/:\s*(\-?\d+(\.\d+)?([e|E][\-|\+]\d+)?)/', ': "$1"', $resCleaned);
                $resJson = json_decode($resCleaned, true);
                //Yii::$app->appLog->writeLog("5:".print_r($resJson,true));
                break;

            case 'RES_CAMP_ID':
                $resCleaned = '{' . preg_replace('/^[^,]*{\s*/', '', $response) . '}';
                $resJson = json_decode($resCleaned, true);
                break;
        }

        return $resJson;
    }

    /**
     * For bootstrap inline forms it does not add star mark for required fileds. So we add it manually
     * @return string HTML content of required filed mark
     */
    public function getRequiredFiledMark()
    {
        return ' <span class="required">*</span> ';
    }

    /**
     * Retrive initial longitude and latitude value to point the google map
     * @return array Lon Lat details
     */
    public function getDefLonLat()
    {
        $ipInfo = $this->getLocationByIp();
        if (null != $ipInfo) {
            list($lat, $lon) = explode(",", $ipInfo->loc);
        } else {
            list($lat, $lon) = explode(",", Yii::$app->params['defLongLat']);
        }

        return array(
            'lon' => $lon,
            'lat' => $lat
        );
    }

    /**
     * Get bootstrap label
     * @param string $type message type.default,success,warning,important,info,inverse
     * @return string HTML string of label
     */
    public function getBootLabel($type, $message)
    {
        $class = "default" == $type ? "badge badge-pill badge-info" : "badge badge-pill badge-{$type}";
        return "<span class='{$class}'>{$message}</span>";
    }

    public function getSmallBootLabel($type)
    {
        $class = "default" == $type ? "fa fa-minus" : "fa fa-{$type}";
        return "<span class='{$class}'></span>";
    }

    /**
     * Add an activity to database
     * @param string $type message type.default,success,warning,important,info,inverse
     * @return string HTML string of label
     * @throws \Exception
     */
    public function addActivity($userId, $actId, $teamId = 0, $params = '')
    {
        $teamId = is_null($teamId) ? 0 : $teamId;
        $this->changeDbConnectionWeb();
        if (!Yii::$app->session->get('is_super_admin')) {
            $model = new Activity();
            $model->dateTime = User::convertSystemTime();
            $model->teamId = $teamId;
            $model->userId = $userId;
            $model->activityMsgId = $actId;
            $model->params = $params;

            try {
                $model->save(false);
            } catch (Exception $e) {
                Yii::error('Add Activity error {{$e->getMessage()}}');
            }
        }
    }

    /**
     * Retrieve php maximum upload file size in bytes
     * @return integer Upload max size in bytes
     */
    public function getUploadMaxSize()
    {
        $sizeMb = trim(str_replace('M', '', ini_get("upload_max_filesize")));
        $sizeBytes = $sizeMb * 1024 * 1024;
        return $sizeBytes;
    }

    /**
     * Prepare video embed URL according to shared repository ex:Youtube, Vemio etc.
     * @param string Video URL
     * @return string Embeded URL
     */
    public function getVideoEmbedUrl($url, $repository = 'YOUTUBE')
    {
        $embedUrl = '';
        switch ($repository) {
            case 'YOUTUBE':
                parse_str(parse_url($url, PHP_URL_QUERY), $elements);
                $embedUrl = "https://www.youtube.com/embed/{$elements['v']}";
                break;

            case 'VIMEO':
                break;
        }

        return $embedUrl;
    }

    /**
     * Prepare Twitter share button
     * @param string Shared link
     * @return string Embeded URL
     */
    public function getTwitterShareButton($url, $text, $btnCount = 'horizontal')
    {
        $button = '<a href="https://twitter.com/share?ref_src=twsrc%5Etfw" class="twitter-share-button" data-url="' . $url . '" data-count="' . $btnCount . '">Tweet</a><script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>';
        return $button;
    }

    /**
     * Get Google+ share button
     * @param string Shared link
     * @return string Embeded URL
     */
    public function getGooglePlusShareButton($url)
    {
        $button = "<div class='g-plus' data-action='share' data-href='{$url}' data-annotation='bubble'></div>";
        return $button;
    }

    /**
     * Prepare Facebook share button
     * @param string Shared link
     * @return string Embeded URL
     */
    public function getFacebookShareButton($url, $btnType = 'button_count')
    {
        $button = '<div class="fb-share-button" data-href="' . $url . '" data-type="' . $btnType . '"></div><div id="fb-root"></div>';
        Yii::$app->controller->view->registerJs('fbShareBtn', "
				(function(d, s, id) {
					var js, fjs = d.getElementsByTagName(s)[0];
					if (d.getElementById(id)) return;
					js = d.createElement(s); js.id = id;
					js.src = '//connect.facebook.net/en_US/all.js#xfbml=2&appId=" . Yii::$app->facebook->appId . "';
					fjs.parentNode.insertBefore(js, fjs);
				}(document, 'script', 'facebook-jssdk'));"
        );
        return $button;
    }

    /**
     * Convert URLs in a string to Links
     * @param string $text Text with urls
     * @param string $id Id to be appended to link
     * @param string $title Title of the link
     * @return string Text with links
     */
    public function convertTextUrlsToLinks($text, $id = '', $title = '')
    {
        $textWithLinks = $text;
        $reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
        preg_match_all($reg_exUrl, $text, $matches);
        $urls = @$matches[0];

        if ('' != $urls) {
            foreach ($urls as $url) {
                $href = "<a id='{$id}' title='{$title}' class='tool' target='_blank' href='{$url}'>{$url}</a>";
                $textWithLinks = str_replace($url, $href, $textWithLinks);
            }
        }

        return $textWithLinks;
    }

    /**
     * Convert hash tags in a text to links
     * @param string $text Text with urls
     * @param string $network Social network identifier. TW,FB,GP
     * @param string $id Id to be appended to link
     * @param string $title Title of the link
     * @return string Text with links
     */
    public function convertHashtagsToLinks($text, $network, $id = '', $title = '')
    {
        $textWithLinks = $text;
        $regex = "/#(\w+)/";
        preg_match_all($regex, $text, $matches);
        $hashTags = @$matches[0];

        if ('' != $hashTags) {
            foreach ($hashTags as $hashTag) {
                if ('TW' == $network) {
                    $_hashTag = urlencode($hashTag);
                    $hashTagUrl = "http://twitter.com/search?q={$_hashTag}&src=hash";
                } else if ('GP' == $network) {
                    $_hashTag = urlencode($hashTag);
                    $hashTagUrl = "https://plus.google.com/s/{$_hashTag}";
                } else if ('FB' == $network) {
                    $_hashTag = str_replace('#', '', trim($hashTag));
                    $hashTagUrl = "https://www.facebook.com/hashtag/{$_hashTag}";
                }
                $href = "<a id='{$id}' title='{$title}' class='' target='_blank' href='{$hashTagUrl}'>{$hashTag}</a>";
                $textWithLinks = str_replace($hashTag, $href, $textWithLinks);
            }
        }

        return $textWithLinks;
    }

    /**
     * Convert Twitter mentions in a text to links
     * @param string $text Text with urls
     * @param string $network Social network identifier. TW,FB,GP
     * @param string $id Id to be appended to link
     * @param string $title Title of the link
     * @return string Text with links
     */
    public function convertMentionsToLinks($text, $network, $id = '', $title = '')
    {
        $textWithLinks = $text;
        $regex = "/@(\w+)/";
        preg_match_all($regex, $text, $matches);
        $mentions = @$matches[0];

        if ('' != $mentions) {
            foreach ($mentions as $mention) {
                if ('TW' == $network) {
                    $_mention = str_replace('@', '', $mention);
                }
                $mentionUrl = "http://twitter.com/{$_mention}";
                $href = "<a id='{$id}' title='{$title}' class='' target='_blank' href='{$mentionUrl}'>{$mention}</a>";
                $textWithLinks = str_replace($mention, $href, $textWithLinks);
            }
        }

        return $textWithLinks;
    }

    /**
     * Extract URLs from the text given
     * @param string $text Text with urls
     * @return array URLs in the string
     */
    public function extractUrlsFromText($text)
    {
        $regexUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
        preg_match_all($regexUrl, $text, $matches);
        $urls = @$matches[0];
        return $urls;
    }

    /**
     * Return html image tag for given image url
     * @param string $profImg
     * @param integer $width
     * @param integer $height
     * @param string $title
     * @return string
     */
    public function getPic($profImg = null, $width = 30, $height = 30, $title = "", $type = 1)
    {
        $altPic = $type == 1 ? Yii::$app->toolKit->getAltProfPic() : Yii::$app->toolKit->getAltContentPic();

        $pic = Html::image($altPic, '', array('width' => $width, 'height' => $height, 'class' => 'thumbnail tool', 'title' => $title));

        if (null != $profImg) {
            $pic = Html::image($profImg, '', array('width' => $width, 'height' => $height, 'class' => 'thumbnail tool', 'title' => $title, 'onerror' => 'this.src="' . $altPic . '"'));
        }
        return $pic;
    }

    /**
     * Retrieve currency related information
     * @param string $infoType Required currency information type
     * @param string $code Currency code
     * @return mixed
     */
    public function getCurrencyInfo($infoType, $code = null)
    {
        switch ($infoType) {

            case 'ALL_OPTIONS':

                $options = array();
                foreach (Yii::$app->params['currencyTypes'] as $code => $info) {
                    $options[$code] = "{$info['name']}({$info['symbol']})";
                }

                return array('' => Yii::t('messages', '--- Currency ---')) + $options;

                break;

            case 'SYMBOL':

                $info = Yii::$app->params['currencyTypes'][$code];

                return $info['symbol'];

                break;
        }
    }

    /**
     * Send SMS message
     * @param string $to Recepient number
     * @param string $text SMS text to be sent
     * @param integer $callback Callback parameter
     * @return string SMS id
     */
    public function sendSMS($to, $text, $callback = 2)
    {
        $smsInfo = Configuration::getConfigFromSmsOption(true);
        $smsSenderId = Configuration::findOne(Configuration::SMS_SENDER_ID)->value;
        if(!empty($smsInfo)) {
            // Set SMS Component Dynamically according to Users Configuration
            Yii::$app->setComponents([
                'sms' => [
                    'class' => 'wadeshuler\sms\twilio\Sms',

                    // Advanced app use '@common/sms', basic use '@app/sms'
                    'viewPath' => '@app/sms',     // Optional: defaults to '@app/sms'
                    // send all sms to a file by default. You have to set
                    // 'useFileTransport' to false and configure the messageConfig['from'],
                    // 'sid', and 'token' to send real messages
                    'useFileTransport' => false,

                    'messageConfig' => [
                        'from' => !empty($smsSenderId) ? $smsSenderId: $smsInfo['mobile'],  // Your Twilio number (full or shortcode)
                    ],

                    // Find your Account Sid and Auth Token at https://twilio.com/console
                    'sid' => $smsInfo['sid'],
                    'token' => $smsInfo['token'],

                    // Tell Twilio where to POST information about your message.
                    // @see https://www.twilio.com/docs/sms/send-messages#monitor-the-status-of-your-message
                    //'statusCallback' => 'https://example.com/path/to/callback',      // optional
                    'statusCallback' => $smsInfo['statusCallBack'],      // optional
                ]
            ]);
        }

        $msg = $text;
        try {
            $res = Yii::$app->sms->compose()
                ->setFrom(Yii::$app->sms->messageConfig['from'])
                ->setTo($to)
                ->setMessage($msg)
                ->send();

            if ($res->sid) {  // Response not receiving if has any errors.
                Yii::$app->appLog->writeLog("Sending SMS. Response Sid:{$res->sid}"); //A 34 character string that uniquely identifies this resource
                return $res->sid;
            } else {
                Yii::$app->appLog->writeLog("Sending SMS Failed");
                return false;
            }
        } catch (\Exception $ex) {
            Yii::$app->appLog->writeLog("Sending SMS Failed. Exception:{$ex->getMessage()}");
            return false;
        }
    }

    /**
     * Replace last occurance of a string
     * @param string $search String to be searched
     * @param string $replace Replacing word
     * @param string $subject Original string
     * @return string $subject Formatted string
     */
    public function strReplaceLast($search, $replace, $subject)
    {
        $pos = strrpos($subject, $search);

        if ($pos !== false) {
            $subject = substr_replace($subject, $replace, $pos, strlen($search));
        }

        return $subject;
    }

    /**
     * Convert string to unicode
     * @param string $string String to be converted
     * @return string $in Unicode string
     */
    function string2Unicode($string)
    {
        $in = '';
        $out = iconv('UTF-8', 'UTF-16BE', $string);
        for ($i = 0; $i < strlen($out); $i++) {
            $in .= sprintf("%02X", ord($out[$i]));
        }
        return $in;
    }

    /**
     * Prepare web root URL according to the subdomain
     * @return string URL
     */
    public function getWebRootUrl()
    {
        return 'http://' . $this->domain . '/';
    }

    /**
     * Convert under_score type array's keys to camelCase type array's keys
     * @param array $array array to convert
     * @param array $arrayHolder parent array holder for recursive array
     * @return  array   camelCase array
     */
    public function camelCaseKeys($array, $arrayHolder = array())
    {
        $camelCaseArray = !empty($arrayHolder) ? $arrayHolder : array();
        foreach ($array as $key => $val) {
            $newKey = @explode('_', $key);
            array_walk($newKey, create_function('&$v', '$v = ucwords($v);'));
            $newKey = @implode('', $newKey);
            $newKey{0} = strtolower($newKey{0});
            if (!is_array($val)) {
                $camelCaseArray[$newKey] = $val;
            } else {
                $camelCaseArray[$newKey] = @$this->camelCaseKeys($val, $camelCaseArray[$newKey]);
            }
        }
        return $camelCaseArray;
    }

    /**
     * Checks if the given value is empty.
     * A value is considered empty if it is null, an empty array, or the trimmed result is an empty string.
     * Note that this method is different from PHP empty(). It will return false when the value is 0.
     * @param mixed $value The value to be checked
     * @param boolean $trim Whether to perform trimming before checking if the string is empty. Defaults to true.
     * @return boolean Whether the value is empty
     */
    public static function isEmpty($value, $trim = true)
    {
        return $value === null || $value === array() || $value === '' || $trim && is_scalar($value) && trim($value) === '';
    }

    /**
     * Checks if the given value is set for $_POST.
     * @return null if the value is not set otherwise the value
     */
    public static function post($key, $default = NULL, $checkTextNull = false)
    {
        $res = isset($_POST[$key]) ? $_POST[$key] : $default;
        return $checkTextNull && $res == 'null' ? $default : $res;
    }

    /**
     * Checks if the given value is set for $_GET.
     * @return null if the value is not set otherwise the value
     */
    public static function get($key, $default = NULL, $checkTextNull = false)
    {
        $res = isset($_GET[$key]) ? $_GET[$key] : $default;
        return $checkTextNull && $res == 'null' ? $default : $res;
    }

    /**
     * Register scripts needed to load data map
     */
    public static function registerDataMapScript()
    {
        $apiKey = Yii::$app->params['google']['apiKey'];
        echo "<script src='https://maps.googleapis.com/maps/api/js?v=3.exp&key={$apiKey}&sensor=false&libraries=drawing'></script>";
        echo '<script src="https://jawj.github.io/OverlappingMarkerSpiderfier/bin/oms.min.js"></script>';
//		echo '<script src="http://google-maps-utility-library-v3.googlecode.com/svn/trunk/markerclustererplus/src/markerclusterer.js"></script>';
        echo '<script src="https://cdn.rawgit.com/googlemaps/js-marker-clusterer/gh-pages/src/markerclusterer.js"></script>';

    }


    /**
     * Register scripts needed to load osm map
     */

    public static function registerDataOsmMapScript()
    {
        $apiKey = Yii::$app->params['openStreetMap']['consumerKey'];
        echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/leaflet.css" />';
        echo '<link type="text/css" rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.0.6/dist/MarkerCluster.css"/>';
        echo '<link type="text/css" rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.0.6/dist/MarkerCluster.Default.css"/>';
        echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/leaflet.js"></script>';
        echo "<script src='https://www.mapquestapi.com/sdk/leaflet/v2.2/mq-map.js?key={$apiKey}'></script>";
        echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/0.4.2/leaflet.draw.css"/>';
        echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/0.4.2/leaflet.draw.js"></script>';
        echo '<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/0.4.0/leaflet.markercluster.js"></script>';
        echo "<script src=\"https://www.mapquestapi.com/sdk/leaflet/v2.2/mq-geocoding.js?key={$apiKey}\"></script>";
        echo "<script src='https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/Leaflet.fullscreen.min.js'></script>";
        echo "<link href='https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/leaflet.fullscreen.css' rel='stylesheet' />";
    } 
    
    /**
     * dynamicly getting Open Data Map raect js file form folder
     */

    public static function registerOpenMapDataScript()
    {
 
        $dataMap = Yii::getAlias('@webroot/themes/bootstrap_spacelab/data-map-react/build');
        $manifestJsonFile = file_get_contents("$dataMap/asset-manifest.json");
        $manifestJson = json_decode($manifestJsonFile, true);
        $mainCss = $manifestJson['entrypoints'][0];
        $mainJs = $manifestJson['entrypoints'][1];
        echo "<script src='/themes/bootstrap_spacelab/data-map-react/build/$mainJs' defer='defer'></script>";
        echo "<link rel='stylesheet' href='/themes/bootstrap_spacelab/data-map-react/build/$mainCss' />"; 
      }

    /**
     * to check if site is running in https or http
     */
    public function isSecure()
    {
        return
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || $_SERVER['SERVER_PORT'] == 443;
    }

    /**
     * to check if site is running in staging or production environment
     */
    public function isStaging()
    {
        $stagingMainDomainList = array('moncenis.com'); // can have more than one
        $res = explode(".", $this->domain);
        array_shift($res);
        $mainDomain = implode('.', $res); //ex: moncenis.com
        if (in_array($mainDomain, $stagingMainDomainList))
            return true;
        else
            return false;

    }

    /**
     * to check if the given latitude and longitude reside inside the polygon coordinates
     * @param $pointsPolygon
     * @param $verticesX
     * @param $verticesY
     * @param $longitudeX
     * @param $latitudeY
     * @return boolean
     */
    function inPolygon($pointsPolygon, $verticesX, $verticesY, $longitudeX, $latitudeY)
    {
        $i = $j = $c = 0;
        for ($i = 0, $j = $pointsPolygon; $i <= $pointsPolygon; $j = $i++) {
            if ((($verticesY[$i] > $latitudeY != ($verticesY[$j] > $latitudeY)) && ($longitudeX < ($verticesX[$j] - $verticesX[$i]) * ($latitudeY - $verticesY[$i]) / ($verticesY[$j] - $verticesY[$i]) + $verticesX[$i]))) {
                $c = !$c;
            }
        }
        return $c;
    }

    //the Point in Polygon function
    // TODO This code no longer use. If complete the functional test will remove
    function pointInPolygon($lat, $long, $verticesLat, $verticesLong)
    {
        //$lat,$long
        //if you operates with (hundred)thousands of points
        set_time_limit(60);
        $c = 0;
        $p1X = $verticesLat[0]; //lat
        $p1Y = $verticesLong[0]; //long
        $n = count($verticesLong);
        for ($i = 1; $i <= $n; $i++) {
            $p2X = $verticesLat[$i % $n];
            $p2Y = $verticesLong[$i % $n];
            if ($long > min($p1Y, $p2Y)
                && $long <= max($p1Y, $p2Y)
                && $lat <= max($p1X, $p2X)
                && $p1Y != $p2Y
            ) {
                $xinters = ($long - $p1Y) * ($p2X - $p1X) / ($p2Y - $p1Y) + $p1X;
                if ($p1X == $p2X || $lat <= $xinters) {
                    $c++;
                }
            }
            $p1X = $p2X;
            $p1Y = $p2Y;
        }
        // if the number of edges we passed through is even, then it's not in the poly.
        return $c % 2 != 0;
    }

    /**
     * get theme style for google map. ex: Sign up screen
     * @return null if the value is not set otherwise the theme related json
     */
    public function getMapStyle($style)
    {
        $styleJson = null;
        switch ($style) {
            case 'theme-red':
                $styleJson = '[{"stylers":[{"hue":"#dd0d0d"}]},{"featureType":"road","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"road","elementType":"geometry","stylers":[{"lightness":100},{"visibility":"simplified"}]}]';
                break;
            case 'theme-purple':
                $styleJson = '[{"featureType":"landscape.natural","elementType":"geometry.fill","stylers":[{"visibility":"on"},{"color":"336d75"}]},{"featureType":"poi","elementType":"geometry.fill","stylers":[{"visibility":"on"},{"hue":"#1900ff"},{"color":"#d064a4"}]},{"featureType":"landscape.man_made","elementType":"geometry.fill"},{"featureType":"road","elementType":"geometry","stylers":[{"lightness":100},{"visibility":"simplified"}]},{"featureType":"road","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"water","stylers":[{"color":"#6bb1e1"}]},{"featureType":"transit.line","elementType":"geometry","stylers":[{"visibility":"on"},{"lightness":700}]}]';
                break;
            case 'theme-blue':
                $styleJson = '[{"featureType":"administrative","elementType":"labels.text.fill","stylers":[{"color":"#444444"}]},{"featureType":"landscape","elementType":"all","stylers":[{"color":"#f2f2f2"}]},{"featureType":"poi","elementType":"all","stylers":[{"visibility":"on"},{"color":"#58d186"},{"saturation":"-24"},{"lightness":"63"}]},{"featureType":"poi","elementType":"labels.text.fill","stylers":[{"color":"#676767"}]},{"featureType":"poi","elementType":"labels.text.stroke","stylers":[{"visibility":"off"},{"color":"#979797"}]},{"featureType":"poi","elementType":"labels.icon","stylers":[{"visibility":"off"},{"color":"#ff0000"}]},{"featureType":"road","elementType":"all","stylers":[{"saturation":-100},{"lightness":45}]},{"featureType":"road.highway","elementType":"all","stylers":[{"visibility":"simplified"}]},{"featureType":"road.arterial","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"transit","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"water","elementType":"all","stylers":[{"color":"#46bcec"},{"visibility":"on"}]}]';
                break;
            case 'theme-green':
                $styleJson = '[{"featureType":"landscape","stylers":[{"hue":"#FFA800"},{"saturation":0},{"lightness":0},{"gamma":1}]},{"featureType":"road.highway","stylers":[{"hue":"#53FF00"},{"saturation":-73},{"lightness":40},{"gamma":1}]},{"featureType":"road.arterial","stylers":[{"hue":"#FBFF00"},{"saturation":0},{"lightness":0},{"gamma":1}]},{"featureType":"road.local","stylers":[{"hue":"#00FFFD"},{"saturation":0},{"lightness":30},{"gamma":1}]},{"featureType":"water","stylers":[{"hue":"#00BFFF"},{"saturation":6},{"lightness":8},{"gamma":1}]},{"featureType":"poi","stylers":[{"hue":"#679714"},{"saturation":33.4},{"lightness":-25.4},{"gamma":1}]}]';
                break;
            default:
                //theme-green
                $styleJson = '[{"featureType":"landscape","stylers":[{"hue":"#FFA800"},{"saturation":0},{"lightness":0},{"gamma":1}]},{"featureType":"road.highway","stylers":[{"hue":"#53FF00"},{"saturation":-73},{"lightness":40},{"gamma":1}]},{"featureType":"road.arterial","stylers":[{"hue":"#FBFF00"},{"saturation":0},{"lightness":0},{"gamma":1}]},{"featureType":"road.local","stylers":[{"hue":"#00FFFD"},{"saturation":0},{"lightness":30},{"gamma":1}]},{"featureType":"water","stylers":[{"hue":"#00BFFF"},{"saturation":6},{"lightness":8},{"gamma":1}]},{"featureType":"poi","stylers":[{"hue":"#679714"},{"saturation":33.4},{"lightness":-25.4},{"gamma":1}]}]';
                break;
        }
        return $styleJson;

    }

    /*
     * function to get css and js related to google calender.
     */
    public function getFullCalendarScripts()
    {
        $BaseUrl = Yii::$app->getUrlManager()->createAbsoluteUrl('/');

        $ThemeUrl = Yii::$app->view->theme->baseUrl;

        $commonCssBaseUrl = $this->getThemeBaseUri('css');
        $commonJsBaseUrl = $this->getThemeBaseUri('js');

        Yii::$app->view->registerCssFile($ThemeUrl . '/css/fullcalendar.min.css');
        Yii::$app->view->registerCssFile($ThemeUrl . '/css/fullcalendar.print.min.css', ['media' => 'print',]);
        Yii::$app->view->registerJsFile($commonJsBaseUrl[1] . '/moment.min.js', ['position' => View::POS_END]);
        Yii::$app->view->registerJsFile($commonJsBaseUrl[1] . '/fullcalendar.min.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
    }


    /**
     * Potential merge: return what data to be merge in display screen
     * @note need to get this class to potential merger model
     */

    public function getPotentialMergeFormField($parentDataVal, $childDataVal, $fieldName = null)
    {
        $formField = array();
        switch ($fieldName) {
            case 'countryCode':
                $parentData = Country::getContryByCode($parentDataVal);
                $childData = Country::getContryByCode($childDataVal);
                if ($parentDataVal != null) {
                    $formField[$parentDataVal] = $parentData;
                }
                if ($childDataVal != null) {
                    $formField[$childDataVal] = $childData;
                }
                break;

            case 'gender':
                $parentData = User::getGenderLabel($parentDataVal);
                $childData = User::getGenderLabel($childDataVal);
                if ($parentDataVal != null) {
                    $formField[$parentDataVal] = $parentData;
                }
                if ($childDataVal != null) {
                    $formField[$childDataVal] = $childData;
                }
                break;


            default:
                if ($parentDataVal != null) {
                    $formField[$parentDataVal] = $parentDataVal;
                }
                if ($childDataVal != null) {
                    $formField[$childDataVal] = $childDataVal;
                }
                break;

        }
        return $formField;
    }

    /**
     * @param $statusFile
     */
    public function downloadStatusFile($statusFile, $type)
    {
        if (isset($statusFile)) {
            $src = Yii::$app->params['fileUpload'][$type]['path'] . $statusFile;
            if (@file_exists($src)) {
                @pathinfo($src);
                $mime = 'text/csv';
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Type: ' . $mime);
                header('Content-Disposition: attachment; filename=' . basename($src));
                header('Content-Transfer-Encoding: binary');
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: public');
                header('Content-Length: ' . filesize($src));
                ob_clean();
                flush();
                readfile($src);
            } else {
                header("HTTP/1.0 404 Not Found");
                exit();
            }
        }
    }


    public static function registerAdvanceSearchScript()
    {
        echo '<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>';
        echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/16.0.4/js/intlTelInput.js"></script>';
        echo '<link href="/css/advancedSearch.css" rel="stylesheet" type="text/css">';
    }

    public static function registerAdvanceSearchUpdate()
    {
        echo '<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>';
        echo '<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet" type="text/css">';
        echo '<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>';
        echo '<link href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/16.0.4/css/intlTelInput.css" rel="stylesheet" type="text/css">';
        echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/16.0.4/js/intlTelInput.js"></script>';
    }

    /**
     * @param $dirPath
     */
    public function removeDirectory($dirPath)
    {
        if (!is_dir($dirPath)) {
            throw new InvalidArgumentException('$dirPath must be a directory');
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                self::deleteDir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
    }
}
