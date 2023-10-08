<?php


use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$attributeLabels = $model->attributeLabels();

$this->title = Yii::t('messages', 'Reply');
$this->titleDescription = Yii::t('messages', 'Send reply for the message');

$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Communication'), 'url' => ['#']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Inbox'), 'url' => ['msg-box/inbox']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Reply')];

?>

<?php
Yii::$app->toolKit->registerFancyboxScripts();
?>

<?php
$langs = Yii::$app->toolKit->getComponenetSpecificLangIdentifier('tinyMce');
$lang = false;
switch ($langs){
    case 'en-Us':
        $lang = false;
        break;
    case 'fr-FR':
        $lang =  'fr_FR';
        break;

}

$script = <<< JS
 	tinymce.init({
		language : '{$lang}',
		selector:'textarea:not(.mceNoEditor)',
		theme:'modern',
		plugins: [
			'advlist autolink link image lists charmap print preview hr anchor pagebreak',
			'searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime nonbreaking',
			'save table contextmenu directionality emoticons template paste textcolor jbimages'
		],

		relative_urls : false,
		remove_script_host : false,
		convert_urls : true,

	});
JS;

$this->registerJs($script);

?>

<div>
    <div class="row no-gutters">
        <div class="content-panel col-md-12">
            <div class="content-inner">
                <div class="content-area">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="content-panel-sub">
                                <div class="panel-head"><?php Yii::t('messages', 'Message Details') ?></div>
                            </div>
                            <?php $form = ActiveForm::begin();
                            ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <?php
                                            echo $form->field($model,'subject')->textInput(['class' => 'form-control']);
                                        ?>
                                     </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <?php
                                            echo $form->field($model,'message')->textarea(['rows'=>10, 'cols'=>50, 'class' => 'form-control']);
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="form-row text-left text-md-right">
                                <div class="form-group col-md-12">
                                    <?php
                                        echo Html::submitButton(Yii::t('messages', 'Send'),['class'=>'btn btn-primary','type'=>'primary',]);
                                    ?>
                                    <?php
                                        echo Html::a(Yii::t('messages', 'Cancel'),Url::to('inbox'),['class'=>'btn btn-secondary','type'=>'info',]);
                                    ?>
                                </div>
                            </div>

                            <?php ActiveForm::end(); ?>

                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
