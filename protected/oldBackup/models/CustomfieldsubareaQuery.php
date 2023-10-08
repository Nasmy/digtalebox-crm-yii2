<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[CustomFieldSubArea]].
 *
 * @see CustomFieldSubArea
 */
class CustomfieldsubareaQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return CustomFieldSubArea[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return CustomFieldSubArea|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
