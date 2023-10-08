<?php

namespace app\models;

use app\components\ToolKit;
use app\components\Validations\ValidateKeywordConditions;
use MongoDB\Driver\Query;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * This is the model class for table "Keyword".
 *
 * @property int $id
 * @property string $name Short name for keyword
 * @property int $behaviour 1 - manual, 2 - auto
 * @property int $status 0 - active, 1 - inactive, 2 - deleted
 * @property int $type 1-system keyword, 0-other
 * @property string $lastUpdated
 * @property int $createdBy
 * @property string $createdAt
 * @property int $updatedBy
 * @property string $updatedAt
 */
class Keyword extends ActiveRecord
{

    // Behaviours
    const KEY_MANUAL = 1;
    const KEY_AUTO = 2;

    // Keyword status
    const KEY_ACTIVE = 0;
    const KEY_INACTIVE = 1;
    const KEY_DELETED = 2;

    // Kyeword types
    const KEY_TYPE_SYSTEM = 1;

    public $conditions = null;
//     public $behaviour = Keyword::KEY_MANUAL;
    public $team = null;
    public $startDate;
    public $fromDate;
    public $toDate;

    /**
     * Keyword status types
     */
    const ACTIVE = 0;
    const INACTIVE = 1;
    const DELETED = 2;

