<?php

use app\models\MessageTemplate;
use app\models\User;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\MessageTemplate */

$this->title = Yii::t('messages', 'View Message Templates');
$this->titleDescription = Yii::t('messages', 'Email/Facebook/Twitter/LinkedIn templates');
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Communication'), 'url' => ['admin']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Message Templates'), 'url' => ['admin']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'View')];
// \yii\web\YiiAsset::register($this);
?>
<div class="message-template-view">
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
                            'name',
                            'description',
                            [
                                'attribute' => 'type',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    return $model->getTemplateTypeOptions($model->type);
                                }
                            ],
                            [
                                'attribute' => 'createdBy',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    return User::getNameById($model->createdBy);
                                }
                            ],
                            'createdAt',
                            'twMessage',
                            'smsMessage',
                            'lnMessage',
                            'lnSubject',
                            'subject',
                            [
                                'attribute' => 'content',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    if($model->checkEmailTemplate($model->templateCategory, [MessageTemplate::MSG_CAT_EMAIL, MessageTemplate::MSG_CAT_BOTH])) {
                                        $url = Yii::$app->urlManager->createUrl(["message-template/html-preview", "id" => $model->id, "check" => 1]);
                                        return Html::a(Yii::t('messages', 'View Email Content'), true, ['id' => 'htmlPreview',
                                            'onClick' => "
                                           $.fancybox.open({
                                                padding: 10,
                                                href:'$url',
                                                type: 'iframe',
                                                width: '700px',
                                                height: '500px',
                                                transitionIn: 'elastic',
                                                transitionOut: 'elastic',
                                                autoSize: true
                                            });
                                            return false;
                                            "
                                        ]);
                                    } else {
                                        return '';
                                    }
                                }
                            ]
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
</div>
