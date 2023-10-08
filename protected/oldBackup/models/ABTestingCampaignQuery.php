<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[ABTestingCampaign]].
 *
 * @see ABTestingCampaign
 */
class ABTestingCampaignQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return ABTestingCampaign[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return ABTestingCampaign|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
