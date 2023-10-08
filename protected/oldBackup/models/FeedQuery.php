<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[Feed]].
 *
 * @see Feed
 */
class FeedQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Feed[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Feed|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
