<?php
/* @var $this NewsletterController */

$meta = [
    'http-equiv' => 'Refresh',
    'content' => '12; url=' . $requestUrl,
];
\Yii::$app->view->registerMetaTag($meta);


 if (null == $msg){
?>
<div class="register-promo">

    <div class="container">

        <div class="row">
            <div class="col-lg-6 mx-auto">
                <div class="mainframe">
                    <div class="panel panel-danger">
                        <div class="row align-items-center">
                            <div class="col-lg-9 ml-5 text-center mx-auto my-3 align-items-center">
                                <div class="panel-heading" style="background-color: #FFD9D9">
                                    <h3 class="panel-title"
                                        style="font-weight: bold;"><?php echo Yii::t('messages', 'Please fix the following errors.') ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col text-left my-3">
                                <div class="panel-body">
                                    <ul>
                                        <?php
                                        if ($error) {
                                            echo "<li>" . $error[0] . "</li>";
                                        } else {
                                            foreach ($model->getErrors() as $error) {
                                                echo "<li>" . $error[0] . "</li>";
                                            }
                                        }
                                        ?>

                                        <?php

                                        if ($customErrors) {
                                            foreach ($customErrors as $error) {
                                                echo "<li>" . $error['fieldValue'][0] . "</li>";
                                            }
                                        }
                                        ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php } else { ?>
                        <div class="row">
                            <div class="text-center my-3">
                                <div class="panel panel-danger" style="padding: 10px">
                                    <div class="panel-heading" style="background-color: #FFD9D9">
                                        <h3 class="panel-title" style="font-weight: bold;"><?php echo $msg; ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>

                    <div class="alert alert-info text-center mx-auto my-3"
                         role="alert"><?php echo Yii::t('messages', 'You will now be redirected.') ?>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>