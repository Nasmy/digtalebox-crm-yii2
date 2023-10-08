<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[EventReminderTracker]].
 *
 * @see EventReminderTracker
 */
class EventReminderTrackerQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return EventReminderTracker[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return EventReminderTracker|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
