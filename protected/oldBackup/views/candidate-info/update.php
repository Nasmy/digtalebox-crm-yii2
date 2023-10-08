<?php

use yii\helpers\Html;
/* @var $this yii\web\View */
/* @var $model app\models\CandidateInfo */
$attributeLabels = $model->attributeLabels();
$this->title = 'Volunteer Portal';
$this->titleDescription = Yii::t('messages', 'Update Volunteer Portal');
$this->params['breadcrumbs'][] = ['label' => 'Volunteer Portal', 'url' => ['index']];
// $this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'System')];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="candidate-info-update">

    <?= $this->render('_form', [
        'model'=>$model,
        'profImageUrl'=>$profImageUrl,
        'profImage'=>$profImage,
        'customSinupFields'=>$customSinupFields,
        'attributeLabels'=>$attributeLabels
    ]) ?>

</div>
