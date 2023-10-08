<?php

use app\models\EventUser;
use yii\bootstrap\Tabs;
use app\controllers;
use yii\helpers\Url;
use yii\web\JqueryAsset;

JqueryAsset::register($this);
$attributeLabels = $model->attributeLabels();
$paramKey = 0; //RSVP disable

if (isset($_GET['key'])) {
    $eventReminderModel->rsvpStatus = $_GET['key'];
    $paramKey = $eventReminderModel->rsvpStatus;
}
foreach ($rsvpGroups as $key => $group) {
    $attribute = EventUser::getRsvpString($key);
    $url = Url::to(['event/view', 'id' => $model->id, 'key' => $key]);
    $items[] = array(
        'label' => $attributeLabels["$attribute"] . '(' . $group . ')',
        'url' => $url,
        'active' => in_array($paramKey, array($key)),
        'visible' => Yii::$app->user->checkAccess('Event.View'),
        'options' => array('id' => 'ppl-event-participants', 'class' => "nav-item")
    );

}

echo Tabs::widget([
    'items' => $items,
    'options' => ['class' => "nav-item"],
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

    .list-view {
        min-height: 300px;
        height: 100%;
    }

    html {
        font-size: initial !important;
        padding: initial !important;
    }
</style>
