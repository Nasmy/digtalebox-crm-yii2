<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Feed;

/**
 * FeedSearch represents the model behind the search form of `app\models\Feed`.
 */
class FeedSearch extends Feed
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'name', 'twScreenName', 'networkUserId', 'text', 'dateTime', 'location', 'msgDateTime', 'profImageUrl'], 'safe'],
            [['keywordId', 'type', 'network', 'userType'], 'integer'],
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
        $query = Feed::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [ 'pageSize' => 10 ],

        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'keywordId' => $this->keywordId,
            'type' => $this->type,
            'network' => $this->network,
            'dateTime' => $this->dateTime,
            'msgDateTime' => $this->msgDateTime,
            'userType' => $this->userType,
        ]);

        $query->andFilterWhere(['like', 'id', $this->id])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'twScreenName', $this->twScreenName])
            ->andFilterWhere(['like', 'networkUserId', $this->networkUserId])
            ->andFilterWhere(['like', 'text', $this->text])
            ->andFilterWhere(['like', 'location', $this->location])
            ->andFilterWhere(['like', 'profImageUrl', $this->profImageUrl]);

        return $dataProvider;
    }
}
