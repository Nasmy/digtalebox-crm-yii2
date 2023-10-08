<?php

namespace app\controllers;
use borales\extensions\phoneInput\PhoneInputBehavior;
use Yii;
use app\models\Campaign;
use app\components\TwitterApi;
use app\models\Feed;
use app\models\User;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\controllers\WebUserController;

class DashboardController extends WebUserController
{
    public $layout = 'column1';

    /**
     * {@inheritdoc}
     */

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],

                ],
            ],
        ];
    }


    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    public function beforeAction($action) {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function allowed()
    {
        return [
            'Dashboard.GetFeedMini',
            'Dashboard.GetActivities',
            'Dashboard.Dashboard',
            'Dashboard.Index'
        ];
    }

    public function actionAccessDenied()
    {
        return $this->render('accessDenied', []);
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionDashboard()
    {
        $userModal = new User();
        $campainModal = new Campaign();
        $userCount = $userModal->getUserCountByUserType();

        $userCountByTimeLine = $userModal->getUserCountByTimeLine(date('Y'));

        $userCountByCampaignMedia = $userModal->getUserCountByCampaignMedia();

        $userCountByCampaignMediaTimeLine = $campainModal->getCampaignCountByCampaignMediaTimeLine(date('Y'));

        return $this->render('dashboard', ['userCount' => $userCount,
            'userCountByTimeLine' => $userCountByTimeLine,
            'userCountByCampaignMedia' => $userCountByCampaignMedia,
            'userCountByCampaignMediaTimeLine' => $userCountByCampaignMediaTimeLine
        ]);
    }

    public function actionGetFeedMini()
    {
        $this->layout = '';

        $results = Feed::find()->orderBy('dateTime DESC')->limit(5)->all();
        $userModel = new User();

        if (null != $results) {
            $str = '';
            $icon = '';
            foreach ($results as $model) {
                switch ($model->network){
                   /* case FacebookApi::FACEBOOK:
                        $icon = '<div class="account"><span class="icons"><i class="fa fb fa-facebook-square"></i></span><span class="account-name">@'.$model->name.'</span></div>';
                        break;*/
                   case TwitterApi::TWITTER:
                        $icon = '<div class="account"><span class="icons"><i class="fa tw fa-twitter-square"></i></span><span class="account-name">@'.$model->name.'</span></div>';
                        break;
                    /*case \LinkedInApi::LINKEDIN:
                        $icon = '<div class="account"><span class="icons"><i class="fa in fa-linkedin-square"></i></span><span class="account-name">@'.$model->name.'</span></div>';
                        break;*/

                    default:
                        $icon = '<div class="account"><span class="account-name">@'.$model->name.'</span></div>';
                        break;
                }

                $str .= '<a class="line" href="#"> <div class="media">';
                $profImg = $userModel->getPic($model->profImageUrl, 40, 40);
                $timelapse = "<div class='activity-mini-grid-timelapse'>" . Feed::getTimeElapsed($model->dateTime) . "</div>";
                $str .= "<div class=\"profile-pic\">" . $profImg . "</div> <div class=\"media-body text\"> <div class=\"desc wordBreak\">".$model->text.$timelapse . "</div>";
                $str .= $icon.'</div></div></a>';
            }
            return $str;
        }
    }

}
