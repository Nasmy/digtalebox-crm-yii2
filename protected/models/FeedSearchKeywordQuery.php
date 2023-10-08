<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[FeedSearchKeyword]].
 *
 * @see FeedSearchKeyword
 */
class FeedSearchKeywordQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return FeedSearchKeyword[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return FeedSearchKeyword|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
