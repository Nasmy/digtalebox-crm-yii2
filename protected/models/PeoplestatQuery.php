<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[PeopleStat]].
 *
 * @see PeopleStat
 */
class PeoplestatQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return PeopleStat[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return PeopleStat|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
