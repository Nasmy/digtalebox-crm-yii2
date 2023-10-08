<?php

use app\models\AuthItem;
use yii\helpers\Html;
$attributeLabels = $model->attributeLabels();
$this->title = Yii::t('messages','Create Permission');
$this->titleDescription = Yii::t('messages', 'Add new permission item');
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'System'), 'url' => ['admin', 'type' => AuthItem::TYPE_OPERATION]];
// $this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'System')];
// $this->params['breadcrumbs'][] = ['label' => 'Manage Permission', 'url' => ['admin', 'type' => AuthItem::TYPE_OPERATION]];
$this->params['breadcrumbs'][] = Yii::t('messages','Create Permission');
?>
    <?php echo $this->render('_form', [
        'model' => $model,
        'attributeLabels' => $attributeLabels
    ]) ?>
