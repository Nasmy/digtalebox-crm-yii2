<?php

use yii\helpers\Html;
use yii\web\View;

Yii::$app->toolKit->registerFancyboxScripts();

$script = <<< JS
    jQuery(document).ready(function() {
        $('.btnTempSelect').on('click',function(){
             $.ajax({
                type: 'GET',
                url: $(this).attr('data-contUrl'),
                success: function(data){
                    if (data != '') {
                        $('#MessageTemplate_dragDropMessageCode', window.parent.document).val(null);
                        //$('#MessageTemplate_dragDropMessageCode').val(null);
                        window.parent.objEditor.setContent(data, {format : 'raw'});
                        setInterval(function () {
                            parent.$.fancybox.close();
                            }, 1000);
                        $.fancybox.resize();//auto resize

                    }
                }
            });
        }); 
	  
	 
     $('.fancyImg').click(function(e){
         console.log(1)
        e.preventDefault();
        $.fancybox({
            'href':$(this).attr('data-prvImage'), 
        }); 
        return false;
        });   
    });

JS;
$this->registerJs($script, View::POS_READY);

?>
<style type="text/css">
    img.fancybox-image {
        max-width: 100%;
    }
</style>
<div class="multiple">
    <div class="msg_temp_thumb_frm">
        <?php
        foreach ($model->emailTemplates as $key => $theme) { ?>
            <div class="span4">
                <div class="span12" style="text-align:center;">
                    <?php
                        $imgUrl = Yii::$app->toolKit->getImagePath() . $theme['thumbnail'];
                        $prevUrl = Yii::$app->toolKit->getImagePath() . $theme['prvImage'];
                    ?>
                    <?php echo Html::a(Html::img($imgUrl), $prevUrl, ['class' => 'fancyImg',
                        'id' => 'fancyImg', 'data-prvImage' => $prevUrl,]) ?>

                </div>

                <div class="span12" style="text-align:center; margin:10px 0px;">
                    <?php echo Html::a(Yii::t('messages', 'Select'), false, ['class' => 'btnTempSelect btn btn-primary px-4', 'data-contUrl' => Yii::$app->urlManager->createUrl(['message-template/get-template-content', 'fileName' => $theme['fileName']])]) ?>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