    /**
     * Keyword behaviour types
     */
    const MANUAL = 1;
    const AUTO = 2;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Keyword';
    }

    /*
     * Function called before saving validating
     */
    public function beforeValidate()
    {
        $this->name = trim($this->name);

        return parent::beforeValidate();
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [

            // Create,Update
            [['name', 'behaviour', 'status'], 'required', 'on' => ['create', 'update']],
            [['behaviour', 'status', 'name'], 'required', 'on' => ['create', 'update']],
//
            [['conditions'], ValidateKeywordConditions::className(), 'keyAuto'=>self::KEY_AUTO, 'on' => ['create', 'update']],
            [['behaviour'], 'number', 'on' => ['create', 'update']],
            [['name'], 'string', 'max' => 45, 'on' => ['create', 'update']],
            [['name'], 'unique', 'on' => ['create', 'update']],
            [['team'], 'validateTeam', 'on' => ['create', 'update']],
            [['createdAt', 'updatedAt', 'createdBy', 'updatedBy'], 'safe', 'on' => ['create', 'update']],

            //array('name', 'match', 'pattern'=>'/^[a-zA-Z][a-zA-Z0-9_]*$/', 'on'=>'create,update'),

            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.

            [['id', 'name', 'behaviour', 'status', 'lastUpdated', 'createdAt', 'fromDate', 'toDate'], 'safe', 'on' => ['search']],
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
            'behaviour' => Yii::t('messages', 'Behaviour'),
            'lastUpdated' => Yii::t('messages', 'Last Updated'),
            'status' => Yii::t('messages', 'Status'),
            'conditions' => Yii::t('messages', 'Conditions'),
            'createdAt' => Yii::t('messages', 'Created At'),
            'createdBy' => Yii::t('messages', 'Created By'),
            'fromDate' => Yii::t('messages', 'From Date'),
            'toDate' => Yii::t('messages', 'To Date'),
        ];
    }

    /**
     * Check condition required on selected behaviour
     */
    public function validateConditions()
    {
         if ($this->behaviour == self::KEY_AUTO && null == $this->conditions) {
            $this->addError('conditions', Yii::t('messages', 'Please select condition(s)'));
        }
    }

    /**
     * Check whether user selected any team according to selected condition
     */
    public function validateTeam()
    {
        if (null != $this->conditions &&
            $this->behaviour == self::KEY_AUTO &&
            in_array(Autokeywordcondition::APPLY_TEAMS, $this->conditions) &&
            $this->team == null
        ) {
            $this->addError('team', Yii::t('messages', 'Please select a team'));
        }
    }


    /**
     * Returns people active keywords.
     * @return array Available valid keywords
     */
    public static function getActiveKeywords()
    {
        $keywordModel = Keyword::find()->where('status = :status', [':status' => self::KEY_ACTIVE])->all();
        $keywordsList = ArrayHelper::map($keywordModel, 'id', 'name', 'behaviour');
        return $keywordsList;
    }

    /**
     * @return array
     */
    public static function getTempKeyword($formBuilder = false)
    {
        $keywords = self::getActiveKeywords();
        $tmpKeywords = [];
        if($formBuilder) {
            foreach ($keywords as $behaviour => $behaviours) {
                $tmpKeywords[Keyword::getBehaviourOptions($behaviour)] = $behaviours;
            }
        } else { // TODO need to remove the condition if upper code is working
            foreach ($keywords as $behaviour => $behaviours) {
                if ($behaviour == 2) {
                    $behaviour = 1;
                }
                $tmpKeywords[self::getBehaviourOptions($behaviour)] = $behaviours;
            }
        }

        return $tmpKeywords;
    }


    /**
     * When adding Feed keyword or importing Mailchimp contact list we need to dynamically add keyword.
     * @param string $keyword Keyword name
     * @param string $userId User id
     * @return boolean true or false
     */
    public function addSystemKeyword($keyword, $userId)
    {
        $modelKeyword = Keyword::find()->where(['name' => $keyword])->one();
        if (empty($modelKeyword)) {
            $modelKeyword = new Keyword();
            $modelKeyword->name = $keyword;
            $modelKeyword->behaviour = Keyword::KEY_MANUAL;
            $modelKeyword->status = Keyword::KEY_ACTIVE;
            $modelKeyword->type = Keyword::KEY_TYPE_SYSTEM;
            $modelKeyword->lastUpdated = date('Y-m-d H:i:s');
            $modelKeyword->createdBy = $userId;
            $modelKeyword->createdAt = date('Y-m-d H:i:s');
        } else {
            $modelKeyword->behaviour = Keyword::KEY_MANUAL;
            $modelKeyword->status = Keyword::KEY_ACTIVE;
            $modelKeyword->type = Keyword::KEY_TYPE_SYSTEM;
            $modelKeyword->lastUpdated = date('Y-m-d H:i:s');
            $modelKeyword->updatedBy = $userId;
            $modelKeyword->updatedAt = date('Y-m-d H:i:s');
        }

        /*
         * if needed in feature uncomment
         *  $modelKeyword->scenario = 'create';
         *
         * */

        try {
            if ($modelKeyword->save()) {
                return $modelKeyword->id;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Retrieve keyword behaviour dropdown option or its label when $key is given.
     * @param integer $key Behaviour identifier. Ex:KEY_MANUAL
     * @return mixed Dropdown option array or label
     */
    public static function getBehaviourOptions($key = null)
    {
        $arrOptions = array(
            self::KEY_MANUAL => Yii::t('messages', 'Manual'),
            self::KEY_AUTO => Yii::t('messages', 'Auto')
        );

        if (null != $key) {
            return $arrOptions[$key];
        }

        return $arrOptions;
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
     * @return ActiveDataProvider
     * based on the search/filter conditions.
     */
    public function searchKeyword($params = null)
    {
        // @todo Please modify the following code to remove attributes that should not be searched.
        $query = Keyword::find();


        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 10],
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]]

        ]);

        $this->load($params);


        $isSameDay = false;

        if (!empty($params['Keyword'])) {
            if (!ToolKit::isEmpty($params['Keyword']['toDate']) && !ToolKit::isEmpty($params['Keyword']['fromDate']) && $params['Keyword']['toDate'] === $params['Keyword']['fromDate']) {
                $isSameDay = true;
                $query->andFilterWhere(['= ', 'createdAt', $params['Keyword']['toDate']]);
            }

            if (!$isSameDay && !ToolKit::isEmpty($params['Keyword']['toDate'])) {
                $query->andFilterWhere(['<= ', 'createdAt', $params['Keyword']['toDate']]);
            }
            if (!$isSameDay && !ToolKit::isEmpty($params['Keyword']['fromDate'])) {
                $query->andFilterWhere([' >=  ', 'createdAt', $params['Keyword']['fromDate']]);
            }

            if (!ToolKit::isEmpty($params['Keyword']['name'])) {
                $query->andFilterWhere(['like', 'name', $params['Keyword']['name']]);
            }

            if (!ToolKit::isEmpty($params['Keyword']['behaviour'])) {
                $query->andFilterWhere(['=', 'behaviour', $params['Keyword']['behaviour']]);
            }

            if (!ToolKit::isEmpty($params['Keyword']['status'])) {
                $query->andFilterWhere(['=', 'status', $params['Keyword']['status']]);
            }


        }

        return $dataProvider;

    }

    /**
     * Retrieve keyword label
     * @param integer $keywordId
     * @return string label
     */
    public static function getLabel($keywordId)
    {

        $model = self::findOne(['id' => $keywordId]);
        return $model ? $model->name : '';
    }

    /**
     * @param $keywordData
     * @return string
     * @description return keyword label values as an array
     */
    public function getKeywordLabelList($keywordData)
    {
        $labelList = array();

        foreach ($keywordData as $key) {
            if (isset($key)) {
                $labelList[] = Keyword::getLabel($key);
            }

        }

        return implode(',', $labelList);
    }

    /**
     *
     * @param string $field
     * @return Ambigous <multitype:, multitype:NULL >
     */
    public function fillDropDown($field)
    {
        $return = array();
        switch ($field) {
            case 'behaviour':
                $return = array(
                    self::MANUAL => Yii::t('messages', 'Manual'),
                    self::AUTO => Yii::t('messages', 'Auto'),
                );
                break;

            case 'status':
                $return = array(
                    self::ACTIVE => Yii::t('messages', 'Active'),
                    self::INACTIVE => Yii::t('messages', 'Inactive'),
                    self::DELETED => Yii::t('messages', 'Deleted'),
                );
                break;
        }

        return $return;
    }

    /**
     * When update User keywords, Check new keywords list contain old auto keywords
     * @param string $oldIdList
     * @param string $newIdList
     * @return boolean
     */
    public static function isAutoKeywordsExist($oldIdList, $newIdList)
    {
        $arrIdList = explode(',', $oldIdList);

        foreach ($arrIdList as $id) {
            $keywordModel = Keyword::findAll(array('id' => $id, 'behaviour' => self::KEY_AUTO));
            if (null != $keywordModel) {
                if (strpos(",{$newIdList},", ",{$id},") === false) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Retrieve keyword status dropdown option or its label when $key is given.
     * @param integer $key Behaviour identifier. Ex:KEY_ACTIVE
     * @return mixed Dropdown option array or label
     */
    public function getKeywordStatusOptions($key = null)
    {
        $arrOptions = array(
            self::KEY_ACTIVE => Yii::t('messages', 'Active'),
            self::KEY_INACTIVE => Yii::t('messages', 'Inactive'),
            self::KEY_DELETED => Yii::t('messages', 'Deleted')
        );

        if (null != $key) {
            return $arrOptions[$key];
        }

        return $arrOptions;
    }

    /**
     * Retrieve keyword status label
     * @param integer $status Status
     * @return string $label Status label
     */
    public function getKeywordLable($status)
    {
        $badgeCss = '';

        switch ($status) {
            case self::KEY_ACTIVE:
                $badgeCss = "badge badge-success";
                break;

            case self::KEY_INACTIVE:
                $badgeCss = "badge bg-bounced";
                break;

            case self::KEY_DELETED:
                $badgeCss = "badge-danger";
                break;
        }
        $KeywordStatusArray = $this->getKeywordStatusOptions($status);
        if (is_array($KeywordStatusArray)) {
            $KeywordStatus = $KeywordStatusArray[0];
        } else {
            $KeywordStatus = $KeywordStatusArray;
        }
        return "<span class='badge {$badgeCss}'>{$KeywordStatus}</span>";
    }


    /**
     * @return null
     */
    public static function isAllowed($data, $accessType)
    {
        if (Keyword::KEY_DELETED != $data->status && Yii::$app->user->checkAccess($accessType) &&
            $data->createdBy == Yii::$app->user->id && Keyword::KEY_TYPE_SYSTEM != $data->type) {
            return true;
        } elseif (Keyword::KEY_DELETED != $data->status && Yii::$app->user->checkAccess("superadmin") &&
            Keyword::KEY_TYPE_SYSTEM != $data->type) {
            return true;
        }
    }

    /**
     * When comma seperated list of keyword ids given, return comma seperated name list
     * @param string $idList Comma seperated id list
     * @return string $nameList Comma seperated list of names
     */
    public static function getKeywordsByIdList($idList)
    {
        $nameList = array();
        $arrIdList = explode(',', $idList);

        foreach ($arrIdList as $id) {
            $keywordModel = Keyword::findOne(['id' => $id]);
            if (null != $keywordModel) {
                $nameList[] = $keywordModel->name;
            }
        }

        return implode(",", $nameList);
    }

    /**
     * {@inheritdoc}
     * @return KeywordQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new KeywordQuery(get_called_class());
    }

    /*
       * function to get keyword name by one ID or list
       */
    /**
     * @param $idList
     * @return string
     */
    function getKeywordsByIdAndList($idList)
    {
        $arrIdList = explode(',', $idList);
        $nameList = array();
        foreach ($arrIdList as $id) {
            $keywordModel = Keyword::find()->where(['id' => $id])->one();
            if (null != $keywordModel) {
                $nameList[] = $keywordModel->name;
            }
        }
        return '<div class="break-words">' . implode(", ", $nameList) . '</div>';
    }

    /*
    * function to get keyword label from a CSV
    */
    /**
     * @param $idList
     * @return string
     */
    function getKeywordLabelByList($idList)
    {
        $arrIdList = explode(',', $idList);
        $nameList = array();
        foreach ($arrIdList as $id) {
            $keywordModel = Keyword::find()->where(['id' => $id])->one();
            if (null != $keywordModel) {
                $nameList[] = $keywordModel->name;
            }
        }
        return implode(", ", $nameList);
    }


}
