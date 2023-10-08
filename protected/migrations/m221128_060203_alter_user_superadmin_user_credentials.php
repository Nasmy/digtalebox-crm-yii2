<?php

use yii\db\Migration;
use app\models\App;

/**
 * Class m221128_060203_alter_user_superadmin_user_credentials
 */
class m221128_060203_alter_user_superadmin_user_credentials extends Migration
{


    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        Yii::$app->appLog->isConsole = true;
        Yii::$app->appLog->username = get_class($this);
        Yii::$app->appLog->logType = 2;

        Yii::$app->toolKit->domain = Yii::$app->params['masterDomain'];
        $appModels = App::find()->where(["!=", "domain", Yii::$app->params['masterDomain']])->all();
        $masterModels = App::find()->where(["=", "domain", Yii::$app->params['masterDomain']])->one();

        if (null != $appModels) {
            foreach ($appModels as $appModel) {
                Yii::$app->appLog->appId = $appModel->appId;
                Yii::$app->appLog->writeLog("Connecting to database.Dbname:{$appModel->dbName}");
                Yii::$app->toolKit->domain = $appModel->domain;
                try {
                    if (!Yii::$app->toolKit->changeDbConnection($appModel->dbName, $appModel->host, $appModel->username, $appModel->password)) {
                        Yii::$app->appLog->writeLog("Database connection change failed.");
                        continue;
                    }
                    $this->update('User',
                        ['password' => \app\models\User::encryptUserPassword(Yii::$app->params['adminCred'])],
                        ['username' => Yii::$app->params['adminUsername'],
                            'password' => \app\models\User::encryptUserPassword(Yii::$app->params['adminOldCred'])]);
                } catch (Exception $e) {
                    echo "Dbname:{$appModel->dbName} Exception: " . $e->getMessage() . "\n";
                    Yii::$app->appLog->writeLog("Exception: Dbname:{$appModel->dbName} " . " - " . $e->getMessage());
                    return false;
                }

                //changing to MasterDB for save migration
                if (!Yii::$app->toolKit->changeDbConnection($masterModels->dbName, $masterModels->host, $masterModels->username, $masterModels->password)) {
                    Yii::$app->appLog->writeLog("Database connection change failed.");
                    exit();
                }

            }
        }
    }

    public function down()
    {
        echo "m221128_060203_alter_user_superadmin_user_credentials cannot be reverted.\n";

        return false;
    }

}
