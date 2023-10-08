<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\CustomField;

/**
 * CustomFieldSearch represents the model behind the search form of `app\models\CustomField`.
 */
class CustomFieldSearch extends CustomField
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'customTypeId', 'sortOrder'], 'integer'],
            [['fieldName', 'relatedTable', 'defaultValue', 'enabled', 'listItemTag', 'required', 'onCreate', 'onEdit', 'onView', 'listValues', 'label', 'htmlOptions'], 'safe'],
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
        $query = CustomField::find();

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
            'customTypeId' => $this->customTypeId,
            'sortOrder' => $this->sortOrder,
        ]);

        $query->andFilterWhere(['like', 'fieldName', $this->fieldName])
            ->andFilterWhere(['like', 'relatedTable', $this->relatedTable])
            ->andFilterWhere(['like', 'defaultValue', $this->defaultValue])
            ->andFilterWhere(['like', 'enabled', $this->enabled])
            ->andFilterWhere(['like', 'listItemTag', $this->listItemTag])
            ->andFilterWhere(['like', 'required', $this->required])
            ->andFilterWhere(['like', 'onCreate', $this->onCreate])
            ->andFilterWhere(['like', 'onEdit', $this->onEdit])
            ->andFilterWhere(['like', 'onView', $this->onView])
            ->andFilterWhere(['like', 'listValues', $this->listValues])
            ->andFilterWhere(['like', 'label', $this->label])
            ->andFilterWhere(['like', 'htmlOptions', $this->htmlOptions]);

        return $dataProvider;
    }
}
