<?php

use yii\db\Migration;
use app\models\App;

/**
 * Class m211201_071516_alter_table_message_template_category
 */
class m211201_071516_alter_table_message_template_category extends Migration
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
                    $table = Yii::$app->db->schema->getTableSchema('MessageTemplate');
                    if (!isset($table->columns['templateCategory'])) {
                        $this->addColumn('MessageTemplate', 'templateCategory', $this->tinyInteger(1)->defaultValue(0)->after('type')->comment('0-both, 1-emailTemplate, 2-smsTemplate'));
                    }

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
                    $table = Yii::$app->db->schema->getTableSchema('MessageTemplate');
                    if (isset($table->columns['templateCategory'])) {
                        $this->dropColumn('MessageTemplate', 'templateCategory');
                    }

                } catch (Exception $e) {
                    echo "Dbname:{$appModel->dbName} Exception: " . $e->getMessage() . "\n";
                    Yii::$app->appLog->writeLog("Exception: Dbname:{$appModel->dbName} " . " - " . $e->getMessage());
                    return false;
                }

            }
        }
    }

}
