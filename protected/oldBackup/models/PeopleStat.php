<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "PeopleStat".
 *
 * @property int $id
 * @property int $graphId
 * @property string $type
 * @property string $category
 * @property int $totalCount
 * @property string $graphData jeson array of the graph
 */
class PeopleStat extends \yii\db\ActiveRecord
{

    /*
 * conts for charts
 */
    const KEYWORD_GRAPH = 1;
    const AGE_GRAPH = 2;
    const CITY_GRAPH = 3;
    const ZIP_GRAPH = 4;
    const GENDER_GRAPH = 5;
    const TYPE_GRAPH = 6;
    const TEAM_GRAPH = 7;
    const MEDIA_GRAPH = 8;
    const EMAIL_GRAPH = 9;

    /*
* Const for get top 5 Records
*/
    const MAX_LIMIT = 5;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'PeopleStat';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['graphId', 'type', 'category', 'totalCount', 'graphData'], 'required'],
            [['graphId', 'totalCount'], 'integer'],
            [['category', 'graphData'], 'string'],
            [['type'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'graphId' => 'Graph ID',
            'type' => 'Type',
            'category' => 'Category',
            'totalCount' => 'Total Count',
            'graphData' => 'Graph Data',
        ];
    }

    /*
     * get records for bar chart by top 5 used Keywords with user type
     */
    public static function getKeyWordPieResult($keywordsString = null)
    {
        $keywordArr = array();
        $criteria = new CDbCriteria;
        $criteria->together = true;
        $con = '';
        $criteria->select = "id,keywords,userType";
        $criteria->addCondition("keywords != '' AND keywords IS NOT NULL AND keywords != ','");
        if (null != $keywordsString) {
            $keyWords = explode(',', $keywordsString);
            foreach ($keyWords as $key) {
                $subcon = ' FIND_IN_SET(' . $key . ', keywords) > 0 ';
                $con .= empty($con) ? $subcon : " OR " . $subcon;
            }
            $criteria->condition .= ' AND (' . $con . ')';
        }
        $criteria->addCondition("userType != :userType1 AND userType != :userType2 AND (userType = :supporter 
        OR userType = :prospect OR userType = :nonSupporter OR userType = :unknown) AND userType != :userType3 AND isSysUser = :isSysUser");
        $criteria->params[':userType1'] = User::SUPER_ADMIN;
        $criteria->params[':userType2'] = User::POLITICIAN;
        $criteria->params[':supporter'] = User::SUPPORTER;
        $criteria->params[':prospect'] = User::PROSPECT;
        $criteria->params[':nonSupporter'] = User::NON_SUPPORTER;
        $criteria->params[':unknown'] = User::UNKNOWN;
        $criteria->params[':isSysUser'] = 0;
        $criteria->params[':userType3'] = 0;
        $results = User::model()->getCommandBuilder()->createFindCommand(User::model()->tableName(), $criteria)->queryAll();
        foreach ($results as $result) {
            $userType = $result['userType'];
            if (User::UNKNOWN == $userType || User::SUPPORTER == $userType || User::NON_SUPPORTER == $userType || User::PROSPECT == $userType) {
                $keywords = array_unique(explode(',', $result['keywords']));
                foreach ($keywords as $keyword) {
                    if ($keyword > 0) {
                        $typeCount[$keyword][$userType][] = null;
                        if (null != $keywordsString) { // for the selected Keywords
                            $selectedKeywords = array_unique(explode(',', $keywordsString));
                            if (in_array($keyword, $selectedKeywords)) {
                                $keywordUserTypeArray[] = array($keyword, $userType);
                                $keywordArr[] = $keyword;
                            }
                        } else {
                            $keywordUserTypeArray[] = array($keyword, $userType);
                            $keywordArr[] = $keyword;
                        }
                    }
                }
            }
        }

        $return = PeopleStat::getKeyWordPieChartResult($keywordUserTypeArray, $keywordArr);
        unset($keywordUserTypeArray);
        unset($keywordArr);
        return $return;
    }


    public static function getPeopleStatResult()
    {
        $keywordGraph = array();
        $ageGraph = array();
        $zipGraph = array();
        $cityGraph = array();
        $genderGraph = array();
        $userTypeGraph = array();
        $teamGraph = array();
        $contactGraph = array();
        $emailGraph = array();
        $keyLabel = array();
        $categoryArray = array();
        $results = PeopleStat::find()->all();
        if($results) {
            foreach ($results as $result) {
                $graphId = $result['graphId'];
                $type = $result['type'];
                $category = $result['category'];
                $totalCount = $result['totalCount'];
                $graphData = $result['graphData'];
                if ($graphId == self::KEYWORD_GRAPH) {
                    $categoryArray = json_decode($category);
                    if($categoryArray) {
                        foreach ($categoryArray as $keywordId) {
                            $keyLabel[] = Keyword::getLabel($keywordId);
                        }
                    }
                }
                $keywordGraph = ($graphId == self::KEYWORD_GRAPH) ? array("type" => $type, "category" => $category, "totalCount" => $totalCount, "graphData" => $graphData, "keywordLabel" => $keyLabel) : $keywordGraph;
                $ageGraph = ($graphId == self::AGE_GRAPH) ? array("type" => $type, "category" => $category, "totalCount" => $totalCount, "graphData" => $graphData) : $ageGraph;
                $zipGraph = ($graphId == self::ZIP_GRAPH) ? array("type" => $type, "category" => $category, "totalCount" => $totalCount, "graphData" => $graphData) : $zipGraph;
                $cityGraph = ($graphId == self::CITY_GRAPH) ? array("type" => $type, "category" => $category, "totalCount" => $totalCount, "graphData" => $graphData) : $cityGraph;
                $genderGraph = ($graphId == self::GENDER_GRAPH) ? array("type" => $type, "category" => $category, "totalCount" => $totalCount, "graphData" => $graphData) : $genderGraph;
                $userTypeGraph = ($graphId == self::TYPE_GRAPH) ? array("type" => $type, "category" => $category, "totalCount" => $totalCount, "graphData" => $graphData) : $userTypeGraph;
                $teamGraph = ($graphId == self::TEAM_GRAPH) ? array("type" => $type, "category" => $category, "totalCount" => $totalCount, "graphData" => $graphData) : $teamGraph;
                $contactGraph = ($graphId == self::MEDIA_GRAPH) ? array("type" => $type, "category" => $category, "totalCount" => $totalCount, "graphData" => $graphData) : $contactGraph;
                $emailGraph = ($graphId == self::EMAIL_GRAPH) ? array("type" => $type, "category" => $category, "totalCount" => $totalCount, "graphData" => $graphData) : $emailGraph;

            }
        }
        $return = array('top5Keyword' => $keywordGraph, 'ageGraph' => $ageGraph, 'top5Zip' => $zipGraph, 'top5City' => $cityGraph,
            'genderGraph' => $genderGraph, 'userTypeGraph' => $userTypeGraph, 'teamGraph' => $teamGraph, 'contactGraph' => $contactGraph,
            'emailGraph' => $emailGraph);

        unset($keywordGraph);
        unset($ageGraph);
        unset($zipGraph);
        unset($cityGraph);
        unset($genderGraph);
        unset($userTypeGraph);
        unset($teamGraph);
        unset($contactGraph);
        unset($emailGraph);
        return $return;
    }


    /**
     * {@inheritdoc}
     * @return PeoplestatQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new PeoplestatQuery(get_called_class());
    }
}
