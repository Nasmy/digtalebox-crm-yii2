<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[AdBannerData]].
 *
 * @see AdBannerData
 */
class AdbannerdataQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return AdBannerData[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return AdBannerData|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
