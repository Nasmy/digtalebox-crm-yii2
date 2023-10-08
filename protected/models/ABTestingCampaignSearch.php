<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\ABTestingCampaign;

/**
 * ABTestingCampaignSearch represents the model behind the search form of `app\models\ABTestingCampaign`.
 */
class ABTestingCampaignSearch extends ABTestingCampaign
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'messageTemplateIdA', 'messageTemplateIdB', 'countA', 'countB', 'countRemain', 'createdBy', 'updatedBy'], 'integer'],
            [['name', 'fromA', 'subjectA', 'fromB', 'subjectB', 'fromRemain', 'subjectRemain', 'startDate', 'createdAt', 'updatedAt'], 'safe'],
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
        // print_r($params); die();
        $query = ABTestingCampaign::find()->where(['id'=>$params])->limit(1);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'messageTemplateIdA' => $this->messageTemplateIdA,
            'messageTemplateIdB' => $this->messageTemplateIdB,
            'countA' => $this->countA,
            'countB' => $this->countB,
            'countRemain' => $this->countRemain,
            'startDate' => $this->startDate,
            'createdAt' => $this->createdAt,
            'createdBy' => $this->createdBy,
            'updatedAt' => $this->updatedAt,
            'updatedBy' => $this->updatedBy,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'fromA', $this->fromA])
            ->andFilterWhere(['like', 'subjectA', $this->subjectA])
            ->andFilterWhere(['like', 'fromB', $this->fromB])
            ->andFilterWhere(['like', 'subjectB', $this->subjectB])
            ->andFilterWhere(['like', 'fromRemain', $this->fromRemain])
            ->andFilterWhere(['like', 'subjectRemain', $this->subjectRemain]);

        return $dataProvider;
    }
}
