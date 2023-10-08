<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[FormFieldList]].
 *
 * @see FormFieldList
 */
class FormFieldListQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return FormFieldList[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return FormFieldList|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
