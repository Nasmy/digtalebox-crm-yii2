<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Resource */

 $this->params['breadcrumbs'][] = ['label' => 'People', 'url' => ['#']];
$this->params['breadcrumbs'][] = ['label' => 'People', 'Resource' => ['resource/admin']];
 $this->params['breadcrumbs'][] = 'Update Resource';
?>
<div class="resource-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
