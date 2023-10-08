<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\BroadcastMessage */
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'People'), 'url' => ['#']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Broadcast Messages'), 'url' => ['broadcast-message/admin']];
$this->params['breadcrumbs'][] = Yii::t('messages', 'Create');


$this->title = Yii::t('messages', 'Broadcast Messages');
$this->titleDescription = Yii::t('messages', 'Create & Publish new message on Facebook/Twitter/LinkedIn');

?>
<?php

//echo Yii::$app->view->renderFile(Yii::getAlias('@app') . '/views/feed/_tabMenu.php');
//Yii::$app->toolKit->registerBootstrapFileInputStyleScripts();
Yii::$app->toolKit->registerFancyboxScripts();

?>

<div class="row no-gutters">
    <div class="content-panel col-md-12">
        <div class="content-inner">
            <div class="content-area">
                <?php
                echo $this->render('_form', array(
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
