<?php

namespace app\models;

use app\components\ToolKit;
use app\models\MapZone;
use app\models\Team;
use app\models\User;
use app\models\TwitterApi;
use app\models\FacebookApi;
use app\models\LinkedInApi;
use Yii;
use app\components\WebUser;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Query;


/**
 * UserSearch represents the model behind the search form of `app\models\User`.
 */
class UserMapSearch extends User
{
    const LOCATION_UPDATE = 1;
    const LOCATION_SEARCH = 0;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'gender', 'userType', 'signup', 'isSysUser', 'reqruiteCount', 'delStatus', 'isUnsubEmail', 'isManual', 'isSignupConfirmed', 'isMcContact', 'emailStatus', 'formId'], 'integer'],
            [['address1', 'mobile', 'name', 'firstName', 'lastName', 'username', 'password', 'email', 'zip', 'countryCode', 'joinedDate', 'signUpDate', 'supporterDate', 'dateOfBirth', 'keywords', 'city', 'longLat', 'profImage', 'notes', 'network', 'addressInvalidatedAt', 'pwResetToken', 'resetPasswordTime', 'createdAt', 'updatedAt'], 'safe'],
            [['totalDonations'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return User::scenarios();
    }


    /**
     * @param $params
     * @description Search People for map grid
     * @return mixed
     */
    public function searchPeopleMap($params)
    {
        $query = User::find();
        $query->select("t.*,CONCAT_WS(' ',`t`.address1,`t`.`zip`, `t`.`city`, `c`.`countryName`) AS `fullAddress`,YEAR(CURDATE()) - YEAR(t.dateOfBirth) AS `age` ");
        $query->from('User t');
        $query->join('LEFT JOIN', 'Country c', 't.countryCode = c.countryCode');
        return $this->filterMapData($query, $params);
    }

    /**
     * @param array $params
     * @description Search locations for display in map
     * @return mixed
     */
    public function searchLocationData($params = [], $type = self::LOCATION_SEARCH)
    {
        $query = new Query();
        $query->select(['t.id', 'SUBSTRING_INDEX(`longLat`, ",", 1) as `longitude`', 'SUBSTRING_INDEX(`longLat`, ",", -1) as `latitude`', 'CONCAT_WS(" ", `address1`,`zip`, `city`, `c`.`countryName`) AS `fullAddress`', 'YEAR(CURDATE()) - YEAR(t.dateOfBirth) AS `age`']);
        $query->from('User t');
        $query->join('LEFT JOIN', 'Country c', 't.countryCode = c.countryCode');
        if($type != self::LOCATION_UPDATE) {
            $results = $this->filterMapData($query, $params);
            $results->limit(100000);
            return $results->all();
        }
        $query->where(['not', ['longLat' => null]]);
        $query->andWhere(['geoPoint'=>null]);
        return $query->all();
    }

    /**
     * @param $query
     * @param $params
     * @description Common logics for filter data related to map and grid.
     * @return mixed
     */
    public function filterMapData($query, $params)
    {
        $query->where(['not', ['longLat' => null]]);
        $query->andWhere("t.userType NOT IN ('" . User::SUPER_ADMIN . "','" . User::POLITICIAN . "','0') AND t.isSysUser = '" . User::NOT_SYSTEM_USER . "'");
        // allow regional admin to export his zip people
        $isRegional = Yii::$app->user->CheckUserType(WebUser::REGIONAL_ADMIN_NAME) && !Yii::$app->session->get('is_super_admin');
        if ($isRegional) {
            $user = User::find()->where(['id' => Yii::$app->user->id])->one();
            $query->andOnCondition('zip LIKE "' . $user->zip . '%"');
            // regional admin can see only zip based
        }
        $this->load($params);
        if (!($this->load($params) && $this->validate())) {
            if ($params) {
                $this->firstName = (!empty($params['User']['firstName'])) ? trim($params['User']['firstName']) : $this->firstName;
                $this->lastName = (!empty($params['User']['lastName'])) ? trim($params['User']['lastName']) : $this->lastName;
                $this->city = (!empty($params['User']['city'])) ? trim($params['User']['city']) : $this->city;
                $this->countryCode = (!empty($params['User']['countryCode'])) ? trim($params['User']['countryCode']) : $this->countryCode;
                $this->gender = (isset($params['User']['gender']) && $params['User']['gender'] >= 0) ? trim($params['User']['gender']) : '';
                $this->email = (!empty($params['User']['email'])) ? trim($params['User']['email']) : $this->email;
                $this->mobile = (!empty($params['User']['mobile'])) ? trim($params['User']['mobile']) : $this->mobile;
                $this->userType = (!empty($params['User']['userType'])) ? trim($params['User']['userType']) : $this->userType;
                $this->zip = (!empty($params['User']['zip'])) ? trim($params['User']['zip']) : $this->zip;
                $this->keywords = (!empty($params['User']['keywords'])) ? $params['User']['keywords'] : $this->keywords;
                $this->searchType = (!empty($params['User']['searchType'])) ? $params['User']['searchType'] : $this->searchType;
                $this->keywordsExclude = (!empty($params['User']['keywordsExclude'])) ? $params['User']['keywordsExclude'] : $this->keywordsExclude;
                $this->age = (!empty($params['User']['age'])) ? trim($params['User']['age']) : $this->age;
                $this->network = (!empty($params['User']['network'])) ? $params['User']['network'] : $this->network;
                $this->emailStatus = (!empty($params['User']['emailStatus'])) ? trim($params['User']['emailStatus']) : $this->emailStatus;
                $this->formId = (!empty($params['User']['formId'])) ? trim($params['User']['formId']) : $this->formId;
                $this->fullAddress = (!empty($params['User']['fullAddress'])) ? $params['User']['fullAddress'] : $this->fullAddress;
                // condition to filter
                if (isset($this->emailStatus) && $this->emailStatus != '') {
                    if ($this->emailStatus == User::UNSUBSCRIBE_EMAIL) {
                        $this->subEmail = User::UNSUBSCRIBED_EMAILS;
                    } elseif ($this->emailStatus == User::BOUNCED_EMAIL || $this->emailStatus == User::BLOCKED_EMAIL) {
                        $this->checkEmail = $this->emailStatus;
                    }
                }
                if (!empty($this->firstName)) {
                    $query->andWhere(['like', 'firstName', $this->firstName]);
                }
                if (!empty($this->lastName)) {
                    $query->andWhere(['like', 'lastName', $this->lastName]);
                }
                if (!empty($this->city)) {
                    $query->andWhere(['like', 'city', $this->city]);
                }
                if (!empty($this->countryCode)) {
                    $query->andWhere(['t.countryCode' => $this->countryCode]);
                }
                if ($this->gender != '') {
                    $query->andFilterWhere(['gender' => $this->gender]);
                }
                if (!empty($this->email)) {
                    $query->andFilterWhere(['like', 'email', $this->email]);
                }
                if (!empty($this->mobile)) {
                    $query->andWhere(['like', 'mobile', $this->mobile]);
                }
                if (!empty($this->userType)) {
                    $query->andWhere(['userType' => $this->userType]);
                }
                if (!empty($this->zip)) {
                    $query->andWhere('zip LIKE "' . $this->zip . '%"');
                }
                if (!empty($this->emailStatus)) {
                    $query->andWhere(['emailStatus' => $this->emailStatus]);
                }
                if (!empty($this->formId)) {
                    $query->andWhere(['formId' => $this->formId]);
                }
                if (!ToolKit::isEmpty($this->fullAddress)) {
                    $fullAddress = addslashes($this->fullAddress);
                    $query->andWhere("address1 IS NOT NULL");
                    $query->andWhere("concat(LOWER(trim(address1)), ', ',t.zip, ' ',LOWER(trim(t.city))) like '%" . strtolower($fullAddress) . "%'");
                    // $query->andWhere("address1 IS NOT NULL AND zip IS NOT NULL AND city IS NOT NULL AND t.countryCode IS NOT NULL");
                    // $query->andWhere("concat(trim(t.address1), ', ',t.zip, ' ',trim(t.city), ', ',c.countryName ) like '" . $fullAddress . "%'");
                    //Only for France Ex : 4 Route de Buzancais, 36500 Sainte-Gemme, France
                    //$criteria->addCondition("concat(t.address1, ', ', t.zip, ' ', t.city, ', ', c.countryName ) regexp '^[0-9]+,? [^,]+, [0-9]+,? [^,]+, [a-zA-Z]+$'");
                    // For France, Belgium, Sri Lanka
                    // $query->andWhere("concat(t.address1, ', ', t.zip, ' ', t.city, ', ', c.countryName ) regexp '^([0-9a-zA-Z/]+, )?[^-\s][^,]+,( [a-zA-z ]+,)? [0-9]+,? [^,]+,( [a-zA-Z]+)*$'");
                }
                if (!empty($this->keywords) && is_array($this->keywords)) {
                    $con = "";
                    $keywordCount = count($this->keywords);
                    if ($keywordCount > 1) {
                        if ($this->searchType == self::SEARCH_NORMAL) {
                            foreach ($this->keywords as $key) {
                                $subcon = ' FIND_IN_SET("' . $key . '", keywords) > 0 ';
                                $con .= empty($con) ? $subcon : " OR " . $subcon;
                            }
                            $query->andWhere($con);
                        } else if ($this->searchType == self::SEARCH_STRICT) {
                            foreach ($this->keywords as $key) {
                                $subcon = ' FIND_IN_SET("' . $key . '", keywords) > 0 ';
                                $con .= empty($con) ? $subcon : " AND " . $subcon;
                            }
                            $query->andWhere($con);
                        } else if ($this->searchType == self::SEARCH_EXCLUDE) {
                            foreach ($this->keywords as $key) {
                                $subcon = ' FIND_IN_SET(' . $key . ', keywords) > 0 ';
                                $con .= empty($con) ? $subcon : " AND " . $subcon;
                            }
                            $keywordExcludeCount = count($this->keywordsExclude);
                            if ($keywordExcludeCount > 1) {
                                foreach ($this->keywordsExclude as $key) {
                                    $subcon = ' FIND_IN_SET(' . $key . ', keywords) = 0 ';
                                    $con .= " AND " . $subcon;
                                }
                            } else {
                                $subcon = ' FIND_IN_SET(' . $this->keywordsExclude[0] . ', keywords) = 0 ';
                                $con .= " AND " . $subcon;
                            }
                            $query->andWhere($con);
                        } else if ('' == $this->searchType) {
                            foreach ($this->keywords as $key) {
                                $subcon = ' FIND_IN_SET("' . $key . '", keywords) > 0 ';
                                $con .= empty($con) ? $subcon : " OR " . $subcon;
                            }
                            $query->andWhere($con);
                        }
                    } else {

                        if ($this->searchType == self::SEARCH_NORMAL) {
                            $query->andWhere('FIND_IN_SET("' . $this->keywords[0] . '", keywords) > 0');
                        } else if ($this->searchType == self::SEARCH_STRICT) {
                            $con = 'keywords like ' . $this->keywords[0] . ' or keywords like ",' . $this->keywords[0] . '" or keywords like ",' . $this->keywords[0] . '," or keywords like "' . $this->keywords[0] . ',"'; //todo: quick fix
                            $query->andWhere('(' . $con . ')');
                        } else if ($this->searchType == self::SEARCH_EXCLUDE) {
                            $keywordExcludeCount = count($this->keywordsExclude);
                            if ($keywordExcludeCount > 1) {
                                $con = ' FIND_IN_SET(' . $this->keywords[0] . ', keywords) > 0 ';
                                foreach ($this->keywordsExclude as $key) {
                                    $subcon = ' FIND_IN_SET(' . $key . ', keywords) = 0 ';
                                    $con .= " AND " . $subcon;
                                }
                                $query->andWhere($con);
                            } else {
                                $query->andWhere('FIND_IN_SET(' . $this->keywords[0] . ', keywords) > 0 AND FIND_IN_SET(' . $this->keywordsExclude[0] . ', keywords) = 0');
                            }
                        } else if ('' == $this->searchType) {
                            $query->andWhere('FIND_IN_SET("' . $this->keywords[0] . '", keywords) > 0');
                        }
                    }
                } else {
                    $query->andFilterCompare('keywords', $this->keywords);
                }
                if (!empty($this->teams)) {
                    $this->teams = implode(",", $this->teams);
                    $query->join('INNER JOIN', 'TeamMember', 'TeamMember.memberUserId=t.id');
                    $query->andOnCondition("TeamMember.teamId IN ({$this->teams})");
                } else {
                    if (Yii::$app->user->checkAccess(WebUser::TEAM_LEAD_ROLE_NAME) && !Yii::$app->session->get('is_super_admin')) {
                        $teams = Team::getTeamsByLogedUser();
                        if (empty($teams)) {
                            $teams = array('0');
                        }
                        $query->join('INNER JOIN', 'TeamMember', 'TeamMember.memberUserId=t.id');
                        $query->andOnCondition('TeamMember.teamId IN (' . implode(array_keys($teams)) . ')');
                    }
                }
                if (!empty($this->age)) {
                    if (is_numeric($this->age)) {
                        $query->andWhere('YEAR(dateOfBirth) = YEAR(DATE_ADD(CURDATE(), INTERVAL -' . $this->age . ' YEAR))');
                    } else {
                        $Age = explode("-", $this->age);
                        if ($Age[1] > 150 or $Age[0] > 150) {
                            $this->age = '0-0';
                        }
                        $tmpAge = explode("-", $this->age);
                        $query->andWhere('YEAR(dateOfBirth) BETWEEN YEAR(DATE_ADD(CURDATE(), INTERVAL -' . $tmpAge[1] . ' YEAR)) AND YEAR(DATE_ADD(CURDATE() ,INTERVAL -' . $tmpAge[0] . ' YEAR))');
                    }
                }
                $isRegional = Yii::$app->user->CheckUserType(WebUser::REGIONAL_ADMIN_NAME) && !Yii::$app->session->get('is_super_admin');
                if ($isRegional) {
                    $user = User::find()->where(['id' => Yii::$app->user->id])->one();
                    $query->andOnCondition('zip LIKE "' . $user->zip . '%"'); // regional admin can see only zip based
                }
            }
        }
        return $query;
    }

    public function unserializeForm($str)
    {
        $returndata = array();
        $strArray = explode("&", $str);
        $i = 0;
        foreach ($strArray as $item) {
            $array = explode("=", $item);
            $returndata[$array[0]] = $array[1];
        }

        return $returndata;
    }
}
