<?php

namespace app\models;

use app\components\WebUser;
use Yii;
use yii\bootstrap\Html;
use yii\db\Query;
use yii\data\ActiveDataProvider;
use yii\data\SqlDataProvider;
use app\components\ToolKit;
use app\models\Team;
use yii\helpers\Url;

/**
 * This is the model class for table "BulkExport".
 *
 * The followings are the available columns in table 'BulkExport':
 * @property integer $id
 * @property integer $searchCriteriaId
 * @property string $createdAt
 * @property integer $totalRecords
 * @property integer $lastRecord
 * @property string $status
 * @property string $startAt
 * @property string $finishedAt
 * @property integer $createdBy
 */
class BulkExport extends yii\db\ActiveRecord
{
    /**
     * status
     */
    const PENDING = 0;
    const IN_PROGRESS = 1;
    const FINISHED = 2;
    const ERROR = 3;
    const CANCELED = 5;
    const BULKEXPORT_TYPE = 0;
    const ADDRESS_EXPORT_TYPE = 1;

    const CRON_COMMAND = 'mass-bulk-export';
    const CRON_COMMAND_ADDRESS_EXPORT = 'address-export';

    public $exportType;
    public $searchCriteriaId;
    public $createdAt;
    public $totalRecords;
    public $lastRecord;
    public $status;
    public $startAt;
    public $finishedAt;
    public $createdBy;

