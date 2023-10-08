<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[FbProfile]].
 *
 * @see FbProfile
 */
class FbProfileQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return FbProfile[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return FbProfile|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
