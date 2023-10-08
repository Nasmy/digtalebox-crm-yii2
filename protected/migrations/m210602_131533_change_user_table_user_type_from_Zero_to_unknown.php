<?php

use app\models\App;
use app\models\User;
use yii\db\Migration;

/**
 * Class m210602_131533_change_user_table_user_type_from_Zero_to_unknown
 */
class m210602_131533_change_user_table_user_type_from_Zero_to_unknown extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        Yii::$app->appLog->isConsole = true;
        Yii::$app->appLog->username = get_class($this);
        Yii::$app->appLog->logType = 2;

        Yii::$app->toolKit->domain = Yii::$app->params['masterDomain'];
        $appModels = App::find()->where(["!=", "domain", Yii::$app->params['masterDomain']])->all();
        $masterModels = App::find()->where(["=", "domain", Yii::$app->params['masterDomain']])->one();

        if (null != $appModels) {
            // Loop through each application(client)
            foreach ($appModels as $appModel) {
                Yii::$app->appLog->appId = $appModel->appId;
                Yii::$app->appLog->writeLog("Connecting to database.Dbname:{$appModel->dbName}");
                Yii::$app->toolKit->domain = $appModel->domain;
                if (!Yii::$app->toolKit->changeDbConnection($appModel->dbName, $appModel->host, $appModel->username, $appModel->password)) {
                    Yii::$app->appLog->writeLog("Database connection change failed.");
                    continue;
                }

                try {
                    $this->update('User',["userType" => User::UNKNOWN],['userType'=>0]);

                } catch (Exception $e) {
                    echo "Dbname:{$appModel->dbName} Exception: " . $e->getMessage() . "\n";
                    Yii::$app->appLog->writeLog("Exception: Dbname:{$appModel->dbName} " . " - " . $e->getMessage());
                    return false;
                }

            }
        }

        //changing to MasterDB for save migration
        if (!Yii::$app->toolKit->changeDbConnection($masterModels->dbName, $masterModels->host, $masterModels->username, $masterModels->password)) {
            Yii::$app->appLog->writeLog("Database connection change failed.");
            exit();
        }
    }

    public function down()
    {

    }

}
