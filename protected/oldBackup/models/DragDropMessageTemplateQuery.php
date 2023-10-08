<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[DragDropMessageTemplate]].
 *
 * @see DragDropMessageTemplate
 */
class DragDropMessageTemplateQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return DragDropMessageTemplate[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return DragDropMessageTemplate|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
