<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\StatSummary;

/**
 * StatSummarySearch represents the model behind the search form of `app\models\StatSummary`.
 */
class StatSummarySearch extends StatSummary
{

    /**
     * {@inheritdoc}
     */
//    public static function tableName()
//    {
//        return 'StatSummarySearch';
//    }


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date'], 'safe'],
            [['newSupporterCount', 'dataUsage', 'newRegistrationCount', 'feedCount', 'supporterCount', 'prospectCount'], 'integer'],
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
        $query = StatSummary::find();

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
            'date' => $this->date,
            'newSupporterCount' => $this->newSupporterCount,
            'dataUsage' => $this->dataUsage,
            'newRegistrationCount' => $this->newRegistrationCount,
            'feedCount' => $this->feedCount,
            'supporterCount' => $this->supporterCount,
            'prospectCount' => $this->prospectCount,
        ]);

        return $dataProvider;
    }
}
