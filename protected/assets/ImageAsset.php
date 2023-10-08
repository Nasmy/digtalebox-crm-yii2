<?php
namespace app\assets;


use yii\web\AssetBundle;

class ImageAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseurl = '@web';
    public $css = [
        // 'themes/bootstrap_spacelab/css/old/bootstrap.min.css', // Using for grids layouts issue fix
        'themes/bootstrap_spacelab/css/jquery.mCustomScrollbar.min.css',
        'themes/bootstrap_spacelab/css/cropper.css',
        'themes/bootstrap_spacelab/css/cropper-main.css',
    ];
    public $js = [
        // 'themes/bootstrap_spacelab/js/jquery-3.3.1.slim.min.js',
        'themes/bootstrap_spacelab/js/jquery.min.js',
        'themes/bootstrap_spacelab/js/popper.min.js',
        'themes/bootstrap_spacelab/js/vendor/bootstrap.bundle.min.js',
        'themes/bootstrap_spacelab/js/cropper.js',
        'themes/bootstrap_spacelab/js/jquery-cropper.js',
        'themes/bootstrap_spacelab/js/moment.min.js',
        'themes/bootstrap_spacelab/js/jquery.mCustomScrollbar.concat.min.js',

    ];
    public $depends = [
        'yii\web\YiiAsset',
    ];
}