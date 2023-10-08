<style>
    .mce-fullscreen {
        z-index: 1050;
    }


    .event-description.mt-4 img {
        max-width: 100% !important;
        height: 100%  !important;;
    }
</style>
<?php
use yii\helpers\Html;
Yii::$app->toolKit->registerTinyMceScripts(); ?>
<?php
$mapStyle = Yii::$app->toolKit->getMapStyle(Yii::$app->session['themeStyle']);
Yii::$app->toolKit->registerDataOsmMapScript();
$fName = Yii::$app->user->identity->firstName;
?>

<div class="container"> 
<div  id="eventView">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 1000px;" role="document">
        <div class="modal-content col-md-12 mb-3" style="box-shadow: unset;border: unset;">

            <div class="modal-body" id="eventContent" align="center" style="background-color:#f3f3f4;font-family:'Helvetica',Arial,sans-serif" >
                <!-- content here -->
                <div class="event-details" align="center" style="border-collapse:collapse;min-height:100%;background-color:#ffffff;width: 70%;
                 padding-bottom: 25px;">
               <?php if (null != $model->imageName): ?> 
                        <div align="center"><?php
                           echo Html::img('@web/resources/' . $appId. '/'. $model->imageName ,
                               ['align' => "center",'class'=>"img-fluid",'style'=>"padding:25px 10px 10px;width: 100%;"]
                        ); ?>
                           </div>
                    <?php endif; ?>
                    <div style="font-size: 14px; padding: 10px; text-align: center; color: #232323;">
                    <?php echo $model->location ?>
                    </div>
                    <div align="center">
                    <a style="margin-top: 20px;background-color: darkgray; color: #fff; padding: 5px 20px; text-decoration: none; margin-right: 5px;font: small/1.5 Arial,Helvetica,sans-serif;"  href="http://maps.google.com/?q=<?php echo $model->location; ?>"><?php echo Yii::t('messages', 'Event Location'); ?> </a>
                    </div>
                    <div class="row mt-4 mb-3">
                            <div class="col-md-12" align="center" style="font: small/1.5 Arial,Helvetica,sans-serif;padding-bottom: 5px;"><b><?php echo Yii::t('messages', 'Please Confirm Your Participation'); ?> </b></div>
                                 <div class="col-md-3"></div>
                                    <div class="col-md-2" align="center">
                                        <a style="background-color: #57BDB9; color: #fff; 
                                                           text-decoration: none; -webkit-border-radius: 2px; -moz-border-radius: 2px;
                                                           border-radius: 2px;  height: 20px; display: block;width: 104px;font-weight: bold;padding: 5px 20px;float:left;height: 30px;font: small/1.5 Arial,Helvetica,sans-serif;"
                                        href="#"> <?php echo Yii::t('messages', 'Yes'); ?></a>
                                     </div>
                                     <div class="col-md-2" align="center">
                                        <a style="background-color: #ED7E7D; color: #fff;  text-decoration: none; -webkit-border-radius: 2px; -moz-border-radius: 2px;border-radius: 2px;  height: 20px; display: block;width: 104px;font-weight: bold;padding: 5px 20px;float:left;height: 30px;font: small/1.5 Arial,Helvetica,sans-serif;"
                                         href="#"> <?php echo Yii::t('messages', 'No'); ?></a>
                                      </div>
                                        <div class="col-md-2" align="center"><a style="background-color: #FDD695; color: #fff; 
                                                           white-space: nowrap; text-decoration: none; -webkit-border-radius: 2px;
                                                           -moz-border-radius: 2px; border-radius: 2px;  height: 20px; display: block;width: 104px;font-weight: bold;padding: 5px 20px;float:left;height: 30px;font: small/1.5 Arial,Helvetica,sans-serif;"
                                                           href="#">
                                         <?php echo Yii::t('messages', 'Maybe'); ?></a>

                                        </div>
                                        <div class="col-md-4"></div>
                             
                                   </div>
                            <div class="col-md-12" align="left" class="event-description" width="650px"
                             style="background-color: #fff;">
                                    <?php echo str_replace("max-width: 510px; margin: none;", "margin: none;", $model->description); ?>
                            </div>
                </div>
              </div>
             <div class="col-md-12">
                       <p style="font: small/1.5 Arial,Helvetica,sans-serif;"><?php echo  \Yii::t('messages', 'You are receiving this email because you are subscribed newsletter {name}. Under the Data Protection Act of 6 January 1978, you have a right to access, rectify and delete data concerning you. To unsubscribe,',['name'=>$fName]);?><a href="#"><?php echo Yii::t('messages', 'Click here');?></a></p>
               </div>
        </div>
    </div>
  </div>
 </div>   

