<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[TwProfile]].
 *
 * @see TwProfile
 */
class TwProfileQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return TwProfile[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return TwProfile|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
