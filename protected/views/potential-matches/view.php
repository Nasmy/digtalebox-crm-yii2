<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\User */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="user-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'address1:ntext',
            'mobile',
            'name',
            'firstName',
            'lastName',
            'username',
            'password',
            'email:email',
            'gender',
            'zip',
            'countryCode',
            'joinedDate',
            'signUpDate',
            'supporterDate',
            'userType',
            'signup',
            'isSysUser',
            'dateOfBirth',
            'reqruiteCount',
            'keywords:ntext',
            'delStatus',
            'city',
            'isUnsubEmail:email',
            'isManual',
            'longLat',
            'isSignupConfirmed',
            'profImage:ntext',
            'totalDonations',
            'isMcContact',
            'emailStatus:email',
            'notes:ntext',
            'network',
            'formId',
            'addressInvalidatedAt',
            'pwResetToken',
            'resetPasswordTime',
            'createdAt',
            'updatedAt',
        ],
    ]) ?>

</div>
