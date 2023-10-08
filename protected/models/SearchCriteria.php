<?php

namespace app\models;

use app\components\LinkedInApi;
use app\components\ToolKit;
use app\components\TwitterApi;
use Yii;
use yii\db\Query;

/**
 * This is the model class for table "SearchCriteria".
 *
 * @property int $id
 * @property string $name
 * @property string $firstName
 * @property string $lastName
 * @property string $email
 * @property string $mobile
 * @property string $mapZone
 * @property string $keywords
 * @property string $keywordsExclude
 * @property string $searchType Type of search. 1 - Normal 2 - Strict 3 - Exclude
 * @property int $isDisplayKeywords2
 * @property string $keywordsExclude2
 * @property string $searchType2
 * @property string $keywords2
 * @property string $teams Comma separated team ids
 * @property int $gender
 * @property string $zip
 * @property string $fullAddress
 * @property string $city
 * @property string $countryCode
 * @property int $userType Type of user. 1 - Politician 2 - Supporter 3 - Prospects 4 - Non support
 * @property string $criteriaName Name for saved search
 * @property int $emailStatus
 * @property int $formId
 * @property string $age
 * @property string $network
 * @property int $excludeFbPersonalContacts
 * @property int $critetiaType Advance - 0 Basic -1
 * @property string $date
 * @property int $createdBy
 * @property string $createdAt
 * @property int $updatedBy
 * @property string $updatedAt
 */
class SearchCriteria extends \yii\db\ActiveRecord
{
    /**
     * Saved Search type
     */
    const ADVANCED = 0;
    const BASIC = 1;
    const BULK = 2;

