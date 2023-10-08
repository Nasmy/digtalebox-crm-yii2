<?php


namespace app\commands;


use app\models\App;
use app\models\User;
use app\models\OsmLog;
use app\models\UserMapSearch;
use Symfony\Component\Debug\Debug;
use yii\console\Controller;
use Yii;
use yii\helpers\Json;

class GeoLocationController extends Controller
{
    public function actionIndex()
    {
        set_time_limit(0); // no time limitation
        ini_set('memory_limit', '-1'); // no memory limitation
        Yii::$app->appLog->isConsole = true;
        Yii::$app->appLog->username = __class__;
        Yii::$app->appLog->logType = 2;

        Yii::$app->appLog->writeLog('Process started');
        Yii::$app->toolKit->domain = Yii::$app->params['masterDomain'];
        $appModels = App::getAppModels();
        $countApp = count($appModels);
        Yii::$app->appLog->writeLog('App Count' . $countApp);

        if (null != $appModels) {
            foreach ($appModels as $appModel) {
                Yii::$app->appLog->writeLog("Connecting to database.Dbname:{$appModel['dbName']}");
                Yii::$app->toolKit->domain = $appModel['domain'];
                if (!Yii::$app->toolKit->changeDbConnection($appModel['dbName'], $appModel['host'], $appModel['username'], $appModel['password'])) {
                    Yii::$app->appLog->writeLog("Database connection change failed.");
                    continue;
                }

                $dbBatchSize = 1000;
                $startIndex = 0;
                $exit = false;

                do {
                    $modelUsers = (new UserMapSearch())->searchLocationData([], UserMapSearch::LOCATION_UPDATE);
                    $i = 0;
                    $userCount = count($modelUsers);
                    if (null != $modelUsers) {
                        Yii::$app->appLog->writeLog('Geocode query count: ' . count($modelUsers));
                        foreach ($modelUsers as $modelUser) {
                            if ($i < $userCount AND $i < $dbBatchSize) {
                                $latitude = $modelUser['latitude'];
                                $longitude = $modelUser['longitude'];
                                echo $latitude;
                                $userId = $modelUser['id'];
                                Yii::$app->db->createCommand('UPDATE User SET geoPoint = POINT(:lat, :lon) WHERE id = :id')
                                    ->bindValue(':lat', $latitude)
                                    ->bindValue(':lon', $longitude)
                                    ->bindValue(':id', $userId)
                                    ->execute();
                                $i++;
                            }

                        }
                        $exit = true;
                    } else {
                        Yii::$app->appLog->writeLog("No users found to geocode.");
                        $exit = true;
                    }
                } while ($i > $dbBatchSize);
            }
        }
    }

}