    /**
     * @return string the associated database table name
     */
    static public function tableName()
    {
        return 'BulkExport';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return [
            [['searchCriteriaId', 'totalRecords', 'status', 'createdBy'], 'required'],
            [['searchCriteriaId', 'totalRecords', 'createdBy'], 'integer'],
//            [['status', 'length',], 'max'=>1],
            [['exportType', 'createdAt', 'totalRecords', 'createdBy', 'startAt', 'status', 'finishedAt'], 'safe'],
            [['id', 'searchCriteriaId', 'createdAt', 'totalRecords', 'lastRecord', 'status', 'startAt', 'finishedAt', 'createdBy'], 'safe', 'on' => 'search'],
        ];
    }


    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'searchCriteriaId' => 'Search Criteria',
            'createdAt' => 'Created At',
            'totalRecords' => 'Total Records',
            'lastRecord' => 'Last Record',
            'status' => 'Status',
            'startAt' => 'Start At',
            'finishedAt' => 'Finished At',
            'createdBy' => 'Created By',
            'exportType' => 'Export Type'
        );
    }

    /**
     * executes the command given based on the running OS.
     */
    public static function runCommand($command = self::CRON_COMMAND, $recordId, $kill_id)
    {
        $domain = Yii::$app->toolKit->domain;
        $status = 0;
        if (Yii::$app->toolKit->osType == ToolKit::OS_WINDOWS) {
            $cmd = "php " . Yii::$app->params['consolePath'] . "console.php {$command} {$domain} {$recordId}";
            Yii::$app->appLog->writeLog("Windows: " . $cmd);
            $handle = popen("start /B " . $cmd, 'r'); // For windows
            $read = fread($handle, 2096);
            pclose($handle);
        } else {
            if (!empty($kill_id)) {
                $kill_id = $kill_id - 1;
                $cmd = "kill $kill_id";
                Yii::$app->appLog->writeLog("Linux:Kill $kill_id " . $cmd);
                $str = exec($cmd, $op); // For Linux
            } else {
                $cmd = "php " . Yii::$app->params['consolePath'] . "console.php {$command} {$domain} {$recordId} > /dev/null & 2>&1 & echo $!";
                Yii::$app->appLog->writeLog("Linux: " . $cmd);
                $str = exec($cmd, $op); // For Linux

                /**
                 * update execution process ID.
                 */
                $pid = (int)$op[0];
                $connection = Yii::$app->getDb();
                $command = $connection->createCommand("UPDATE BulkExport SET processId = $pid WHERE id =$recordId");
                $command->execute();
            }

        }
        if ($status != 0) {
            Yii::$app->appLog->writeLog("Command {$command} failed to run.");
        }

    }

    /**
     * format the progress text in to a better reading form
     */
    public static function getFormattedProgress($id)
    {
        $result = '0';
        $style = "width:0%;";
        $progress = 0;
        $data = AdvanceBulkInsertStatus::find()->where(['bulkId' => $id])->one();
        if (!is_null($data)) {
            $progress = ceil(($data->successRecords / $data->totalRecords) * 100);
            $string = Yii::t('messages', '% completed');
            $result = ToolKit::isEmpty($progress) ? $progress : $progress;
            $style = "width:" . $progress . "%;";
        }
        return '<div class="progress themed-progress"><div class="progress-bar" role="progressbar" style="' . $style . '" aria-valuenow="' . $progress . '" aria-valuemin="0" aria-valuemax="100">' . $result . "%" . '</div></div>';
    }

    /**
     * format the progress text in to a better reading form
     */
    public static function getFormattedSpentTime($id)
    {
        $string = '';
        $differenceInSeconds = '';
        $icon = '';
        $status = AdvanceBulkInsertStatus::find()->where('bulkId=:bulkId')->params([':bulkId' => $id])->one();
        if ($status) {
            $string = ' sec';
            $timeFirst = strtotime($status->startedAt);
            $timeSecond = strtotime($status->lastUpdated);
            $differenceInSeconds = $timeSecond - $timeFirst;
            $icon = '<i class="fa fa-clock-o fa-lg"></i>';
        }
        return $icon . ' ' . $differenceInSeconds . $string;
    }

    /**
     * @return String status labels
     */
    public static function getStatusLabel($status)
    {
        switch ($status) {

            case self::IN_PROGRESS:
                $cssClass = 'badge badge-pill badge-info';
                $text = Yii::t('messages', 'In Progress');
                break;

            case self::ERROR:
                $cssClass = 'badge badge-pill badge-danger';
                $text = Yii::t('messages', 'Error');
                break;

            case self::FINISHED:
                $cssClass = 'badge badge-pill badge-success';
                $text = Yii::t('messages', 'Finished');
                break;

            case self::CANCELED:
                $cssClass = 'badge badge-pill bg-bounced';
                $text = Yii::t('messages', 'Cancelled');
                break;

            case self::PENDING:
                $cssClass = 'badge badge-pill bg-blocked';
                $text = Yii::t('messages', 'Pending');
                break;
        }
        return "<div class='{$cssClass}'>{$text}</div>";
    }

    /**
     * Generate error file
     * @return string
     */
    public static function getErrorFile($errorFile)
    {
        $link = '';
        if (!ToolKit::isEmpty($errorFile)) {
            $link = Html::tag('a', '<i class="fa fa-download fa-lg"></i>', ['people/bulk-insert', 'status_file' =>
                $errorFile]);
        }
        return $link;
    }

    public static function getDownlaodUrl($export_file)
    {
        $link = '';
        if (!ToolKit::isEmpty($export_file)) {
            $url = Url::to(['advanced-search/download-status', 'status_file' => $export_file]); // your url code to retrieve the profile view

            $link = Html::a('<i class="fa fa-download fa-lg"></i>', false, [
                'onClick' => "
                      var url = $(this).attr('href');
                      window.location.href ='$url';                          
                      return false;
                      ",
                'id' => 'export_download'
            ]);
        }

        return $link;
    }

    public static function getExportTypeLabel($type_id)
    {
        switch ($type_id) {
            case 0:
                return Yii::t('messages', 'Bulk Export');
                break;
            case 1:
                return Yii::t('messages', 'Address Export');
                break;
        }
    }


    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * Typical usecase:
     * - Initialize the model fields with values from filter form.
     * - Execute this method to get CActiveDataProvider instance which will filter
     * models according to data in model fields.
     * - Pass data provider to GridView, CListView or any similar widget.
     *
     * @return dataProvider the data provider that can return the models
     * based on the search/filter conditions.
     */
    public function search()
    {
        // @todo Please modify the following code to remove attributes that should not be searched.

        $count = Yii::$app->db->createCommand('SELECT COUNT(*) FROM BulkExport')->queryScalar();

        $dataProvider = new SqlDataProvider([
            'sql' => 'SELECT * FROM BulkExport',
            'totalCount' => $count,
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC],
                'attributes' => [
                    'id'
                ],
            ],
        ]);

        return $dataProvider;

    }

    /**
     * {@inheritdoc}
     * @return MsgboxQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new BulkExportQuery(get_called_class());
    }


}
