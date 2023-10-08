<?php
use yii\helpers\Html;
$this->registerJs("
	$('#here').click(function(){
		window.parent.location = $('#here').attr('href');
		return false;
	});
");
?>

<div class="alert alert-info">
    <?php
    echo Yii::t('messages', 'Please configure your email address first. Click {here} to configure email.', array(
        'here' => Html::a(Yii::t('messages','here'), ['configuration/update', 'hlt' => base64_encode('fromEmail')], ['id' => 'here'])
    ));
    ?>
</div>
