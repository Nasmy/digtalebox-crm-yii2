<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[EventReminder]].
 *
 * @see EventReminder
 */
class EventReminderQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return EventReminder[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return EventReminder|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
