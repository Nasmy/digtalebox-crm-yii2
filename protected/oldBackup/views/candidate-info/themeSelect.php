<?php

use yii\helpers\Html;

$this->title = Yii::t('messages', 'Themes');
$this->titleDescription = Yii::t('messages', 'Change your theme');

$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'System'), 'url' => ['#']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Portal Settings'),'url' => ['#']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Themes')];


?>

<?php echo  Yii::$app->controller->renderPartial('_tabMenu'); ?>
<div class="content-inner">
    <div class="content-area">

        <div class="content-panel-sub">
            <div class="panel-head">
                <?php echo Yii::t('messages', 'Active Theme'); ?>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6 col-md-4 col-xl-3 text-center mb-3">
                <div class="resource" id="activeTheme">

                    <a href="#"
                       class="portal-setting-images" data-toggle="modal" data-target="#viewDetailss"
                       onclick="changeView('<?php echo Yii::$app->toolKit->getImagePath() . Yii::$app->params['themes'][$model->themeStyle]['preview'] ?>')"
                       data-lgimage="<?php echo Yii::$app->toolKit->getImagePath() .
                           Yii::$app->params['themes'][$model->themeStyle]['lgImage'] ?>">

                        <img class="img-thumbnail object-fit_cover"
                             src="<?php echo Yii::$app->toolKit->getImagePath() .
                                 Yii::$app->params['themes'][$model->themeStyle]['thumbnail'] ?>"/>
                    </a>
                </div>
            </div>
        </div>

        <div class="content-panel-sub">
            <div class="panel-head">
                <?php echo Yii::t('messages', 'Available Themes'); ?>
            </div>
        </div>

        <div class="row">
            <?php foreach (Yii::$app->params['themes'] as $key => $theme) { ?>
                <div class="col-sm-6 col-md-4 col-xl-3 text-center">
                    <div class="mb-4">

                        <a href="#" class="portal-setting-images"
                            data-toggle="modal"
                            data-lgimage="<?php  echo Yii::$app->toolKit->getImagePath().$theme['lgImage'] ?>"
                            data-preview="<?php echo Yii::$app->toolKit->getImagePath() . $theme['preview'] ?>"
                            data-target="#viewDetailss"
                            onclick="changeView(<?php echo Yii::$app->toolKit->getImagePath().$theme['preview'] ?>)"
                        >

                        <img class="img-thumbnail object-fit_cover" src="<?php echo Yii::$app->toolKit->getImagePath() . $theme['thumbnail'] ?>"/>
                        </a>

                        <div class="actions mt-2">
                            <?php
                                echo Html::a('<span class="fa fa-edit fa-lg"></span> '.Yii::t('messages', 'Activate'),
                               Yii::$app->urlManager->createUrl('candidate-info/apply-theme/?id='.$key),['class'=>'btn btn-primary']) ;
                            ?>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>

    </div>
</div>

</div> <!-- start at column1.php -->
</div><!-- start at column1.php -->
</div><!-- start at column1.php -->

<!-- View Modal -->

<div class="modal fade" id="viewDetailss" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">

     <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"
                    id="exampleModalLongTitle"><?php echo Yii::t('messages', 'Theme Preview'); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="text-center" id="viewTheme">

                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function changeView(srcUrl) {
         $('#viewDetailss .modal-content .modal-body').html('<img src="' + srcUrl + '" alt="Theme Blue Preview" class="img-thumbnail">');
    }
</script>