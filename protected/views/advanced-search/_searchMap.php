<?php
Yii::app()->clientScript->scriptMap = array(
    'jquery.js' => false,
);
?>
<?php
$form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
    'action' => Yii::app()->createUrl($this->route),
    'type' => 'inline',
    'method' => 'post',
));
?>

<?php echo $form->textFieldRow($model, 'firstName', array('class' => 'span2', 'maxlength' => 45)); ?>
<?php echo $form->textFieldRow($model, 'lastName', array('class' => 'span2', 'maxlength' => 45)); ?>
<?php echo $form->dropDownList($model, "countryCode", Country::model()->getCountryDropdown(), array('class' => 'span2')); ?>
<?php echo $form->textFieldRow($model, 'city', array('class' => 'span3', 'maxlength' => 64)); ?>
<?php echo $form->dropDownList($model, 'gender', array('' => Yii::t('messages', '- Gender -'), User::MALE => Yii::t('messages', 'Male'), User::FEMALE => Yii::t('messages', 'Female'), User::ASEXUAL => Yii::t('messages', 'Unknown')), array('class' => 'span2'));
echo '&nbsp;'; ?>
<?php echo $form->dropDownList($model, "userType", User::model()->getUserTypes(), array('class' => 'span2', 'prompt' => Yii::t('messages', '- Category -'))); ?>
<?php echo $form->textFieldRow($model, 'zip', array('class' => 'span2', 'maxlength' => 45));
echo '&nbsp;'; ?>
<?php //echo $form->dropDownList($model, 'network', User::model()->getNetworkTypes(), array('class'=>'span3')); echo '&nbsp;';?>
<?php echo $form->textFieldRow($model, 'email', array('class' => 'span2', 'maxlength' => 45)); ?>
<?php //echo $form->textFieldRow($model,'mobile',array('class'=>'span2','maxlength'=>45)); ?>
<?php echo $form->textFieldRow($model, 'age', array('class' => 'span2', 'maxlength' => 45)); ?>
<?php echo $form->error($model, 'age'); ?>
    <p></p>

<?php $this->widget('ext.yii-selectize.YiiSelectize', array(
    'model' => $model,
    'attribute' => 'keywords',
    'data' => $tagList,
    'defaultOptions' => array(
        'create' => false,
    ),
    'fullWidth' => false,
    'multiple' => true,
    'htmlOptions' => array(
        'style' => 'width:98%',
    ),
    'useWithBootstrap' => true,
    'placeholder' => Yii::t('messages', 'Keywords'),
));
?>

<?php $this->widget('ext.yii-selectize.YiiSelectize', array(
    'model' => $model,
    'attribute' => 'teams',
    'data' => $teams,
    'defaultOptions' => array(
        'create' => false,
    ),
    'fullWidth' => false,
    'multiple' => true,
    'htmlOptions' => array(
        'style' => 'width:98%',
    ),
    'useWithBootstrap' => true,
    'placeholder' => Yii::t('messages', 'Team Names'),
));
?>

<?php echo $form->hiddenField($model, 'id'); ?>
<?php
//if ($modelConfig->value) {
//    echo $form->checkboxRow($model, 'excludeFbPersonalContacts');
//}
?>
    <p></p>
<?php
$this->widget('bootstrap.widgets.TbButton', array(
    'buttonType' => 'submit',
    'type' => 'primary',
    'size' => 'small',
    'label' => Yii::t('messages', 'Search'),
));
?>
<?php $this->endWidget(); ?>
