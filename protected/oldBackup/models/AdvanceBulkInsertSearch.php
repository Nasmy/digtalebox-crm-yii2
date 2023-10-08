<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\AdvanceBulkInsert;

/**
 * AdvanceBulkInsertSearch represents the model behind the search form of `app\models\AdvanceBulkInsert`.
 * advance search model
 */
class AdvanceBulkInsertSearch extends AdvanceBulkInsert
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'userType', 'createdBy'], 'integer'],
            [['source', 'renameSource', 'countryCode', 'keywords', 'size', 'errors', 'timeSpent', 'status', 'columnMap', 'createdAt'], 'safe'],
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
        $query = AdvanceBulkInsert::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 10],
            
            'sort' => [
                'defaultOrder' => [
                    'createdAt' => SORT_DESC,
                ],
            ],
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
            'userType' => $this->userType,
            'createdAt' => $this->createdAt,
            'createdBy' => $this->createdBy,
        ]);

        $query->andFilterWhere(['like', 'source', $this->source])
            ->andFilterWhere(['like', 'renameSource', $this->renameSource])
            ->andFilterWhere(['like', 'countryCode', $this->countryCode])
            ->andFilterWhere(['like', 'keywords', $this->keywords])
            ->andFilterWhere(['like', 'size', $this->size])
            ->andFilterWhere(['like', 'errors', $this->errors])
            ->andFilterWhere(['like', 'timeSpent', $this->timeSpent])
            ->andFilterWhere(['like', 'status', $this->status])
            ->andFilterWhere(['like', 'columnMap', $this->columnMap]);

        return $dataProvider;
    }
}
