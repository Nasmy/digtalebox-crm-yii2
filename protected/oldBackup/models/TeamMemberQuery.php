<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[TeamMember]].
 *
 * @see TeamMember
 */
class TeamMemberQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return TeamMember[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return TeamMember|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
