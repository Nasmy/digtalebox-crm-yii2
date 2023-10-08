<?php
$feedMiniUrl = Yii::$app->urlManager->createUrl('dashboard/get-feed-mini');
$feedMini = <<<JS
    counter = 0;
	initial = true;
	
	setInterval(function() {
		loadSocialFeed();	
	}, 10000);
	
	function loadSocialFeed() {
		$.ajax({
			type: 'POST',
			url: '{$feedMiniUrl}',
			data: 'counter=' + counter,
			success: function(data){		
				if ('' == data.trim()) {
					if (!initial) {
						loadSocialFeed();
					}
					counter = 0;
					//
				} else {
					initial = false;
					counter++;
				}
				$('#social-feed-mini').html(data);
			}
		});
	}
	
	loadSocialFeed();
JS;
$this->registerJs($feedMini);
?>
<div class="col-lg-12 col-xl-6">
    <div class="content-panel">
        <div class="content-inner">
            <div class="panel-head"><?php echo Yii::t('messages', 'Social Activities'); ?></div>


            <div class="content-area">
                <div id="activity">

                    <div class="row">
                        <div class="col-md-12" id = "social-feed-mini"></div>
                        <div class="col-md-12">
                            <a class="line read-more" href="">

                                <div class="text-center">
                                    <a class="nav-link" href="<?php //echo Yii::$app->urlManager->createUrl('feed/social-feed'); ?>"><?php echo Yii::t('messages', 'Read More'); ?></a>
                                </div>

                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>