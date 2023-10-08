<?php
use app\assets\AppAsset;
use yii\bootstrap\Tabs;
//use yii\jui\Tabs;
use app\controllers;
echo Tabs::widget([
	'items' => [
		[
			'label'=>Yii::t('messages', 'Advanced Search'),
			'active' =>  in_array(Yii::$app->controller->action->id, array('admin')),
			'visible'=>Yii::$app->user->checkAccess('AdvancedSearch.Admin'),
			'url' => ['advanced-search/admin/'],
            'headerOptions' =>['id' => 'ppl-advanced-search','class'=>"nav-item"],

		],
		[
            'label' => Yii::t('messages', 'Map View'),
			'url' => ['advanced-search/data-map/'],
			'active' =>  in_array(Yii::$app->controller->action->id, array('data-map')),
			'visible'=>Yii::$app->user->checkAccess('AdvancedSearch.Admin'), // TODO: need to check the permission
            'headerOptions' =>['id' => 'ppl-advanced-search','class'=>"nav-item"],

		],
		[
			'label' => Yii::t('messages', 'Zone View'),
			'url' => ['advanced-search/all-map-zones/'],
			'active' =>  in_array(Yii::$app->controller->action->id, array('all-map-zones')),
			'visible'=>Yii::$app->user->checkAccess('AdvancedSearch.Admin'), // TODO: need to check the permission
            'headerOptions' => ['id' => 'ppl-advanced-search','class'=>"nav-item"],
		],
		[
            'label' => Yii::t('messages', 'Open Data Map'),
			'url' => ['advanced-search/open-data-map/'],
			'active' =>  in_array(Yii::$app->controller->action->id, array('open-data-map')),
			'visible'=>Yii::$app->user->checkAccess('AdvancedSearch.Admin'), // TODO: need to check the permission
            'headerOptions' =>['id' => 'ppl-advanced-search','class'=>"nav-item"],

		]

	],
	'options' => ['class'=>"nav-item"],
	'headerOptions' => ['id' => 'yw2'],
	'itemOptions' => ['class' => 'nav-item'],
	'clientOptions' => ['collapsible' => false],
]);

?>
<style type="text/css">
	a.navbar-brand {
		height: inherit;
		padding-left: 0;
	}

	.list-view{
		min-height: 300px;
		height: 100%;
	}

	html{
		font-size: initial !important;
		padding: initial !important;
	}
</style>
