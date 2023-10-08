<?php

use app\models\User;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;
?>
<?php Pjax::begin(['id' => 'pjax-list']);  ?>
        <?php  $altPic = Yii::$app->toolKit->getAltProfPic();   ?>
            <?= GridView::widget([
            'id' => 'form-membership-donation-grid',
            'dataProvider' => $dataProvider,
            'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => ''],
            'tableOptions' => ['class' => 'table table-striped table-bordered'],
            'summary' => '<div class="text-right results-count mt-4">' . Yii::t('messages', 'Displaying {begin}-{end} of {count} imports') . '</div>',
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
            'headerRowOptions' => array('class' => 'table-wrap table-custom'),
            'columns' => [
                [
                    'attribute'=>'profile',
                    'format' => 'raw',
                    'value' => function ($data,$row) use ($altPic){
                        return Html::a(Html::img($data->profImageUrl), '#', array("id" => "showProfile", "data-id" => $data->id, 'width' => 48, 'height' => 48, 'class' => 'profile-pic profile-pic-lg thumbnail', 'onerror' => 'this.src="' . $altPic . '"'));
                     }
                ],
                [
                    'attribute'=>'post',
                    'format' => 'raw',
                    'value' => function ($data){
                        return   $data->getFormattedFeedText($data);
                    }

                ],
                [
                    'attribute'=>'type',
                    'format' => 'raw',
                    'value' => function ($data){
                        return   $data->getUserTypeLabel($data->userType);
                    }
                ],
                [
                    'header'=>'<i class="fa fa-link fa-lg" data-toggle="tooltip" title="Connections"></i>',
                    'format' => 'raw',
                    'value' => function ($data){
                $user = new User();
                         return   $user->getConnectionCount($data->userType, $data->networkUserId);
                    }

                ],
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]);

    ?>

    <?php Pjax::end(); ?>

<script>
    $(document).ready(function () {
        var from_date = $('#from-date').val();
        var to_date = $('#to-date').val();
        $(".chosen-select").chosen();
        $('#from-date')
            .datetimepicker({
                format: 'YYYY-MM-DD HH:mm',
                ignoreReadonly: true
            });
        $('#from-date').val(from_date);

        $('#to-date').datetimepicker({
            format: 'YYYY-MM-DD HH:mm',
            ignoreReadonly: true,
        });
        $('#to-date').val(to_date);
    });
</script>