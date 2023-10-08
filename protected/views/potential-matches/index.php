<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use app\models\User;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('messages', 'Potential Matches');
$this->params['breadcrumbs'][] = $this->title;
$this->titleDescription = Yii::t('messages', 'Merge duplicate records to same user into one profile');
//$this->tabMenuPath = 'application.views.potentialMatches._tabMenu';
?>
<?php
$script = <<< JS
    $('.update').on('click',function() {
        $('#iframe-potential-match').attr('src', $(this).attr('href'));
        $('#potential-match').modal('show');
        return false;
    });
JS;
?>
<script type="text/javascript">
    function editModel(data) { // Edit model
        var j = jQuery(data);
        $('#iframe-potential-match').attr('src', jQuery(j[0]).attr('url'));
        $('#potential-match').modal('show');
        return false;
    }
</script>
<?php
$script = <<< JS
    $('.search-button').click(function(){
        $('.search-form').toggle();
        return false;
    });
    $('.search-form form').submit(function(){
        $.fn.yiiGridView.update('people-match-main-grid', {
            data: $(this).serialize()
        });
        return false;
    });
JS;
?>
<div class="row no-gutters">
    <div class="content-panel col-md-12">
        <div class="content-inner">
            <div class="content-area">
                <div class="content-panel-sub">
                    <div class="panel-head">
                        <?php echo Yii::t('messages', 'Search by') ?>
                    </div>
                </div>
                <div class="search-form" style="display:block">
                    <?php
                    echo $this->render('_search', array(
                        'model' => $model,
                    ));
                    ?>
                </div>
                <?php
                Pjax::begin(['id' => 'pjax-list']);
                ?>
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
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
                        'maxButtonCount' => 5,
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
                            'value' => function ($data, $row) {
                                $user = new User();
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
                            'attribute' => '',
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
                            'attribute' => Yii::t('messages', 'firstName'),
                            'value' => function ($data) {
                                return $data->firstName;
                            },
                        ],
                        [
                            'attribute' => Yii::t('messages', 'lastName'),
                            'value' => function ($data) {
                                return $data->lastName;
                            },
                        ],
                        [
                            'attribute' => Yii::t('messages', 'email'),
                            'value' => function ($data) {
                                return $data->email;
                            },
                        ],
                        [
                            'attribute' => Yii::t('messages', 'mobile'),
                            'value' => function ($data) {
                                return $data->mobile;
                            },
                        ],
                        [
                            'attribute' => Yii::t('messages', 'dateOfBirth'),
                            'value' => function ($data) {
                                return $data->dateOfBirth;
                            },
                        ],
                        [
                            'attribute' => Yii::t('messages', 'dupCount'),
                            'value' => function ($data) {
                                $user = new \app\models\UserMerge();
                                return $user->searchUniqueCount($data);
                            },
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'headerOptions' => ['style' => 'width: 4.5%; text-align: center'],
                            'contentOptions' => ['style' => 'width: 4.5%; text-align: center'],
                            'template' => '{update}',
                            'buttons' => [
                                'update' => function ($url, $model, $key) {
                                    $return = '';
                                    $url = '/index.php/potential-matches/duplicates?userId=' . $key;
                                    $return = Html::a('<span class="fa fa-edit fa-lg viewMatches"></span>', '#',
                                        [
                                            'id' => 'potential-edit',
                                            'url' => $url,
                                            'title' => Yii::t('yii', 'Edit'),
                                            'data-toggle' => 'modal',
                                            'data-target' => '#potential-match',
                                            'data-id' => $key,
                                            'data-pjax' => '0',
                                            'onclick' => 'editModel(this)']
                                    );

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

<!-- START Potential match Modal -->
<div class="modal fade" id="potential-match" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">

        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id=""> <?php echo Yii::t('messages', 'Merge Contacts'); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <iframe id="iframe-potential-match" src="" frameborder="0" scrolling="auto" width="100%"
                    height="520px"></iframe>
        </div>
    </div>
</div>

