<?php

use app\models\AuthItem;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\AuthItem */
$attributeLabels = $model->attributeLabels();
$this->title = 'Update Permission';
// $this->titleDescription = Yii::t('messages', 'Add new permission item');
$this->params['breadcrumbs'][] = ['label' => 'System', 'url' => ['admin', 'type' => AuthItem::TYPE_OPERATION]];
// $this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'System')];
$this->params['breadcrumbs'][] = ['label' => 'Manage Permission', 'url' => ['admin', 'type' => AuthItem::TYPE_OPERATION]];
$this->params['breadcrumbs'][] = 'Update Permission';
?>
<div class="auth-item-update">

    <?= $this->render('_form', [
        'model' => $model,
        'attributeLabels' => $attributeLabels
    ]) ?>

</div>
