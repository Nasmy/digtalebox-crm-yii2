<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[FollowUp]].
 *
 * @see FollowUp
 */
class FollowupQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return FollowUp[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return FollowUp|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
