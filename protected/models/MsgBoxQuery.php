<?php
/**
 * Created by PhpStorm.
 * User: nasmy
 * Date: 8/19/2019
 * Time: 4:43 PM
 */

namespace app\models;


class MsgBoxQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return App[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return App|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

}