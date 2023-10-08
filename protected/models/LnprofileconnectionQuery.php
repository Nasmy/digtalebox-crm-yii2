<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[LnProfileConnection]].
 *
 * @see LnProfileConnection
 */
class LnprofileconnectionQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return LnProfileConnection[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return LnProfileConnection|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
