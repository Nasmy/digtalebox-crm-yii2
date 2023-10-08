<?php
namespace app\models;

use yii\base\Model;
use yii;
/**
 * LoginForm class.
 * LoginForm is the data structure for keeping
 * user login form data. It is used by the 'login' action of 'SiteController'.
 */
class FilePreview extends Model
{
    public $firstName;
    public $lastName;
    public $email;
    public $mobile;
    public $zip;
    public $address1;
    public $gender;
    public $city;
    public $countryCode;
    public $dateOfBirth;
    public $keywords;
    public $notes;
    public $userType;

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            [['firstName','lastName','email','mobile','zip','address1','gender','city','dateOfBirth','keywords','notes','countryCode','userType'], 'required'],
            [['firstName','email','mobile','zip','address1','gender','city','dateOfBirth','keywords','notes','countryCode','userType'], 'compare','compareAttribute' =>'lastName','operator' => '!=','message'  => '{attribute} must not be the same as {compareAttribute}'  ],
            [['lastName','email','mobile','zip','address1','gender','city','dateOfBirth','keywords','notes','countryCode','userType'], 'compare','compareAttribute' =>'firstName','operator' => '!=','message'  => '{attribute} must not be the same as {compareAttribute}'  ],
            [['firstName','lastName','mobile','zip','address1','gender','city','dateOfBirth','keywords','notes','countryCode','userType'], 'compare','compareAttribute' =>'email','operator' => '!=','message'  => '{attribute} must not be the same as {compareAttribute}'  ],
            [['firstName','lastName','email','zip','address1','gender','city','dateOfBirth','keywords','notes','countryCode','userType'], 'compare','compareAttribute' =>'mobile','operator' => '!=','message'  => '{attribute} must not be the same as {compareAttribute}'  ],
            [['firstName','lastName','email','mobile','address1','gender','city','dateOfBirth','keywords','notes','countryCode','userType'], 'compare','compareAttribute' =>'zip','operator' => '!=','message'  => '{attribute} must not be the same as {compareAttribute}'  ],
            [['firstName','lastName','email','mobile','zip','gender','city','dateOfBirth','keywords','notes','countryCode','userType'], 'compare','compareAttribute' =>'address1','operator' => '!=','message'  => '{attribute} must not be the same as {compareAttribute}'  ],
            [['firstName','lastName','email','mobile','address1','city','dateOfBirth','keywords','notes','countryCode','userType'], 'compare','compareAttribute' =>'gender','operator' => '!=','message'  => '{attribute} must not be the same as {compareAttribute}'  ],
            [['firstName','lastName','email','mobile','address1','gender','dateOfBirth','keywords','notes','countryCode','userType'], 'compare','compareAttribute' =>'city','operator' => '!=','message'  => '{attribute} must not be the same as {compareAttribute}'  ],
            [['firstName','lastName','email','mobile','address1','gender','city','keywords','notes','countryCode','userType'], 'compare','compareAttribute' =>'dateOfBirth','operator' => '!=','message'  => '{attribute} must not be the same as {compareAttribute}'  ],
            [['firstName','lastName','email','mobile','address1','gender','city','dateOfBirth','countryCode','userType'], 'compare','compareAttribute' =>'notes','operator' => '!=','message'  => '{attribute} must not be the same as {compareAttribute}'  ],
            [['firstName','lastName','email','mobile','address1','gender','city','dateOfBirth','notes','userType'], 'compare','compareAttribute' =>'countryCode','operator' => '!=','message'  => '{attribute} must not be the same as {compareAttribute}'  ],
            [['firstName','lastName','email','mobile','address1','gender','city','dateOfBirth','notes','countryCode'], 'compare','compareAttribute' =>'userType','operator' => '!=','message'  => '{attribute} must not be the same as {compareAttribute}'  ],
         ];
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'address1' => Yii::t('messages', 'Street Address'),
            'mobile' => Yii::t('messages', 'Mobile'),
            'firstName' => Yii::t('messages', 'First Name'),
            'lastName' => Yii::t('messages', 'Last Name'),
            'email' => Yii::t('messages', 'Email'),
            'gender' => Yii::t('messages', 'Gender'),
            'zip' => Yii::t('messages', 'Zip'),
            'countryCode' => Yii::t('messages', 'Country'),
            'userType' => Yii::t('messages', 'Category'),
            'dateOfBirth' => Yii::t('messages', 'Date of Birth'),
            'keywords' => Yii::t('messages', 'Keywords'),
            'notes' => Yii::t('messages', 'Notes'),
            'city' => Yii::t('messages', 'City'),
        ];
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
