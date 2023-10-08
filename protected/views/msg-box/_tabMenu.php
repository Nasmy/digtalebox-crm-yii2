<?php
use yii\bootstrap\Tabs;
use app\controllers;
use yii\web\JqueryAsset;

JqueryAsset::register($this);

echo Tabs::widget([
    'items' => [
        [
            'label'=>Yii::t('messages', 'Inbox'),
            'active' =>  Yii::$app->controller->action->id == 'inbox',
            'options' => ['class'=>"nav-item"],
            'visible'=>true,
            'url' => ['msg-box/inbox'],

        ],
        [
            'active' =>  Yii::$app->controller->action->id == 'compose',
            'label' => yii::t('messages', 'Compose'),
            'options' => ['class'=>"nav-item"],
            'visible'=>Yii::$app->user->checkAccess('MsgBox.Compose'),
            'url' => ['msg-box/compose'],

        ],
        [
            'active' =>  Yii::$app->controller->action->id == 'sent-items',
            'visible'=>Yii::$app->user->checkAccess('MsgBox.SentItems', 'MsgBox.ViewSentItems'),
            'label' => yii::t('messages', 'Sent Items'),
            'options' => ['class'=>"nav-item"],
            'url' => ['msg-box/sent-items'],

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
