<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "FormMembershipDonation".
 *
 * @property int $id
 * @property int $formId
 * @property int|null $userId User table id
 * @property string|null $memberType 1:Young, 2:Single, 3:Couple
 * @property float|null $memberFee member fee associated with the transaction
 * @property float|null $donationFee donation fee associated with the transaction
 * @property string|null $payerEmail Customer's primary email address. Use this email to provide any credits.
 * @property string|null $stripeCustomerId Customer Id returned from Stripe
 * @property string $createdAt
 */
class FormMembershipDonation extends \yii\db\ActiveRecord
{
    public $firstName;
    public $lastName;
    public $memberDonationType;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'FormMembershipDonation';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['formId','paymentDate','createdAt'], 'required'],
            [['id'], 'integer'],
            [['userId','addressZip','contactPhone'],'string','max'=>20],
            [['memberFee', 'donationFee'], 'number','max' => 10],
            [['createdAt'], 'safe'],
            [['receiverId'], 'string', 'max' => 13],
            [['receiverEmail','payerEmail'], 'string', 'max' => 127],
            [['firstName','lastName','addressCountry'], 'string', 'max' => 64],
            [['addressName'], 'string', 'max' => 128],
            [['addressStreet'], 'string', 'max' => 200],
            [['addressCity','addressState'], 'string', 'max' => 40],
            [['addressCountryCode','residenceCountry'], 'string', 'max' => 2],
            [['addressStatus','paymentType'], 'string', 'max' => 15],
            [['id','userId','memberType','memberFee','donationFee','payerEmail','memberDonationType','firstName','lastName','createdAt'], 'safe', 'on' => 'search'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'formId' => 'Form ID',
            'userId' => 'User ID',
            'memberType' => Yii::t('messages','Member Type'),
            'memberFee' => Yii::t('messages','Member Fee'),
            'donationFee' => Yii::t('messages','Donation Fee'),
            'payerEmail' => Yii::t('messages','Payer Email'),
            'stripeCustomerId' => Yii::t('messages','Stripe Customer ID'),
            'createdAt' => Yii::t('messages','Created At'),
        ];
    }

    /**
     * {@inheritdoc}
     * @return FormMembershipDonationQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new FormMembershipDonationQuery(get_called_class());
    }
}
