<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[Autokeywordcondition]].
 *
 * @see Autokeywordcondition
 */
class AutokeywordconditionQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Autokeywordcondition[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Autokeywordcondition|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
