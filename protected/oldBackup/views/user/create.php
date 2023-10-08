<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\User */
$attributeLabels = $model->attributeLabels();
$this->title = Yii::t('app', 'Create User');
$this->titleDescription = Yii::t('messages', 'Add new user to the system');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Users'), 'url' => ['user/admin']];
// $this->params['breadcrumbs'][] = $this->title;
// $this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'System'), 'url' => ['user/admin']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Create')];
?>
<div class="user-create">



    <?= $this->render('_form', [
        'model' => $model,
        'roles'=>$roles,
        'attributeLabels' => $attributeLabels
    ]) ?>

</div>
