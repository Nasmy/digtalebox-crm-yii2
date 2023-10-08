<?php

use app\assets\StripeAsset;

Yii::$app->toolKit->registerVolunteerThemeStyle();
StripeAsset::register($this);

if (!empty($stripeSecretKey)):

    ?>
    <style type="text/css">
        .w-65 {
            width: 65% !important;
        }

        .success, .error {
            display: none !important;
            font-size: 15px;
        }

        .success.visible, .error.visible {
            display: inline !important;
        }

        .error {
            color: #E4584C;
        }

        .error.visible {
            text-align: center;
            width: 100%;
            display: inherit !important;
            margin: 0;
        }

        .success {
            color: #34D08C;
        }

        .success .token {
            font-weight: 500;
            font-size: 15px;
        }

        .example.example2 .error svg {
            width: 12px;
            top: 0;
            height: 10px;
        }

    </style>

    <div class="register-promo">

        <div class="container  ">


            <div class="row">
                <div class="col-lg-6 mx-auto">
                    <div class="mainframe">
                        <!--Example 2-->
                        <div class="cell example example2" id="example-2">
                            <div class="row ">
                                <div class="col text-center my-3">
                                    <img src="<?php echo Yii::$app->toolKit->getImagePath() ?>digitalebox-logo.png">
                                </div>

                            </div>
                            <div class="row">
                                <div class="col text-center ">
                                    <div class="alert alert-info m-0">
                                        <?php echo $msg ?>
                                    </div>
                                </div>
                            </div>
                            <form action="<?php echo $stripeUrl ?>" method="POST" id="payment-form">
                                <input type="hidden" name="data" value='<?php echo json_encode($data); ?>'>

                                <div data-locale-reversible>
                                    <div class="row m-0">
                                        <div class="field m-0">
                                            <label for="example2-email m-0">
                                                <h3 class="form-control-file text-capitalize text-center text-info"> <?php echo $data['email']; ?></h3>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="field">
                                            <div id="example2-card-number" class="input empty"></div>
                                            <label for="example2-card-number"
                                                   data-tid="elements_examples.form.card_number_label">Card
                                                number</label>
                                            <div class="baseline"></div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="field w-65">
                                            <div id="example2-card-expiry" class="input empty"></div>
                                            <label for="example2-card-expiry"
                                                   data-tid="elements_examples.form.card_expiry_label">Expiration</label>
                                            <div class="baseline"></div>
                                        </div>
                                        <div class="field w-25">
                                            <div id="example2-card-cvc" class="input empty"></div>
                                            <label for="example2-card-cvc"
                                                   data-tid="elements_examples.form.card_cvc_label">CVC</label>
                                            <div class="baseline"></div>
                                        </div>
                                    </div>
                                    <button type="submit" data-tid="elements_examples.form.pay_button" class="mb-3">
                                        Pay
                                    </button>
                                    <div class="error <?php echo Yii::$app->session->hasFlash('error') == true ? 'visible' : null; ?>"
                                         role="alert">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17"
                                             viewBox="0 0 17 17">
                                            <path class="base" fill="#000"
                                                  d="M8.5,17 C3.80557963,17 0,13.1944204 0,8.5 C0,3.80557963 3.80557963,0 8.5,0 C13.1944204,0 17,3.80557963 17,8.5 C17,13.1944204 13.1944204,17 8.5,17 Z"></path>
                                            <path class="glyph" fill="#FFF"
                                                  d="M8.5,7.29791847 L6.12604076,4.92395924 C5.79409512,4.59201359 5.25590488,4.59201359 4.92395924,4.92395924 C4.59201359,5.25590488 4.59201359,5.79409512 4.92395924,6.12604076 L7.29791847,8.5 L4.92395924,10.8739592 C4.59201359,11.2059049 4.59201359,11.7440951 4.92395924,12.0760408 C5.25590488,12.4079864 5.79409512,12.4079864 6.12604076,12.0760408 L8.5,9.70208153 L10.8739592,12.0760408 C11.2059049,12.4079864 11.7440951,12.4079864 12.0760408,12.0760408 C12.4079864,11.7440951 12.4079864,11.2059049 12.0760408,10.8739592 L9.70208153,8.5 L12.0760408,6.12604076 C12.4079864,5.79409512 12.4079864,5.25590488 12.0760408,4.92395924 C11.7440951,4.59201359 11.2059049,4.59201359 10.8739592,4.92395924 L8.5,7.29791847 L8.5,7.29791847 Z"></path>
                                        </svg>
                                        <span class="message "><?= Yii::$app->session->getFlash('error') ?></span></div>
                            </form>

                            <div class="success">
                                <div class="icon">
                                    <svg width="84px" height="84px" viewBox="0 0 84 84" version="1.1"
                                         xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                        <circle class="border" cx="42" cy="42" r="40" stroke-linecap="round"
                                                stroke-width="4" stroke="#000" fill="none"></circle>
                                        <path class="checkmark" stroke-linecap="round" stroke-linejoin="round"
                                              d="M23.375 42.5488281 36.8840688 56.0578969 64.891932 28.0500338"
                                              stroke-width="4" stroke="#000" fill="none"></path>
                                    </svg>
                                </div>
                                <h3 class="title" data-tid="elements_examples.success.title">Payment successful</h3>
                                <p class="message"><span data-tid="elements_examples.success.message">Thanks for trying Stripe Elements. No money was charged, but we generated a token: </span><span
                                            class="token">tok_189gMN2eZvKYlo2CwTBv9KKh</span></p>
                                <a class="reset" href="#">
                                    <svg width="32px" height="32px" viewBox="0 0 32 32" version="1.1"
                                         xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                        <path fill="#000000"
                                              d="M15,7.05492878 C10.5000495,7.55237307 7,11.3674463 7,16 C7,20.9705627 11.0294373,25 16,25 C20.9705627,25 25,20.9705627 25,16 C25,15.3627484 24.4834055,14.8461538 23.8461538,14.8461538 C23.2089022,14.8461538 22.6923077,15.3627484 22.6923077,16 C22.6923077,19.6960595 19.6960595,22.6923077 16,22.6923077 C12.3039405,22.6923077 9.30769231,19.6960595 9.30769231,16 C9.30769231,12.3039405 12.3039405,9.30769231 16,9.30769231 L16,12.0841673 C16,12.1800431 16.0275652,12.2738974 16.0794108,12.354546 C16.2287368,12.5868311 16.5380938,12.6540826 16.7703788,12.5047565 L22.3457501,8.92058924 L22.3457501,8.92058924 C22.4060014,8.88185624 22.4572275,8.83063012 22.4959605,8.7703788 C22.6452866,8.53809377 22.5780351,8.22873685 22.3457501,8.07941076 L22.3457501,8.07941076 L16.7703788,4.49524351 C16.6897301,4.44339794 16.5958758,4.41583275 16.5,4.41583275 C16.2238576,4.41583275 16,4.63969037 16,4.91583275 L16,7 L15,7 L15,7.05492878 Z M16,32 C7.163444,32 0,24.836556 0,16 C0,7.163444 7.163444,0 16,0 C24.836556,0 32,7.163444 32,16 C32,24.836556 24.836556,32 16,32 Z"></path>
                                    </svg>
                                </a>
                            </div>
                            <img src='<?php echo Yii::$app->toolKit->getImagePath() . Yii::$app->params['stripe']['cardBrands']; ?>'
                                 style="display: block;height: 40px;margin: 5px auto;padding: 5px 0px;"/>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        // Create a Stripe client.
        var pk = '<?php  echo $stripeSecretKey; ?>';
        var stripe = Stripe(pk);
    </script>

<?php else: ?>
    <div class="container">
        <div class="alert alert-danger text-error alert-dismissable">
            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
            <?= Yii::t('messages', 'please re check your strip API key from configuration') ?>
        </div>
    </div>
<?php endif; ?>

