<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Form;

/**
 * FormSearch represents the model behind the search form of `app\models\Form`.
 */
class FormSearch extends Form
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'templateId'], 'integer'],
            [['title', 'content', 'keywords', 'redirectAddress', 'enabled', 'notified', 'enablePayment', 'isMembership', 'isDonation', 'createdAt', 'updatedAt'], 'safe'],
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
        $query = Form::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 10],
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'content', $this->content])
            ->andFilterWhere(['like', 'createdAt', $this->createdAt])
            ->andFilterWhere(['like', 'keywords', $this->keywords])
            ->andFilterWhere(['like', 'redirectAddress', $this->redirectAddress])
            ->andFilterWhere(['like', 'enabled', $this->enabled])
            ->andFilterWhere(['like', 'notified', $this->notified])
            ->andFilterWhere(['like', 'enablePayment', $this->enablePayment])
            ->andFilterWhere(['like', 'isMembership', $this->isMembership])
            ->andFilterWhere(['like', 'isDonation', $this->isDonation]);

        return $dataProvider;
    }
}
