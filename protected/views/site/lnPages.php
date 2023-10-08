<?php

use yii\helpers\Html;
use app\components\ToolKit;
use yii\web\View;

$toolKit = new ToolKit();
$toolKit->setJsFlash();

?>


<?php

$lnpageUrl = Yii::$app->urlManager->createUrl('site/add-lnpage');
$csrf = Yii::$app->request->csrfToken;
$csrfParam = Yii::$app->request->csrfParam;
$lnPages = <<<JS
$('.lnpages').on('click',function(){
 				$.ajax({
					type: 'POST',
					url:"$lnpageUrl",
                    dataType: "json",
                    data: {
					    'id':  $(this).attr('id'),
					    'name':  $(this).attr('value'), 
					    "$csrfParam":"$csrf"
					    },
 					success: function(data){
  						 if (data.status == 0) {
						    window.parent.$.fn.yiiGridView.update('search-criteria-grid', {
								data: $(this).serialize()
							});
                            setInterval(function () {
                            parent.jQuery.fancybox.close();
                            }, 2000);
                        } 
                    parent.jQuery.fancybox.close();
                    window.parent.$('#statusMsg').html(data.message);
                    window.parent.location.reload();
					}
				});
	});     
JS;
$this->registerJs($lnPages);


?>
<div id="auth-item-grid" class="grid-view">
    <table class="items table">
        <tr>
            <th><?php echo Yii::t('messages', 'LinkedIn Page Name') ?></th>
            <th class="button-column"
                id="user-match-grid_c1">&nbsp;
            </th>
        </tr>
        <?php
        foreach ($userP as $key => $page) {
            if (isset($page["localizedName"])) {
                ?>
                <tr>
                    <td><?php echo $page['localizedName'] ?></td>
                    <td class="text-right">
                        <?php echo Html::a('<i class="fa fa-check fa-2x fb"> </i>', '#', array('class' => 'lnpages', 'id' => $page['id'], 'value' => $page['localizedName'])); ?>
                    </td>
                </tr>
            <?php }
        } ?>
    </table>
    <div class="alert alert-info"><?php echo Yii::t('messages', 'Please select the desired linkedIn page from the list above to connect with DigitaleBox') ?>
    </div>
</div>
