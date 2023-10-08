<?php
use yii\helpers\Html;
?>

<form method="post" action='<?php
$url = 'http://' . Yii::$app->request->getServerName().'/Newsletter/index';
echo $url; ?>'>
    First name:<br>
    <input type='text' name='firstName'>
    <br>
    Last name:<br>
    <input type='text' name='lastName'>
    <br>
    Email:<br>
    <input type='text' name='email'>
    <br><br>
    <input type='hidden' name='callBackUrl' value=''><!-- Redirect URL ex: http://www.google.com, if not set redirects to client page -->
    <input type='submit' name='Submit' value='Submit'>
</form>