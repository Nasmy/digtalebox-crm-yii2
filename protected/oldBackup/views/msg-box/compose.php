<?php

use yii\helpers\Html;
$attributeLabels = $model->attributeLabels();

/* @var $this yii\web\View */
/* @var $model app\models\MsgBox */

$this->title = 'Compose';
$this->titleDescription = Yii::t('messages', 'Send message to a user or group of users');


$this->params['breadcrumbs'][] = ['label' => 'Communication', 'url' => ['#']];
$this->params['breadcrumbs'][] = ['label' => 'Inbox', 'url' => ['msg-box/inbox']];
$this->params['breadcrumbs'][] = ['label' => 'Compose'];
?>

     <?php echo  Yii::$app->controller->renderPartial('_tabMenu'); ?>

    <?= $this->render('_form', [
        'model'=>$model,
        'criteriaOptions'=>$criteriaOptions,
        'userlist'=>$userlist,
        'attributeLabels' => $attributeLabels
    ]) ?>

