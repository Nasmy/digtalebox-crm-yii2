<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\BroadcastMessage */

$this->params['breadcrumbs'][] = ['label' =>  Yii::t('messages', 'People') ,'url' => ['#']];
$this->params['breadcrumbs'][] = ['label' =>  Yii::t('messages', 'Broadcast Messages'), 'url' => ['broadcast-message/admin']];
$this->params['breadcrumbs'][] =  Yii::t('messages', 'Update');


$this->title = Yii::t('messages', 'Broadcast Messages');
$this->titleDescription = Yii::t('messages', 'Update & Publish new message on Facebook/Twitter/LinkedIn');

Yii::$app->toolKit->registerFancyboxScripts();
Yii::$app->toolKit->setJsFlash();


?>
<div class="row no-gutters">
    <div class="content-panel col-md-12">
        <div class="content-inner">
            <div class="content-area">
                <?php echo $this->render('_form', array(
                    'model' => $model,
                    'attributeLabels' => $attributeLabels,
                    'fbPostLength' => $fbPostLength,
                    'twPostLength' => $twPostLength,
                    'lnPostLength' => $lnPostLength,
                    'modelBlyProfile' => $modelBlyProfile
                )); ?>
            </div>
        </div>
    </div>
</div>
