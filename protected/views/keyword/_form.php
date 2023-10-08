<?php

use app\models\Autokeywordcondition;
use app\models\Keyword;
use app\models\Team;
use yii\widgets\ActiveForm;

use yii\helpers\ArrayHelper;
use yii\helpers\Html;

$Keyword = new Keyword();

$constManual = Keyword::KEY_MANUAL;
$constAuto = Keyword::KEY_AUTO;
$teamCond = Autokeywordcondition::APPLY_TEAMS;

$teamCond = Autokeywordcondition::APPLY_TEAMS;

$autoKeywordCondition = new  Autokeywordcondition();

$script = <<< JS
	$('#behaviour').on('change',function(){
		showAutoConditions($('#behaviour').val());
	});

	$('#conditions').on('change',function(){
		showTeams($('#conditions').val());
	});

	$('.chosen-select').chosen();

	function showAutoConditions(curBehaviour)
	{
		if ('{$constManual}' == curBehaviour) {
			$('#autoConditions').hide();
		} else {
			$('#autoConditions').show();
		}
	}

	function showTeams(selConditions)
	{
		if (jQuery.inArray('{$teamCond}', selConditions) > -1) {
			$('#divTeams').show();
		} else {
			$('#divTeams').hide();
		}
	}
    /* have issue with  Keyword->behaviour so instead of it using static 1 */
	showAutoConditions('1');
	showTeams($('#conditions').val());
JS;

$this->registerJs($script);

?>
<div class="row no-gutters">
    <div class="content-panel col-md-12">
        <div class="content-inner">
            <div class="content-area">
                <?php
                $form = ActiveForm::begin([
                    'id' => 'keyword-form',
                    'options' => [
                        'type' => 'horizontal',
                        'class' => 'form-horizontal',
                        'method' => 'post',
                        'enableAjaxValidation' => true,
                        'validateOnSubmit' => true,
                    ],
                ]);
                ?>
                <div class="form-group col-md-4">

                    <?php
                    if ($model->isNewRecord) {
                        echo $form->field($model, 'name')->textInput(['class' => 'form-control', 'maxlength' => 45]);
                    } else {
                        echo $form->field($model, 'name')->textInput(['class' => 'form-control', 'maxlength' => 45]);
                    }
                    ?>
                </div>

                <div class="form-group col-md-4">
                    <label for="Form_titles"><?php echo $attributeLabels['behaviour']; ?></label>
                    <?php

                    echo $form->field($model, 'behaviour')->dropDownList(array_diff($model->getBehaviourOptions(), ["Auto"]), ['class' => 'form-control', 'id' => 'behaviour'])->label(false);
                    ?>
                </div>

                <div id="autoConditions" class="form-group col-md-4">
                    <?php
                    echo $form->field($model, 'conditions[]')->dropDownList($autoKeywordCondition->rules, ['class' => 'form-control chosen-select ', 'multiple' => true, 'id' => 'conditions'])->label(false);
                    ?>
                </div>

                <div id="divTeams" class="form-group col-md-4">
                    <?php
                    echo $form->field($model, 'team[]')->dropDownList(ArrayHelper::map(Team::find()->all(), 'id', 'name'), ['class' => 'form-control ', 'multiple' => true, 'id' => 'team'])->label(false);
                    ?>
                </div>


                <div class="form-group col-md-4">
                    <label for="Form_titles"><?php echo $attributeLabels['status']; ?></label>

                    <?php
                    echo $form->field($model, 'status')->dropDownList($model->getKeywordStatusOptions(), ['class' => 'form-control '])->label(false);
                    ?>
                </div>
                <div class="form-group">
                    <div class="text-left text-md-left">
                        <div class="form-group col-md-12">
                            <?php
                            echo Html::submitButton($model->isNewRecord ? Yii::t('messages', 'Create') : Yii::t
                            ('messages', 'Save'), ['class' => 'btn btn-primary']);
                            ?>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>
