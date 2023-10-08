<?php
/* @var $this NewsletterController */

$meta = [
    'http-equiv' => 'Refresh',
    'content' => '12; url=' . $requestUrl,
];
\Yii::$app->view->registerMetaTag($meta);

?>

<div class="register-promo">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 mx-auto">
                <div class="mainframe">
                    <?php if ($success): ?>
                        <div class="row">
                            <div class="col text-center my-3">
                                <h2><?php echo Yii::t('messages', 'Thank you for registration.') ?></h2>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if ($msg): ?>
                        <div class="row">
                            <div class="col text-center my-3">
                                <h5 style="text-align: center;"><?php echo $msg ?></h5>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="row">
                        <div class="col text-center my-3">
                            <div class="alert alert-info"
                                 role="alert"><?php echo Yii::t('messages', 'You will now be redirected.') ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>