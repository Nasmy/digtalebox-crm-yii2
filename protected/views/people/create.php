<?php
use yii\helpers\Html;
$attributeLabels = $model->attributeLabels();
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'People'), 'url' => ['advancedSearch/admin']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Add People')];
$this->title = Yii::t('messages', 'Add People');
$this->titleDescription = Yii::t('messages', 'Add new person to the system');

?>

<?php echo Yii::$app->controller->renderPartial('_form',array(
    'model' => $model,
    'keywords' => $keywords,
    'closeUrl' => $closeUrl,
    'customFields' => $customFields,
    'attributeLabels' => $attributeLabels
)); ?>