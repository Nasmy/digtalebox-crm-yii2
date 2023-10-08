<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[CustomField]].
 *
 * @see CustomField
 */
class CustomFieldQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return CustomField[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return CustomField|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
