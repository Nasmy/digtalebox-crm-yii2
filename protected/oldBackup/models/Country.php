<?php

namespace app\models;

use app\components\RActiveRecord;
use Yii;

/**
 * This is the model class for table "Country".
 *
 * @property string $countryCode
 * @property string $countryName
 */
class Country extends RActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Country';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return [
            ['countryCode, countryName', 'required'],
            ['countryCode', 'length', 'max'=>3],
            ['countryName', 'length', 'max'=>64],
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            ['countryCode, countryName', 'safe', 'on'=>'search'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'countryCode' => Yii::t('app', 'Country Code'),
            'countryName' => Yii::t('app', 'Country Name'),
        ];
    }

    /**
     * Prepare country selecting dropdown menu.
     * @return array $options Country options
     */
    public static function getCountryDropdown()
    {
        $options = array();
        $options[''] = Yii::t('messages','- Country -');

        $models = Country::find()->orderBy('countryName',SORT_ASC)->all();

        foreach($models as $model) {
            $options[$model->countryCode] = Yii::t('messages', $model->countryName);
        }

        return $options;
    }

    /**
     * {@inheritdoc}
     * @return CountryQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CountryQuery(get_called_class());
    }

    public static function getContryByCode($countryCode)
    {
        $country = '';
        $model=Country::find()->where(['countryCode'=>$countryCode])->one();
        if($model===null)
            $country = 'N/A';
        else
            $country = $model->countryName;

        return $country;
    }
}
