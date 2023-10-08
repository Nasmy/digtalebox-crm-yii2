<?php

namespace app\models;

use app\models\AuthItem;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * AuthItemSearch represents the model behind the search form of `app\models\AuthItem`.
 */
class AuthItemSearch extends AuthItem
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'description', 'category', 'bizrule', 'data'], 'safe'],
            [['type', 'isGenerated'], 'integer'],
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
    public function search($params = [])
    {
        if (!empty($params)) {
            $query = AuthItem::find()->where(['type' => $params['type']]);

        } else {
            $query = AuthItem::findAll();
        }

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'type' => $this->type,
            'isGenerated' => $this->isGenerated,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            // ->andFilterWhere(['like','type', $this->type])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'category', $this->category])
            ->andFilterWhere(['like', 'bizrule', $this->bizrule])
            ->andFilterWhere(['like', 'data', $this->data])
            ->orderBy('category');


        return $dataProvider;
    }

    /**
     * Prepare role type label whether system default role or custom role
     * @param string $roleName Role name
     * @return string Bootstrap label
     */
    public function getRoleTypeLabel($roleName)
    {
        if ($this->isDefaultRole($roleName)) {
            return '<span class="label label-important">' . \Yii::t('messages', 'System Default') . '</span>';
        } else {
            return '<span class="label label-success">' . \Yii::t('messages', 'User Defined') . '</span>';
        }
    }
}
