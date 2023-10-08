<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\MessageTemplate;

/**
 * MessageTemplateSearch represents the model behind the search form of `app\models\MessageTemplate`.
 */
class MessageTemplateSearch extends MessageTemplate
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'type', 'createdBy', 'updatedBy'], 'integer'],
            [['name', 'subject', 'twMessage', 'fbMessage', 'smsMessage', 'lnMessage', 'lnSubject', 'description', 'dateTime', 'createdAt', 'updatedAt', 'dragDropMessageCode'], 'safe'],
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
        $query = MessageTemplate::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [ 'pageSize' => 10 ],
            'sort'=> ['defaultOrder' => ['id'=>SORT_DESC]]
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
            'type' => $this->type,
            'dateTime' => $this->dateTime,
            'createdBy' => $this->createdBy,
            'createdAt' => $this->createdAt,
            'updatedBy' => $this->updatedBy,
            'updatedAt' => $this->updatedAt,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'subject', $this->subject])
            ->andFilterWhere(['like', 'twMessage', $this->twMessage])
            ->andFilterWhere(['like', 'fbMessage', $this->fbMessage])
            ->andFilterWhere(['like', 'smsMessage', $this->smsMessage])
            ->andFilterWhere(['like', 'lnMessage', $this->lnMessage])
            ->andFilterWhere(['like', 'lnSubject', $this->lnSubject])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'dragDropMessageCode', $this->dragDropMessageCode]);

        return $dataProvider;
    }
}
