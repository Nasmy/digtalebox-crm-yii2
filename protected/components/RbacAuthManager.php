<?php
/**
 * Created by PhpStorm.
 * User: nasmy
 * Date: 8/13/2019
 * Time: 5:03 PM
 */
namespace app\components;

use Yii;
class RbacAuthManager extends \yii\rbac\DbManager
{
    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
        $this->db = Yii::$app->toolKit->changeDbConnectionWeb();
    }
}