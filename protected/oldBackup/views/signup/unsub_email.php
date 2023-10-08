<?php
Yii::$app->toolKit->registerVolunteerThemeStyle();
?>

<div class="register-promo">

    <div class="container">

        <div class="row">
            <div class="col-lg-6 mx-auto">
                <div class="mainframe">
                    <div class="row">
                        <div class="col text-center my-5">
                            <img src="<?php echo Yii::$app->toolKit->getImagePath() ?>digitalebox-logo.png">
                        </div>

                    </div>
                    <?php if ($unsubSuccess): ?>
                        <div class="row">
                            <div class="col text-center mb-4">
                                <h3 class="text-success"><?php echo Yii::t('messages', 'Unsubscribe Successful.'); ?>
                                </h3>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (! $unsubSuccess): ?>
                        <div class="row">
                            <div class="col text-center mb-4">
                                <h3 class="text-success"><?php echo Yii::t('messages', 'Unsubscribe Failed.'); ?>
                                    </h3>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="bottom text-center">
                        <?php if ($unsubSuccess): ?>

                            <div class="desc mb-3">
                                <p><?php echo Yii::t('messages', 'You have been removed from Digitalebox System Alerts'); ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if (! $unsubSuccess): ?>

                            <div class="desc mb-3">
                                <p><?php echo Yii::t('messages', 'Unsubscribe from Digitalebox System Alerts failed. Please try again later.'); ?></p>
                            </div>
                        <?php endif; ?>

                        <div class="cprt mt-4 mb-1">Copyright © <?php echo date('Y') ?> by DigitaleBox. All Rights Reserved.</div>
                    </div>

                </div>
            </div>

        </div>

    </div>

</div>

