<?php
use yii\bootstrap\Tabs;
use app\controllers;
use yii\web\JqueryAsset;

JqueryAsset::register($this);

echo Tabs::widget([
    'items' => [
        [
            'label'=>Yii::t('messages', 'Overview'),
            'active' =>  Yii::$app->controller->action->id == 'statistics',
            'options' => ['class'=>"nav-item"],
            'visible'=>true,
            'url' => ['people/statistics'],
            'id' => 'ppl-safolup-feed'
        ],
        [
            'active' =>  Yii::$app->controller->action->id == 'analytics',
            'label' => yii::t('messages', 'Analytics'),
            'options' => ['class'=>"nav-item"],
            'visible'=>true,
            'url' => ['people/analytics'],
            'id' => 'ppl-safolup-folup'
        ],
        [
            'active' =>  Yii::$app->controller->action->id == 'posts',
            'visible'=>true,
            'label' => yii::t('messages', 'Posts'),
            'options' => ['class'=>"nav-item"],
            'url' => ['people/posts'],
            'data-count'=>"1/10"
        ],
        [
            'active' =>  Yii::$app->controller->action->id == 'population',
            'visible'=>true,
            'label' => yii::t('messages', 'People Statistics'),
            'options' => ['class'=>"nav-item"],
            'url' => ['people/population'],
            'data-count'=>"1/10"
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