    public $template;
    public $endDateTime;
    public $fromDate;
    public $toDate;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'SearchCriteria';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['criteriaName'], 'required'],
            [['criteriaName'], 'unique'],
            [['gender', 'userType'], 'integer'],
            [['name', 'firstName', 'lastName', 'criteriaName'], 'string', 'max' => 45],
            [['email'], 'string', 'max' => 64],
            [['zip', 'formId'], 'string', 'max' => 15],
            [['mobile'], 'string', 'max' => 15],
            [['countryCode'], 'string', 'max' => 3],
            [['city'], 'string', 'max' => 50],
            [['age'], 'string', 'max' => 10],
            [['age'], 'match', 'pattern' => '/^(?:110|\d{1,3})(?:\-\d{1,3})?$/'],
            [['id', 'name', 'firstName', 'lastName', 'keywords', 'keywords2', 'teams', 'gender', 'zip', 'mobile', 'mapZone', 'city', 'countryCode', 'excludeFbPersonalContacts', 'fullAddress', 'userType', 'criteriaName', 'emailStatus', 'formId', 'age', 'criteriaType', 'date', 'fromDate', 'toDate', 'createdAt', 'keywordsExclude', 'keywordsExclude2', 'searchType', 'searchType2', 'isDisplayKeywords2', 'network'], 'safe']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => Yii::t('messages', 'Name'),
            'keywords' => Yii::t('messages', 'Keywords'),
            'teams' => Yii::t('messages', 'Team'),
            'gender' => Yii::t('messages', 'Gender'),
            'zip' => Yii::t('messages', 'Zip'),
            'mobile' => Yii::t('messages', 'Mobile'),
            'mapZone' => Yii::t('messages', 'Map Zone'),
            'city' => Yii::t('messages', 'City'),
            'userType' => Yii::t('messages', 'User Category'),
            'criteriaName' => Yii::t('messages', 'Criteria Name'),
            'emailStatus' => Yii::t('messages', 'Email Status'),
            'formId' => Yii::t('messages', 'Form'),
            'age' => Yii::t('messages', 'Age'),
            'createdAt' => Yii::t('messages', 'Created At'),
            'createdBy' => Yii::t('messages', 'Created By'),
            'critetiaType' => Yii::t('messages', 'Critetia Type'),
            'date' => Yii::t('messages', 'Date'),
            'endDateTime' => Yii::t('messages', 'End Date'),
            'fromDate' => Yii::t('messages', 'From Date'),
            'toDate' => Yii::t('messages', 'To Date'),
            'isDisplayKeywords2' => Yii::t('messages', 'Enable Keywords 2'),
            'lastName' => Yii::t('messages', 'Last Name'),
            'firstName' => Yii::t('messages', 'First Name'),
        ];
    }

    /**
     * @return bool
     * @throws \yii\db\Exception
     */
    public static function getConfigFromEmailValueCheck()
    {
        $rows_name = Yii::$app->db->createCommand('select * from Configuration where Configuration.key = "FROM_EMAIL_1"')->queryAll();
        if (count($rows_name) > 0) {
            $emailCheck = true;
        } else {
            $emailCheck = false;
        }

        return $emailCheck;
    }

    /**
     * Retrieve saved search options
     * @param null $createdBy
     * @param int $criteriaType
     * @return array $arrOptions Available saved searches
     * @throws \yii\db\Exception
     */
    public static function getSavedSearchOptions($createdBy = null, $criteriaType = 3)
    {
        $arrOptions = ['' => Yii::t('messages', '- Saved Searches -')];


        $criteria = new Query();

        if (null != $createdBy) {
            $criteria->where(['createdBy' => $createdBy]);
        }

        if (3 != $criteriaType) {
            $criteria->where(['critetiaType' => $criteriaType]);
        }

        $criteria->from('SearchCriteria');
        $models = $criteria->all();

        foreach ($models as $model) {
            $arrOptions[$model['id']] = $model['criteriaName'];
        }

        return $arrOptions;
    }

    /**
     * Returns saved search name when its id is given
     * @param integer $savedSearchId Saved search id
     * @param Array $savedSearches Array of saved searches retrun from getSavedSearchOptions().
     * @return string $label Saved search name label
     */

    public static function getSavedSearchLabel($savedSearchId, $savedSearches)
    {
        $label = 'not-set';

        if (isset($savedSearches[$savedSearchId])) {
            $label = $savedSearches[$savedSearchId];
        }

        return $label;
    }

    /**
     * Create criteria object
     *
     * @param SearchCriteria $modelCriteria SearchCriteria class instance
     * @return DbCriteria $criteria Criteria object
     * @throws \yii\db\Exception
     */

    public static function getCriteria($modelCriteria)
    {
        $criteria = new Query();
        $criteria->from('User');
        $criteria->where(['!=', 'userType', User::SUPER_ADMIN]);
        $criteria->andWhere(['!=', 'userType', User::POLITICIAN]);

        // check if there is a custom field search
        $customSearchCriteriaList = CustomValueSearch::find()->where(['relatedId' => $modelCriteria->id])->all();

        if (!empty($customSearchCriteriaList)) {
            $con = '';
            $customFieldSearchCount = count($customSearchCriteriaList);
            if ($customFieldSearchCount > 1) {
                $customFieldSearchCount--;
                $join = '(SELECT cv.relatedId FROM CustomValue cv WHERE <customFieldCondition> GROUP BY cv.relatedId HAVING count(*) > ' . $customFieldSearchCount . ') tblcv';
                foreach ($customSearchCriteriaList as $customFieldSearch) {
                    $customType = CustomType::getCustomFieldType($customFieldSearch->customFieldId);
                    if (null != $customType && CustomType::CF_TYPE_TEXT == $customType) {
                        $subcon = '(cv.customFieldId=' . $customFieldSearch->customFieldId . " AND (cv.fieldValue LIKE '" . "%" . $customFieldSearch->fieldValue . "%'))";
                    } else {
                        $subcon = '(cv.customFieldId=' . $customFieldSearch->customFieldId . " AND (cv.fieldValue = '" . $customFieldSearch->fieldValue . "'))";
                    }
                    $con .= empty($con) ? $subcon : " OR " . $subcon;
                }
                $criteria->join('INNER JOIN',str_replace('<customFieldCondition>', $con, $join),'tblcv.relatedId = t.id');
            } else {
                $customType = CustomType::getCustomFieldType($customSearchCriteriaList[0]->customFieldId );   
                if (null != $customType && CustomType::CF_TYPE_TEXT == $customType){

                    $criteria->join('INNER JOIN','(SELECT cv.relatedId FROM CustomValue cv WHERE (cv.customFieldId=' . $customSearchCriteriaList[0]->customFieldId . ' AND (cv.fieldValue LIKE  "%'.$customSearchCriteriaList[0]->fieldValue .'%")) GROUP BY cv.relatedId ORDER BY cv.id DESC) tblcv','tblcv.relatedId = t.id');
                } else {

                    $criteria->join('INNER JOIN', '(SELECT cv.relatedId FROM CustomValue cv WHERE (cv.customFieldId=' . $customSearchCriteriaList[0]->customFieldId . ' AND (cv.fieldValue = "' . $customSearchCriteriaList[0]->fieldValue . '")) GROUP BY cv.relatedId ORDER BY cv.id DESC) tblcv','tblcv.relatedId = t.id');
                }
            }
        }

        // keyword 1 logic start
        if ('' != $modelCriteria->keywords) {
            if (is_null($modelCriteria->searchType)) { // old saved search campaigns using keywords will go here
                $criteria->andWhere('keywords REGEXP \'[[:<:]]' . str_replace(',', '|', $modelCriteria->keywords) . '[[:>:]]\'');
            } else { // new saved search after keyword exclude feature release
                $con = '';
                $modelCriteria->keywords = explode(",", $modelCriteria->keywords);
                $keywordCount = count($modelCriteria->keywords);
                if ($keywordCount > 1) {
                    if ($modelCriteria->searchType == User::SEARCH_NORMAL) {
                        foreach ($modelCriteria->keywords as $key) {
                            $subcon = ' FIND_IN_SET(' . $key . ', keywords) > 0 ';
                            $con .= empty($con) ? $subcon : " OR " . $subcon;
                        }
                        $criteria->andWhere(new \yii\db\Expression($con));
                    } else if ($modelCriteria->searchType == User::SEARCH_STRICT) {
                        foreach ($modelCriteria->keywords as $key) {
                            $subcon = ' FIND_IN_SET(' . $key . ', keywords) > 0 ';
                            $con .= empty($con) ? $subcon : " AND " . $subcon;
                        }
                        $criteria->andWhere(new \yii\db\Expression($con));
                    } else if ($modelCriteria->searchType == User::SEARCH_EXCLUDE) {
                        foreach ($modelCriteria->keywords as $key) {
                            $subcon = ' FIND_IN_SET(' . $key . ', keywords) > 0 ';
                            $con .= empty($con) ? $subcon : " OR " . $subcon;
                        }
                        $con = "(".$con.")";
                        $modelCriteria->keywordsExclude = explode(",", $modelCriteria->keywordsExclude);
                        $keywordExcludeCount = count($modelCriteria->keywordsExclude);
                        if ($keywordExcludeCount > 1) {
                            foreach ($modelCriteria->keywordsExclude as $key) {
                                $subcon = ' FIND_IN_SET(' . $key . ', keywords) = 0 ';
                                $con .= " AND " . $subcon;
                            }
                        } else {
                            $subcon = ' FIND_IN_SET(' . $modelCriteria->keywordsExclude[0] . ', keywords) = 0 ';
                            $con .= " AND " . $subcon;
                        }
                        $criteria->andWhere(new \yii\db\Expression($con));
                    }
                } else {
                    if ($modelCriteria->searchType == User::SEARCH_NORMAL) {
                        $criteria->andWhere(new \yii\db\Expression('FIND_IN_SET(' . $modelCriteria->keywords[0] . ', keywords) > 0'));
                    }
                    else if ($modelCriteria->searchType == User::SEARCH_STRICT) {
                        $criteria->andWhere(new \yii\db\Expression('keywords like ' . $modelCriteria->keywords[0] . ' or keywords like ",' . $modelCriteria->keywords[0] . '" or keywords like ",' . $modelCriteria->keywords[0] . '," or keywords like "' . $modelCriteria->keywords[0] . ',"'));
                    } else if ($modelCriteria->searchType == User::SEARCH_EXCLUDE) {
                        $modelCriteria->keywordsExclude = explode(",", $modelCriteria->keywordsExclude);
                        $keywordExcludeCount = count($modelCriteria->keywordsExclude);
                        if ($keywordExcludeCount > 1) {
                            $con = ' FIND_IN_SET(' . $modelCriteria->keywords[0] . ', keywords) > 0 ';
                            foreach ($modelCriteria->keywordsExclude as $key) {
                                $subcon = ' FIND_IN_SET(' . $key . ', keywords) = 0 ';
                                $con .= " AND " . $subcon;
                            }
                            $criteria->andWhere(new \yii\db\Expression($con));
                        } else {
                            $criteria->andWhere(new \yii\db\Expression('FIND_IN_SET(' . $modelCriteria->keywords[0] . ', keywords) > 0 AND FIND_IN_SET(' . $modelCriteria->keywordsExclude[0] . ', keywords) = 0'));
                        }
                    }
                }
            }
        }
        // keyword 1 logic end

        // keyword 2 logic start
        if (!empty($modelCriteria->keywords2)) {
            $con = '';
            $condition = '';
            $modelCriteria->keywords2 = explode(",", $modelCriteria->keywords2);

            if (!empty($modelCriteria->keywordsExclude2)) {
                $modelCriteria->keywordsExclude2 = explode(",", $modelCriteria->keywordsExclude2);
            }

            $keywordCount = count($modelCriteria->keywords2);
            if ($keywordCount > 1) {
                if ($modelCriteria->searchType2 == User::SEARCH_NORMAL) {
                    foreach ($modelCriteria->keywords2 as $key) {
                        $subcon = ' FIND_IN_SET(' . $key . ', keywords) > 0 ';
                        $con .= empty($con) ? $subcon : " OR " . $subcon;
                    }
                    $condition = ' AND (' . $con . ')';
                } else if ($modelCriteria->searchType2 == User::SEARCH_STRICT) {
                    foreach ($modelCriteria->keywords2 as $key) {
                        $subcon = ' FIND_IN_SET(' . $key . ', keywords) > 0 ';
                        $con .= empty($con) ? $subcon : " AND " . $subcon;
                    }
                    $condition = ' AND (' . $con . ')';
                } else if ($modelCriteria->searchType2 == User::SEARCH_EXCLUDE) {
                    foreach ($modelCriteria->keywords2 as $key) {
                        $subcon = ' FIND_IN_SET(' . $key . ', keywords) > 0 ';
                        $con .= empty($con) ? $subcon : " OR " . $subcon;
                    }
                    $con = "(".$con.")";

                    $keywordExcludeCount = count($modelCriteria->keywordsExclude2);
                    if ($keywordExcludeCount > 1) {
                        foreach ($modelCriteria->keywordsExclude2 as $key) {
                            $subcon = ' FIND_IN_SET(' . $key . ', keywords) = 0 ';
                            $con .= " AND " . $subcon;
                        }
                    } else {
                        $subcon = ' FIND_IN_SET(' . $modelCriteria->keywordsExclude2[0] . ', keywords) = 0 ';
                        $con .= " AND " . $subcon;
                    }
                    $condition = ' AND (' . $con . ')';
                } else if ('' == $modelCriteria->searchType2) {
                    foreach ($modelCriteria->keywords2 as $key) {
                        $subcon = ' FIND_IN_SET(' . $key . ', keywords) > 0 ';
                        $con .= empty($con) ? $subcon : " OR " . $subcon;
                    }
                    $condition = ' AND (' . $con . ')';
                }
            } else {
                if ($modelCriteria->searchType2 == User::SEARCH_NORMAL) {
                    $condition = ' AND FIND_IN_SET(' . $modelCriteria->keywords2[0] . ', keywords) > 0';
                } else if ($modelCriteria->searchType2 == User::SEARCH_STRICT) {
                    $con = 'keywords like ' . $modelCriteria->keywords2[0] . ' or keywords like ",' . $modelCriteria->keywords2[0] . '" or keywords like ",' . $modelCriteria->keywords2[0] . '," or keywords like "' . $modelCriteria->keywords2[0] . ',"'; //todo: quick fix
                    $condition = ' AND (' . $con . ')';

                } else if ($modelCriteria->searchType2 == User::SEARCH_EXCLUDE) {
                    $keywordExcludeCount = count($modelCriteria->keywordsExclude2);
                    if ($keywordExcludeCount > 1) {
                        $con = ' FIND_IN_SET(' . $modelCriteria->keywords2[0] . ', keywords) > 0 ';
                        foreach ($modelCriteria->keywordsExclude2 as $key) {
                            $subcon = ' FIND_IN_SET(' . $key . ', keywords) = 0 ';
                            $con .= " AND " . $subcon;
                        }
                        $condition = ' AND (' . $con . ')';
                    } else {
                        $condition = ' AND FIND_IN_SET(' . $modelCriteria->keywords2[0] . ', keywords) > 0 AND FIND_IN_SET(' . $modelCriteria->keywordsExclude2[0] . ', keywords) = 0';
                    }
                } else if ('' == $modelCriteria->searchType2) {
                    $condition = ' AND FIND_IN_SET(' . $modelCriteria->keywords2[0] . ', keywords) > 0';
                }
            }

            $matchingRecords = array();
            $rows = Yii::$app->db->createCommand("select id from User where userType !=' . User::SUPER_ADMIN . ' AND userType !=' . User::POLITICIAN . 'AND (isSysUser='0') $condition")->queryAll();
            foreach ($rows as $row) {
                $matchingRecords[] = $row['id']; //user id
            }
            if (!empty($matchingRecords)) {
                // $criteria->addCondition("t.id IN (" . implode(',', $matchingRecords) . ")");
                $criteria->andWhere("id IN (" . implode(',', $matchingRecords) . ")");
            } else {
                $criteria->andWhere("t.id IN (0)"); // forcing to fail
            }

        }

        // keyword 2 logic end
        if (!empty($modelCriteria->teams)) {
            $criteria->join('INNER JOIN', 'TeamMember', 'TeamMember.memberUserId=id');
            $criteria->andWhere(['in', 'TeamMember.teamId', $modelCriteria->teams]);
        }
        if ($modelCriteria->userType != 0) {
            $criteria->andFilterWhere([
                'userType' => $modelCriteria->userType
            ]);
        }

        // Filter the two columns in DB using the one drop down
        if ($modelCriteria->emailStatus != null) {
            if ($modelCriteria->emailStatus == User::UNSUBSCRIBE_EMAIL) {
                $criteria->andFilterWhere([
                    'isUnsubEmail' => 1
                ]);
            } else {
                $criteria->andFilterWhere([
                    'emailStatus' => $modelCriteria->emailStatus
                ]);
            }
        }
        if (!empty($modelCriteria->formId)) {
            $criteria->andFilterWhere([
                'formId' => $modelCriteria->formId
            ]);
        }
        if (!ToolKit::isEmpty($modelCriteria->zip)) {
            $criteria->andWhere(['like', 'zip', $modelCriteria->zip . '%', false]);
        }
        if (!empty($modelCriteria->firstName)) {
            $criteria->andWhere(['like', 'firstName', $modelCriteria->firstName]);
        }
        if (!empty($modelCriteria->lastName)) {
            $criteria->andWhere(['like', 'lastName', $modelCriteria->lastName]);
        }
        if (!empty($modelCriteria->city)) {
            $criteria->andWhere(['like', 'city', $modelCriteria->city]);
        }
        if (!empty($modelCriteria->countryCode)) {
            $criteria->andWhere(['countryCode' => $modelCriteria->countryCode]);
        }
        if (!empty($modelCriteria->gender)) {
            $criteria->andWhere(['gender' => $modelCriteria->gender]);
        }
        if (!empty($modelCriteria->email)) {
            $criteria->andWhere(['like', 'email', $modelCriteria->email]);
        }

        // mapZone logic start
        if (!empty($modelCriteria->mapZone)) {
            $mapZone = MapZone::findOne(['mapZone', $modelCriteria->mapZone]);
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
                $condition .= " AND concat(LOWER(trim(t.address1)), ', ',t.zip, ' ',LOWER(trim(t.city)), ', ',c.countryName) like '%" . strtolower($fullAddress) . "%'";
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
                $resCon = (new UserSearch())->searchKeywords($mapZone);
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
                $criteria->andWhere("longLat IS NOT NULL AND t.id IN (" . implode(',', $matchingRecords) . ")");
            } else {
                $criteria->andWhere("longLat IS NOT NULL AND t.id IN (0)"); // forcing to fail
            }

        }
        // mapZone logic ends

        if (!empty($modelCriteria->mobile)) {
            $criteria->andWhere(['LIKE', 'mobile', $modelCriteria->mobile]);
        }
        if (!empty($modelCriteria->fullAddress)) {
            $fullAddress = addslashes($modelCriteria->fullAddress);
            $criteria->andWhere("address1 IS NOT NULL");
            $criteria->andWhere("concat(LOWER(trim(address1)), ', ',zip, ' ',LOWER(trim(city))) like '%" . strtolower($fullAddress) . "%'");
        }

        if (!empty($modelCriteria->age)) {
            if (is_numeric($modelCriteria->age)) {
                $criteria->andWhere('YEAR(dateOfBirth) = YEAR(DATE_ADD(CURDATE() ,INTERVAL -' . $modelCriteria->age . ' YEAR))');
            } else {
                $pattern = '/^(?:110|\d{1,3})(?:\-\d{1,3})?$/';
                if (0 == preg_match($pattern, $modelCriteria->age)) {
                    $modelCriteria->age = '0-0';
                }
                $tmpAge = explode("-", $modelCriteria->age);
                $criteria->andWhere('YEAR(dateOfBirth) BETWEEN YEAR(DATE_ADD(CURDATE() ,INTERVAL -' . $tmpAge[1] . ' YEAR)) AND YEAR(DATE_ADD(CURDATE() ,INTERVAL -' . $tmpAge[0] . ' YEAR))');
            }
        }
        if (!empty($modelCriteria->network)) {
            $conditionArray = array();
            $networks = explode(',', $modelCriteria->network);
            foreach ($networks as $network) {
                if (User::MOBILE == $network) {
                    $conditionArray[] = 'mobile IS NOT NULL AND mobile != ""';
                }
                if (User::EMAIL == $network) {
                    $conditionArray[] = 'email IS NOT NULL AND email != ""';
                }
            }
            if (count($conditionArray) > 1) {
                $criteria->andWhere(implode(' OR ', $conditionArray));
            } else {
                $criteria->andWhere(implode('', $conditionArray));
            }
            unset($conditionArray);
        }
        $criteria->andWhere('isSysUser = 0');
        // Execute the command:
        return $criteria;
    }

    /**
     * Check whether criteria is allocated for inprogress campaign
     */
    public static function isCriteriaInUse($criteriaId)
    {
        $campModel = Campaign::find()
            ->where(['searchCriteriaId' => $criteriaId])
            ->andWhere(['status'=>Campaign::CAMP_PENDING])
            ->orWhere(['status'=>Campaign::CAMP_INPROGRESS])
            ->all();
        if(!empty($campModel)) {
            return true;
        }
        else false;
    }

    /**
     * {@inheritdoc}
     * @return SearchCriteriaQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new SearchCriteriaQuery(get_called_class());
    }
}
