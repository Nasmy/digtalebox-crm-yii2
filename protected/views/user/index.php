<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\models\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Users');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-index">

    <p>
        <?= Html::a(Yii::t('app', 'Create User'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'address1:ntext',
            'mobile',
            'name',
            'firstName',
            //'lastName',
            //'username',
            //'password',
            //'email:email',
            //'gender',
            //'zip',
            //'countryCode',
            //'joinedDate',
            //'signUpDate',
            //'supporterDate',
            //'userType',
            //'signup',
            //'isSysUser',
            //'dateOfBirth',
            //'reqruiteCount',
            //'keywords:ntext',
            //'delStatus',
            //'city',
            //'isUnsubEmail:email',
            //'isManual',
            //'longLat',
            //'isSignupConfirmed',
            //'profImage:ntext',
            //'totalDonations',
            //'isMcContact',
            //'emailStatus:email',
            //'notes:ntext',
            //'network',
            //'formId',
            //'addressInvalidatedAt',
            //'pwResetToken',
            //'resetPasswordTime',
            //'createdAt',
            //'updatedAt',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
