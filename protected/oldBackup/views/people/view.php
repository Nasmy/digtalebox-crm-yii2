<?php
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\models\User;
use rmrevin\yii\fontawesome\FA;
use app\assets\DialogAsset;
DialogAsset::register($this);

echo DetailView::widget([
    'model'=>$model,
    'attributes'=>[
        [
            'format' => 'html',
            'attribute'=>Yii::t('messages', 'network'),
            'value'=>User::getPeopleNetworkIcons($model),
        ],
        'name',
        'dateOfBirth',
        'address1',
        'mobile',
        'email',
        [
            'type' => 'raw',
            'attribute'=>'gender',
            'value'=> User::getGenderLabel($model->gender, 1),
        ],
        'zip',
        [
            'attribute'=>'joinedDate',
            'value'=> User::convertDBTime($model->joinedDate),
        ],
        [
            'attribute'=>'userType',
            'value'=> User::getUserTypes($model->userType),
        ],
    ],
]); ?>
