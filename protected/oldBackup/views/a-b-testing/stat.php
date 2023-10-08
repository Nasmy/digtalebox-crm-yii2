<?php

use app\models\ABTestingCampaignSearch;
use app\models\Campaign;
use yii\grid\GridView;
use yii\widgets\DetailView;

?>
<style>
    .row {
        width: 100% !important;
    }

    .column {
        width: 32%;
        float: left;
    }

    .progress {
        margin-top: 5px;
        margin-left: 5px;
    }

    .app-body {
        background-color: #fff !important;
    }

    .progress {
        margin-top: 5px;
        margin-left: 5px;
        margin-bottom: 10px;
    }
</style>
<div class="row no-gutters">
    <div class="content-panel col-md-12">
        <div class="content-inner">
            <div class="content-area">
                <?php
                if (!$isScheduleRemain && Campaign::CAMP_SCHEDULE_WINNER == $model->status && $sendCountSendB > 0 && Yii::$app->user->id == $abTestModel->createdBy) {
                    // echo $this->renderPartial('_form', array('abTestModel' => $abTestModel, 'reason' => $reason));
                }
                ?>
                <div class="app-body">
                    <?php
                     echo GridView::widget([
                        'id' => 'user-grid',
                        'dataProvider' => $dataProvider,
                        'layout' => '{items}{pager}',
                        'headerRowOptions' => array('class' => 'table-wrap table-custom'),
                        'columns' => [
                            [
                                'format' => 'html',
                                'attribute' => 'fromA',
                                'value' => function ($dataProvider) {
                                    return $dataProvider->fromA;
                                },
                            ],
                            [
                                'format' => 'html',
                                'attribute' => 'subjectA',
                                'value' => function ($dataProvider) {
                                    return $dataProvider->subjectA;
                                },
                            ],
                            [
                                'format' => 'html',
                                'attribute' => 'fromB',
                                'value' => function ($dataProvider) {
                                    return $dataProvider->fromB;
                                },
                            ],
                            [
                                'format' => 'html',
                                'attribute' => 'subjectB',
                                'value' => function ($dataProvider) {
                                    return $dataProvider->subjectB;
                                },
                            ],
                        ]
                    ]);
                    ?>

                    <div class="column">
                        <div class="progress progress-striped">
                            <div class="progress-bar" style="width: 0%; background-color: transparent !important;">
                                &nbsp;
                            </div>
                        </div>
                        <?php
                        echo DetailView::widget([
                            'model' => $model,
                            'attributes' => array(
                                array(
                                    'label' => Yii::t('messages', 'Campaign'),
                                    'format' => 'html',
                                    'value' => "",
                                ),
                                array(
                                    'label' => html_entity_decode(htmlspecialchars(htmlentities(Yii::t('messages', 'Sent'), ENT_COMPAT | ENT_HTML401, "UTF-8"))),
                                    'format' => 'html',
                                    'value' => "",
                                ),
                                array(
                                    'label' => Yii::t('messages', 'Opened'),
                                    'format' => 'html',
                                    'value' => "",
                                ),
                                array(
                                    'label' => Yii::t('messages', 'Failed'),
                                    'format' => 'html',
                                    'value' => "",
                                ),
                                array(
                                    'label' => html_entity_decode(htmlspecialchars(htmlentities(Yii::t('messages', 'Clicked'), ENT_COMPAT | ENT_HTML401, "UTF-8"))),
                                    'format' => 'html',
                                    'value' => "",
                                ),
                                array(
                                    'label' => html_entity_decode(htmlspecialchars(htmlentities(Yii::t('messages', 'Bounced'), ENT_COMPAT | ENT_HTML401, "UTF-8"))),
                                    'format' => 'html',
                                    'value' => "",
                                ),
                                array(
                                    'label' => Yii::t('messages', 'Spam'),
                                    'format' => 'html',
                                    'value' => "",
                                ),
                                array(
                                    'label' => Yii::t('messages', 'Total'),
                                    'format' => 'html',
                                    'value' => "",
                                ),
                                array(
                                    'label' => html_entity_decode(htmlspecialchars(htmlentities(Yii::t('messages', 'Template'), ENT_COMPAT | ENT_HTML401, "UTF-8"))),
                                    'format' => 'html',
                                    'value' => "",
                                ),
                            )
                        ]);
                        ?>
                    </div>
                    <div class="column">
                        <div class="progress progress-striped">
                            <div class="progress-bar" style="width: <?php echo $progressA ?>%;"><?php echo $progressA ?>
                                %
                            </div>
                        </div>
                        <?php
                        echo DetailView::widget([
                            'model' => $model,
                            'template' => function ($attribute, $index, $widget) {
                                return "<td style='text-align: center'>{$attribute['value']}</td></tr>";
                            },
                            'attributes' => array(
                                array(
                                    'label' => false,
                                    'format' => 'html',
                                    'value' => function () {
                                        return "<span class='badge'>A</span>";
                                    },
                                ),
                                array(
                                    'label' => false,
                                    'format' => 'html',
                                    'value' => "<span class='badge badge-pill bg-not-opened'>$sendCountSendA</span>",
                                ),
                                array(
                                    'label' => false,
                                    'format' => 'html',
                                    'value' => "<span class='badge badge-pill bg-opened'>$sendCountOpenedA</span>",
                                ),
                                array(
                                    'label' => false,
                                    'format' => 'html',
                                    'value' => "<span class='badge badge-pill bg-failed'>$sendCountFailedA</span>",
                                ),
                                array(
                                    'label' => false,
                                    'format' => 'html',
                                    'value' => "<span class='badge badge-pill bg-clicked'>$sendCountClickedA</span>",
                                ),
                                array(
                                    'label' => false,
                                    'format' => 'html',
                                    'value' => "<span class='badge badge-pill bg-bounced'>$sendCountBouncedA</span>",
                                ),
                                array(
                                    'label' => false,
                                    'format' => 'html',
                                    'value' => "<span class='badge badge-pill bg-spam'>$sendCountSpamB</span>",
                                ),
                                array(
                                    'label' => false,
                                    'format' => 'html',
                                    'value' => "<span class='badge badge-pill badge-warning'>{$totalA}</span>",
                                ),
                                array(
                                    'label' => false,
                                    'format' => 'raw',
                                    'value' => $templateA,
                                ),
                            ),
                        ]);
                        ?>
                    </div>
                    <div class="column">
                        <div class="progress progress-striped">
                            <div class="progress-bar" style="width: <?php echo $progressB ?>%;"><?php echo $progressB ?>
                                %
                            </div>
                        </div>
                        <?php
                        echo DetailView::widget([
                            'model' => $model,
                            'template' => function ($attribute, $index, $widget) {
                                return "<td style='text-align: center'>{$attribute['value']}</td></tr>";
                            },
                            'attributes' => array(
                                array(
                                    'label' => false,
                                    'format' => 'html',
                                    'value' => function () {
                                        return "<span class='badge'>B</span>";
                                    },
                                ),
                                array(
                                    'label' => false,
                                    'format' => 'html',
                                    'value' => "<span class='badge badge-pill bg-not-opened'>$sendCountSendB</span>",
                                ),
                                array(
                                    'label' => false,
                                    'format' => 'html',
                                    'value' => "<span class='badge badge-pill bg-opened'>$sendCountOpenedB</span>",
                                ),
                                array(
                                    'label' => false,
                                    'format' => 'html',
                                    'value' => "<span class='badge badge-pill bg-failed'>$sendCountFailedB</span>",
                                ),
                                array(
                                    'label' => false,
                                    'format' => 'html',
                                    'value' => "<span class='badge badge-pill bg-clicked'>$sendCountClickedB</span>",
                                ),
                                array(
                                    'label' => false,
                                    'format' => 'html',
                                    'value' => "<span class='badge badge-pill bg-bounced'>$sendCountBouncedB</span>",
                                ),
                                array(
                                    'label' => false,
                                    'format' => 'html',
                                    'value' => "<span class='badge badge-pill bg-spam'>{$sendCountSpamA}</span>",
                                ),
                                array(
                                    'label' => false,
                                    'format' => 'html',
                                    'value' => "<span class='badge badge-pill badge-warning'>{$totalB}</span>",
                                ),
                                array(
                                    'label' => false,
                                    'format' => 'raw',
                                    'value' => $templateB,
                                ),
                            ),
                        ]);
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>