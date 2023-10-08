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
class UserSearch extends User
{
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
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = User::find();

        // add conditions that should always apply here
        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        $query->from('User u')
            ->select('u.*,')
            ->innerJoin('AuthAssignment AA', 'AA.userid=u.id')
            ->where('AA.itemname != :itemname ', [':itemname' => WebUser::SUPERADMIN_ROLE_NAME])
            ->andWhere('u.id != :currentUid ', [':currentUid' => Yii::$app->user->id])
            ->andFilterWhere([
                'id' => $this->id,
                'gender' => $this->gender,
                'joinedDate' => $this->joinedDate,
                'signUpDate' => $this->signUpDate,
                'supporterDate' => $this->supporterDate,
                'userType' => $this->userType,
                'signup' => $this->signup,
                'isSysUser' => $this->isSysUser,
                'dateOfBirth' => $this->dateOfBirth,
                'reqruiteCount' => $this->reqruiteCount,
                'delStatus' => $this->delStatus,
                'isUnsubEmail' => $this->isUnsubEmail,
                'isManual' => $this->isManual,
                'isSignupConfirmed' => $this->isSignupConfirmed,
                'totalDonations' => $this->totalDonations,
                'isMcContact' => $this->isMcContact,
                'emailStatus' => $this->emailStatus,
                'formId' => $this->formId,
                'addressInvalidatedAt' => $this->addressInvalidatedAt,
                'resetPasswordTime' => $this->resetPasswordTime,
                'createdAt' => $this->createdAt,
                'updatedAt' => $this->updatedAt,
            ]);

