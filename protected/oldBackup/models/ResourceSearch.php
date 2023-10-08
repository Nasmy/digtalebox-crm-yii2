<?php

namespace app\models;

use app\components\WebUser;
use app\models\Resource;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ResourceSearch represents the model behind the search form of `app\models\Resource`.
 */
class ResourceSearch extends Resource
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'type', 'size', 'status', 'createdBy', 'updatedBy'], 'integer'],
            [['title', 'description', 'tag', 'fileName', 'createdAt', 'updatedAt'], 'safe'],
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
        $query = Resource::find();
        $this->load($params);
        if ($params) {
            $this->fromDate = (!empty($params['ResourceSearch']['fromDate'])) ? trim($params['ResourceSearch']['fromDate']) : $this->fromDate;
            $this->toDate = (!empty($params['ResourceSearch']['toDate'])) ? trim($params['ResourceSearch']['toDate']) : $this->toDate;
            $this->title = (!empty($params['ResourceSearch']['title'])) ? trim($params['ResourceSearch']['title']) : $this->title;
            $this->tag = (!empty($params['ResourceSearch']['tag'])) ? trim($params['ResourceSearch']['tag']) : $this->tag;
            $this->status = (!empty($params['ResourceSearch']['status'])) ? trim($params['ResourceSearch']['status']) : $this->status;
            $this->type = (!empty($params['ResourceSearch']['type'])) ? trim($params['ResourceSearch']['type']) : $this->type;
            $this->createdBy = (!empty($params['ResourceSearch']['createdBy'])) ? trim($params['ResourceSearch']['createdBy']) : $this->createdBy;
        }

        if (!empty($this->fromDate) && empty($this->toDate)) {
            $query->andFilterWhere(['>', 'createdAt', $this->fromDate]);
        } else if (empty($this->fromDate) && !empty($this->toDate)) {
            $query->andFilterWhere(['<', 'createdAt', $this->toDate]);
        }
        if (!empty($this->title)) {
            $query->andFilterWhere(['like', 'title', $this->title]);
        }
        if (!empty($this->createdBy)) {
            $query->andFilterWhere(['=', 'createdBy', $this->createdBy]);
        }
        if (!empty($this->tag)) {
            $query->andFilterWhere(['like', 'tag', $this->tag]);
        }
        if (!empty($this->status)) {
            $query->andFilterWhere(['=', 'status', $this->status]);
        }
        if (!empty($this->type)) {
            $query->andFilterWhere(['=', 'type', $this->type]);
        }

        if (\Yii::$app->session->get('is_super_admin') || \Yii::$app->user->checkUserPermissions(WebUser::RESOURCE)) {
            // List all resources
        } else {
            // List only approved resources
            $query->where(['=', 'status', self::APPROVED]);
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 10, 'route' => 'resource/admin'],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ],

        ]);
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        return $dataProvider;
    }

}
