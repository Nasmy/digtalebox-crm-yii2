<?php

namespace app\models;

use app\components\ToolKit;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Campaign;

/**
 * CampaignSearch represents the model behind the search form of `app\models\Campaign`.
 */
class CampaignSearch extends Campaign
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'messageTemplateId', 'searchCriteriaId', 'status', 'campType', 'totalUsers', 'batchOffset', 'batchOffsetEmail', 'batchOffsetTwitter', 'batchOffesetLinkedIn', 'aBTestId', 'createdBy', 'updatedBy'], 'integer'],
            [['fromName', 'fromEmail', 'startDateTime', 'endDateTime', 'createdAt', 'updatedAt'], 'safe'],
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
    public function search($params)
    {
        if(isset ($_POST)){
            $params = $_POST;
        }

        $query = Campaign::find();

        $isSameDay = false;

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 10],
            'sort' => ['defaultOrder' => ['startDateTime' => SORT_DESC]]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        if (!ToolKit::isEmpty($this->startDateTime) && !ToolKit::isEmpty($this->endDateTime) && $this->startDateTime === $this->endDateTime) {
            $isSameDay = true;
            // $query->andFilterWhere('DATE_FORMAT(startDateTime, \'%Y-%m-%d\') = "'.$this->startDateTime.'"');
            $query->andFilterWhere(['=', 'startDateTime', $this->startDateTime]);
        }

        if (!$isSameDay && !empty($this->startDateTime)) {
            $query->andFilterWhere(['>=', 'startDateTime', $this->startDateTime]);
        }

        if (!$isSameDay && !empty($this->endDateTime)) {
            $query->andFilterWhere(['<=', 'endDateTime', $this->endDateTime]);
        }
        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'messageTemplateId' => $this->messageTemplateId,
            'searchCriteriaId' => $this->searchCriteriaId,
            'status' => $this->status,
            'campType' => $this->campType
        ]);

        return $dataProvider;
    }
}
