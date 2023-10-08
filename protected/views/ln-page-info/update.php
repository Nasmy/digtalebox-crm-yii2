<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\LnPageInfo */

$this->title = 'Update Ln Page Info: ' . $model->pageId;
$this->params['breadcrumbs'][] = ['label' => 'Ln Page Infos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->pageId, 'url' => ['view', 'id' => $model->pageId]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="ln-page-info-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
