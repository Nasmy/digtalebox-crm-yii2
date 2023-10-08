<?php


use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = Yii::t('messages', 'Texts');
$this->titleDescription = Yii::t('messages', 'Change portal texts');

$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'System'), 'url' => ['#']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Portal Settings'),'url' => ['#']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Texts')];

Yii::$app->toolKit->registerFancyboxScripts();
//Yii::$app->toolKit->registerTinyMceScripts();
?>
<!--<script>tinymce.init({ selector:'.text-editor' });</script>-->
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

//var_dump($lang);

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


<?php echo  Yii::$app->controller->renderPartial('_tabMenu'); ?>
<div class="content-inner">
    <div class="content-area">
        <?php $form = ActiveForm::begin([
            'id' => 'candidate-info-form',
            'options' => ['enctype' => 'multipart/form-data'],
//            'enableAjaxValidation' => true,
        ]); ?>
         <div class="form-row">
            <div class="form-group col-md-6">
                 <?php echo $form->field($model, 'headerText')->textInput(['class' => 'form-control', 'size' => 100, 'maxlength' => 100]); ?>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-12">
                 <?php echo $form->field($model, 'aboutText')->textarea(['class' => 'form-control text-editor']); ?>
            </div>
        </div>
        <div class="form-row text-left text-md-right">
            <div class="form-group col-md-12">
                <?php
                  echo  Html::submitButton(Yii::t('messages', 'Save'),[
                         'type' => 'primary',
                        'class' => 'btn btn-primary'
                    ]); ?>
            </div>
        </div>

        <?php
       ActiveForm::end();
        ?>

    </div>
</div>

</div> <!-- start at column1.php -->
</div><!-- start at column1.php -->
</div><!-- start at column1.php -->