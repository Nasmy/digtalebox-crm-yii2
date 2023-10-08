<?php header("Access-Control-Allow-Origin: *");

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    require('../botdetect-captcha-lib/simple-botdetect.php');

    $postedData = $_GET;

    $userEnteredCaptchaCode = $postedData['userEnteredCaptchaCode'];
    $captchaId = $postedData['captchaId'];

    // create a captcha instance to be used for the captcha validation
    $captcha = new SimpleCaptcha();

    // execute the captcha validation
    $isHuman = $captcha->Validate($userEnteredCaptchaCode, $captchaId);

    if ($isHuman == false) {
        // captcha validation failed
        $result = array('success' => false);
        // TODO: consider logging the attempt
    } else {
        // captcha validation succeeded
        $result = array('success' => true);
    }

    // return the json string with the validation result to the frontend
    echo $_GET['callback']."([".json_encode($result)."])";
    exit;
}