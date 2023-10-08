<?php
Yii::app()->toolKit->registerOwlCarouselScripts();
$items = Yii::app()->toolKit->getSiteGuideFeatures();
$itemsJson = json_encode($items);
?>

<?php
$urlHome = Yii::app()->createUrl('site/index');
Yii::app()->clientScript->registerScript('site-guide', "
	var items = {$itemsJson};
	var viewType = '';
	
	if( $(window).width() >= 980 ){
		// Desktop
        $('.slider_frm').animate({
			bottom : 0
		}, 1500);

		$('.close_btn').click(function(){
			location.href = '{$urlHome}';
		});
		
		viewType = 'desktop';
    }else{
        $('.slider_frm').animate({
            bottom : 0
        }, 1500, function(){
            $('.navbar-inner .btn-navbar').trigger('click');
        });

        // close button
        $('.close_btn').click(function(){
            location.href = '{$urlHome}';
        });
		
		viewType = 'mobile';
    }
	
	$('#owl-demo').owlCarousel({
		navigation : true, // Show next and prev buttons
		slideSpeed : 300,
		paginationSpeed : 400,
		singleItem: true,
		pagination: false,
		responsive: true,

		afterMove : function(){
			highlightItem(this.owl.currentItem);
		}
	});
	
	function getItemInfo(id) {
		var info = '';
		$.each(items, function(idx, rec){
			if (id == idx) {
				info = rec;
			}
		});
		
		return info;
	}
	
	function highlightItem(currentItemNumber) {
	
		switch (viewType) {
			case 'mobile':
				$('.nav li').removeClass('active');
				$('#people .dropdown-menu').css('display', 'none');
				$('#people').removeClass('active');
				break;
				
			case 'desktop':
				$('.nav li').removeClass('active');
				$('#people .dropdown-menu').css('display', 'none');
				$('#people').removeClass('active');

				$('#communication .dropdown-menu').css('display', 'none');
				$('#communication').removeClass('active');

				$('#system .dropdown-menu').css('display', 'none');
				$('#system').removeClass('active');

				$('#account .dropdown-menu').css('display', 'none');
				$('#account').removeClass('active');
				break;
		}
	
		var itemInfo = getItemInfo(currentItemNumber);
		
		switch(itemInfo.id) {
			case 'home':
			case 'dash':
				$('#' + itemInfo.id).addClass('active');
				break;
				
			case 'basic_srch':
			case 'advanced_srch':
			case 'saved_srch':
			case 'keywords':
			case 'bulk_insert':
			case 'stats':
			case 'teams':
			case 'social_activity':
			case 'volunteers':
			case 'events':
			case 'resource':
			case 'activity':
			case 'donation':
			case 'friend_find':
			
				$('#people').addClass('active');
                $('#people .dropdown-menu').css('display', 'block');
                $('#' + itemInfo.id).addClass('active');
				
				break;
			
			case 'msg-template':
			case 'campaigns':
			case 'msg-box':
			
				$('#communication').addClass('active');
                $('#communication .dropdown-menu').css('display', 'block');
                $('#' + itemInfo.id).addClass('active');

				break;
				
			case 'mng-roles':
			case 'mng-sys-users':
			case 'org-info':
			case 'ad-banner':
			case 'feed-keywords':
			case 'config':
			case 'portal-settings':
			
				$('#system').addClass('active');
                $('#system .dropdown-menu').css('display', 'block');
                $('#' + itemInfo.id).addClass('active');
			
				break;
				
			case 'my-account':
			case 'chng-pass':
			case 'lang':
			case 'Logout':
			
				$('#account').addClass('active');
                $('#account .dropdown-menu').css('display', 'block');
                $('#' + itemInfo.id).addClass('active');
			
				break;
			
		}
		
		if (viewType == 'mobile') {
			$('html, body').animate({
				scrollTop: $('#' + itemInfo.id).offset().top
			}, 500);
		}
	}
");
?>

<div class="slider_frm">
    <div class="close_btn"></div>
	<div id="owl-demo" class="owl-carousel owl-theme">
		<!--<div class="item">
            <div class="col-md-12">
                <h4><?php echo Yii::t('messages', 'DigitaleBox, Take a Tour')?></h4>
                <p><?php echo Yii::t('messages', 'Take a Tour with our Guide to understand DigitaleBox features');?></p>
            </div>
        </div>-->
		<?php foreach ($items as $item) {
			if ('' == $item['image']) {
				continue;
			}
		?>
			<div class="item">
				<div class="col-md-6">
					<img class="img-thumbnail" src="<?php echo Yii::app()->toolKit->getImagePath()?>/guide/<?php echo $item['image'] ?>">
				</div>
				<div class="col-md-6">
					<h4><?php echo @$item['title']?></h4>
					<p><?php echo $item['message'] ?></p>
				</div>
			</div>
		<?php } ?>
	</div>
</div>

<script>
/*$(document).ready(function() {

    if( $(window).width() >= 980 ){
        desktop();
    }else{
        mobile();
    }

    
    function desktop(){
        $('.slider_frm').animate({
            bottom : 0
        }, 1500);

        $('.close_btn').click(function(){
            $('.slider_frm').animate({
                bottom : -300
            }, 1000, function(){
                $('.slider_frm').hide();
            });
        });

        $("#owl-demo").owlCarousel({
            navigation : true, // Show next and prev buttons
            slideSpeed : 300,
            paginationSpeed : 400,
            singleItem: true,
            pagination: false,
            responsive: true,

            afterMove : function(){
				console.log(this.innerHTML);
                check_active(this.owl.currentItem);
            }
        });
		
		var owl = $("#owl-demo").data('owlCarousel');
		console.log(owl);

        function check_active(current){
            //alert(current);

            $( ".nav li" ).removeClass( "active" );

            $( "#people .dropdown-menu" ).css('display', 'none');
            $( "#people" ).removeClass( "active" );

            $( "#communication .dropdown-menu" ).css('display', 'none');
            $( "#communication" ).removeClass( "active" );

            $( "#system .dropdown-menu" ).css('display', 'none');
            $( "#system" ).removeClass( "active" );

            $( "#account .dropdown-menu" ).css('display', 'none');
            $( "#account" ).removeClass( "active" );


            switch(current) {
                case 0:
                    $( "#home" ).addClass( "active" );
                    break;
                case 1:
                    $( "#dash" ).addClass( "active" );
                    break;
                case 2:
                    $( "#people" ).addClass( "active" );
                    $( "#people .dropdown-menu" ).css('display', 'block');
                    $( "#basic_srch" ).addClass( "active" );
                    break;
                case 3:
                    $( "#people" ).addClass( "active" );
                    $( "#people .dropdown-menu" ).css('display', 'block');
                    $( "#advanced_srch" ).addClass( "active" );
                    break;
                case 4:
                    $( "#people" ).addClass( "active" );
                    $( "#people .dropdown-menu" ).css('display', 'block');
                    $( "#saved_srch" ).addClass( "active" );
                    break;
                case 5:
                    $( "#people" ).addClass( "active" );
                    $( "#people .dropdown-menu" ).css('display', 'block');
                    $( "#keywords" ).addClass( "active" );
                    break;
                case 6:
                    $( "#people" ).addClass( "active" );
                    $( "#people .dropdown-menu" ).css('display', 'block');
                    $( "#bulk_insert" ).addClass( "active" );
                    break;
                case 7:
                    $( "#people" ).addClass( "active" );
                    $( "#people .dropdown-menu" ).css('display', 'block');
                    $( "#stats" ).addClass( "active" );
                    break;
                case 8:
                    $( "#people" ).addClass( "active" );
                    $( "#people .dropdown-menu" ).css('display', 'block');
                    $( "#teams" ).addClass( "active" );
                    break;
                case 9:
                    $( "#people" ).addClass( "active" );
                    $( "#people .dropdown-menu" ).css('display', 'block');
                    $( "#social_activity" ).addClass( "active" );
                    break;
                case 10:
                    $( "#people" ).addClass( "active" );
                    $( "#people .dropdown-menu" ).css('display', 'block');
                    $( "#volunteers" ).addClass( "active" );
                    break;
                case 11:
                    $( "#people" ).addClass( "active" );
                    $( "#people .dropdown-menu" ).css('display', 'block');
                    $( "#events" ).addClass( "active" );
                    break;
                case 12:
                    $( "#people" ).addClass( "active" );
                    $( "#people .dropdown-menu" ).css('display', 'block');
                    $( "#resource" ).addClass( "active" );
                    break;
                case 13:
                    $( "#people" ).addClass( "active" );
                    $( "#people .dropdown-menu" ).css('display', 'block');
                    $( "#activity" ).addClass( "active" );
                    break;
                case 14:
                    $( "#people" ).addClass( "active" );
                    $( "#people .dropdown-menu" ).css('display', 'block');
                    $( "#donation" ).addClass( "active" );
                    break;
                case 15:
                    $( "#people" ).addClass( "active" );
                    $( "#people .dropdown-menu" ).css('display', 'block');
                    $( "#friend_find" ).addClass( "active" );
                    break;

                //communication
                case 16:
                    $( "#communication" ).addClass( "active" );
                    $( "#communication .dropdown-menu" ).css('display', 'block');
                    $( "#msg-template" ).addClass( "active" );
                    break;
                case 17:
                    $( "#communication" ).addClass( "active" );
                    $( "#communication .dropdown-menu" ).css('display', 'block');
                    $( "#campaigns" ).addClass( "active" );
                    break;
                case 18:
                    $( "#communication" ).addClass( "active" );
                    $( "#communication .dropdown-menu" ).css('display', 'block');
                    $( "#msg-box" ).addClass( "active" );
                    break;

                //system
                case 19:
                    $( "#system" ).addClass( "active" );
                    $( "#system .dropdown-menu" ).css('display', 'block');
                    $( "#mng-roles" ).addClass( "active" );
                    break;
                case 20:
                    $( "#system" ).addClass( "active" );
                    $( "#system .dropdown-menu" ).css('display', 'block');
                    $( "#mng-sys-users" ).addClass( "active" );
                    break;
                case 21:
                    $( "#system" ).addClass( "active" );
                    $( "#system .dropdown-menu" ).css('display', 'block');
                    $( "#org-info" ).addClass( "active" );
                    break;
                case 22:
                    $( "#system" ).addClass( "active" );
                    $( "#system .dropdown-menu" ).css('display', 'block');
                    $( "#ad-banner" ).addClass( "active" );
                    break;
                case 23:
                    $( "#system" ).addClass( "active" );
                    $( "#system .dropdown-menu" ).css('display', 'block');
                    $( "#feed-keywords" ).addClass( "active" );
                    break;
                case 24:
                    $( "#system" ).addClass( "active" );
                    $( "#system .dropdown-menu" ).css('display', 'block');
                    $( "#config" ).addClass( "active" );
                    break;
                case 25:
                    $( "#system" ).addClass( "active" );
                    $( "#system .dropdown-menu" ).css('display', 'block');
                    $( "#portal-settings" ).addClass( "active" );
                    break;

                //account
                case 26:
                    $( "#account" ).addClass( "active" );
                    $( "#account .dropdown-menu" ).css('display', 'block');
                    $( "#my-account" ).addClass( "active" );
                    break;
                case 27:
                    $( "#account" ).addClass( "active" );
                    $( "#account .dropdown-menu" ).css('display', 'block');
                    $( "#chng-pass" ).addClass( "active" );
                    break;
                case 28:
                    $( "#account" ).addClass( "active" );
                    $( "#account .dropdown-menu" ).css('display', 'block');
                    $( "#lang" ).addClass( "active" );
                    break;
                case 29:
                    $( "#account" ).addClass( "active" );
                    $( "#account .dropdown-menu" ).css('display', 'block');
                    $( "#Logout" ).addClass( "active" );
                    break;

                default:
            }
        }
    };


    
    function mobile(){
        $('.slider_frm').animate({
            bottom : 0
        }, 1500, function(){
            $('.navbar-inner .btn-navbar').trigger("click");
        });

        // close button
        $('.close_btn').click(function(){
            $('.slider_frm').animate({
                bottom : -300
            }, 1000, function(){
                $('.slider_frm').hide();

                if(!$( ".navbar-inner .btn-navbar" ).hasClass( "collapsed" )){
                    $('.navbar-inner .btn-navbar').trigger("click");
                }
            });
        });

        $("#owl-demo").owlCarousel({
            navigation : true, // Show next and prev buttons
            slideSpeed : 300,
            paginationSpeed : 400,
            singleItem: true,
            pagination: false,
            responsive: true,

            afterMove : function(){
                check_active(this.owl.currentItem);
            }
        });

        function check_active(current){
            //alert(current);

            $( ".nav li" ).removeClass( "active" );
            $( "#people .dropdown-menu" ).css('display', 'none');
            $( "#people" ).removeClass( "active" );

            switch(current) {
                case 0:
                    $( "#home" ).addClass( "active" );
                    scrollTop('#home');
                    break;
                case 1:
                    $( "#dash" ).addClass( "active" );
                    scrollTop('#dash');
                    break;
                case 2:
                    $( "#people" ).addClass( "active" );
                    $( "#people .dropdown-menu" ).css('display', 'block');
                    $( "#basic_srch" ).addClass( "active" );

                    scrollTop('#basic_srch');
                    break;
                case 3:
                    $( "#people" ).addClass( "active" );
                    $( "#people .dropdown-menu" ).css('display', 'block');
                    $( "#advanced_srch" ).addClass( "active" );

                    scrollTop('#advanced_srch');
                    break;
                case 4:
                    $( "#people" ).addClass( "active" );
                    $( "#people .dropdown-menu" ).css('display', 'block');
                    $( "#saved_srch" ).addClass( "active" );

                    scrollTop('#saved_srch');
                    break;
                case 5:
                    $( "#people" ).addClass( "active" );
                    $( "#people .dropdown-menu" ).css('display', 'block');
                    $( "#keywords" ).addClass( "active" );

                    scrollTop('#keywords');
                    break;
                case 6:
                    $( "#people" ).addClass( "active" );
                    $( "#people .dropdown-menu" ).css('display', 'block');
                    $( "#bulk_insert" ).addClass( "active" );

                    scrollTop('#bulk_insert');
                    break;
                case 7:
                    $( "#people" ).addClass( "active" );
                    $( "#people .dropdown-menu" ).css('display', 'block');
                    $( "#stats" ).addClass( "active" );

                    scrollTop('#stats');
                    break;
                case 8:
                    $( "#people" ).addClass( "active" );
                    $( "#people .dropdown-menu" ).css('display', 'block');
                    $( "#teams" ).addClass( "active" );

                    scrollTop('#teams');
                    break;
                case 9:
                    $( "#people" ).addClass( "active" );
                    $( "#people .dropdown-menu" ).css('display', 'block');
                    $( "#social_activity" ).addClass( "active" );

                    scrollTop('#social_activity');
                    break;
                case 10:
                    $( "#people" ).addClass( "active" );
                    $( "#people .dropdown-menu" ).css('display', 'block');
                    $( "#volunteers" ).addClass( "active" );

                    scrollTop('#volunteers');
                    break;
                case 11:
                    $( "#people" ).addClass( "active" );
                    $( "#people .dropdown-menu" ).css('display', 'block');
                    $( "#events" ).addClass( "active" );

                    scrollTop('#events');
                    break;
                case 12:
                    $( "#people" ).addClass( "active" );
                    $( "#people .dropdown-menu" ).css('display', 'block');
                    $( "#resource" ).addClass( "active" );

                    scrollTop('#resource');
                    break;
                case 13:
                    $( "#people" ).addClass( "active" );
                    $( "#people .dropdown-menu" ).css('display', 'block');
                    $( "#activity" ).addClass( "active" );

                    scrollTop('#activity');
                    break;
                case 14:
                    $( "#people" ).addClass( "active" );
                    $( "#people .dropdown-menu" ).css('display', 'block');
                    $( "#donation" ).addClass( "active" );

                    scrollTop('#donation');
                    break;
                case 15:
                    $( "#people" ).addClass( "active" );
                    $( "#people .dropdown-menu" ).css('display', 'block');
                    $( "#friend_find" ).addClass( "active" );

                    scrollTop('#friend_find');
                    break;

                //communication
                case 16:
                    $( "#communication" ).addClass( "active" );
                    $( "#communication .dropdown-menu" ).css('display', 'block');
                    $( "#msg-template" ).addClass( "active");

                    scrollTop('#msg-template');
                    break;
                case 17:
                    $( "#communication" ).addClass( "active" );
                    $( "#communication .dropdown-menu" ).css('display', 'block');
                    $( "#campaigns" ).addClass( "active" );

                    scrollTop('#campaigns');
                    break;
                case 18:
                    $( "#communication" ).addClass( "active" );
                    $( "#communication .dropdown-menu" ).css('display', 'block');
                    $( "#msg-box" ).addClass( "active" );

                    scrollTop('#msg-box');
                    break;

                //system
                case 19:
                    $( "#system" ).addClass( "active" );
                    $( "#system .dropdown-menu" ).css('display', 'block');
                    $( "#mng-roles" ).addClass( "active" );

                    scrollTop('#mng-roles');
                    break;
                case 20:
                    $( "#system" ).addClass( "active" );
                    $( "#system .dropdown-menu" ).css('display', 'block');
                    $( "#mng-sys-users" ).addClass( "active" );

                    scrollTop('#mng-sys-users');
                    break;
                case 21:
                    $( "#system" ).addClass( "active" );
                    $( "#system .dropdown-menu" ).css('display', 'block');
                    $( "#org-info" ).addClass( "active" );

                    scrollTop('#org-info');
                    break;
                case 22:
                    $( "#system" ).addClass( "active" );
                    $( "#system .dropdown-menu" ).css('display', 'block');
                    $( "#ad-banner" ).addClass( "active" );

                    scrollTop('#ad-banner');
                    break;
                case 23:
                    $( "#system" ).addClass( "active" );
                    $( "#system .dropdown-menu" ).css('display', 'block');
                    $( "#feed-keywords" ).addClass( "active" );

                    scrollTop('#feed-keywords');
                    break;
                case 24:
                    $( "#system" ).addClass( "active" );
                    $( "#system .dropdown-menu" ).css('display', 'block');
                    $( "#config" ).addClass( "active" );

                    scrollTop('#config');
                    break;
                case 25:
                    $( "#system" ).addClass( "active" );
                    $( "#system .dropdown-menu" ).css('display', 'block');
                    $( "#portal-settings" ).addClass( "active" );

                    scrollTop('#portal-settings');
                    break;

                //account
                case 26:
                    $( "#account" ).addClass( "active" );
                    $( "#account .dropdown-menu" ).css('display', 'block');
                    $( "#my-account" ).addClass( "active" );

                    scrollTop('#my-account');
                    break;
                case 27:
                    $( "#account" ).addClass( "active" );
                    $( "#account .dropdown-menu" ).css('display', 'block');
                    $( "#chng-pass" ).addClass( "active" );

                    scrollTop('#chng-pass');
                    break;
                case 28:
                    $( "#account" ).addClass( "active" );
                    $( "#account .dropdown-menu" ).css('display', 'block');
                    $( "#lang" ).addClass( "active" );

                    scrollTop('#lang');
                    break;
                case 29:
                    $( "#account" ).addClass( "active" );
                    $( "#account .dropdown-menu" ).css('display', 'block');
                    $( "#Logout" ).addClass( "active" );

                    scrollTop('#Logout');
                    break;

                default:
            }
        }
    };


    //-------scroll to function
    function scrollTop(ids){
        $('html, body').animate({
            scrollTop: $(ids).offset().top
        }, 500);
    }

});*/
</script>