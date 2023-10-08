<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\FormMembershipDonation;
use yii\db\Query;

/**
 * FormMembershipDonationSearch represents the model behind the search form of `app\models\FormMembershipDonation`.
 */
class FormMembershipDonationSearch extends FormMembershipDonation
{
    public $firstName;
    public $lastName;
    public $memberDonationType;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'formId', 'userId'], 'integer'],
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
        $this->memberDonationType = isset($params['FormMembershipDonationSearch']['memberDonationType']) ? $params['FormMembershipDonationSearch']['memberDonationType'] : '';
        $query = new Query();

        $query->select(["SUM(md.memberFee) AS memberFee", "sum(md.donationFee) as donationFee", "md.payerEmail", "u.firstName", "u.lastName"])
            ->leftJoin('User u', 'md.userId = u.id');

        $query->groupBy('md.userId');

        switch ($this->memberDonationType) {
            case Form::MEMBER_ONLY:
                $query->having('donationFee IS NULL OR donationFee = ""');
                break;
            case Form::DONNER_ONLY:
                $query->having('memberFee IS NULL OR memberFee = ""');
                break;
            case Form::MEMBER:
                $query->having('memberFee IS NOT NULL');
                break;
            case Form::DONNER:
                $query->having('donationFee IS NOT NULL');
                break;
            default:
                $query->having('memberFee IS NOT NULL OR donationFee IS NOT NULL');
        }

        $query->from('FormMembershipDonation md');

        if (!empty($params) && isset($params['FormMembershipDonationSearch'])) {
            $query->filterWhere(['like', 'md.memberType', $params['FormMembershipDonationSearch']['memberType']])
                ->FilterWhere(['like', 'md.payerEmail', $params['FormMembershipDonationSearch']['payerEmail']])
                ->FilterWhere(['like', 'md.firstName', $params['FormMembershipDonationSearch']['firstName']])
                ->FilterWhere(['like', 'md.lastName', $params['FormMembershipDonationSearch']['lastName']]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);


        if (!$this->validate()) {
            return $dataProvider;
        }

        return $dataProvider;
    }
}
