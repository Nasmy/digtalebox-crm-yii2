<?php

use app\components\WebUser;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\MsgBox */
/* @var $form yii\widgets\ActiveForm */
?>
<?php

$url = Yii::$app->urlManager->createUrl('msg-box/get-names');
$hintText = Yii::t('messages', 'Type Name');
$searchingText = Yii::t('messages', 'Searching...');

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

$scriptTinymce = <<< JS
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

$this->registerJs($scriptTinymce);
 ?>
<!---->
<?php $this->registerCssFile('@web/themes/bootstrap_spacelab/jquery-token/styles/token-input.css'); ?>
<?php $this->registerCssFile('@web/themes/bootstrap_spacelab/jquery-token/styles/token-input-facebook.css'); ?>
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
                            <?php
                                $form = ActiveForm::begin([
                                    'id'=>'msg-queue-form',
                                    'enableAjaxValidation' => true,
                                    // 'validateOnSubmit' => true,
                                ]);
                            ?>
                            <div class="row">
                                <?php
                                if(!Yii::$app->user->checkAccess(WebUser::SUPERADMIN_ROLE_NAME) || Yii::$app->session->get('is_super_admin')): ?>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <?php
                                               echo  $form->field($model,'criteriaId')->dropDownList($criteriaOptions,['class'=>'form-control']);
                                             ?>
                                            <div class="form-feild-info"><?php echo Yii::t('messages','Note:Message sent only to users with email.') ?></div>
                                        </div>
                                    </div>
                                <?php  endif; ?>
                                <div class="col-md-6">
                                    <label for="" class="d-none d-md-block">&nbsp;</label>
                                    <?php
                                      echo  Html::a(Yii::t('messages', 'Define New Criteria'),
                                            Yii::$app->urlManager->createUrl('advanced-search/admin'),
                                        [
                                            'class'=>'btn btn-secondary',
                                            'type'=>'info',
                                        ]);
                                    ?>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="MsgBox_userlist"><?php echo $attributeLabels['userlist']; ?></label>
                                        <?php
                                        echo $form->field($model,'userlist')->textInput()->label(false);
                                        ?>
                                        <div class="form-feild-info"><?php echo Yii::t('messages','Note:List only users with email.') ?></div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="MsgBox_subject"><?php echo $attributeLabels['subject']; ?></label>
                                        <?php
                                            echo $form->field($model,'subject')->textInput(['form-control'])->label(false);
                                        ?>
                                     </div>
                                </div>

                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="MsgBox_message"><?php echo $attributeLabels['message']; ?></label>
                                        <?php
                                            echo $form->field($model, 'message')->textarea(['rows'=>10,
                                                'cols'=>50, 'class'=>'form-control'])->label(false); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row text-left text-md-right">
                                <div class="form-group col-md-12">
                                    <?php
                                       echo Html::submitButton(Yii::t('messages','Send'),[ 'buttonType' => 'submit',
                                        'type' => 'primary','class'=>'btn btn-primary']);
                                    ?>
                                    <?php
                                        echo Html::a(Yii::t('messages', 'Cancel'),Yii::$app->urlManager->createUrl('msg-box/inbox'),[ 'class'=>'btn btn-secondary']);
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


<?php $this->registerJsFile('@web/themes/bootstrap_spacelab/jquery-token/src/jquery.tokeninput.js', ['depends' => 'yii\web\JqueryAsset']); ?>

<script type="text/javascript">
    $(document).ready(function() {

        var userlist = '<?php json_decode($userlist,false);?>';
          $('#msgbox-userlist').tokenInput('<?php echo $url ;?>', {theme: 'facebook', hintText:'<?php echo  $hintText ;?>', searchingText:'<?php echo $searchingText ;?>'});

        if (userlist != '' ) {
           for (var i in userlist) {
               $("#msgbox-userlist").tokenInput("add", {id: userlist[i].id, name: userlist[i].name});
            }
        }

    });
</script>