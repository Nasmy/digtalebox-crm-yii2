<?php

namespace app\models;

use app\components\ToolKit;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * SearchCriteriaSearch represents the model behind the search form of `app\models\SearchCriteria`.
 */
class SearchCriteriaSearch extends SearchCriteria
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'isDisplayKeywords2', 'gender', 'userType', 'emailStatus', 'formId', 'excludeFbPersonalContacts', 'critetiaType', 'createdBy', 'updatedBy'], 'integer'],
            [['criteriaName'], 'safe', 'on' => 'search'],
            [['name', 'firstName', 'lastName', 'email', 'mobile', 'mapZone', 'keywords', 'keywordsExclude', 'searchType', 'keywordsExclude2', 'searchType2', 'keywords2', 'teams', 'zip', 'fullAddress', 'city', 'countryCode', 'criteriaName', 'age', 'network', 'date', 'createdAt', 'updatedAt'], 'safe', 'on' => 'search'],
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
        if (isset($_POST)) {
            $params = $_POST;
        }

        $query = SearchCriteria::find();

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
            'name' => $this->name,
            'keywords' => $this->keywords,
            'teams' => $this->teams,
            'gender' => $this->gender,
            'zip' => $this->zip,
            'city' => $this->city,
            'userType' => $this->userType,
            'critetiaType' => $this->critetiaType,
        ]);

        $isSameDay = false;

        if (!empty($params['SearchCriteriaSearch'])) {
            if (!ToolKit::isEmpty($params['SearchCriteriaSearch']['toDate']) && !ToolKit::isEmpty($params['SearchCriteriaSearch']['fromDate']) && $params['SearchCriteriaSearch']['toDate'] === $params['SearchCriteriaSearch']['fromDate']) {
                $isSameDay = true;
                $query->andFilterWhere(['=', 'DATE_FORMAT(createdAt, \'%Y-%m-%d\')', $params['SearchCriteriaSearch']['toDate']]);
            }

            if (!$isSameDay && !ToolKit::isEmpty($params['SearchCriteriaSearch']['toDate'])) {
                $query->andFilterWhere(['<=', 'DATE_FORMAT(createdAt, \'%Y-%m-%d\')', $params['SearchCriteriaSearch']['toDate']]);
            }

            if (!$isSameDay && !ToolKit::isEmpty($params['SearchCriteriaSearch']['fromDate'])) {
                $query->andFilterWhere(['>=', 'DATE_FORMAT(createdAt, \'%Y-%m-%d\')', $params['SearchCriteriaSearch']['fromDate']]);
            }

            if (!ToolKit::isEmpty($params['SearchCriteriaSearch']['criteriaName'])) {
                $query->andFilterWhere(['like', 'criteriaName', $params['SearchCriteriaSearch']['criteriaName']]);
            }
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 10, 'route' => 'search-criteria/admin'],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ],

        ]);

        return $dataProvider;
    }
}
