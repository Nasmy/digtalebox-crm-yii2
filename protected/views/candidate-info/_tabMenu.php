<?php

use app\assets\AppAsset;
use yii\bootstrap\Tabs;
//use yii\jui\Tabs;
use app\controllers;

echo Tabs::widget([
    'items' => [
        [
            'label'=>Yii::t('messages', 'Images'),
            'active' =>  in_array(Yii::$app->controller->action->id, array('manage-images')),
            'options' => ['class'=>"nav-item"],
            'visible'=>Yii::$app->user->checkAccess('CandidateInfo.ManageImages'),
            'url' => ['candidate-info/manage-images'],

        ],
        [
            'active' =>  in_array(Yii::$app->controller->action->id, array('update-texts')),
            'label' => 'Texts',
            'options' => ['class'=>"nav-item"],
            'visible'=>Yii::$app->user->checkAccess('CandidateInfo.UpdateTexts'),
            'url' => ['candidate-info/update-texts'],

        ],
        [
            'active' =>  in_array(Yii::$app->controller->action->id, array('theme')),
            'visible'=>Yii::$app->user->checkAccess('CandidateInfo.Theme'),
            'label' => 'Theme',
            'options' => ['class'=>"nav-item"],
            'url' => ['candidate-info/theme'],

        ],
        [
            'active' =>  in_array(Yii::$app->controller->action->id, array('change-bg-image')),
            'visible'=>Yii::$app->user->checkAccess('CandidateInfo.ChangeBgImage'),
            'label' => 'Background',
            'options' => ['class'=>"nav-item"],
            'url' => ['candidate-info/change-bg-image'],
        ],
    ],
    'options' => ['class'=>"nav-item"],
    'headerOptions' => ['class' => 'nav-item'],
    'itemOptions' => ['class' => 'nav-item'],
    'clientOptions' => ['collapsible' => false],
]);

?>


<style type="text/css">
    a.navbar-brand {
        height: inherit;
        padding-left: 0;
    }

    .list-view{
        min-height: 300px;
        height: 100%;
    }

    html{
        font-size: initial !important;
        padding: initial !important;
    }
</style>
