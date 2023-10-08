<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "TeamMember".
 *
 * @property int $teamId
 * @property int $memberUserId
 * @property int $status Whether user has deleted or not.1 - deleted, 0 - not deleted
 * @property int $isTeamLead 0-not team lead, 1-team lead
 * @property int $isApproved 0-approved, 1-pending
 * @property string $createdAt Created date time
 */
class TeamMember extends \yii\db\ActiveRecord
{
    public $memberName;
    public $name;
    public $firstName;
    public $lastName;
    public $id;
    public $email;
    public $zip;
    public $profImage;

    const APPROVED = 0;
    const PENDING = 1;
    const NOT_DELETE = 0;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'TeamMember';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return [
            [['teamId', 'memberUserId'], 'required'],
            [['teamId'], 'numerical', 'integerOnly'=>true],
            [['memberUserId'], 'length', 'max'=>20],
            [['isApproved'], 'safe'],
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            [['teamId', 'memberUserId', 'firstName', 'lastName', 'status'], 'safe', 'on'=>'search'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'teamId' => 'Team ID',
            'memberUserId' => 'Member User ID',
            'status' => 'Status',
            'isTeamLead' => 'Is Team Lead',
            'isApproved' => 'Is Approved',
            'createdAt' => 'Created At',
        ];
    }

    public function getTeamIdByMemberId($memberUserId) {
        $model = TeamMember::find()->where(['memberUserId'=>$memberUserId])->one();
         if (null != $model) {
            return $model->teamId;
        }

        return null;
    }
    /**
     * {@inheritdoc}
     * @return TeamMemberQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TeamMemberQuery(get_called_class());
    }
}
