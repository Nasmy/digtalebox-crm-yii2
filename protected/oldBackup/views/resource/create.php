<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Resource */

$this->title = 'Create Resource';
$this->params['breadcrumbs'][] = ['label' => 'Resources', 'url' => ['admin']];
$this->params['breadcrumbs'][] = $this->title;
$this->title = Yii::t('messages', 'Create Resource');
$this->titleDescription = Yii::t('messages', 'Upload documents, images, videos to share with users');

?>
<?= Yii::$app->controller->renderPartial('_form', [
    'model' => $model,
]) ?>


