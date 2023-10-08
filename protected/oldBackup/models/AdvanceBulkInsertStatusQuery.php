<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[AdvanceBulkInsertStatus]].
 *
 * @see AdvanceBulkInsertStatus
 */
class AdvanceBulkInsertStatusQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return AdvanceBulkInsertStatus[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return AdvanceBulkInsertStatus|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
