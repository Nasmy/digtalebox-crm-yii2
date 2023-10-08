<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[FeedLimit]].
 *
 * @see FeedLimit
 */
class FeedLimitQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return FeedLimit[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return FeedLimit|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
