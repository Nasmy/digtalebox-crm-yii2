<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[SearchCriteria]].
 *
 * @see SearchCriteria
 */
class SearchCriteriaQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return SearchCriteria[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return SearchCriteria|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
