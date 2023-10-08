<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[Keyword]].
 *
 * @see Keyword
 */
class KeywordQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Keyword[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Keyword|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
