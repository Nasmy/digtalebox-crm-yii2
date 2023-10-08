<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[Formmembershiptype]].
 *
 * @see Formmembershiptype
 */
class FormMembershipTypeQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Formmembershiptype[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Formmembershiptype|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
