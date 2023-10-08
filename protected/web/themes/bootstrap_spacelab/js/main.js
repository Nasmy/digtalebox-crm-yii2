/*! DigitaleBox - amrg */

$( document ).ready(function() {

    // Guide video close
    $("#guide-video-close").click(function() {
        $('#guide-video').trigger('pause');
    });

    // Password Toggle
    $(".show_hide_password a").on('click', function(event) {
        event.preventDefault();
        if($('.show_hide_password input').attr("type") == "text"){
            $('.show_hide_password input').attr('type', 'password');
            $('.show_hide_password i').addClass( "fa-eye-slash" );
            $('.show_hide_password i').removeClass( "fa-eye" );
        }else if($('.show_hide_password input').attr("type") == "password"){
            $('.show_hide_password input').attr('type', 'text');
            $('.show_hide_password i').removeClass( "fa-eye-slash" );
            $('.show_hide_password i').addClass( "fa-eye" );
        }
    });
    /////////////////////////////////////////////////////////////////////////////////

    //Show account section
    $('.account-section').show();

    // side menu start
    $("#menu1").metisMenu();

    // side menu scroller start
    $('#slimscrolldiv').slimScroll({
        width: '220px',
        height: '100%'
    });
    /////////////////////////////////////////////////////////////////////////////////


    //side bar initial sizes
    set_sidebar_heights();

    // change heights when window resized
    $( window ).resize(function() {
        //set_sidebar_heights();
        setTimeout(set_sidebar_heights, 100);
    });

    function set_sidebar_heights() {

        //side bar height set
        //$('#side-bar').height( $(window).outerHeight() - $('.app-header .navbar-expand-lg').height() );

        //menu height set
        $('.slimScrollDiv').height( menu_height() );

        function menu_height(){
            //var header_h = $('.app-header .navbar-expand-lg').height();
            //var w_height = $(window).height();
            var w_height = $( '#side-bar' ).height();

            var box_h = $('.account-section').height();
            //var menu_h = w_height - header_h - box_h;
            var menu_h = w_height - box_h;

            return menu_h;
        }
    }
    /////////////////////////////////////////////////////////////////////////////////


    // Config panel

    // Theme change
    $('.config-panel .theme').click(function (event) {
        event.preventDefault();

        var selectedTheme = $(this).data('theme');
        $('body').removeClass().addClass('theme-' + selectedTheme);

        $('.config-panel .theme').removeClass('active');
        $(this).addClass('active');
    });
    // Slide toggle
    $('.config-panel .panel-toggle').click(function () {

        if ( $(this).hasClass("isDown") ) {
            //alert('closing');
            $( ".config-panel" ).animate({
                right: -243
            }, 500, function() {
                // Animation complete.
            });
        } else {
            //alert('openning');
            $( ".config-panel" ).animate({
                right: 0
            }, 500, function() {
                // Animation complete.
            });
        }

        $(this).toggleClass("isDown");
        return false;
    });

    $('#configPanel').mCustomScrollbar({
        theme:"dark",
        scrollInertia: 500,
        scrollbarPosition: "outside"
    });
    /////////////////////////////////////////////////////////////////////////////////

    // Account status toglle
    $(".account-status i").click( function(event){
        event.preventDefault();

        if ( $(this).hasClass("isDown") ) {

            var menu_h = $('#side-bar').height() - 210;

            $( ".slimScrollDiv" ).animate({
                height: menu_h
            }, 500, function() {
                // Animation complete.
            });
            $( ".account-status" ).animate({
                height: "180"
            }, 500, function() {
                // Animation complete.
                $('.account-status .title  > i').addClass('fa-angle-down').removeClass('fa-angle-up');
            });

        } else {

            var menu_h = $('#side-bar').height() - 70;

            $( ".slimScrollDiv" ).animate({
                height: menu_h
            }, 500, function() {
                // Animation complete.
            });
            $( ".account-status" ).animate({
                height: '40'
            }, 500, function() {
                // Animation complete.
                $('.account-status .title  > i').addClass('fa-angle-up').removeClass('fa-angle-down');
            });

        }
        $(this).toggleClass("isDown");
        return false;
    });


    // Menu button toggle
    $( ".menu-btn" ).click(function(event) {
        event.preventDefault();

        //closing connection popup
        $('.connection-popup').hide();
        $('.connection').removeClass("isDown");

        if ( $(this).hasClass("isDown") ) {

            $( ".side-bar" ).animate({
                left: -220
            }, 500, function() {
                // Animation complete.
            });

        } else {

            $( ".side-bar" ).animate({
                left: 0
            }, 500, function() {
                // Animation complete.
            });

        }
        $(this).toggleClass("isDown");
        return false;
    });
    /////////////////////////////////////////////////////////////////////////////////


    // Social Connect popup

    $( ".connection" ).click(function(event) {
        event.preventDefault();

        if ( $(this).hasClass("isDown") ) {

            $('.connection-popup').hide();

        } else {

            $('.connection-popup').show();

        }
        $(this).toggleClass("isDown");
        return false;
    });


    // Check box recolor initiate

    $('input.custom-icheck').iCheck({
        checkboxClass: 'icheckbox_minimal',
        radioClass: 'iradio_minimal',
        increaseArea: '20%' // optional
    });
    /////////////////////////////////////////////////////////////////////////////////


    //Tooltips Initiating
    $('[data-toggle="tooltip"]').tooltip();
    /////////////////////////////////////////////////////////////////////////////////


    // Web Store State
    if (localStorage) {
        /*
         * Open menu on load
         */

        // Main Menu State
        if (localStorage) {
            /*$(".has-arrow").attr('aria-expanded',localStorage.secExpanded);
            if(localStorage.secExpanded == 'true'){
                $(".collapse").attr('aria-expanded',localStorage.secExpanded).addClass('in');
            }else{
                $(".collapse").attr('aria-expanded',localStorage.secExpanded).removeClass('in');
            }*/

            $('*[data-section="' + localStorage.secExpanded + '"]').attr('aria-expanded','true');
            $('*[data-section="' + localStorage.secExpanded + '"]').next().attr('aria-expanded',localStorage.secExpanded).addClass('in');
        }

        // Change Account State panel status
        if(localStorage.accountStatus == 'off'){
            var menu_h = $('#side-bar').height() - 70;

            $( ".slimScrollDiv" ).css('height',menu_h);
            $( ".account-status" ).css('height','40');
            $('.account-status .title  > i').addClass('fa-angle-up').removeClass('fa-angle-down');

            $('.account-status i').addClass("isDown");

        }else if(localStorage.accountStatus == 'on'){
            var menu_h = $('#side-bar').height() - 210;

            $( ".slimScrollDiv" ).css('height',menu_h);
            $( ".account-status" ).css('height','180');
            $('.account-status .title  > i').addClass('fa-angle-down').removeClass('fa-angle-up');

            $('.account-status i').removeClass("isDown");
        }


        /*
         * Save the state
         */
        // Side bar opened section
        $(".side-bar .metismenu > li > a").click(function () {
            localStorage.secExpanded = $(this).data('section');
            /*localStorage.secExpanded = $(this).delay(500).attr('aria-expanded');*/
        });

        // Side bar account section status
        if(!localStorage.accountStatus){
            localStorage.accountStatus = 'on';
        }
        $(".account-status > .title > i").click(function () {
            if(localStorage.accountStatus == 'off'){
                localStorage.accountStatus = 'on';
            }else if(localStorage.accountStatus == 'on')
            {
                localStorage.accountStatus = 'off';
            }
        });
    }
    /////////////////////////////////////////////////////////////////////////////////

    /*
     * change theme from config-panel
     */
    $( ".config-theme" ).click(function(event) {
        var selectedTheme = $(this).data('theme');
        jQuery.ajax({
            'type':'POST',
            'url':$(this).attr('href'),
            'success':function(data) {
                return false;
            },
            'cache':false
        });
    });

});