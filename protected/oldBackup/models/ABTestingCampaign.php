<?php

namespace app\models;

use Yii;
use yii\db\Query;

/**
 * This is the model class for table "ABTestingCampaign".
 *
 * @property int $id
 * @property string $name
 * @property int $messageTemplateIdA
 * @property int $messageTemplateIdB
 * @property string $fromA
 * @property string $subjectA
 * @property int $countA
 * @property string $fromB
 * @property string $subjectB
 * @property int $countB
 * @property string $fromRemain
 * @property string $subjectRemain
 * @property int $countRemain
 * @property string $startDate
 * @property string $createdAt
 * @property int $createdBy
 * @property string $updatedAt
 * @property int $updatedBy
 */
class ABTestingCampaign extends \yii\db\ActiveRecord
{
    //A/B test variables
    public $searchCriteriaId;
    public $amountB;
    public $amountA;
    public $aBTestTemplateId;
    public $endDateTime;
    public $userCount;

    const TEMPLATE_DEFAULT = '0';
    const TEMPLATE_A = 'A';
    const TEMPLATE_B = 'B';

    const MINIMUM = 10;
    const MAXIMUM = 1000;
    const MIN_DEFAULT = 1;
    const MAX_DEFAULT = 1;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ABTestingCampaign';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'messageTemplateIdA', 'messageTemplateIdB', 'fromA', 'subjectA', 'countA', 'fromB', 'subjectB', 'countB', 'startDate', 'searchCriteriaId'], 'required'],
            [['messageTemplateIdA', 'messageTemplateIdB', 'countA', 'countB', 'countRemain', 'countRemain', 'userCount', 'createdBy', 'updatedBy'], 'integer'],
            [['startDate', 'createdAt', 'updatedAt'], 'safe'],
            [['name', 'fromA', 'subjectA', 'fromB', 'subjectB', 'fromRemain', 'subjectRemain'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'name' => Yii::t('messages', 'Name'),
            'messageTemplateIdA' => Yii::t('messages', 'Template A'),
            'messageTemplateIdB' => Yii::t('messages', 'Template B'),
            'fromA' => Yii::t('messages', 'Sender A'),
            'subjectA' => Yii::t('messages', 'Subject A'),
            'countA' => Yii::t('messages', 'Count A'),
            'fromB' => Yii::t('messages', 'Sender B'),
            'subjectB' => Yii::t('messages', 'Subject B'),
            'countB' => Yii::t('messages', 'Count B'),
            'fromRemain' => Yii::t('messages', 'From Remain'),
            'subjectRemain' => Yii::t('messages', 'Subject Remain'),
            'countRemain' => Yii::t('messages', 'Count Remain'),
            'startDate' => Yii::t('messages', 'Start Date'),
            'createdAt' => Yii::t('messages', 'Created At'),
            'createdBy' => Yii::t('messages', 'Created By'),
            'updatedAt' => Yii::t('messages', 'Updated At'),
            'updatedBy' => Yii::t('messages', 'Updated By'),
            'searchCriteriaId' => Yii::t('messages', 'Saved Search'),
            'aBTestTemplateId' => Yii::t('messages', 'Remaining Campaign Template'),
            'endDateTime' => Yii::t('messages', 'End Date'),
            'userCount' => Yii::t('messages', 'User Count'),
        ];
    }

    public function getCampaignResults($id, $limit, $offset)
    {
        if(empty($limit)) {
            $limit = 1;
        }

        $subQuery = (new Query())
            ->select('*')
            ->from('CampaignUsers')
            ->where(['campaignId'=>$id])
            ->orderBy('userId')
            ->limit($limit)
            ->offset($offset);
        $query = (new Query())
            ->select('COUNT(emailStatus) as sendCount,emailStatus')
            ->from($subQuery)
            ->groupBy('emailStatus')
            ->all();

        $results = $query;
        return $results;
    }

    public function isRemainSchedule($id) {
        $return = false;
        $campaign = ABTestingCampaign::find()->where(['id'=>$id])->one();
        if($campaign->fromRemain != '') {
            $return = true;
        }
        return $return;
    }

    /**
     * {@inheritdoc}
     * @return ABTestingCampaignQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ABTestingCampaignQuery(get_called_class());
    }
}
