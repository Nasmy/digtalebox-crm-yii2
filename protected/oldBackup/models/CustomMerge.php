<?php

namespace app\models;
use app\components\RActiveRecord;
use Yii;
/**
 * CustomMerge class.
 */
class CustomMerge extends Form
{
    public $parentId;
    public $childId;
    public $email;
    public $mobile;
    public $zip;
    public $address1;
    public $gender;
    public $city;
    public $dateOfBirth;
    public $keywords;
    public $notes;
    public $countryCode;
    public $customField;



    /**
     * Declares the validation rules.
     * The rules state that username and password are required,
     * and password needs to be authenticated.
     */
    public function rules()
    {
        return array(
            array('email, mobile, zip, address1, gender, city, dateOfBirth, keywords, notes, countryCode', 'required'),

        );
    }


    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'address1' => Yii::t('messages', 'Street Address'),
            'mobile' => Yii::t('messages', 'Mobile'),
            'email' => Yii::t('messages', 'Email'),
            'gender' => Yii::t('messages', 'Gender'),
            'zip' => Yii::t('messages', 'Zip'),
            'countryCode' => Yii::t('messages', 'Country'),
            'userType' => Yii::t('messages', 'Category'),
            'dateOfBirth' => Yii::t('messages', 'Date of Birth'),
            'keywords' => Yii::t('messages', 'Keywords'),
            'notes' => Yii::t('messages', 'Notes'),
            'city' => Yii::t('messages', 'City'),
            'countryCode' => Yii::t('messages', 'Country'),
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

    /*
     * Used to get potential category based on two category types
     * */
    public static function getPotentialCategory($cat1, $cat2){

        $result = User::SUPPORTER;

        if($cat1 == User::PROSPECT && $cat2 == User::PROSPECT){
            $result = User::PROSPECT;
        }
        else if($cat1 == User::NON_SUPPORTER && $cat2 == User::NON_SUPPORTER){
            $result = User::NON_SUPPORTER;
        }
        else if($cat1 == User::NON_SUPPORTER && $cat2 == User::PROSPECT || $cat1 == User::PROSPECT && $cat2 == User::NON_SUPPORTER){
            $result = User::NON_SUPPORTER;
        }
        else if($cat1 == User::UNKNOWN || $cat2 == User::UNKNOWN){
            $result = User::UNKNOWN;
        }
        else { //supporter
            $result = User::SUPPORTER;
        }

        return $result;

    }

    /**
     * @description Merge parent keyword and child keyword if any unique values
     */
    public function combineKeywords($parentKeyword, $childKeyword) {
        $parentChildMerge = array();
        if(!empty($parentKeyword) && !empty($childKeyword)) { // check is there any N/A value
            if($parentKeyword === null || $childKeyword === null) {
                return $parentChildMerge;
            }
            if(array_unique(array_merge($parentKeyword, $childKeyword))) { // Check is there any unique values
                $parentChildMerge = array_unique(array_merge($parentKeyword, $childKeyword));
            }
        }

        return $parentChildMerge;
    }

    /**
     * @param $parentNote
     * @param $childNote
     * @description Merge parent note and child note if not any N/A
     */

    public function combineNotes($parentNotes, $childNotes) {
        $parentChildMerge = null;
        if(!empty($parentNotes) && !empty($childNotes)) {
            if($parentNotes === null || $childNotes === null) {
                return $parentChildMerge;
            } else {
                $parentChildMerge = $parentNotes .' '. '&'.' '. $childNotes;
            }
        }
        return $parentChildMerge;
    }

}
