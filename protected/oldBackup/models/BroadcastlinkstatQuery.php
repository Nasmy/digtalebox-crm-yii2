<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[BroadcastLinkStat]].
 *
 * @see BroadcastLinkStat
 */
class BroadcastlinkstatQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return BroadcastLinkStat[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return BroadcastLinkStat|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
