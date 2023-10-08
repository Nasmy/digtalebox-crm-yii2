<?php
namespace app\assets;
use yii\web\AssetBundle;
/**
 * Created by PhpStorm.
 * User: nasmy
 * Date: 8/20/2019
 * Time: 9:59 AM
 */
class LoginAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseurl = '@web';
    public $css = [
        // 'themes/bootstrap_spacelab/css/query.mCustomScrollbar.min.css',
        'themes/bootstrap_spacelab/css/front-pages.css',
        'themes/bootstrap_spacelab/css/bootstrap.min.css',
        'themes/bootstrap_spacelab/css/bootstrap-grid.min.css',
        'themes/bootstrap_spacelab/css/bootstrap-reboot.min.css',
        'themes/bootstrap_spacelab/css/font-awesome.min.css',
        'themes/bootstrap_spacelab/css/metis-menu.min.css',
        'themes/bootstrap_spacelab/css/mm-vertical.css',
        'themes/bootstrap_spacelab/css/minimal.css',
        'themes/bootstrap_spacelab/css/owl.carousel.min.css',
        'themes/bootstrap_spacelab/css/jquery.mCustomScrollbar.min.css',
        'themes/bootstrap_spacelab/css/tempusdominus-bootstrap-4.min.css',
        'themes/bootstrap_spacelab/css/chosen.min.css',
        'themes/bootstrap_spacelab/css/bootstrap-toggle.min.css',
        'themes/bootstrap_spacelab/css/main.css',
        'themes/bootstrap_spacelab/css/custom-overight.css'
    ];
    public $js = [
        'themes/bootstrap_spacelab/js/vendor/modernizr-2.8.3-respond-1.4.2.min.js',
        'themes/bootstrap_spacelab/js/vendor/bootstrap.bundle.min.js',
        'themes/bootstrap_spacelab/js/moment.min.js',
        'themes/bootstrap_spacelab/js/icheck.min.js',
        'themes/bootstrap_spacelab/js/jquery.slimscroll.min.js',
        'themes/bootstrap_spacelab/js/metisMenu.min.js',
        'themes/bootstrap_spacelab/js/bootstrap-show-password.min.js',
        'themes/bootstrap_spacelab/js/bootstrap-show-password.min.js',
        'themes/bootstrap_spacelab/js/owl.carousel.min.js',
        'themes/bootstrap_spacelab/js/tempusdominus-bootstrap-4.min.js',
        'themes/bootstrap_spacelab/js/chosen.jquery.min.js',
        'themes/bootstrap_spacelab/js/bootstrap-toggle.min.js',
        'themes/bootstrap_spacelab/js/jquery.mCustomScrollbar.concat.min.js',
        'themes/bootstrap_spacelab/js/main.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
    ];
}