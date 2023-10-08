<?php

namespace app\models;

use app\models\AuthItem;
use yii\web\IdentityInterface;

class UserIdentity extends \yii\base\BaseObject implements \yii\web\IdentityInterface
{
    public $id;
    public $username;
    public $email;
    public $password;
    public $firstName;
    public $lastName;
    public $userType;
    public $isSysUser;
    public $authKey;
    public $isSignupConfirmed;
    // public $accessToken;


    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        try {
            $user = User::findOne(['id' => $id]);
            if (null != $user) {
                return new static([
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'firstName' => $user->firstName,
                    'lastName' => $user->lastName,
                    'userType' => $user->userType,

                ]);
            }
        } catch (Exception $e) {

        }

        return null;
    }
    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        foreach (self::$users as $user) {
            if ($user['accessToken'] === $token) {
                return new static($user);
            }
        }

        return null;
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        $user = User::find()->where(['username' => $username])->one();

        if (null != $user) {
            return new static([
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'password' => $user->password,
                'userType' => $user->userType,
                'isSysUser' => $user->isSysUser,
                'isSignupConfirmed' => $user->isSignupConfirmed
            ]);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->authKey === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        if(\Yii::$app->session->get('isBypass')) {
            return $this->password === $password;
        } else {
            return $this->password === User::encryptUserPassword($password);
        }
    }

    /**
     * Validate password for social login
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validateSocialLoginPassword($password) {
        return $this->password === $password;
    }




    /*public  function isSysUser($username) {
        $user = User::find()->where(['username' => $username])->one();
        if($user->isSysUser === User::SYSTEM_USER ) {
            return true;
        }
    }*/

}
