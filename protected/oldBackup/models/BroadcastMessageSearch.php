<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\BroadcastMessage;

/**
 * BroadcastMessageSearch represents the model behind the search form of `app\models\BroadcastMessage`.
 */
class BroadcastMessageSearch extends BroadcastMessage
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'fbPostStatus', 'twPostStatus', 'lnPostStatus', 'fbProfPostStatus', 'lnPagePostStatus', 'createdBy', 'updatedBy', 'recordStatus'], 'integer'],
            [['fbPost', 'twPost', 'lnPost', 'fbProfPost', 'lnPagePost', 'fbImageName', 'twImageName', 'lnImageName', 'lnPageImageName', 'fbProfImageName', 'publishDate', 'createdAt', 'updatedAt'], 'safe'],
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
        $query = BroadcastMessage::find();

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
            'fbPostStatus' => $this->fbPostStatus,
            'twPostStatus' => $this->twPostStatus,
            'lnPostStatus' => $this->lnPostStatus,
            'fbProfPostStatus' => $this->fbProfPostStatus,
            'lnPagePostStatus' => $this->lnPagePostStatus,
            'publishDate' => $this->publishDate,
            'createdBy' => $this->createdBy,
            'createdAt' => $this->createdAt,
            'updatedBy' => $this->updatedBy,
            'updatedAt' => $this->updatedAt,
            'recordStatus' => $this->recordStatus,
        ]);

        $query->andFilterWhere(['like', 'fbPost', $this->fbPost])
            ->andFilterWhere(['like', 'twPost', $this->twPost])
            ->andFilterWhere(['like', 'lnPost', $this->lnPost])
            ->andFilterWhere(['like', 'fbProfPost', $this->fbProfPost])
            ->andFilterWhere(['like', 'lnPagePost', $this->lnPagePost])
            ->andFilterWhere(['like', 'fbImageName', $this->fbImageName])
            ->andFilterWhere(['like', 'twImageName', $this->twImageName])
            ->andFilterWhere(['like', 'lnImageName', $this->lnImageName])
            ->andFilterWhere(['like', 'lnPageImageName', $this->lnPageImageName])
            ->andFilterWhere(['like', 'fbProfImageName', $this->fbProfImageName]);

        return $dataProvider;
    }
}
