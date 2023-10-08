<?php

use app\widgets\Alert;
use yii\helpers\Html;

$attributeLabels = $model->attributeLabels();

/* @var $this yii\web\View */
/* @var $model app\models\User */

$this->title = Yii::t('app', 'Update User: {name}', [
    'name' => $model->firstName,
]);
$this->titleDescription = Yii::t('messages', 'Update existing user');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Users'), 'url' => ['user/update']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Update')];
?>
<div class="user-update">

    <?= $this->render('_form', [
        'model' => $model,
        'roles' => $roles,
        'currentRole' => $currentRole,
        'attributeLabels' => $attributeLabels
    ]) ?>

</div>
