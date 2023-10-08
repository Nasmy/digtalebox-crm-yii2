<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Main application asset bundle.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DialogAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseurl = '@web';
    public $css = [
        // 'themes/bootstrap_spacelab/css/query.mCustomScrollbar.min.css',
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
        'themes/bootstrap_spacelab/js/intlTelInput-jquery.js',
        // 'themes/bootstrap_spacelab/js/tinymce/js/tinymce/tinymce.min.js',
        // 'themes/bootstrap_spacelab/fancybox/fancybox/source/jquery.fancybox.js'

     ];
    public $depends = [
        'yii\web\YiiAsset',
        // 'yii\web\JqueryAsset'
    ];
}
