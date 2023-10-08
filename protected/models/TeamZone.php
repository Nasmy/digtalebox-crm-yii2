<?php

namespace app\models;

use app\components\RActiveRecord;
use Yii;

/**
 * This is the model class for table "TeamZone".
 *
 * @property int $id
 * @property int $teamId
 * @property string $zip Comma seperated list of zip codes
 * @property string $zoneLongLat zone polygon longitude & latitude coordinates
 */
class TeamZone extends RActiveRecord
{
    public $teamZoneData;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'TeamZone';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // Create
			[['teamId', 'zip', 'teamZoneData'], 'required', 'on'=>'create'],
			[['teamId'], 'numerical', 'integerOnly'=>true, 'on'=>'create'],
			[['zip'], 'length', 'max'=>255, 'on'=>'create'],
			[['zip'], 'match', 'pattern'=>'/^[0-9]+(,[0-9]+)*$/', 'on'=>'create'],
			[['zip'], 'validateZip', 'on'=>'create'],
			[['teamZoneData'], 'safe', 'on'=>'create'],
			// Update
			[['teamId', 'zip', 'teamZoneData'], 'required', 'on'=>'update'],
			[['teamId'], 'numerical', 'integerOnly'=>true, 'on'=>'update'],
			[['zip'], 'length', 'max'=>45, 'on'=>'update'],
			[['zip'], 'match', 'pattern'=>'/^[0-9]+(,[0-9]+)*$/', 'on'=>'update'],
			[['zip'], 'validateZip', 'on'=>'update'],
			[['teamZoneData'], 'safe', 'on'=>'update']
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			// [['id', 'teamId', 'zip', 'zoneLongLat'], 'safe', 'on'=>'search']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'teamId' => Yii::t('messages','Team'),
            'zip' => Yii::t('messages', 'ZIP Codes'),
            'zoneLongLat' => Yii::t('messages','Zone'),
            'teamZoneData' => Yii::t('messages','Zone'),
        ];
    }

    /**
     * {@inheritdoc}
     * @return TeamZoneQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TeamZoneQuery(get_called_class());
    }
}
