<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "OsmLog".
 *
 * @property int $id
 * @property int $monthKey
 * @property int $count
 * @property string $createAt
 * @property string $updatedAt
 */
class OsmLog extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'OsmLog';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return [
            ['monthKey', 'safe'],
            ['count', 'safe'],
            ['createAt', 'safe'],
            ['updatedAt', 'safe']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'monthKey' => 'Month Key',
            'count' => 'Count',
            'createAt' => 'Create At',
            'updatedAt' => 'Updated At',
        ];
    }

    /**
     * @param $count
     * @return bool
     */
    public function countUp($count)
    {
        $monthKey = Date('Ym');

        $record = OsmLog::find()->where(['monthKey' => $monthKey])->one();
        if ($record) {
            $record->count = $record->count + $count;
            return $record->save();
        } else {
            // return $this->_addRow($monthKey, $count);
            OsmLog::_addRow($monthKey, $count);
            return true;
        }

    }

    /**
     * @param $limit
     * @return bool
     */
    public function checkLimit($limit)
    {
        if ($limit == '-1') {
            return true;
        }
        $monthKey = Date('Ym');
        $record = OsmLog::find()->where(['monthKey' => $monthKey])->all();
        if ($record) {
            return (count($record) < $limit);

        } else {
            OsmLog::_addRow($monthKey);
            return true;
        }
    }

    /**
     * @param $monthKey
     * @param int $count
     * @return bool
     */
    private function _addRow($monthKey, $count = 0)
    {
        $log = new OsmLog();
        $log->monthKey = $monthKey;
        $log->count = $count;
        return $log->save();
    }

    /**
     * {@inheritdoc}
     * @return OsmLogQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new OsmLogQuery(get_called_class());
    }
}
