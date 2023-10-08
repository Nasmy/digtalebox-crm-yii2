<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\models\FeedSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */


$this->title = 'Social Activities';
$this->titleDescription = Yii::t('messages', 'Twitter & Facebook activities');
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'People'), 'url' => '#'];
$this->params['breadcrumbs'][] = 'Social Activities';

echo Yii::$app->controller->renderPartial('_tabMenu');

?>
<div class="row no-gutters">
    <div class="content-panel col-md-12">
        <div class="content-inner">
            <div class="content-area">
                <div class="content-panel-sub">
                    <div class="panel-head">
                        <?php echo Yii::t('messages','Search by') ?>
                    </div>
                </div>

                <div class="search-form" style="display:block">
                    <?php
                        echo Yii::$app->controller->renderPartial('_search',[
                            'model' => $searchModel,
                        ]);
                    ?>
                </div>

                <?php
                    echo Yii::$app->controller->renderPartial('_feedGrid',[
                        'model' => $searchModel,
                        'dataProvider'=>$dataProvider
                    ]);
                ?>



            </div>
        </div>
    </div>
</div>
