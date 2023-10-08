<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[BroadcastMessage]].
 *
 * @see BroadcastMessage
 */
class BroadcastMessageQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return BroadcastMessage[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return BroadcastMessage|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
