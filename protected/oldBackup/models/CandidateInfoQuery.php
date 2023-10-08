<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[CandidateInfo]].
 *
 * @see CandidateInfo
 */
class CandidateInfoQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return CandidateInfo[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return CandidateInfo|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
