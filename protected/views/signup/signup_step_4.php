<?php Yii::$app->toolKit->registerVolunteerThemeStyle(); ?>

<div class="col-lg-6 mx-auto forgot-pass">
    <div class="mainframe">
        <div class="row">
            <div class="upper col-md-12 text-center">
                <div class="mx-auto mb-4">
                    <img src="<?php echo Yii::$app->toolKit->getImagePath() ?>digitalebox-logo.png">
                </div>
                <div class="forgot-text text-success"><i class="fa fa-check-circle-o mr-2"></i><?php echo Yii::t('messages', 'Sign Up Complete') ?></div>
            </div>
        </div>

        <div class="bottom text-center">
            <div class="col-md-12 mx-auto">
                <div class="desc">
                    <?php if($reauth){ ?>
                    <?php echo Yii::t('messages', 'Thank you for registering with us. If you want to support and promote our Organization you can share the following link on your social media pages as well as blog pages.'); ?>
                    <?php } else { ?>
                    <?php echo Yii::t('messages', 'Thank you for registering with us. An email has been sent with futher information. If you want to support and promote our Organization you can share the following link on your social media pages as well as blog pages.');?>
                    <?php } ?>
                    <a class="mt-2 d-block" href="<?php echo $signUpLink ?>"><?php echo $signUpLink ?></a>
                </div>

                <?php
                echo "<br/>" . \yii\bootstrap\Html::a(Yii::t('messages', 'Complete'),
                    Yii::$app->urlManager->createUrl('site/init'), array(
                        'label'=>Yii::t('messages', 'Complete'),
                        'class' => 'btn btn-success',
                        'style' => array(
                            'color' => '#ffffff',
                        ),
                    ));
                ?>
            </div>
            <div class="cprt mt-4">Copyright © 2018 by DigitaleBox. All Rights Reserved.</div>
        </div>
    </div>
</div>