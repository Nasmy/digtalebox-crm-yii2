<?php

namespace app\models;

use app\components\RActiveRecord;
use Yii;
use yii\db\Query;

/**
 * This is the model class for table "AuthItemChild".
 *
 * @property string $parent
 * @property string $child
 */
class AuthItemChild extends RActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'AuthItemChild';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['parent', 'child'], 'required'],
            [['parent', 'child'], 'string', 'max' => 64],
            [['parent', 'child'], 'unique', 'targetAttribute' => ['parent', 'child']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'parent' => Yii::t('app', 'Parent'),
            'child' => Yii::t('app', 'Child'),
        ];
    }

    public static function getChild($parentItem, $authItemType = AuthItem::TYPE_OPERATION)
    {
        $authItemChildModel = AuthItemChild::find()->where(['parent' => $parentItem])->all();
        if (null != $authItemChildModel) {
            if(isset($authItemChildModel->child)) {
                return $authItemChildModel->child;
            } else {
                return '';
            }

        }
    }

    public function getItemChildren($parentItem)
    {
        $authItemChildModels = AuthItemChild::find()->where(['parent'=>$parentItem])->all();
        $strChildrens=array();
        if (null != $authItemChildModels) {
            foreach ($authItemChildModels as $authItemChildModel) {
                $strChildrens[]= $authItemChildModel->parent;

            }
        }

        return $strChildrens;

    }

    public function getChildren($parentItem, $authItemType = AuthItem::TYPE_OPERATION)
    {
        $query = new Query();
        $query->select('t.*')
            ->where("AI.type='{$authItemType}'")
            ->join('INNER JOIN', 'AuthItem AI',
                't.child =AI.name')
            ->andWhere("t.child='{$parentItem}'")
            ->from('AuthItemChild t')
            ->all();

        $strChildrens = '';
        $authItemChildModels = AuthItemChild::findAll($query);
        if(null != $authItemChildModels) {
            foreach ($authItemChildModels as $authItemChildModel) {
                $strChildrens .= $authItemChildModel->parent . ',';
            }
        }

        return $strChildrens;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthItems()
    {
        return $this->hasMany(AuthItem::className(), ['name' => 'child']);
    }

    public static function getItemOperations($parentItem)
    {
        $query = new Query();
        $query->where(["parent"=>$parentItem]);
        $query->from("AuthItemChild");
        $strChildrens = '';
        $authItemChildModels = $query->all();
        if (null != $authItemChildModels) {
            foreach ($authItemChildModels as $authItemChildModel) {
                $strChildrens .= $authItemChildModel['child'] . ',';
            }
        }

        return rtrim($strChildrens, ',');
    }

    /**
     * Check whether role has this permission
     * @param integer $roleName Role name
     * @param integer $permission Id of the permission
     * @return boolean true if have otherwise false
     */
    public function checkRolePermission($roleName, $permission)
    {
        $model = new AuthItemChild();
        $criteria = new Query();
        $criteria->where = 'parent = :parent AND child = :child';
        $criteria->params = array(':parent' => $roleName, ':child'=> $permission);
        $authItemChildModels = $model->find($criteria);

        if (null != $authItemChildModels)
        {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     * @return AuthItemChildQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new AuthItemChildQuery(get_called_class());
    }
}
