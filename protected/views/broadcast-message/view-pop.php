<div class="modal-body edit-keyword">
    <div class="form-group">
        <?php

        use app\models\User;
        use yii\helpers\Html;
        use yii\helpers\Url;
        use yii\widgets\DetailView;

        $statUrl = Yii::$app->urlManager->createUrl('broadcastMessage/showLinkStat');
        $this->registerJs("
            $('#linkStat').on('click', function() {
             if ($('#viewCountDetails').is(':hidden')) {
                 $('#viewCountDetails').show(10);
             }
            $('#viewCountDetails').modal({backdrop:'static'});
            jQuery.ajax({
                'type':'POST',
                'url':'{$statUrl}?link='+$(this).attr('href'),
                'success':function(data) {
                    $('#iframe-viewCountDetails').html(data)
                        return false;
                    },
                'cache':false
            });
            return false;
            });
        ");
        ?>

        <?php
        $this->registerJs("
            $('.tool').tooltip();
        ");
        ?>

        <?= DetailView::widget([
            'model' => $model,
            'attributes' => [
                [
                    'format' => 'raw',
                    'attribute'=>'fbPost',
                    'value' => function ($model) {
                        $title = Yii::t('messages', 'Click to view link statistics');
                        $fbImage = '';
                        $fbImgUrl = Url::base() . '/' . Yii::$app->toolKit->resourcePathRelative . $model->fbImageName . '?rand' . mt_rand(10, 100);
                        $fbImghtml = Html::img($fbImgUrl, array('width' => 20, 'height' => 20, 'class' => 'thumbnail', 'style' => ' display: inline-block;'));

                        if ('' != $model->fbImageName) {
                            $fbImage = Html::a($fbImghtml, $fbImgUrl, array('id' => 'fbImage'));
                        }

                        return Yii::$app->toolKit->convertTextUrlsToLinks($model->fbPost, 'linkStat', $title) . ' ' . $fbImage;
                    }
                ],
                [
                    'format' => 'raw',
                    'attribute'=>'fbProfPost',
                    'value' => function ($model) {
                        $title = Yii::t('messages', 'Click to view link statistics');

                        $fbProfImage = '';
                        $fbProfImgUrl = Url::base() . '/' . Yii::$app->toolKit->resourcePathRelative . $model->fbProfImageName . '?rand' . mt_rand(10, 100);
                        $fbProfImghtml = Html::img($fbProfImgUrl,  array('width' => 20, 'height' => 20, 'class' => 'thumbnail', 'style' => ' display: inline-block;'));
                        if ('' != $model->fbProfImageName) {
                            $fbProfImage = Html::a($fbProfImghtml, $fbProfImgUrl, array('id' => 'fbProfImage'));
                        }

                        return Yii::$app->toolKit->convertTextUrlsToLinks($model->fbProfPost, 'linkStat', $title) . ' ' . $fbProfImage;
                    }
                ],
                [
                    'format' => 'raw',
                    'attribute'=>'twPost',
                    'value' => function ($model) {
                        $title = Yii::t('messages', 'Click to view link statistics');


                        $twImage = '';
                        $twImgUrl = Yii::$app->urlManager->baseUrl . '/' . Yii::$app->toolKit->resourcePathRelative . $model->twImageName . '?rand' . mt_rand(10, 100);
                        $twImghtml = Html::img($twImgUrl, array('width' => 20, 'height' => 20, 'class' => 'thumbnail', 'style' => ' display: inline-block;'));
                        if ('' != $model->twImageName) {
                            $twImage = Html::a($twImghtml, $twImgUrl, array('id' => 'twImage'));
                        }

                        return Yii::$app->toolKit->convertTextUrlsToLinks($model->twPost, 'linkStat', $title) . ' ' . $twImage;
                    }
                ],
                [
                    'format' => 'raw',
                    'attribute'=>'lnPost',
                    'value' => function ($model) {
                        $title = Yii::t('messages', 'Click to view link statistics');

                        $lnImage = '';
                        $lnImgUrl = Yii::$app->urlManager->baseUrl . '/' . Yii::$app->toolKit->resourcePathRelative . $model->lnImageName . '?rand' . mt_rand(10, 100);
                        $lnImghtml = Html::img($lnImgUrl,  array('width' => 20, 'height' => 20, 'class' => 'thumbnail', 'style' => ' display: inline-block;'));
                        if ('' != $model->lnImageName) {
                            $lnImage = Html::a($lnImghtml, $lnImgUrl, array('id' => 'lnImage'));
                        }

                        return Yii::$app->toolKit->convertTextUrlsToLinks($model->lnPost, 'linkStat', $title) . ' ' . $lnImage;
                    }
                ],
                [
                    'format' => 'raw',
                    'attribute'=>'lnPagePost',
                    'value' => function ($model) {
                        $title = Yii::t('messages', 'Click to view link statistics');

                        $lnPageImage = '';
                        $lnPageImgUrl = Yii::$app->urlManager->baseUrl . '/' . Yii::$app->toolKit->resourcePathRelative . $model->lnPageImageName . '?rand' . mt_rand(10, 100);
                        $lnPageImghtml = Html::img($lnPageImgUrl, array('width' => 20, 'height' => 20, 'class' => 'thumbnail', 'style' => ' display: inline-block;'));

                        if ('' != $model->lnPageImageName) {
                            $lnPageImage = Html::a($lnPageImghtml, $lnPageImgUrl, array('id' => 'lnPageImage'));
                        }

                        return Yii::$app->toolKit->convertTextUrlsToLinks($model->lnPagePost, 'linkStat', $title) . ' ' . $lnPageImage;
                    }
                ],
                [
                    'format' => 'raw',
                    'attribute'=>'fbPostStatus',
                    'value' => function ($model) {
                        return $model->getFbStatusLabel();
                    }
                ],
                [
                    'format' => 'raw',
                    'attribute'=>'fbProfPostStatus',
                    'value' => function ($model) {
                        return $model->getFbProfStatusLabel();
                    }
                ],
                [
                    'format' => 'raw',
                    'attribute'=>'twPostStatus',
                    'value' => function ($model) {
                        return $model->getTwStatusLabel();
                    }
                ],
                [
                    'format' => 'raw',
                    'attribute'=>'lnPostStatus',
                    'value' => function ($model) {
                        return $model->getLnStatusLabel();
                    }
                ],
                [
                    'format' => 'raw',
                    'attribute'=>'lnPagePostStatus',
                    'value' => function ($model) {
                        return $model->getLnPageStatusLabel();
                    }
                ],
                [
                    'format' => 'raw',
                    'attribute'=>'publishDate',
                    'value' => function ($model) {
                        $User = new User();
                        return $User->convertDBTime($model->publishDate);
                    }
                ],
                [
                    'format' => 'raw',
                    'attribute'=>'createdBy',
                    'value' => function ($model) {
                        $User = new User();
                        return $User->getNameById($model->createdBy);
                    }
                ],
                [
                    'format' => 'raw',
                    'attribute'=>'createdAt',
                    'value' => function ($model) {
                        $User = new User();
                        return $User->convertDBTime($model->createdAt);
                    }
                ],
            ],
        ]) ?>
        <?php
        //            $this->widget('bootstrap.widgets.TbDetailView', array(
        //            'htmlOptions' => array('class' => 'table-wrap'),
        //            'type' => 'striped custom hover',
        //            'data' => $model,
        //            'attributes' => array(
        //                array(
        //                    'name' => 'fbPost',
        //                    'type' => 'raw',
        //                    'value' => Yii::app()->toolKit->convertTextUrlsToLinks($model->fbPost, 'linkStat', $title) . ' ' . $fbImage,
        //                ),
        //                array(
        //                    'name' => 'fbProfPost',
        //                    'type' => 'raw',
        //                    'value' => Yii::app()->toolKit->convertTextUrlsToLinks($model->fbProfPost, 'linkStat', $title) . ' ' . $fbProfImage,
        //                ),
        //                array(
        //                    'name' => 'twPost',
        //                    'type' => 'raw',
        //                    'value' => Yii::app()->toolKit->convertTextUrlsToLinks($model->twPost, 'linkStat', $title) . ' ' . $twImage,
        //                ),
        //                array(
        //                    'name' => 'lnPost',
        //                    'type' => 'raw',
        //                    'value' => Yii::app()->toolKit->convertTextUrlsToLinks($model->lnPost, 'linkStat', $title) . ' ' . $lnImage,
        //                ),
        //                array(
        //                    'name' => 'lnPagePost',
        //                    'type' => 'raw',
        //                    'value' => Yii::app()->toolKit->convertTextUrlsToLinks($model->lnPagePost, 'linkStat', $title) . ' ' . $lnPageImage,
        //                ),
        //                array(
        //                    'name' => 'fbPostStatus',
        //                    'type' => 'raw',
        //                    'value' => $model->getFbStatusLabel(),
        //                ),
        //                array(
        //                    'name' => 'fbProfPostStatus',
        //                    'type' => 'raw',
        //                    'value' => $model->getFbProfStatusLabel(),
        //                ),
        //                array(
        //                    'name' => 'twPostStatus',
        //                    'type' => 'raw',
        //                    'value' => $model->getTwStatusLabel(),
        //                ),
        //                array(
        //                    'name' => 'lnPostStatus',
        //                    'type' => 'raw',
        //                    'value' => $model->getLnStatusLabel(),
        //                ),
        //                array(
        //                    'name' => 'lnPagePostStatus',
        //                    'type' => 'raw',
        //                    'value' => $model->getLnPageStatusLabel(),
        //                ),
        //                array(
        //                    'name' => 'publishDate',
        //                    'type' => 'raw',
        //                    'value' => User::model()->convertDBTime($model->publishDate),
        //                ),
        //                array(
        //                    'name' => 'createdBy',
        //                    'type' => 'raw',
        //                    'value' => User::model()->getNameById($model->createdBy),
        //                ),
        //                array(
        //                    'name' => 'createdAt',
        //                    'type' => 'raw',
        //                    'value' => User::model()->convertDBTime($model->createdAt),
        //                ),
        //            ),
        //        ));

        ?>
    </div>
</div>

<!-- START View Modal -->
<div class="modal fade" id="viewCountDetails" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">

        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id=""> <?php echo Yii::t('messages', 'Link Statistic'); ?> </h5>
                <button type="button" class="close" aria-label="Close" id="btnClose">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="row">
                <div class="col-md-12 no-gutters">
                    <div id="iframe-viewCountDetails" style="vertical-align: center;" class="div-min-height">
                        <div class="progress loader themed-progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated"
                                 role="progressbar" style="width: 100%"
                                 aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END View Modal -->

<script>
    $(document).ready(function () {
        $('#btnClose').on('click', function() {
            $('#viewCountDetails').hide(10);
            $(".modal-backdrop:eq(1)").remove();
            $(".modal-backdrop:eq(2)").remove();
        });
        $("#viewCountDetails").on("hidden.bs.modal", function (e) {
            $(".modal-backdrop:eq(1)").remove();
            $(".modal-backdrop:eq(2)").remove();
            $(".modal-backdrop:eq(3)").remove();
        });
        $(document).on("hidden.bs.modal", function () {
            $("#iframe-viewCountDetails").html('<div class="progress loader themed-progress">\n' +
                '                            <div class="progress-bar progress-bar-striped progress-bar-animated"\n' +
                '                                 role="progressbar" style="width: 100%"\n' +
                '                                 aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>\n' +
                '                        </div>');
        });
    });
</script>
