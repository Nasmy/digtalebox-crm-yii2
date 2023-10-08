<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>
<!-- View Popup  -->
<div id="statusMsg"></div>
<?php
$form = ActiveForm::begin(['id' => 'searchCriteriaForm', 'class' => 'searchCriteriaForm form-vertical', 'method' => 'multipart/form-data']); ?>
<!-- Popup Content -->
<div class="modal-body">
    <div class="form-row">
        <div class="form-control no-border p-0 col-sm-9 col-md-8 col-lg-9">
            <label class="control-label required" for="SearchCriteria_criteriaName"><?=Yii::t('messages', 'Criteria Name')?><span class="required">*</span></label>
            <?php echo $form->field($model, 'criteriaName')->textInput(['class' => 'input-block-level form-control', 'maxlength' => 45])->label(false); ?>
        </div>
        <?php echo $form->field($model, 'id')->hiddenInput()->label(false); ?>
    </div>
</div>
<!-- Popup Footer -->
<div class="modal-footer">
    <script type="text/javascript">
        $(document).ready(function () {
            <?php if(preg_match( '#^Saved search-#',$model->criteriaName)){?>  //Saved search-1608703130
            $('#searchcriteria-criterianame').val('');
            <?php } ?>
            var url = '<?=Yii::$app->urlManager->createUrl(['search-criteria/ajax-save/']);?>';
            var data = $('.search-form form').serialize();
            $('#searchCriteriaSave').click(function (e) {
                e.preventDefault();
                $.ajax({
                    type: 'POST',
                    url: url,
                    dataType: "json",
                    data: {searchCriteria:$('form').serialize()},
                    success: function (data) {
                        var data = JSON.parse(data);
                        if(data.status=="success"){
                            $("#search-criteria").modal('hide');
                            $("#statusMsg").html('<div class="alert in alert-success">'+data.msg+'<a data-dismiss="alert" class="close">×</a></div>');
                            parent.$("#criteriaId").html(data.options);
                            $.each(data.attributes, function(key, value) {
                                $("#User_" + key).val(value);
                                $("#SearchCriteria_" + key + "_em_").html("");
                            });
                            $("#save-search").attr("data",data.name);
                        }else{
                            $("#statusMsg").html('<div class="alert in alert-error">'+data.msg+'<a data-dismiss="alert" class="close">×</a></div>');
                            $.each(data, function(key, val) {
                                $("#search-criteria-form #"+key+"_em_").text(val);
                                $("#search-criteria-form #"+key+"_em_").show();
                            });
                        }
                        return false;
                    },
                });
            });
        });
    </script>
    <?php $ajaxSaveUrl = Yii::$app->urlManager->createUrl(['search-criteria/ajax-save/']) ?>
    <?= Html::submitButton(Yii::t('messages', 'Save Search Criteria'), [
        'type' => 'submit',
        'encodeLabel' => false,
        'size' => 'small',
        'class' => 'btn btn-primary',
        'id' => 'searchCriteriaSave',
        'data-dismiss' => 'modal0'
    ]); ?>
    <!-- close button -->
    <?php
    echo Html::a(Yii::t('messages', 'Cancel'), '#', ['class' => 'cancel btn btn-secondary','data-dismiss'=>'modal','aria-hidden'=>'true']);
    ?>

    <!-- close button ends-->
</div>
<?php ActiveForm::end(); ?>

<!-- View Popup ends -->
<script>
    $(document).ready(function () {
        $('#search-criteria').on('hidden.bs.modal', function (e) {
            $("#iframe-searchCriteria").attr("src", "")
        });
        $('.cancel').on('click', function (e) {
            parent.$('#search-criteria').modal('hide');
        });
    });
</script>