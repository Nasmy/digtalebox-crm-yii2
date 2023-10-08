<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[BitlyProfile]].
 *
 * @see BitlyProfile
 */
class BitlyprofileQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return BitlyProfile[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return BitlyProfile|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
