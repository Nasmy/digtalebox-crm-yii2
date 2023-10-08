<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[MessageTemplate]].
 *
 * @see MessageTemplate
 */
class MessageTemplateQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return MessageTemplate[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return MessageTemplate|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
