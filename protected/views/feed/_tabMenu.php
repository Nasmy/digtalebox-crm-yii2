<?php
use yii\bootstrap\Tabs;
use app\controllers;
use yii\web\JqueryAsset;
 JqueryAsset::register($this);
 echo Tabs::widget([
    'items' => [
        [
            'label'=>Yii::t('messages', 'Social Activities'),
            'active' =>  Yii::$app->controller->action->id == 'social-feed',
            'options' => ['class'=>"nav-item"],
            'visible'=>true,
            'url' => ['feed/social-feed'],
            'id' => 'ppl-safolup-feed'
        ],
        [
            'active' =>  Yii::$app->controller->id == 'broadcast-message',
            'label' => yii::t('messages', 'Broadcast'),
            'options' => ['class'=>"nav-item"],
            'visible'=>true,
            'url' => ['broadcast-message/admin'],
            'id' => 'ppl-broadcast-feed',
            'data-count'=>"1/10"
        ]
    ],
    'options' => ['class'=>"nav-item"],
    'headerOptions' => ['class' => 'nav-item '],
    'itemOptions' => ['class' => 'nav-item nav nav-tabs'],
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
