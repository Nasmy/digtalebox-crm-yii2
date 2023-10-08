<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[FormCustomField]].
 *
 * @see FormCustomField
 */
class FormCustomFieldQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return FormCustomField[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return FormCustomField|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
