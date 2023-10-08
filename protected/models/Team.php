<?php

namespace app\models;

use Yii;
use yii\helpers\Html;

/**
 * This is the model class for table "Team".
 *
 * @property int $id
 * @property string $name
 * @property int $leaderUserId
 * @property string $teamImgName Team image name
 * @property string $createdAt Created date time
 * @property int $createdBy User id of the creator
 * @property string $updatedAt Updated date time
 * @property int $updatedBy User id of the updator
 */
class Team extends \yii\db\ActiveRecord
{
    // Maximum width and height and size for image in pixels

    const MAX_IMG_WIDTH = 240;
    const MAX_IMG_HEIGHT = 240;
    const MAX_SIZE = 512000;

    public $leaderUserName = '';
    public $leaderUserData = '';
    public $teamImgFile;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Team';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // Create
            [['name', 'leaderUserData', 'teamImgFile'], 'required', 'on' => 'create'],
            [['leaderUserId'], 'required', 'message' => Yii::t('messages', 'Leader Name is be blank or invalid'), 'on' => 'create'],
            [['name', 'length'], 'max' => 45, 'on' => 'create'],
            [['leaderUserId'], 'length', 'max' => 20, 'on' => 'create'],
            [['teamImgFile'], 'file', 'allowEmpty' => true, 'types' => 'jpg,jpeg', 'maxSize' => self::MAX_SIZE, 'tooLarge' => Yii::t('messages', 'File needs to be smaller than 500 Kb'), 'on' => 'create'],
            [['teamImgFile'], 'validateImageDimesions', 'on' => 'create'],
            [['name'], 'unique', 'on' => 'create'],
            // Update
            [['name', 'leaderUserData'], 'required', 'on' => 'update'],
            [['leaderUserId'], 'required', 'message' => Yii::t('messages', 'Leader Name is be blank or invalid'), 'on' => 'update'],
            [['name'], 'length', 'max' => 45, 'on' => 'update'],
            [['leaderUserId'], 'length', 'max' => 20, 'on' => 'update'],
            [['teamImgFile'], 'file', 'allowEmpty' => true, 'types' => 'jpg,jpeg', 'maxSize' => self::MAX_SIZE, 'tooLarge' => Yii::t('messages', 'File needs to be smaller than 500 Kb'), 'on' => 'update'],
            [['teamImgFile'], 'validateImageDimesions', 'on' => 'update'],
            [['name'], 'unique', 'on' => 'update'],
            [['leaderUserId'], 'validateLeader', 'on' => 'update'],
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            [['id', 'name', 'leaderUserId'], 'safe', 'on' => 'search'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'leaderUserId' => 'Leader User ID',
            'teamImgName' => 'Team Img Name',
            'createdAt' => 'Created At',
            'createdBy' => 'Created By',
            'updatedAt' => 'Updated At',
            'updatedBy' => 'Updated By',
        ];
    }


    /**
     * Findout matching team for new user
     * Create default team if there is no matching team
     * @param string $zip Zip code entered by user
     */
    public static function getMatchingTeam($zip)
    {

        $model = TeamZone::find()->where('zip REGEXP \'[[:<:]]' . $zip . '[[:>:]]\'')->all();
        $teamId = null;

        if (null != $model) {
            $teamId = $model->teamId;
        }

        return $teamId;
    }

    /**
     * {@inheritdoc}
     * @return TeamQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TeamQuery(get_called_class());
    }

    /**
     * When comma seperated list of Team ids given, return comma seperated name list
     * @param string $idList Comma seperated id list
     * @return string $nameList Comma seperated list of names
     */
    public static function getTeamNamesByIdList($idList)
    {
        $nameList = array();
        $arrIdList = explode(',', $idList);

        foreach ($arrIdList as $id) {
            $teamModel = Team::findOne($idList);
            if (null != $teamModel) {
                $nameList[] = $teamModel->name;
            }
        }

        return implode(",", $nameList);
    }

    public function getTeamsByLogedUser()
    {
        $teamsList = '';
        if (Yii::$app->user->checkAccess("teamLead") && !Yii::$app->user->checkAccess("superadmin")) {
            $teams = Team::find()->where('leaderUserId = :leaderUserId', [':leaderUserId' => Yii::$app->getUser()->getId()])->all();

            $teamsList = Html::activeDropDownList('id', 'name', $teams);
        }


        return $teamsList;
    }
}
