<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[LnProfile]].
 *
 * @see LnProfile
 */
class LnProfileQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return LnProfile[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return LnProfile|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
