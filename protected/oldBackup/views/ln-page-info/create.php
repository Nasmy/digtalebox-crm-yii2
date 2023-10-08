<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\LnPageInfo */

$this->title = 'Create Ln Page Info';
$this->params['breadcrumbs'][] = ['label' => 'Ln Page Infos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ln-page-info-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
