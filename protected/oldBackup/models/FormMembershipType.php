<?php

namespace app\models;

use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "formmembershiptype".
 *
 * @property int $id
 * @property string $title
 * @property string $fee
 */
class FormMembershipType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'FormMembershipType';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'fee'], 'required'],
            [['fee'], 'number'],
            [['title'], 'string', 'max' => 64],
            [['fee'], 'number','integerOnly'=>false,'min'=>1,'max'=>1000],
            [['id','title','fee'],'safe','on'=>'search']

        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => Yii::t('messages','Title'),
            'fee' => Yii::t('messages','Fee'),
        ];
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
    public function search()
    {

        $query = FormMembershipType::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [ 'pageSize' => 10 ],
        ]);

        $query->filterWhere([
            'id' => $this->id,
            'title' => $this->title,
            'fee',$this->fee,

        ]);

        $query->andFilterWhere(['like', 'title', $this->title]);

        return $dataProvider;
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return FormMembershipType the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    /**
     * {@inheritdoc}
     * @return FormMembershipTypeQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new FormMembershipTypeQuery(get_called_class());
    }
}
