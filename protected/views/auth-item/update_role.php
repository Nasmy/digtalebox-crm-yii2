<?php

use app\models\AuthItem;

$attributeLabels = $model->attributeLabels();
$this->title = Yii::t('messages','Update Role');
$this->titleDescription = Yii::t('messages', 'Update role permissions and details');
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages','System'), 'url' => ['admin', 'type' => AuthItem::TYPE_ROLE]];
// $this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'System')];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages','Manage Roles'), 'url' => ['admin', 'type' => AuthItem::TYPE_ROLE]];
$this->params['breadcrumbs'][] = Yii::t('messages','Update Role');

?>

<?php
echo $this->render('_form_role', array(
    'model' => $model,
    'dataProvider' => $dataProvider,
    'permissions' => $permissions,
    'itemName' => $itemName,
    'permissionsSubmitFail' => $permissionsSubmitFail,
    'isSubmitFail' => $isSubmitFail,
    'attributeLabels' => $attributeLabels
));
?>