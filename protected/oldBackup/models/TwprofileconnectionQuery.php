<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[TwProfileConnection]].
 *
 * @see TwProfileConnection
 */
class TwprofileconnectionQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return TwProfileConnection[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return TwProfileConnection|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
