<?php use yii\helpers\Html;
use yii\widgets\ActiveForm;

        $form = ActiveForm::begin([
            'id' => 'broadcast-message-form',
//            'enableAjaxValidation' => true,
             'options' => array('enctype' => 'multipart/form-data')
        ]);

  ?>

    <div class="form-row">
        <div class="form-group col-md-12">
            <?php echo $form->field($model, 'longUrl')->textInput(array('class' => 'form-control')); ?>
        </div>

        <p/>

        <?php
        if ('' != $shortUrl): ?>
            <div class="alert alert-info"><?php echo $shortUrl ?></div>
            <p/>
        <?php endif; ?>
        <div class="form-group col-md-12">
            <?php
               echo Html::submitButton(Yii::t('messages', 'Short URL'),[
                    'class' => 'input-block-level btn btn-primary',
                ]);
            ?>
        </div>
    </div>
<?php
ActiveForm::end();
?>