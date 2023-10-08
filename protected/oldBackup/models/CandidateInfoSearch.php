<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\CandidateInfo;

/**
 * CandidateInfoSearch represents the model behind the search form of `app\models\CandidateInfo`.
 */
class CandidateInfoSearch extends CandidateInfo
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'themeStyle'], 'integer'],
            [['profImageName', 'volunteerBgImageName', 'slogan', 'introduction', 'promoText', 'signupFields', 'frontImages', 'aboutText', 'headerText', 'bgImage'], 'safe'],
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
        $query = CandidateInfo::find();

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
            'id' => $this->id,
            'themeStyle' => $this->themeStyle,
        ]);

        $query->andFilterWhere(['like', 'profImageName', $this->profImageName])
            ->andFilterWhere(['like', 'volunteerBgImageName', $this->volunteerBgImageName])
            ->andFilterWhere(['like', 'slogan', $this->slogan])
            ->andFilterWhere(['like', 'introduction', $this->introduction])
            ->andFilterWhere(['like', 'promoText', $this->promoText])
            ->andFilterWhere(['like', 'signupFields', $this->signupFields])
            ->andFilterWhere(['like', 'frontImages', $this->frontImages])
            ->andFilterWhere(['like', 'aboutText', $this->aboutText])
            ->andFilterWhere(['like', 'headerText', $this->headerText])
            ->andFilterWhere(['like', 'bgImage', $this->bgImage]);

        return $dataProvider;
    }
}
