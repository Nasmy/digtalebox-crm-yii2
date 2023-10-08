<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "advancebulkinsertstatus".
 *
 * @property int $id
 * @property int $bulkId
 * @property int|null $totalRecords
 * @property int|null $successRecords
 * @property int|null $invalidRecords
 * @property int|null $currentRecord
 * @property string $startedAt
 * @property string $lastUpdated
 */
class AdvanceBulkInsertStatus extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'AdvanceBulkInsertStatus';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['bulkId', 'startedAt'], 'required'],
            [['bulkId', 'totalRecords', 'successRecords', 'invalidRecords', 'currentRecord'], 'integer'],
            [['startedAt', 'lastUpdated'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'bulkId' => 'Bulk ID',
            'totalRecords' => 'Total Records',
            'successRecords' => 'Success Records',
            'invalidRecords' => 'Invalid Records',
            'currentRecord' => 'Current Record',
            'startedAt' => 'Started At',
            'lastUpdated' => 'Last Updated',
        ];
    }

    /**
     * {@inheritdoc}
     * @return AdvanceBulkInsertStatusQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new AdvanceBulkInsertStatusQuery(get_called_class());
    }
}
