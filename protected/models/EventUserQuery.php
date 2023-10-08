<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[EventUser]].
 *
 * @see EventUser
 */
class EventUserQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return EventUser[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return EventUser|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
