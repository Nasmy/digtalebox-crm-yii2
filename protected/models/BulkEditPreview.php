<?php

namespace app\models;

use app\components\Validations\LocationEscapeSpecialCharacters;
use Yii;
use yii\base\Model;
use yii\db\Query;


class BulkEditPreview extends Model
{
    public $zip;
    public $gender;
    public $city;
    public $countryCode;
    public $keywords;
    public $notes;
    public $userType;
    public $emailStatus;
    public $isUnsubEmail;

    /**
     * Declares the validation rules.
     * The rules state that username and password are required,
     * and password needs to be authenticated.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return [
            [['gender,zip,city,countryCode,keywords,notes,userType,emailStatus,isUnsubEmail'], 'safe'],
            [['zip,city'], LocationEscapeSpecialCharacters::className()],
            [['zip'],'string', 'max' => 15],
            [['city'], 'string', 'max'=>50],
            [[[['gender', 'in'], 'range'=> User::ASEXUAL, User::FEMALE, User::MALE],'allowEmpty' => true], 'message' => 'Please enter a valid value for {attribute}.'],
        ];
    }
    /**
     * Validate strings to avoid ; character
     */
    public function escapeSpecialCharacters()
    {
        $notAllowedList = array(';', '@', '~');
        foreach ($notAllowedList as $letter) {
            if (strpos($this->zip, $letter) !== false && !isset($this->errors['zip'])) {
                $this->addError('zip', Yii::t('messages', 'Zip contains not allowed characters'));
            }

            if (strpos($this->city, $letter) !== false && !isset($this->errors['city'])) {
                $this->addError('city', Yii::t('messages', 'City contains not allowed characters'));
            }

        }
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'gender' => Yii::t('messages', 'Gender'),
            'zip' => Yii::t('messages', 'Zip'),
            'countryCode' => Yii::t('messages', 'Country'),
            'userType' => Yii::t('messages', 'Category'),
            'keywords' => Yii::t('messages', 'Keywords'),
            'notes' => Yii::t('messages', 'Notes'),
            'city' => Yii::t('messages', 'City'),
            'emailStatus' => Yii::t('messages', 'Email Status'),
            'isUnsubEmail' => Yii::t('messages', 'Unsubscribe Email'),
        );

    }


    public function getLastError()
    {
        $errorsArr = $this->getErrors();

        if (count($errorsArr) > 0) {
            $errors = array_values($errorsArr);
            return $errors[0][0];
        }
        return '';
    }
}
