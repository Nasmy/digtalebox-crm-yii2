<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\LnPageInfo;

/**
 * LnPageInfoSearch represents the model behind the search form of `app\models\LnPageInfo`.
 */
class LnPageInfoSearch extends LnPageInfo
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['pageId', 'pageName', 'postCollectedTime'], 'safe'],
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
        $query = LnPageInfo::find();

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
            'postCollectedTime' => $this->postCollectedTime,
        ]);

        $query->andFilterWhere(['like', 'pageId', $this->pageId])
            ->andFilterWhere(['like', 'pageName', $this->pageName]);

        return $dataProvider;
    }
}
