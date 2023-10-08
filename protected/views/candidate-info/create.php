<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\CandidateInfo */

$this->title = 'Create Candidate Info';
$this->params['breadcrumbs'][] = ['label' => 'Candidate Infos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="candidate-info-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
