<?php

use app\models\App;
use yii\db\Migration;
use app\components\ToolKit;

/**
 * Class m210301_020926_BroadcastMessage
 */
class m210301_020926_BroadcastMessage extends Migration
{

    // up
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

                    $this->alterColumn('BroadcastMessage', 'fbImageName', $this->string(120));
                    $this->alterColumn('BroadcastMessage', 'twImageName', $this->string(120));
                    $this->alterColumn('BroadcastMessage', 'lnImageName', $this->string(120));
                    $this->alterColumn('BroadcastMessage', 'lnPageImageName', $this->string(120));
                    $this->alterColumn('BroadcastMessage', 'fbProfImageName', $this->string(120));


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
        Yii::$app->appLog->isConsole = true;
        Yii::$app->appLog->username = get_class($this);
        Yii::$app->appLog->logType = 2;

        Yii::$app->toolKit->domain = Yii::$app->params['masterDomain'];
        $appModels = App::find()->where(["!=", "domain", Yii::$app->params['masterDomain']])->all();
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

                    $this->alterColumn('BroadcastMessage', 'fbImageName', $this->string(20));
                    $this->alterColumn('BroadcastMessage', 'twImageName', $this->string(20));
                    $this->alterColumn('BroadcastMessage', 'lnImageName', $this->string(20));
                    $this->alterColumn('BroadcastMessage', 'lnPageImageName', $this->string(20));
                    $this->alterColumn('BroadcastMessage', 'fbProfImageName', $this->string(20));

                } catch (Exception $e) {
                    echo "Dbname:{$appModel->dbName} Exception: " . $e->getMessage() . "\n";
                    Yii::$app->appLog->writeLog("Exception: Dbname:{$appModel->dbName} " . " - " . $e->getMessage());
                    return false;
                }
            }
        }

    }


    /**
     * {@inheritdoc}
     *
     * public function safeDown()
     * {
     * echo "m210301_020926_BroadcastMessage cannot be reverted.\n";
     *
     * return false;
     * }
     */

}
