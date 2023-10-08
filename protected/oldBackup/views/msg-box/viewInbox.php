<?php

use app\models\User;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\MsgBox */

$this->title =  Yii::t('messages','Message Details');
$this->titleDescription = Yii::t('messages','Details of the message you received');
$this->params['breadcrumbs'][] = ['label' =>  Yii::t('messages','Communication'), 'url' => ['#']];
$this->params['breadcrumbs'][] = ['label' =>  Yii::t('messages','Messages'), 'url' => ['msg-box/inbox']];
$this->params['breadcrumbs'][] = Yii::t('messages','Message Details');

\yii\web\YiiAsset::register($this);

$message = str_replace('{USER_ID}',base64_encode(Yii::$app->user->id),$message);

?>

<div class="row no-gutters">
    <div class="content-panel col-md-12">
        <div class="content-inner">
            <div class="content-area">
                <div class="form-row mb-2">
                    <div class="form-group col-md-12"></div>
                </div>
                <div class="content-panel-sub">
                </div>
                <?= DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        [
                            'label'=>Yii::t('messages','Date & Time'),
                            'value'=>$userModel->convertDBTime($model->dateTime),
                        ],
                        [
                            'label'=>Yii::t('messages','Name'),
                            'value'=>!is_null($userModel) ? $userModel->getName() : 'N/A',
                        ],
                        'subject',
                        [
                            'format'=>'html',
                            'attribute'=>'message',
                            'value' => Yii::$app->toolKit->convertTextUrlsToLinks($message),
                        ],
                    ],
                ]) ?>


                <div class="form-row text-left text-md-right">
                    <div class="form-group col-md-12">
                        <?php
                       echo Html::a(Yii::t('messages','Cancel'),Url::to('inbox'),['type' => 'info','class' => 'btn btn-secondary',])
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
