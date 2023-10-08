<?php
Yii::app()->toolKit->registerOwlCarouselScripts();
$mainMenuItems = Yii::app()->toolKit->getSiteGuideFeatures('main');
$mainMenuItemsJson = json_encode($mainMenuItems);

$peopleSubItems = Yii::app()->toolKit->getSiteGuideFeatures('people');
$peopleSubItemsJson = json_encode($peopleSubItems);

$communicationSubItems = Yii::app()->toolKit->getSiteGuideFeatures('communication');
$communicationSubItemsJson = json_encode($communicationSubItems);

$systemSubItems = Yii::app()->toolKit->getSiteGuideFeatures('system');
$systemSubItemsJson = json_encode($systemSubItems);

$acountSubItems = Yii::app()->toolKit->getSiteGuideFeatures('account');
$acountSubItemsJson = json_encode($acountSubItems);


?>

<?php
$urlHome = Yii::app()->createUrl('site/index');
Yii::app()->clientScript->registerScript('site-guide', "
	var mainMenuItems = {$mainMenuItemsJson};
	var peopleSubItems = {$peopleSubItemsJson};
	var communicationSubItems = {$communicationSubItemsJson};
	var systemSubItems = {$systemSubItemsJson};
	var acountSubItems = {$acountSubItemsJson};
	var curMmItemNum = 0;
	
	var viewType = '';
	
	if( $(window).width() >= 980 ){
		// Desktop
        /*$('.slider_frm').animate({
			bottom : 0
		}, 1500);*/

		$('.close_btn').click(function(){
			location.href = '{$urlHome}';
		});
		
		viewType = 'desktop';
    }else{
        /*$('.slider_frm').animate({
            bottom : 0
        }, 1500, function(){
            $('.navbar-inner .btn-navbar').trigger('click');
        });*/

        // close button
        $('.close_btn').click(function(){
            location.href = '{$urlHome}';
        });
		
		viewType = 'mobile';
    }

	function cssChange() {
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
	}
	
	//========== Main Menu Items ============
	$('#mainMenuItems').animate({
		bottom : 0
	}, 1500);

	$('#owl-mainMenuItems').owlCarousel({
		navigation : true, // Show next and prev buttons
		slideSpeed : 300,
		paginationSpeed : 400,
		singleItem: true,
		pagination: false,
		responsive: true,
		afterMove : function(){
			highlightMainMenuItem(this.owl.currentItem);
		}
	});
	
	function getMainMenuItemInfo(id) {
		var info = '';
		$.each(mainMenuItems, function(idx, rec){
			if (id == idx) {
				info = rec;
			}
		});
		
		return info;
	}
	
	function highlightMainMenuItem(currentItemNumber) {
		curMmItemNum = currentItemNumber;
		cssChange();
		var itemInfo = getMainMenuItemInfo(currentItemNumber);
		$('#' + itemInfo.id).addClass('active');
	}
	// End
	
	//========== People sub menu items ============
	var isPeopleSubItemsOver = false;
	$('#owl-peopleSubItems').owlCarousel({
		navigation : true, // Show next and prev buttons
		slideSpeed : 300,
		paginationSpeed : 400,
		singleItem: true,
		pagination: false,
		responsive: true,
		afterMove : function(){
			highlightPeopleSubItem(this.owl.currentItem);
			if (isPeopleSubItemsOver) {
				$('#peopleSubItems').hide('slow');
				$('#mainMenuItems').show('slow');
				$('#mainMenuItems').animate({
					bottom : 0
				}, 1500);
				isPeopleSubItemsOver = false;
				highlightMainMenuItem(curMmItemNum);
			}
			if(this.currentItem === this.maximumItem){
				isPeopleSubItemsOver = true;
			}
		},
	});
	
	function getPeopleSubItemInfo(id) {
		var info = '';
		$.each(peopleSubItems, function(idx, rec){
			if (id == idx) {
				info = rec;
			}
		});
		
		return info;
	}
	
	function highlightPeopleSubItem(currentItemNumber) {
		cssChange();
		var itemInfo = getPeopleSubItemInfo(currentItemNumber);
		$('#people').addClass('active');
		$('#people .dropdown-menu').css('display', 'block');
		$('#' + itemInfo.id).addClass('active');
	}
	
	$('#peopleMore').click(function(){
		highlightPeopleSubItem(0);
		$('#mainMenuItems').hide('slow');
		$('#peopleSubItems').show('slow');
		$('#peopleSubItems').animate({
			bottom : 0
		}, 1500);
	});
	
	//========== Communication sub menu items ============
	var isCommunicationSubItemsOver = false;
	$('#owl-communicationSubItems').owlCarousel({
		navigation : true, // Show next and prev buttons
		slideSpeed : 300,
		paginationSpeed : 400,
		singleItem: true,
		pagination: false,
		responsive: true,
		afterMove : function(){
			highlightCommunicationSubItem(this.owl.currentItem);
			if (isCommunicationSubItemsOver) {
				$('#communicationSubItems').hide('slow');
				$('#mainMenuItems').show('slow');
				$('#mainMenuItems').animate({
					bottom : 0
				}, 1500);
				isCommunicationSubItemsOver = false;
				highlightMainMenuItem(curMmItemNum);
			}
			if(this.currentItem === this.maximumItem){
				isCommunicationSubItemsOver = true;
			}
		},
	});
	
	function getCommunicationSubItemInfo(id) {
		var info = '';
		$.each(communicationSubItems, function(idx, rec){
			if (id == idx) {
				info = rec;
			}
		});
		
		return info;
	}
	
	function highlightCommunicationSubItem(currentItemNumber) {
		cssChange();
		var itemInfo = getCommunicationSubItemInfo(currentItemNumber);
		$('#communication').addClass('active');
		$('#communication .dropdown-menu').css('display', 'block');
		$('#' + itemInfo.id).addClass('active');
	}
	
	$('#communicationMore').click(function(){
		highlightCommunicationSubItem(0);
		$('#mainMenuItems').hide('slow');
		$('#communicationSubItems').show('slow');
		$('#communicationSubItems').animate({
			bottom : 0
		}, 1500);
	});
	
	//========== System sub menu items ============
	var isSystemSubItemsOver = false;
	$('#owl-systemSubItems').owlCarousel({
		navigation : true, // Show next and prev buttons
		slideSpeed : 300,
		paginationSpeed : 400,
		singleItem: true,
		pagination: false,
		responsive: true,
		afterMove : function(){
			highlightSystemSubItem(this.owl.currentItem);
			if (isSystemSubItemsOver) {
				$('#systemSubItems').hide('slow');
				$('#mainMenuItems').show('slow');
				$('#mainMenuItems').animate({
					bottom : 0
				}, 1500);
				isSystemSubItemsOver = false;
				highlightMainMenuItem(curMmItemNum);
			}
			if(this.currentItem === this.maximumItem){
				isSystemSubItemsOver = true;
			}
		},
	});
	
	function getSystemSubItemInfo(id) {
		var info = '';
		$.each(systemSubItems, function(idx, rec){
			if (id == idx) {
				info = rec;
			}
		});
		
		return info;
	}
	
	function highlightSystemSubItem(currentItemNumber) {
		cssChange();
		var itemInfo = getSystemSubItemInfo(currentItemNumber);
		$('#system').addClass('active');
		$('#system .dropdown-menu').css('display', 'block');
		$('#' + itemInfo.id).addClass('active');
	}
	
	$('#systemMore').click(function(){
		highlightSystemSubItem(0);
		$('#mainMenuItems').hide('slow');
		$('#systemSubItems').show('slow');
		$('#systemSubItems').animate({
			bottom : 0
		}, 1500);
	});
	
	//========== Profile sub menu items ============
	var isAccountSubItemsOver = false;
	$('#owl-accountSubItems').owlCarousel({
		navigation : true, // Show next and prev buttons
		slideSpeed : 300,
		paginationSpeed : 400,
		singleItem: true,
		pagination: false,
		responsive: true,
		afterMove : function(){
			highlightAccountSubItem(this.owl.currentItem);
			if (isAccountSubItemsOver) {
				$('#accountSubItems').hide('slow');
				$('#mainMenuItems').show('slow');
				$('#mainMenuItems').animate({
					bottom : 0
				}, 1500);
				isAccountSubItemsOver = false;
				highlightMainMenuItem(curMmItemNum);
			}
			if(this.currentItem === this.maximumItem){
				isAccountSubItemsOver = true;
			}
		},
	});
	
	function getAccountSubItemInfo(id) {
		var info = '';
		$.each(acountSubItems, function(idx, rec){
			if (id == idx) {
				info = rec;
			}
		});
		
		return info;
	}
	
	function highlightAccountSubItem(currentItemNumber) {
		cssChange();
		var itemInfo = getAccountSubItemInfo(currentItemNumber);
		$('#account').addClass('active');
		$('#account .dropdown-menu').css('display', 'block');
		$('#' + itemInfo.id).addClass('active');
	}
	
	$('#accountMore').click(function(){
		highlightAccountSubItem(0);
		$('#mainMenuItems').hide('slow');
		$('#accountSubItems').show('slow');
		$('#accountSubItems').animate({
			bottom : 0
		}, 1500);
	});
");
?>

<div id="mainMenuItems" class="slider_frm">
    <div class="close_btn"></div>
	<div id="owl-mainMenuItems" class="owl-carousel owl-theme">
		<?php foreach ($mainMenuItems as $mainMenuItem) {
			if ('' == $mainMenuItem['image']) {
				continue;
			}
		?>
			<div class="item">
				<div class="col-md-6">
					<img class="img-thumbnail" src="<?php echo Yii::app()->toolKit->getImagePath()?>/guide/<?php echo $mainMenuItem['image'] ?>">
				</div>
				<div class="col-md-6">
					<h4><?php echo @$mainMenuItem['title']?></h4>
					<p><?php echo $mainMenuItem['message']?></p>
					<?php if(isset($mainMenuItem['link'])): ?>
						<p><?php echo $mainMenuItem['link']?></p>
					<?php endif; ?>
				</div>
			</div>
		<?php } ?>
	</div>
</div>

<div id="peopleSubItems" class="slider_frm">
    <div class="close_btn"></div>
	<div id="owl-peopleSubItems" class="owl-carousel owl-theme">
		<?php foreach ($peopleSubItems as $peopleSubItem) {
			if ('' == $peopleSubItem['image']) {
				continue;
			}
		?>
			<div class="item">
				<div class="col-md-6">
					<img class="img-thumbnail" src="<?php echo Yii::app()->toolKit->getImagePath()?>/guide/<?php echo $peopleSubItem['image'] ?>">
				</div>
				<div class="col-md-6">
					<h4><?php echo @$peopleSubItem['title']?></h4>
					<p><?php echo $peopleSubItem['message'] ?></p>
				</div>
			</div>
		<?php } ?>
	</div>
</div>

<div id="communicationSubItems" class="slider_frm">
    <div class="close_btn"></div>
	<div id="owl-communicationSubItems" class="owl-carousel owl-theme">
		<?php foreach ($communicationSubItems as $communicationSubItem) {
			if ('' == $communicationSubItem['image']) {
				continue;
			}
		?>
			<div class="item">
				<div class="col-md-6">
					<img class="img-thumbnail" src="<?php echo Yii::app()->toolKit->getImagePath()?>/guide/<?php echo $communicationSubItem['image'] ?>">
				</div>
				<div class="col-md-6">
					<h4><?php echo @$communicationSubItem['title']?></h4>
					<p><?php echo $communicationSubItem['message'] ?></p>
				</div>
			</div>
		<?php } ?>
	</div>
</div>

<div id="systemSubItems" class="slider_frm">
    <div class="close_btn"></div>
	<div id="owl-systemSubItems" class="owl-carousel owl-theme">
		<?php foreach ($systemSubItems as $systemSubItem) {
			if ('' == $systemSubItem['image']) {
				continue;
			}
		?>
			<div class="item">
				<div class="col-md-6">
					<img class="img-thumbnail" src="<?php echo Yii::app()->toolKit->getImagePath()?>/guide/<?php echo $systemSubItem['image'] ?>">
				</div>
				<div class="col-md-6">
					<h4><?php echo @$systemSubItem['title']?></h4>
					<p><?php echo $systemSubItem['message'] ?></p>
				</div>
			</div>
		<?php } ?>
	</div>
</div>

<div id="accountSubItems" class="slider_frm">
    <div class="close_btn"></div>
	<div id="owl-accountSubItems" class="owl-carousel owl-theme">
		<?php foreach ($acountSubItems as $accountSubItem) {
			if ('' == $accountSubItem['image']) {
				continue;
			}
		?>
			<div class="item">
				<div class="col-md-6">
					<img class="img-thumbnail" src="<?php echo Yii::app()->toolKit->getImagePath()?>/guide/<?php echo $accountSubItem['image'] ?>">
				</div>
				<div class="col-md-6">
					<h4><?php echo @$accountSubItem['title']?></h4>
					<p><?php echo $accountSubItem['message'] ?></p>
				</div>
			</div>
		<?php } ?>
	</div>
</div>
