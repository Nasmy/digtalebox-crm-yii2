<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[McProfile]].
 *
 * @see McProfile
 */
class McProfileQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return McProfile[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return McProfile|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
