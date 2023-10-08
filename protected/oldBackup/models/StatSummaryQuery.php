<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[StatSummary]].
 *
 * @see StatSummary
 */
class StatSummaryQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return StatSummary[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return StatSummary|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
