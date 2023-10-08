<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[CustomValueSearch]].
 *
 * @see CustomValueSearch
 */
class CustomvaluesearchQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return CustomValueSearch[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return CustomValueSearch|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
