<?php

namespace app\commands;

use app\models\App;
use app\models\User;
use app\models\OsmLog;
use Symfony\Component\Debug\Debug;
use yii\console\Controller;
use Yii;
use yii\helpers\Json;

/**
 * This command sync longitude and latitude for given street address from google map api
 */
class LongLatScanController extends Controller
{
    private $isLimitReached = false;

    public function actionIndex()
    {
        set_time_limit(0); //no time limitation
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
            // Loop through each application(client)
            foreach ($appModels as $appModel) {
                if (true == $this->isLimitReached) {
                    Yii::$app->appLog->writeLog("Stopped due to limit reaching");
                    break;
                }
                Yii::$app->appLog->appId = $appModel['appId'];
                if ($appModel['packageTypeId'] == App::FREEMIUM) {
                    Yii::$app->appLog->writeLog('Not available for this package');
                    continue;
                }
                Yii::$app->appLog->writeLog("Connecting to database.Dbname:{$appModel['dbName']}");
                Yii::$app->toolKit->domain = $appModel['domain'];
                if (!Yii::$app->toolKit->changeDbConnection($appModel['dbName'], $appModel['host'], $appModel['username'], $appModel['password'])) {
                    Yii::$app->appLog->writeLog("Database connection change failed.");
                    continue;
                }
                $dbBatchSize = 1000;
                $startIndex = 0;
                $exit = false;
                $geoTaggingLimit = $this->getGeoTaggingLimit($appModel['packageType']);
                do {
                    Yii::$app->appLog->writeLog("Retrieve user batch for geocode, offset:{$startIndex}, limit:{{$dbBatchSize}}");
                    $query = User::find();
                    $query->select("t.*,CONCAT_WS(' ',`t`.address1, `t`.`city`, `c`.`countryName`,`t`.`zip`) AS `fullAddress`");
                    $query->from('User t');
                    $query->join('LEFT JOIN', 'Country c', 't.countryCode = c.countryCode');
                    $query->where("t.userType NOT IN ('" . User::SUPER_ADMIN . "','" . User::POLITICIAN . "','0') AND t.longLat IS NULL AND t.address1 IS NOT NULL AND t.zip IS NOT NULL AND t.city IS NOT NULL AND t.countryCode IS NOT NULL");
                    $query->andWhere("concat(t.address1, ', ', t.zip, ' ', t.city, ', ', c.countryName ) REGEXP '^([0-9a-zA-Z/]+, )?[^-\s][^,]+,( [a-zA-z ]+,)? [0-9]+,? [^,]+,( [a-zA-Z]+)*$'");
                    $query->limit(1000);
                    $modelUsers = $query->all();
                    $i = 0;
                    $userCount = count($modelUsers);
                    if (null != $modelUsers) {
                        Yii::$app->appLog->writeLog('Geocode query count: ' . count($modelUsers));
                        foreach ($modelUsers as $modelUser) {
                            if ($i < $userCount AND $i < $dbBatchSize) {
                                // if already geocoded and found as invalid address, then check if the record is updated to geocode again.
                                if (!is_null($modelUser['addressInvalidatedAt']) && ($modelUser['updatedAt'] <= $modelUser['addressInvalidatedAt'])) {
                                    continue;
                                }
                                $result = $this->lookup($modelUser['fullAddress'], $geoTaggingLimit);
                                usleep(5000); //100 milliseconds sleep, to avoid google's 10 req/per second limitation
                                if ($result['status'] === 0) { // 'OK' for google map
                                    $array = array($result["latitude"], $result["longitude"]);
                                    $connection = Yii::$app->getDb();
                                    $command = $connection->createCommand("UPDATE User SET longLat = '" . implode(",", $array) . "' WHERE id =" . $modelUser["id"]);
                                    $command->execute();
                                    $command = $connection->createCommand("UPDATE User SET geoPoint = POINT(:lat, :lon), longLat = '" . implode(",", $array) . "' WHERE id =" . $modelUser["id"]);
                                    $command->bindValue(':lat', $result["longitude"]);
                                    $command->bindValue(':lon', $result["latitude"]);
                                    $command->execute();

                                    Yii::$app->appLog->writeLog('Id: ' . $modelUser['id'] . '| latLong: ' . json_encode($array));
                                } else if ($result['status'] == 'OVER_QUERY_LIMIT') {
                                    $this->isLimitReached = true;
                                    Yii::$app->appLog->writeLog("Geocode response:" . $result['status']);
                                    // stop the background process
                                    $exit = true;
                                    Yii::$app->appLog->writeLog("Exiting...");
                                    break;
                                } else if ($result['status'] == 'ZERO_RESULTS') {
                                    Yii::$app->appLog->writeLog('Id: ' . $modelUser['id'] . '| non-existent address: ' . $modelUser['fullAddress']);
                                    $connection = Yii::$app->getDb();
                                    $command = $connection->createCommand("UPDATE User SET addressInvalidatedAt = '" . date('Y-m-d H:i:s') . "' WHERE id =" . $modelUser["id"]);
                                    $command->execute();
                                    continue;
                                } else if ($result['status'] == 'LIMIT_EXCEEDED') {
                                    Yii::$app->appLog->writeLog("Map request package exceeded:" . 'Failed');
                                    break;
                                } else {
                                    Yii::$app->appLog->writeLog("Geocode response:" . 'Failed');
                                    break;
                                }
                                $i++;
                            }
                        }
                        $exit = true;
                    } else {
                        Yii::$app->appLog->writeLog("No users found to geocode.");
                        $exit = true;
                    }
                    $startIndex += $dbBatchSize;

                } while ($i > $dbBatchSize);
            }
        }

        Yii::$app->appLog->writeLog('Process over');
    }

    /**
     * This will lookup geo-coordinates by passing address to Mapquestapi
     * @param $string
     * @param $limit
     * @return array
     */

    public function lookup($string, $limit)
    {

        if (!OsmLog::checkLimit($limit)) {
            $result = array(
                'status' => 'LIMIT_EXCEEDED'
            );
            return $result;
        }
        $apiKey = Yii::$app->params['openStreetMap']['consumerKey'];
        $url = "https://www.mapquestapi.com/geocoding/v1/batch?key={$apiKey}&inFormat=kvp&outFormat=json&maxResults=1&location=" . urlencode($string) . "&thumbMaps=false";
        OsmLog::countUp(1);
        $response = file_get_contents($url);
        $response = Json::decode($response, true);
        // If Status Code is Not ZERO INVALID_REQUEST
        if ($response['info']['statuscode'] !== 0) {
            return array(
                'status' => $response['info']['messages']
            );
        }

        $geometry = $response['results'][0];

        // TODO: we need to change this code. Previous Developer assign value of coordinates wrongly.
        $result = array(
            'status' => $response['info']['statuscode'],
            'latitude' => $geometry['locations'][0]['latLng']['lng'],
            'longitude' => $geometry['locations'][0]['latLng']['lat']
        );

        return $result;
    }

    public function getGeoTaggingLimit($packageType)
    {
        $sql = 'SELECT geotaggingLimit FROM  Package p LEFT JOIN PackageType pt ON p.PackageTypeId = pt.id WHERE p.type=' . $packageType;
        $raw = Yii::$app->dbMaster->createCommand($sql)->queryOne();
        return $raw['geotaggingLimit'];
    }


}