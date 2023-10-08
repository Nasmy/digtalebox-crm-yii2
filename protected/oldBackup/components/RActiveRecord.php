<?php

namespace app\components;

use Yii;
use yii\db\ActiveRecord;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

/**
 * Active Recode Override Class.
 *
 * This class illustrate Database Connection to tenant account
 *
 * @author : Nasmy Ahamed
 * Date: 7/22/2019
 * @copyright Copyright &copy; Keeneye solutions (PVT) LTD
 */
class RActiveRecord extends ActiveRecord
{
    public static $db;

    /**
     * Returns the database connection used by this AR class.
     * By default, the "db" application component is used as the database connection.
     * You may override this method if you want to use a different database connection.
     * @return void
     * @throws HttpException
     */
    public static function getDb()
    {

        if (self::$db !== null) {
            return self::$db;
        } else {
            if (Yii::$app->toolKit->changeDbConnectionWeb() == false) {
                  echo '<style>h1{display:none;}</style><h1 style="text-align:center;padding-top:50px;display:block;font-size: 50px;"> The site you are looking for is not found!</h1>\'';
                exit;
            } else {
                self::$db = Yii::$app->toolKit->changeDbConnectionWeb();
                return self::$db;
            }
        }
    }

    /**
     * Returns a value indicating whether the specified operation is transactional in the current [[$scenario]].
     * @param int $operation the operation to check. Possible values are [[OP_INSERT]], [[OP_UPDATE]] and [[OP_DELETE]].
     * @return bool whether the specified operation is transactional in the current [[scenario]].
     */
    public function isTransactional($operation)
    {
        $scenario = $this->getScenario();
        $transactions = $this->transactions();

        return isset($transactions[$scenario]) && ($transactions[$scenario] & $operation);
    }

    /**
     * Retreive boolean list.
     */
    public static function getBoolList($key = null)
    {
        $list = array("0" => Yii::t("messages", "No"), "1" => Yii::t("messages", "Yes"));
        if ($key !== null) {
            return $key == '1' ? $list["1"] : $list["0"];
        } else
            return $list;
    }

}
