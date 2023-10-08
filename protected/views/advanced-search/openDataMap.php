<?php

use app\components\ToolKit;
use app\models\User;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use rmrevin\yii\fontawesome\FA;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\View;

echo Yii::$app->toolKit->registerAdvanceSearchScript();
$this->title = Yii::t('messages', 'Map View');
$this->titleDescription = Yii::t('messages', 'Search people in map view');
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'People'), 'url' => ['#']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Map View')];
?>

<?php ToolKit::registerOpenMapDataScript(); ?>
<?php $markerImg = Yii::$app->toolKit->getMarkerImage();
$searchType = User::SEARCH_EXCLUDE;
$keywordNull = Yii::t('messages', 'Keywords or Exclude keywords cannot be empty');
$keywordExcludeSameValue = Yii::t('messages', 'Keywords or Exclude keywords cannot contain same value');
$url = Yii::$app->urlManager->createUrl(['advanced-search/grid-update-map']);
$urlDataMapView = Yii::$app->urlManager->createUrl(['advanced-search/grid-update-map-view']);
 
?>
 
<?php echo Yii::$app->controller->renderPartial('_tabMenu'); ?>
<div class="row no-gutters map-view">
    <div class="content-panel col-md-12">
        <div class="content-inner">
            <div class="content-area">
                <div id="root"></div>   
            </div>
        </div>
    </div>
</div>
   