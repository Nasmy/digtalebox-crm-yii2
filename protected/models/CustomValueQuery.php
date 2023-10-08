<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[CustomValue]].
 *
 * @see CustomValue
 */
class CustomValueQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return CustomValue[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return CustomValue|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
