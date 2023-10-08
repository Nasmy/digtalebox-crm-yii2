<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[FeedAction]].
 *
 * @see FeedAction
 */
class FeedactionQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return FeedAction[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return FeedAction|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
