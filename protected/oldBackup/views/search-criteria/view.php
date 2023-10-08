<?php

use app\models\Keyword;
use app\models\Team;
use app\models\User;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\SearchCriteria */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Search Criterias', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

$customAttributesArray = array();
if (!empty($customFields) && !empty($customAttributes)) {
    foreach ($customAttributes as $key => $val) {
        $customAttributesArray[] = array('name' => $key, 'value' => $val);
    }
}

$attributesArray = [
    'criteriaName',
    'firstName',
    'lastName',
    'email',
    [
        'format' => 'html',
        'attribute'=>'keywords',
        'value' => function ($model) {
            return Keyword::getKeywordsByIdList($model->keywords);
        }
    ],
    [
        'format' => 'html',
        'attribute'=>'teams',
        'value' => function ($model) {
            return Team::getTeamNamesByIdList($model->teams);
        }
    ],
    [
        'format' => 'html',
        'attribute'=>'userType',
        'value' => function ($model) {
            return ($model->userType == 0 || $model->userType == null) ? 'N/A' :   User::getUserTypes($model->userType);
        }
    ],
    [
        'format' => 'raw',
        'attribute'=>'createdBy',
        'value' => function ($model) {
            return User::getNameById($model->createdBy);
        }
    ],
    [
        'format' => 'html',
        'attribute'=>'createdAt',
        'value' => function ($model) {
            return User::convertDBTime($model->createdAt);
        }
    ],
];
    $attributesArray = array_merge($attributesArray, $customAttributesArray);

?>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => $attributesArray
    ]) ?>

