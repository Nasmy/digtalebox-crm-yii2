<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Configuration */

$this->title = 'Create Configuration';
$this->params['breadcrumbs'][] = ['label' => 'Configurations', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="configuration-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
