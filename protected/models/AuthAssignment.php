<?php

namespace app\models;


use Yii;
use app\models\AuthItem;
use app\models\AuthItemChild;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "AuthAssignment".
 *
 * @property string $itemname
 * @property string $userid
 * @property string $bizrule
 * @property string $data
 */
class AuthAssignment extends ActiveRecord
{
    // public $itemname;
    // public $userId;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'AuthAssignment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['itemname', 'userid'], 'required'],
            [['bizrule', 'data'], 'safe'],
            // [['itemname', 'userid'], 'length'=>[1,64]],
            [['itemname', 'userid'], 'unique', 'targetAttribute' => ['itemname', 'userid']],
        ];
    }


    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'itemname' => Yii::t('app', 'Itemname'),
            'userid' => Yii::t('app', 'Userid'),
            'bizrule' => Yii::t('app', 'Bizrule'),
            'data' => Yii::t('app', 'Data'),
        ];
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthItemChilds()
    {
        return $this->hasMany(AuthItemChild::className(), ['child' => 'name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthItems()
    {
        return $this->hasMany(AuthItem::className(), ['name' => 'itemname']);
    }

    /**
     * Assign permission or role to a user.
     * @return boolean true if assignment success otherwise false.
     */
    public static function assignItem($itemName, $userId)
    {
        $model = new AuthAssignment();
        $model->itemname = $itemName;
        $model->userid = $userId;

        try {
            $command = Yii::$app->db->createCommand('SET foreign_key_checks = 0;');
            $command->execute();
            if ($model->save()) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Delete user assignments.
     * @param Integer $userId Id of the user
     * @param string $itemName Auth item name
     */
    public static function deleteAssignedItems($userId, $itemName = null)
    {
        if (null == $itemName) {
            AuthAssignment::deleteAll('`userid` = :userid', array(
                ':userid' => $userId,
            ));
        } else {
            AuthAssignment::deleteAll('`userid` = :userid AND itemname = :itemname', array(
                ':userid' => $userId,
                ':itemname' => $itemName
            ));
        }
    }

    /**
     * {@inheritdoc}
     * @return AuthAssignmentQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new AuthAssignmentQuery(get_called_class());
    }
}
