<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[KeywordUrl]].
 *
 * @see KeywordUrl
 */
class KeywordUrlQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return KeywordUrl[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return KeywordUrl|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

}
