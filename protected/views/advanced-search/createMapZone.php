<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
Yii::$app->toolKit->setJsFlash();
$alertMsg1 = Yii::t('messages', 'Title cannot be empty');
echo Yii::$app->toolKit->registerAdvanceSearchScript();
$this->registerJs("
    $( document ).ready(function() {
        $('#mapzone-teamzonedata').val(parent.$('#teamZoneData').val());
    });
    $('#cancel-button').click(function(){
        parent.jQuery.fancybox.close();
    });


    $('#map-search').click(function(event) {
        event.preventDefault();
        if ($('#MapZone_title').val() == '') {
            setJsFlash('error', '".$alertMsg1."');
            return false;
        }
        var url = '".Yii::$app->urlManager->createUrl(['/advanced-search/map-zone-create'])."';
        $.ajax({
            type: 'POST',
            url: url,
            data: $('#map-zone-form').serialize() + '&' + parent.$('#searchFormData').val(), // read and prepare all form fields
            success: function(data) {
                var res = $.parseJSON(data);
                if (res.status == 'success') {
                    parent.jQuery.fancybox.close();
                    window.parent.$('#statusMsg').html(res.message);
                    window.parent.location.href='".Yii::$app->urlManager->createUrl(['/advanced-search/all-map-zones'])."';
                }
                else {
                    $('#statusMsg').html(res.message);
                }
            }
        });
    });
    ");
?>
<div class="modal-body">
    <div class="form-control">
        <?php
        $form = ActiveForm::begin(['action' => '/index.php/advanced-search/map-zone-create', 'enableAjaxValidation' => false, 'id' => 'map-zone-form', 'class' => 'form-vertical', 'method' => 'post']);
        ?>
        <label for="MapZone_title">Title</label>
        <?php echo $form->field($model, 'title', ['enableAjaxValidation' => false])->textInput(['class' => 'form-control', 'size' => 54, 'max' => 54, 'id' => 'lastName', 'placeholder' => 'title'])->label(false); ?>
        <p class="help-block">Title to save the map zone.</p>
        <?php echo $form->field($model, 'teamZoneData')->hiddenInput(['value' => ''])->label(false); ?>
    </div>
</div>
<div class="modal-footer">
    <?= Html::submitButton($model->isNewRecord ? Yii::t('messages', 'Create') : Yii::t('messages', 'Save'), ['type' => 'submit', 'encodeLabel' => false, 'size' => 'small', 'class' => 'btn btn-primary', 'id' => 'map-search']); ?>
</div>
<?php ActiveForm::end(); ?>