        $query->andFilterWhere(['like', 'address1', $this->address1])
            ->andFilterWhere(['like', 'mobile', $this->mobile])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'firstName', $this->firstName])
            ->andFilterWhere(['like', 'lastName', $this->lastName])
            ->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'password', $this->password])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'zip', $this->zip])
            ->andFilterWhere(['like', 'countryCode', $this->countryCode])
            ->andFilterWhere(['like', 'keywords', $this->keywords])
            ->andFilterWhere(['like', 'city', $this->city])
            ->andFilterWhere(['like', 'longLat', $this->longLat])
            ->andFilterWhere(['like', 'profImage', $this->profImage])
            ->andFilterWhere(['like', 'notes', $this->notes])
            ->andFilterWhere(['like', 'network', $this->network])
            ->andFilterWhere(['like', 'pwResetToken', $this->pwResetToken]);

        // allow regional admin to export his zip people
        $isRegional = Yii::$app->user->CheckUserType(WebUser::REGIONAL_ADMIN_NAME) && !Yii::$app->session->get('is_super_admin');
        if ($isRegional) {
            $user = User::find()->where(['id' => Yii::$app->user->id])->one();
            $query->andOnCondition('zip LIKE "' . $user->zip . '%"');
            // regional admin can see only zip based
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 10],
            'sort' => [
                'defaultOrder' => [
                    'createdAt' => SORT_DESC,
                ]
            ],

        ]);

        return $dataProvider;
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */


    public function searchPeople($params, $sort = null)
    {
        $query = User::find();
        // add conditions that should always apply here
        $query->select("t.*,CONCAT_WS(' ',`t`.address1,`t`.`zip`, `t`.`city`, `c`.`countryName`) AS `fullAddress`,YEAR(CURDATE()) - YEAR(t.dateOfBirth) AS `age` ");
        $query->from('User t');
        $query->join('LEFT JOIN', 'Country c', 't.countryCode = c.countryCode');
        $query->where("t.userType NOT IN ('" . User::SUPER_ADMIN . "','" . User::POLITICIAN . "','0') AND t.isSysUser = '" . User::NOT_SYSTEM_USER . "'");
        $this->load($params);
        // grid filtering conditions
        $query->FilterWhere([
            'id' => $this->id,
            'gender' => $this->gender,
            'joinedDate' => $this->joinedDate,
            'signUpDate' => $this->signUpDate,
            'supporterDate' => $this->supporterDate,
            'userType' => $this->userType,
            'signup' => $this->signup,
            'isSysUser' => $this->isSysUser,
            'dateOfBirth' => $this->dateOfBirth,
            'reqruiteCount' => $this->reqruiteCount,
            'delStatus' => $this->delStatus,
            'isUnsubEmail' => $this->isUnsubEmail,
            'isManual' => $this->isManual,
            'isSignupConfirmed' => $this->isSignupConfirmed,
            'totalDonations' => $this->totalDonations,
            'isMcContact' => $this->isMcContact,
            'emailStatus' => $this->emailStatus,
            'formId' => $this->formId,
            'addressInvalidatedAt' => $this->addressInvalidatedAt,
            'resetPasswordTime' => $this->resetPasswordTime,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'longLat' => $this->longLat,
        ]);
        $query->andFilterCompare('t.address1', $this->address1);
        $query->andFilterCompare('t.firstName', $this->firstName);
        $query->andFilterCompare('t.lastName', $this->lastName);
        $query->andFilterCompare('username', $this->username);
        $query->andFilterCompare('t.email', $this->email);
        $query->andFilterCompare('t.gender', $this->gender);
        $query->andFilterCompare('city', $this->city);
        $query->andFilterCompare('t.countryCode', $this->countryCode);
        $query->andFilterCompare('joinedDate', $this->joinedDate);
        $query->andFilterCompare('userType', $this->userType);
        $query->andFilterCompare('signup', $this->signup);
        $query->andFilterCompare('isSysUser', $this->isSysUser);
        $query->andFilterCompare('t.emailStatus', $this->checkEmail);
        $query->andFilterCompare('t.isUnsubEmail', $this->subEmail);
        $query->andFilterCompare('t.formId', $this->formId);
        $query->andFilterCompare('t.longLat', $this->longLat);

        // allow regional admin to export his zip people
        $isRegional = Yii::$app->user->CheckUserType(WebUser::REGIONAL_ADMIN_NAME) && !Yii::$app->session->get('is_super_admin');
        if ($isRegional) {
            $user = User::find()->where(['id' => Yii::$app->user->id])->one();
            $query->andOnCondition('zip LIKE "' . $user->zip . '%"');
            // regional admin can see only zip based
        }

        if ($params) {
            $user = Yii::$app->session->get('user');
            $customFields = Yii::$app->session->get('customFields');
            if ($user) {
                parse_str($user, $params);
                $this->firstName = (!empty($params['User']['firstName'])) ? trim($params['User']['firstName']) : $this->firstName;
                $this->lastName = (!empty($params['User']['lastName'])) ? trim($params['User']['lastName']) : $this->lastName;
                $this->city = (!empty($params['User']['city'])) ? trim($params['User']['city']) : $this->city;
                $this->countryCode = (!empty($params['User']['countryCode'])) ? trim($params['User']['countryCode']) : $this->countryCode;
                $this->gender = (isset($params['User']['gender']) && $params['User']['gender'] >= 0) ? trim($params['User']['gender']) : '';
                $this->email = (!empty($params['User']['email'])) ? trim($params['User']['email']) : $this->email;
                $this->mobile = (!empty($params['User']['mobile'])) ? trim($params['User']['mobile']) : $this->mobile;
                $this->userType = (!empty($params['User']['userType'])) ? trim($params['User']['userType']) : $this->userType;
                $this->zip = (!empty($params['User']['zip'])) ? trim($params['User']['zip']) : $this->zip;
                $this->searchType = (!empty($params['User']['searchType'])) ? $params['User']['searchType'] : $this->searchType;
                $this->searchType2 = (!empty($params['User']['searchType2'])) ? $params['User']['searchType2'] : $this->searchType2;
                $this->keywords = (!empty($params['User']['keywords'])) ? $params['User']['keywords'] : $this->keywords;
                $this->keywords2 = (!empty($params['User']['keywords2'])) ? $params['User']['keywords2'] : $this->keywords2;
                $this->keywordsExclude = (!empty($params['User']['keywordsExclude'])) ? $params['User']['keywordsExclude'] : $this->keywordsExclude;
                $this->keywordsExclude2 = (!empty($params['User']['keywordsExclude2'])) ? $params['User']['keywordsExclude2'] : $this->keywordsExclude2;
                $this->age = (!empty($params['User']['age'])) ? trim($params['User']['age']) : $this->age;
                $this->network = (!empty($params['User']['network'])) ? $params['User']['network'] : $this->network;
                $this->emailStatus = ($params['User']['emailStatus'] != '') ? $params['User']['emailStatus'] : '';
                $this->formId = (!empty($params['User']['formId'])) ? trim($params['User']['formId']) : $this->formId;
                $this->fullAddress = (!empty($params['User']['fullAddress'])) ? trim($params['User']['fullAddress']) : $this->fullAddress;
                $this->excludeFbPersonalContacts = (!empty($params['User']['excludeFbPersonalContacts'])) ? trim($params['User']['excludeFbPersonalContacts']) : $this->excludeFbPersonalContacts;
                $this->mapZone = (!empty($params['User']['mapZone'])) ? trim($params['User']['mapZone']) : $this->mapZone;
                $data = (isset($params['CustomValue'])) ? $params['CustomValue'] : null;
                if (!empty($customFields) && !is_null($data) && CustomField::issetCustomFields($data)) {
                    $this->customFieldData = user::setCustomSearchCriteria($params['CustomValue'], $customFields);
                }

                if (!empty($this->customFieldData)) {
                    User::getCustomSearchCriteria($query);
                }

                // condition to filter
                if ($this->emailStatus != '') {
                    if ($this->emailStatus == User::UNSUBSCRIBE_EMAIL) {
                        $this->subEmail = User::UNSUBSCRIBED_EMAILS;
                    } elseif ($this->emailStatus == User::BOUNCED_EMAIL || $this->emailStatus == User::BLOCKED_EMAIL) {
                        $this->checkEmail = $this->emailStatus;
                    }
                }

                if (!empty($this->firstName)) {
                    $query->andFilterWhere(['like', 'firstName', $this->firstName]);
                }
                if (!empty($this->lastName)) {
                    $query->andFilterWhere(['like', 'lastName', $this->lastName]);
                }
                if (!empty($this->city)) {
                    $query->andFilterWhere(['like', 'city', $this->city]);
                }
                if (!empty($this->countryCode)) {
                    $query->andFilterWhere(['t.countryCode' => $this->countryCode]);
                }
                if ($this->gender != '') {
                    $query->andFilterWhere(['gender' => $this->gender]);
                }
                if (!empty($this->email)) {
                    $query->andFilterWhere(['like', 'email', $this->email]);
                }
                if (!empty($this->mobile)) {
                    $query->andFilterWhere(['like', 'mobile', $this->mobile]);
                }
                if (!empty($this->userType)) {
                    $query->andFilterWhere(['userType' => $this->userType]);
                }
                if (!empty($this->zip)) {
                    $query->andOnCondition('zip LIKE "' . $this->zip . '%"');
                }
                if (!empty($this->checkEmail)) {
                    $query->andFilterWhere(['emailStatus' => $this->checkEmail]);
                }
                if (!empty($this->subEmail)) {
                    $query->andFilterWhere(['isUnsubEmail' => $this->subEmail]);
                }
                if (!empty($this->formId)) {
                    $query->andFilterWhere(['formId' => $this->formId]);
                }
                if (!ToolKit::isEmpty($this->fullAddress)) {
                    $fullAddress = addslashes($this->fullAddress);
                    $query->andWhere("address1 IS NOT NULL");
                    $query->andWhere("concat(LOWER(trim(address1)), ', ',t.zip, ' ',LOWER(trim(t.city))) like '%" . strtolower($fullAddress) . "%'");
                }
                if (!ToolKit::isEmpty($this->mapZone)) {
                    $mapZone = MapZone::findOne(['mapZone', $this->mapZone]);
                    // Search Attribute
                    $condition = 'longLat IS NOT NULL';
                    if (!is_null($mapZone['city']) && !empty($mapZone['city'])) {
                        $condition .= " AND city LIKE '" . $mapZone['city'] . "'";
                    }
                    if (!is_null($mapZone['gender']) && !ToolKit::isEmpty($mapZone['gender'])) {
                        $condition .= " AND t.gender = " . $mapZone['gender'];
                    }

                    if (!is_null($mapZone['countryCode']) && !ToolKit::isEmpty($mapZone['countryCode'])) {
                        $condition .= " AND t.countryCode = '" . $mapZone['countryCode'] . "'";
                    }

                    if (!is_null($mapZone['userType']) && !ToolKit::isEmpty($mapZone['userType'])) {
                        $condition .= " AND userType =" . $mapZone['userType'];
                    }

                    if (!is_null($mapZone['zip']) && !ToolKit::isEmpty($mapZone['zip'])) {
                        $condition .= " AND zip LIKE '" . $mapZone['zip'] . "%'";
                    }

                    if (!is_null($mapZone['firstName']) && !ToolKit::isEmpty($mapZone['firstName'])) {
                        $condition .= ' AND firstName LIKE "' . '%' . $mapZone['firstName'] . '%"';
                    }

                    if (!is_null($mapZone['lastName']) && !ToolKit::isEmpty($mapZone['lastName'])) {
                        $condition .= ' AND lastName LIKE "' . '%' . $mapZone['lastName'] . '%"';
                    }

                    if (!ToolKit::isEmpty($mapZone['fullAddress'])) {
                        $fullAddress = addslashes($mapZone['fullAddress']);
                        $condition .= " AND concat(trim(t.address1), ', ',t.zip, ' ',trim(t.city), ', ',c.countryName) like '" . $fullAddress . "%'";
                    }

                    if (!is_null($mapZone['age']) && !ToolKit::isEmpty($mapZone['age'])) {
                        if (is_numeric($mapZone['age'])) {
                            // $condition .= ' AND YEAR(dateOfBirth) = YEAR(DATE_ADD(CURDATE(), INTERVAL -' . $mapZone['age'] . ' YEAR))';
                        } else {
                            $pattern = '/^(?:100|\d{1,2})(?:\-\d{1,2})?$/';
                            if (0 == preg_match($pattern, $mapZone['age'])) {
                                $mapZone['age'] = '0-0';
                            }
                            $tmpAge = explode("-", $mapZone['age']);
                            $condition .= ' AND YEAR(dateOfBirth) BETWEEN YEAR(DATE_ADD(CURDATE(), INTERVAL -' . $tmpAge[1] . ' YEAR)) AND YEAR(DATE_ADD(CURDATE() ,INTERVAL -' . $tmpAge[0] . ' YEAR))';
                        }
                    }

                    // Keyword Fileter
                    if (!empty($mapZone['keywords'])) {
                        $resCon = $this->searchKeywords($mapZone);
                        $condition .= ' AND '. $resCon;
                    }

                    $turfArr = json_decode($mapZone['zoneLongLat'], true);
                    $coordinates = $turfArr[0]['coordinates'];
                    $polygonSet = $mapZone->createPolygon($coordinates);
                    $mapQuery = new Query();
                    $mapQuery->select('t.id');
                    $mapQuery->from('User t');
                    $mapQuery->join('LEFT JOIN', 'Country c', 'c.countryCode = t.countryCode');
                    $mapQuery->where("t.userType NOT IN ('" . User::SUPER_ADMIN . "','" . User::POLITICIAN . "','0') AND t.isSysUser = '" . User::NOT_SYSTEM_USER . "'");
                    $mapQuery->andwhere($condition);
                    $mapQuery->andWhere('t.countryCode IS NOT NULL');
                    $mapQuery->andWhere('concat(t.address1, \', \', t.zip, \' \', t.city, \', \', c.countryName ) regexp \'^([0-9a-zA-Z/]+, )?[^-\s][^,]+,( [a-zA-z ]+,)? [0-9]+,? [^,]+,( [a-zA-Z]+)*$\'');
                    $mapQuery->andWhere("ST_CONTAINS(ST_GEOMFROMTEXT('$polygonSet'), geoPoint)");
                    $rowsQuery = $mapQuery->all();
                    $matchingRecords = array_map(function ($ar) {return $ar['id'];}, $rowsQuery);


                    // $mapQuery->all();
                    if (!empty($matchingRecords)) {
                        $query->andOnCondition("longLat IS NOT NULL AND t.id IN (" . implode(',', $matchingRecords) . ")");
                    } else {
                        $query->andOnCondition("longLat IS NOT NULL AND t.id IN (0)"); // forcing to fail
                    }
                }

                // TODO Needs to refactor this code. Need to bring it to common logic
                if (!empty($this->keywords) && is_array($this->keywords)) {
                    $con = "";
                    $keywordCount = count($this->keywords);
                    if ($keywordCount > 1) {
                        if ($this->searchType == self::SEARCH_NORMAL) {
                            foreach ($this->keywords as $key) {
                                $subcon = ' FIND_IN_SET("' . $key . '", keywords) > 0 ';
                                $con .= empty($con) ? $subcon : " OR " . $subcon;
                            }
                            $query->andOnCondition($con);
                        } else if ($this->searchType == self::SEARCH_STRICT) {
                            foreach ($this->keywords as $key) {
                                $subcon = ' FIND_IN_SET(' . $key . ', keywords) > 0 ';
                                $con .= empty($con) ? $subcon : " AND " . $subcon;
                            }
                            $query->andOnCondition($con);
                        } else if ($this->searchType == self::SEARCH_EXCLUDE) {
                            foreach ($this->keywords as $key) {
                                $subcon = ' FIND_IN_SET(' . $key . ', keywords) > 0 ';
                                $con .= empty($con) ? $subcon : " OR " . $subcon;
                            }
                            $con = "(" . $con . ")";
                            if (isset($this->keywordsExclude)) {
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
                            }

                            $query->andOnCondition($con);
                        } else if ('' == $this->searchType) {
                            foreach ($this->keywords as $key) {
                                $subcon = ' FIND_IN_SET("' . $key . '", keywords) > 0 ';
                                $con .= empty($con) ? $subcon : " OR " . $subcon;
                            }
                            $query->andOnCondition($con);
                        }
                    } else {
                        if ($this->searchType == self::SEARCH_NORMAL) {
                            $query->andOnCondition('FIND_IN_SET("' . $this->keywords[0] . '", keywords) > 0');
                        } else if ($this->searchType == self::SEARCH_STRICT) {
                            $con = 'keywords like ' . $this->keywords[0] . ' or keywords like ",' . $this->keywords[0] . '" or keywords like ",' . $this->keywords[0] . '," or keywords like "' . $this->keywords[0] . ',"'; //todo: quick fix
                            $query->andOnCondition($con);
                        } else if ($this->searchType == self::SEARCH_EXCLUDE) {
                            if (isset($this->keywordsExclude)) {
                                $keywordExcludeCount = count($this->keywordsExclude);
                                if ($keywordExcludeCount > 1) {
                                    $con = ' FIND_IN_SET(' . $this->keywords[0] . ', keywords) > 0 ';
                                    foreach ($this->keywordsExclude as $key) {
                                        $subcon = ' FIND_IN_SET(' . $key . ', keywords) = 0 ';
                                        $con .= " AND " . $subcon;
                                    }
                                    $query->andOnCondition($con);
                                } else {
                                    $query->andOnCondition('FIND_IN_SET(' . $this->keywords[0] . ', keywords) > 0 AND FIND_IN_SET(' . $this->keywordsExclude[0] . ', keywords) = 0');
                                }
                            }
                        } else if ('' == $this->searchType) {
                            $query->andOnCondition('FIND_IN_SET("' . $this->keywords[0] . '", keywords) > 0');
                        }
                    }
                } else {
                    $query->andFilterCompare('keywords', $this->keywords);
                }
                if (!empty($this->keywords2) && is_array($this->keywords2)) {
                    $con = '';
                    $condition = '';
                    $keywordCount = count($this->keywords2);
                    if ($keywordCount > 1) {
                        if ($this->searchType2 == self::SEARCH_NORMAL) {
                            foreach ($this->keywords2 as $key) {
                                $subcon = ' FIND_IN_SET("' . $key . '", keywords) > 0 ';
                                $con .= empty($con) ? $subcon : " OR " . $subcon;
                            }
                            $condition = ' AND (' . $con . ')';
                        } else if ($this->searchType2 == self::SEARCH_STRICT) {
                            foreach ($this->keywords2 as $key) {
                                $subcon = ' FIND_IN_SET(' . $key . ', keywords) > 0 ';
                                $con .= empty($con) ? $subcon : " AND " . $subcon;
                            }
                            $condition = ' AND (' . $con . ')';

                        } else if ($this->searchType2 == self::SEARCH_EXCLUDE) {
                            foreach ($this->keywords2 as $key) {
                                $subcon = ' FIND_IN_SET(' . $key . ', keywords) > 0 ';
                                $con .= empty($con) ? $subcon : " OR " . $subcon;
                            }
                            $con = "(" . $con . ")";
                            if (isset($this->keywordsExclude2)) {
                                $keywordExcludeCount = count($this->keywordsExclude2);
                                if ($keywordExcludeCount > 1) {
                                    foreach ($this->keywordsExclude2 as $key) {
                                        $subcon = ' FIND_IN_SET(' . $key . ', keywords) = 0 ';
                                        $con .= " AND " . $subcon;
                                    }
                                } else {
                                    $subcon = ' FIND_IN_SET(' . $this->keywordsExclude2[0] . ', keywords) = 0 ';
                                    $con .= " AND " . $subcon;
                                }
                            }
                            $condition = ' AND (' . $con . ')';
                        } else if ('' == $this->searchType2) {
                            foreach ($this->keywords2 as $key) {
                                $subcon = ' FIND_IN_SET("' . $key . '", keywords) > 0 ';
                                $con .= empty($con) ? $subcon : " OR " . $subcon;
                            }
                            $condition = ' AND (' . $con . ')';
                        }
                    } else {
                        if ($this->searchType2 == self::SEARCH_NORMAL) {
                            $condition = ' AND FIND_IN_SET(' . $this->keywords2[0] . ', keywords) > 0';
                        } else if ($this->searchType2 == self::SEARCH_STRICT) {
                            $con = 'keywords like ' . $this->keywords2[0] . ' or keywords like ",' . $this->keywords2[0] . '" or keywords like ",' . $this->keywords2[0] . '," or keywords like "' . $this->keywords2[0] . ',"'; //todo: quick fix
                            $condition = ' AND (' . $con . ')';
                        } else if ($this->searchType2 == self::SEARCH_EXCLUDE) {
                            if (isset($this->keywordsExclude2)) {
                                $keywordExcludeCount = count($this->keywordsExclude2);
                                if (isset($this->keywordsExclude2)) {
                                    if ($keywordExcludeCount > 1) {
                                        $con = ' FIND_IN_SET(' . $this->keywords2[0] . ', keywords) > 0 ';
                                        foreach ($this->keywordsExclude2 as $key) {
                                            $subcon = ' FIND_IN_SET(' . $key . ', keywords) = 0 ';
                                            $con .= " AND " . $subcon;
                                        }
                                        $condition = ' AND (' . $con . ')';
                                    } else {
                                        $condition = ' AND FIND_IN_SET(' . $this->keywords2[0] . ', keywords) > 0 AND FIND_IN_SET(' . $this->keywordsExclude2[0] . ', keywords) = 0';
                                    }
                                }
                            }
                        } else if ('' == $this->searchType) {
                            $condition = ' AND FIND_IN_SET(' . $this->keywords2[0] . ', keywords) > 0';
                        }
                    }
                    $matchingRecords = array();
                    $rows = Yii::$app->db->createCommand('SELECT id FROM User WHERE ((userType != "' . User::SUPER_ADMIN . '" AND userType != "' . User::POLITICIAN . '") AND (isSysUser=0))' . $condition)->queryAll();
                    foreach ($rows as $row) {
                        $matchingRecords[] = $row['id']; //user id
                    }
                    if (!empty($matchingRecords)) {
                        $query->andOnCondition("t.id IN (" . implode(',', $matchingRecords) . ")");
                    } else {
                        $query->andOnCondition("t.id IN (0)"); // forcing to fail
                    }
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

                if ($this->excludeFbPersonalContacts) {
                    $clientProfiles = $this->getClientProfile(['FB']);
                    if (null != $clientProfiles['modelFbProfile']) {
                        $clientFbId = $clientProfiles['modelFbProfile']->fbUserId;
                        $query->andOnCondition('t.id NOT IN (SELECT FP.userId FROM FbProfileConnection FBC, FbProfile FP WHERE FBC.parentFbUserId="' . $clientFbId . '" AND FBC.childFbUserId=FP.fbUserId)');
                    }
                }
                if (!empty($this->age)) {
                    if (is_numeric($this->age)) {
                        $query->andOnCondition('YEAR(dateOfBirth) = YEAR(DATE_ADD(CURDATE(), INTERVAL -' . $this->age . ' YEAR))');
                    } else {
                        $Age = explode("-", $this->age);
                        if ($Age[1] > 150 or $Age[0] > 150) {
                            $this->age = '0-0';
                        }
                        $tmpAge = explode("-", $this->age);
                        $query->andOnCondition('YEAR(dateOfBirth) BETWEEN YEAR(DATE_ADD(CURDATE(), INTERVAL -' . $tmpAge[1] . ' YEAR)) AND YEAR(DATE_ADD(CURDATE() ,INTERVAL -' . $tmpAge[0] . ' YEAR))');
                    }
                }
                $isRegional = Yii::$app->user->CheckUserType(WebUser::REGIONAL_ADMIN_NAME) && !Yii::$app->session->get('is_super_admin');
                if ($isRegional) {
                    $user = User::find()->where(['id' => Yii::$app->user->id])->one();
                    $query->andOnCondition('zip LIKE "' . $user->zip . '%"'); // regional admin can see only zip based
                }
                if (!empty($this->network)) {
                    $conditionArray = array();
                    foreach ($this->network as $network) {
                        if (User::MOBILE == $network) {
                            $conditionArray[] = 't.mobile IS NOT NULL AND t.mobile != ""';
                        }
                        if (User::EMAIL == $network) {
                            $conditionArray[] = 't.email IS NOT NULL AND t.email != ""';
                        }
                    }
                    if (count($conditionArray) > 1) {
                        $query->andOnCondition(implode(' OR ', $conditionArray));
                    } else {
                        $query->andOnCondition(implode('', $conditionArray));
                    }
                    unset($conditionArray);
                }
            }
        }

        $order = SORT_DESC;
        $orderBy = "createdAt";
        if ($sort) {
            $sort_order = explode('-', $sort);
            if (isset($sort_order[1])) {
                $order = SORT_ASC;
                $orderBy = $sort_order[0];
            }

        }
        $order = [$orderBy => $order];
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 10, 'route' => 'advanced-search/admin'],
            'sort' => [
                'defaultOrder' => $order,
                'route' => 'advanced-search/admin'
            ],

        ]);


        if (!$this->validate()) {
            return $dataProvider;
        }

        return $dataProvider;
    }

    public function searchKeywords($data = [])
    {

        if (!empty($data)) {
            $data['keywords'] = explode(",", $data['keywords']);
            if (!empty($data['keywordsExclude'])) {
                $data['keywordsExclude'] = explode(",", $data['keywordsExclude']);
            }


            $con = '';
            $keywordCount = count($data['keywords']);

            if ($keywordCount > 1) {
                switch ($data['searchType']) {
                    case User::SEARCH_NORMAL:
                        foreach ($data['keywords'] as $key) {
                            $subcon = ' FIND_IN_SET(' . $key . ', keywords) > 0 ';
                            $con .= empty($con) ? $subcon : " OR " . $subcon;
                        }

                    case User::SEARCH_STRICT:
                        foreach ($data['keywords'] as $key) {
                            $subcon = ' FIND_IN_SET(' . $key . ', keywords) > 0 ';
                            $con .= empty($con) ? $subcon : " AND " . $subcon;
                        }

                    case User::SEARCH_EXCLUDE:
                        foreach ($data['keywords'] as $key) {
                            $subcon = ' FIND_IN_SET(' . $key . ', keywords) > 0 ';
                            $con .= empty($con) ? $subcon : " OR " . $subcon;
                        }
                        $con = "(" . $con . ")";
                        $keywordExcludeCount = count($data['keywordsExclude']);
                        if ($keywordExcludeCount > 1) {
                            foreach ($data['keywordsExclude'] as $key) {
                                $subcon = ' FIND_IN_SET(' . $key . ', keywords) = 0 ';
                                $con .= " AND " . $subcon;
                            }
                        } else {
                            $subcon = ' FIND_IN_SET(' . $data['keywordsExclude'][0] . ', keywords) = 0 ';
                            $con .= " AND " . $subcon;
                        }

                }
            } else {
                switch ($data['searchType']) {
                    case User::SEARCH_NORMAL:
                        $con .= ' FIND_IN_SET(' . $data['keywords'][0] . ', keywords) > 0';
                        return $con;

                    case User::SEARCH_STRICT:
                        $subcon = 'keywords like ' . $data['keywords'][0];
                        $con .= ' (' . $subcon . ')';
                        return $con;

                    case User::SEARCH_EXCLUDE:
                        $keywordExcludeCount = count($data['keywordsExclude']);
                        if ($keywordExcludeCount > 1) {
                            $con = ' FIND_IN_SET(' . $data['keywords'][0] . ', keywords) > 0 ';
                            foreach ($data['keywordsExclude'] as $key) {
                                $subcon = ' FIND_IN_SET(' . $key . ', keywords) = 0 ';
                                $con .= " AND " . $subcon;
                            }

                        } else {
                            $con .= ' FIND_IN_SET(' . $data['keywords'][0] . ', keywords) > 0 AND FIND_IN_SET(' . $data['keywordsExclude'][0] . ', keywords) = 0';
                        }

                }
            }
            return $con;

        }


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
