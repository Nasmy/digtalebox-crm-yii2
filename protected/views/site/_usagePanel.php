<?php

use app\models\App;
use app\models\User;

$upgradeUrl = '';
if (Yii::$app->user->checkAccess('UpgradePlan') && !Yii::$app->session->get('isDefaultApp')){
    $upgradeUrl = Yii::$app->toolKit->getUpgradeUrl();
}

if ($package['packageTypeId'] != App::FREEMIUM) {
    $numberOfmobile = User::getNumberOfMobileContacts();
    $emailContactsUsage = ceil(($usage['emailContacts']['used'] / $usage['emailContacts']['max']) * 100);
    $socialContactsUsage = ceil(($usage['socialContacts']['used'] / $usage['socialContacts']['max']) * 100);
    $smsUsage = @ceil(($usage['smsLimit']['used'] / $usage['smsLimit']['max']) * 100);
}
?>

<div class="account-status">
    <div class="title"><?php echo Yii::t('messages','Account Status') ?> <i class="fa fa-angle-down fa-lg pull-right"></i></div>
    <div class="plans">
        <a href="<?php echo $upgradeUrl ?>"><?php echo Yii::t('messages','Plan - ') . " " . Yii::t('messages',$package['packageName']);?><i class="fa  fa-arrow-circle-o-up fa-lg pull-right"></i></a>
    </div>
    <?php
    if ($package['packageTypeId'] != App::FREEMIUM): ?>
    <div class="email-count">
        <div class="values">
            <div class="heading"><?php echo Yii::t('messages','Email Contacts'); ?></div>
            <div class="value"><?php echo $usage['emailContacts']['used'] ?> / <?php echo $usage['emailContacts']['max']?></div>
        </div>
        <div class="slider">
            <div class="progress">
                <div class="progress-bar" role="progressbar" aria-valuenow="<?php echo $emailContactsUsage ?>"
                     aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $emailContactsUsage ?>%;"></div>
            </div>
        </div>
    </div>
    <div class="social-count">
        <div class="values">
            <div class="heading"><?php echo Yii::t('messages','Social Contacts') ?></div>
            <div class="value"><?php echo $usage['socialContacts']['used'] ?> / <?php echo $usage['socialContacts']['max'] ?></div>
        </div>
        <div class="slider">
            <div class="progress">
                <div class="progress-bar" role="progressbar" aria-valuenow="<?php echo $socialContactsUsage ?>" aria-valuemin="0"
                aria-valuemax="100" style="width: <?php echo $socialContactsUsage ?>%;">
                </div>
            </div>
        </div>
    </div>
    <div class="sms-count">
        <div class="values">
            <div class="heading"><?php echo Yii::t('messages','SMS Usage') ?></div>
            <div class="value"><?php echo $usage['smsLimit']['used'] ?> / <?php echo $usage['smsLimit']['max'] ?></div>
        </div>
        <div class="slider">
            <div class="progress">
                <div class="progress-bar" role="progressbar" aria-valuenow="<?php echo $smsUsage ?>" aria-valuemin="0"
                aria-valuemax="100" style="width: <?php echo $smsUsage ?>%;">
                </div>
            </div>
        </div>
    </div>
    <div class="mobile-count">
        <div class="values">
            <div class="heading"><?php echo Yii::t('messages','Mobile Contacts') ?></div>
            <div class="value"><?php echo $numberOfmobile ?></div>
        </div>

    </div>
    <?php endif; ?>
</div>
