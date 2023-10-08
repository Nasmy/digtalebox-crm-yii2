<?php

use app\models\AuthItem;

$attributeLabels = $model->attributeLabels();
$this->title = Yii::t('messages','Create Role');
$this->titleDescription = Yii::t('messages', 'Add new role to the system');
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'System'), 'url' => ['admin', 'type' => AuthItem::TYPE_ROLE]];
// $this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'System')];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Manage Roles'), 'url' => ['admin', 'type' => AuthItem::TYPE_ROLE]];
$this->params['breadcrumbs'][] = Yii::t('messages','Create Role');

?>

<?php
echo $this->render('_form_role', array(
    'model' => $model,
    'dataProvider' => $dataProvider,
    'permissions' => $permissions,
    'permissionsSubmitFail' => $permissionsSubmitFail,
    'isSubmitFail' => $isSubmitFail,
    'attributeLabels' => $attributeLabels
));
?>
