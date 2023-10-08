<?php

namespace app\models;

use app\components\ToolKit;
use app\models\CampaignUsers;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;
use yii\data\Sort;
use yii\db\Query;
use Yii;

/**
 * CampaignUsersSearch represents the model behind the search form of `app\models\CampaignUsers`.
 */
class CampaignUsersSearch extends CampaignUsers
{

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['campaignId', 'userId', 'status', 'emailStatus', 'smsStatus', 'emailTransactionId'], 'integer'],
            [['campaignId', 'userId', 'status', 'emailStatus', 'email', 'name', 'mobile', 'smsStatus'], 'safe', 'on' => 'search'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params = null)
    {

        $params = (isset($params['CampaignUsersSearch']) == true) ? $params['CampaignUsersSearch'] : $params;
        $query = CampaignUsers::find()
            ->select(['CampaignUsers.*', 'CONCAT_WS(" ",User.firstName, User.lastName) as  name', 'User.email', 'User.mobile', 'User.isUnsubEmail', 'User.emailStatus as userEmailStatus', 'User.firstName', 'User.lastName', 'keywords' => 'User.keywords'])
            ->leftJoin('User', '`User`.`id` = `CampaignUsers`.`userId`')
            ->where(['CampaignUsers.campaignId' => $this->campaignId]);

        /* todo  have to recheck this if need
         //Retrieve email failed status
         if(isset($params['emailStatus']) && $params['emailStatus'] != "") {
             $query->andWhere(['CampaignUsers.status' => 3]);
        }*/

        //Retrive email unsubscribe data
        if (isset($params['emailStatus']) && !empty($params['emailStatus']) && $params['emailStatus'] == 7) {
            $query->andWhere(['isUnsubEmail' => 1]);
        }

        $pageSize = '10';

        if(isset($params['export']) && $params['export']){
            $pageSize = '-1'; // rest of total
        }   

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,
            ],
        ]);

        $dataProvider->setSort([
            'attributes' => array_merge(
                $dataProvider->getSort()->attributes,
                [
                    'email' => [
                        'asc' => ['User.email' => SORT_ASC],
                        'desc' => ['User.email' => SORT_DESC],
                        'label' => 'User.email',
                        'default' => SORT_DESC,
                    ],
                    'mobile' => [
                        'asc' => ['User.mobile' => SORT_ASC],
                        'desc' => ['User.mobile' => SORT_DESC],
                        'label' => 'User.mobile',
                        'default' => SORT_DESC,
                    ],
                    'clickedUrls' => [
                        'asc' => ['CampaignUsers.clickedUrls' => SORT_ASC],
                        'desc' => ['CampaignUsers.clickedUrls' => SORT_DESC],
                        'label' => 'CampaignUsers.clickedUrls',
                        'default' => SORT_DESC,
                    ],
                    'defaultOrder' => [
                        'createdAt' => SORT_ASC
                    ]
                ],
            ),
        ]);

        $this->load($params);
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'campaignId' => $this->campaignId,
            'userId' => $this->userId,
            // 'status' => $this->status,
            // 'CampaignUsers.emailStatus' => $this->emailStatus,
            'smsStatus' => $this->smsStatus,
            'emailTransactionId' => $this->emailTransactionId,
            'createdAt' => $this->createdAt,
        ]);

         $query->andFilterWhere(['like', 'User.email', $this->email])
        ->andFilterWhere(['like', 'User.mobile', $this->mobile])
        ->andFilterWhere(['like', 'clickedUrls', $this->clickedUrls])
        ->andFilterWhere(['like', 'smsId', $this->smsId]);


        if (!empty($params)) {
            if (isset($params['name']) && !empty($params['name'])) {
                $query->andFilterWhere(['like', 'CONCAT_WS(" ",User.firstName, User.lastName)', $params['name'] . '%', false]);
            }

            if (isset($params['mobile']) && !empty($params['mobile'])) {
                $query->andFilterWhere(['like', 'User.mobile', '%' . $params['mobile'] . '%', false]);
            }

            if (isset($params['email']) && !empty($params['email'])) {
                $query->andWhere(['like', 'User.email', $params['email']]);
            }

            if (isset($params['emailStatus']) && !empty($params['emailStatus']) && $params['emailStatus'] != 7) {
                $query->andFilterWhere(['=', 'CampaignUsers.emailStatus', $params['emailStatus']])->andWhere(["=", 'isUnsubEmail', 0]);
            }

            if (isset($params['smsStatus']) && !empty($params['smsStatus'])) {
                $query->andFilterWhere(['=', 'CampaignUsers.smsStatus', $params['smsStatus']]);
            }
        }

        return $dataProvider;

    }
}
