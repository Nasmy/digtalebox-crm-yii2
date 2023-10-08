<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use app\models\User;
Yii::$app->toolKit->registerPopupWindowScript();
Yii::$app->toolKit->setJsFlash();
$acceptConfirm = Yii::t('messages', 'Are you sure you want to merge the record?');
$msg1 = Yii::t('messages', 'Error while connecting to profile');
$parentKey = $parentId;
?>
<?php $delConfirm = Yii::t('messages', 'Are you sure you want to refuse this duplicate record?') ?>
<div class="row no-gutters">
    <div class="content-panel col-md-12">
        <div class="content-inner">
            <div class="content-area">

                <?php Pjax::begin(); ?>

                <?= GridView::widget([
                    'dataProvider' => $model->searchDuplicatePeople($parentId),
                    'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => ''],
                    'tableOptions' => ['class' => 'table table-striped table-bordered'],
                    'summary' => '<div class="text-right results-count mt-4">' . Yii::t('messages', 'Displaying {begin}-{end} of {totalCount} results.') . '</div>',
                    'pager' => [
                        'firstPageLabel' => '',
                        'firstPageCssClass' => 'first',
                        'activePageCssClass' => 'selected',
                        'disabledPageCssClass' => 'hidden',
                        'lastPageLabel' => 'last ',
                        'nextPageLabel' => '<span aria-hidden="true">&raquo;</span>',
                        'nextPageCssClass' => 'page-item next',
                        'maxButtonCount' =>5,
                        'pageCssClass' => 'page-item',
                        'prevPageCssClass' => 'page-item previous',    // Set CSS class for the "previous" page button
                        'options' => ['class' => 'pagination justify-content-md-end'],
                    ],
                    'layout' => '<div class="text-right results-count">{summary}</div>
                        <div class="table-wrap">{items}</div>
                        <div class="row no-gutters d-flex flex-sm-row-reverse flex-sm-column-reverse flex-md-row">
                        <div class="col-md-6"></div>
                        <div class="col-md-6">
                            <div class="float-right"><nav aria-label="Page navigation">{pager}</nav></div>
                        </div></div>',
                    'headerRowOptions' => ['class' => 'table-wrap table-custom'],
                    'columns' => [
                        [
                            'attribute' => 'image',
                            'format' => 'html',
                            'header' => Yii::t('messages', 'Profile'),
                            //'value' => '$model->getPic($data->profImage)',
                            'value' => function ($data, $row) {
                                $user =new User();
                                $imgSrc = $user->getPic($data->profImage);
                                $gender = $user->getGenderLabel($data->gender, 1);
                                $networks = $user->getNetworkIcons($data);

                                if ($networks['count'] > 1) {
                                    $str = '<span class="social"><a href="#" id="network_' . $data->id . '" 
                                        onclick=callToolTip("network_' . $data->id . '")
                                        data-toggle="tooltip" data-html="true" title="' . $networks['network'] . '">
                                        <i class="fa fa-angle-right fa-lg"></i></a></span>';
                                } else {
                                    $str = '<span class="social">' . $networks['network'] . '</span>';
                                }

                                return '<span class="profile-pic">' . $imgSrc . '</span>
                                                        <span class="gender" data-toggle="tooltip">' . $gender . '</span>' . $str;
                            },
                        ],
                        [
                            'attribute'=>'',
                            'format' => 'html',
                            'value' => function ($data) {
                                $user = new User();
                                $icons = $user->getNetworkIcons($data);
                                return $icons['network'];
                            },
                            'headerOptions' => ['style' => 'width: 5%;'],
                            'contentOptions' => ['style' => 'width: 5%;'],
                        ],
                        [
                            'attribute'=> Yii::t('messages', 'firstName'),
                            'value' => function($data){
                                return $data->firstName;
                            },
                        ],
                        [
                            'attribute'=> Yii::t('messages', 'lastName'),
                            'value' => function($data){
                                return $data->lastName;
                            },
                        ],
                        [
                            'attribute'=> Yii::t('messages', 'email'),
                            'value' => function($data){
                                return $data->email;
                            },
                        ],
                        [
                            'attribute'=> Yii::t('messages', 'mobile'),
                            'value' => function($data){
                                return $data->mobile;
                            },
                        ],
                        [
                            'attribute'=> Yii::t('messages', 'dateOfBirth'),
                            'value' => function($data){
                                return $data->dateOfBirth;
                            },
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'headerOptions' => ['style' => 'width: 4.5%; text-align: center'],
                            'contentOptions' => ['style' => 'width: 4.5%; text-align: center'],
                            'template' => '{update}',
                            'buttons' => [
                                'update' => function ($url, $parentId, $key) use ($parentKey) {
                                    $return = '';
                                    /*$url = '/index.php/potential-matches/get-users-to-merge?parentId='. $parentKey.'&userId='.$key;
                                        $return = Html::a('<span class="fa fa-edit fa-lg viewMatches"></span>', $url,[
                                            'target' => '_blank',
                                        ]);*/
                                    $url = '/index.php/potential-matches/get-users-to-merge?userId='.$key;
                                    $return = Html::a('<span class="fa fa-compress fa-lg custom viewMatches"></span>', $url,[
                                        'target' => '_blank',
                                        'rel' => 'tooltip',
                                        'title' => 'Custom Merge'
                                    ]);
                                    return $return;
                                },

                        ],
                        ],
                    ],
                ]); ?>

            </div>
        </div>
    </div>
</div>
