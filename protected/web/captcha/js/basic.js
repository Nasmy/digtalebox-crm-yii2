$(function () {
    // create the frontend captcha instance in the DOM.ready event-handler;
    // and set the captchaEndpoint property to point to 
    // the captcha endpoint path on your app's backend

    var site_base_url = $('#siteBaseUrl').val();

    var captcha = $('#botdetect-captcha').captcha({
        captchaEndpoint: site_base_url + '/captcha/botdetect-captcha-lib/simple-botdetect.php'
    });

    // process the form on submit event
    $('#submitForm').click(function () {

        $('<input>').attr({
            type: 'hidden',
            name: 'Submit',
            value: 'Submit',
        }).appendTo('form');
        // get the user-entered captcha code value to be validated at the backend side        
        var userEnteredCaptchaCode = captcha.getUserEnteredCaptchaCode();

        // get the id of a captcha instance that the user tried to solve
        var captchaId = captcha.getCaptchaId();

        var postData = {
            // add the user-entered captcha code value to the post data
            userEnteredCaptchaCode: userEnteredCaptchaCode,
            // add the id of a captcha instance to the post data
            captchaId: captchaId
        };

        // post the captcha data to the backend
        jQuery.support.cors = true;
        $.ajax({
            method: 'GET',
            url: site_base_url + '/captcha/form/basic.php',
            dataType: 'jsonp',
            crossDomain: true,
            contentType: 'application/json charset=utf-8',
            //data: JSON.stringify(postData),
            data:postData,
            success: function (response) {
                if (response[0].success === false) {
                    // captcha validation failed; show the error message
                    $('#form-messages')
                        .removeClass()
                        .addClass('alert alert-error')
                        .css('color', '#ff0000')
                        .text('CAPTCHA validation failed.');
                    // call the captcha.reloadImage()
                    // in order to generate a new captcha challenge
                    captcha.reloadImage();
                } else {
                    // captcha validation succeeded; proceed with the workflow
                    $('#form-messages')
                        .removeClass()
                        .addClass('alert alert-success')
                        .css('color', '#00ff00')
                        .text('CAPTCHA validation success.');
                    $('#basicForm').submit().serialize();
                }
            },
            error: function (jqXHR, textStatus, ex) {
                alert(textStatus + "," + ex + "," + jqXHR.responseText);
                //throw new Error(error);
            }
        });

        event.preventDefault();
    });

});
