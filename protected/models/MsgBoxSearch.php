<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Query;

/**
 * MsgBoxSearch represents the model behind the search form of `app\models\MsgBox`.
 */
class MsgBoxSearch extends MsgBox
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'senderUserId', 'receiverUserId', 'refMsgId', 'status', 'folder', 'criteriaId', 'totalRecipient', 'deliveredCount'], 'integer'],
            [['message', 'subject', 'dateTime', 'userlist'], 'safe'],
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
    public function searchSent($params)
    {
        $query = MsgBox::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [ 'pageSize' => 10 ],
            'sort' => [
                'defaultOrder' => [
                    'dateTime' => SORT_DESC,
                 ]
            ],

        ]);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->where(['!=', 'status', MsgBox::MSG_STATUS_DELETED]);

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'senderUserId' => $this->senderUserId,
            'receiverUserId' => $this->receiverUserId,
            'refMsgId' => $this->refMsgId,
            'dateTime' => $this->dateTime,
            'status' => $this->status,
            'folder' => $this->folder,
            'criteriaId' => $this->criteriaId,
            'totalRecipient' => $this->totalRecipient,
            'deliveredCount' => $this->deliveredCount,
        ]);

        if (!empty($params['MsgBox']['fromDate']) && empty($params['MsgBox']['toDate'])) {
            $query->andFilterWhere(['>','dateTime',$params['MsgBox']['fromDate']]);
        } else if(empty($params['MsgBox']['fromDate']) && !empty($params['MsgBox']['toDate'])){
            $query->andFilterWhere(['<','dateTime',$params['MsgBox']['toDate']]);
        } else if(!empty($params['MsgBox']['fromDate']) && !empty($params['MsgBox']['toDate'])){
            $query->andFilterWhere(['>','dateTime',$params['MsgBox']['fromDate']]);
            $query->andFilterWhere(['<','dateTime',$params['MsgBox']['toDate']]);
        }

        return $dataProvider;
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * Typical usecase:
     * - Initialize the model fields with values from filter form.
     * - Execute this method to get CActiveDataProvider instance which will filter
     * models according to data in model fields.
     * - Pass data provider to CGridView, CListView or any similar widget.
     *
     * @return CActiveDataProvider the data provider that can return the models
     * based on the search/filter conditions.
     */
    public function searchInbox($params) {
        // @todo Please modify the following code to remove attributes that should not be searched.
         $query = MsgBox::find();

        $query->filterWhere([
            'id'=>$this->id,
            'senderUserId'=>$this->senderUserId,
            'receiverUserId'=>$this->receiverUserId,
            'message'=>$this->message,
            'refMsgId'=>$this->refMsgId,
            'subject'=>$this->subject,
            'folder'=>$this->folder,
            'userlist'=>$this->userlist,
            'criteriaId'=>$this->criteriaId,
            ]);

        if (!empty($params['MsgBox']['fromDate']) && empty($params['MsgBox']['toDate'])) {
            $query->andFilterWhere(['>','dateTime',$params['MsgBox']['fromDate']]);
        } else if(empty($params['MsgBox']['fromDate']) && !empty($params['MsgBox']['toDate'])){
             $query->andFilterWhere(['<','dateTime',$params['MsgBox']['toDate']]);
        } else if(!empty($params['MsgBox']['fromDate']) && !empty($params['MsgBox']['toDate'])){
            $query->andFilterWhere(['>','dateTime',$params['MsgBox']['fromDate']]);
            $query->andFilterWhere(['<','dateTime',$params['MsgBox']['toDate']]);
        }

        // add conditions that should always apply here

        $dataProviderInbox = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [ 'pageSize' => 10 ],
            'sort' => [
                'defaultOrder' => [
                    'dateTime' => SORT_DESC,
                ]
            ],

        ]);
         return $dataProviderInbox ;

    }

}
