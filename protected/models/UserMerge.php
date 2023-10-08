<?php


namespace app\models;


use app\components\ToolKit;
use yii\data\ActiveDataProvider;
use Yii;
use yii\db\Exception;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class UserMerge extends UserSearch
{
    public $query;

    public function __construct($config = [])
    {
        $this->query = new Query();
        parent::__construct($config);
    }

    /**
     * @param null $params
     * @return ActiveDataProvider
     * @throws Exception
     */
    public function searchUniquePeople($params = null): ActiveDataProvider
    {
        $ids = [];

        $connection = Yii::$app->getDb();
        $limit = 10;
        // Get Null date of birth
        $command = $connection->createCommand("SELECT a.id FROM User a JOIN (SELECT firstName,lastName, COUNT('[[id]]') FROM User where userType != 1 AND userType != ':userType2' AND delStatus = ':delStatus' GROUP BY firstName,lastName HAVING Count('[[id]]')>1) b ON a.firstName = b.firstName  AND a.lastName = b.lastName AND (a.dateOfBirth is null OR a.dateOfBirth = '0000-00-00') AND a.delStatus=:delStatus AND a.userType!=:userType1 AND a.userType!=:userType2")
            ->bindValue(':delStatus', User::NOTDELETE)
            ->bindValue(':userType1', User::POLITICIAN)
            ->bindValue(':userType2', User::SUPER_ADMIN);
        $peoples = ArrayHelper::toArray($command->queryAll());
        $peopleIds = ArrayHelper::getColumn($peoples, 'id');


        $command = $connection->createCommand("SELECT id FROM User  WHERE firstName is not null AND lastName is not null AND delStatus=:delStatus AND userType!=:userType1 AND userType!=:userType2 GROUP BY firstname,lastName,dateOfBirth HAVING Count('[[id]]') >1")
            ->bindValue(':delStatus', User::NOTDELETE)
            ->bindValue(':userType1', User::POLITICIAN)
            ->bindValue(':userType2', User::SUPER_ADMIN);
        $sameDobUsers = ArrayHelper::toArray($command->queryAll());
        $sameDubUserIds = ArrayHelper::getColumn($sameDobUsers, 'id');

        $ids = array_merge($peopleIds, $sameDubUserIds);

        $from = (isset($_GET['page'])) ? ($_GET['page'] - 1) * $limit : 0;

        $query = User::find()->select(['id', 'firstName', 'lastName', 'email', 'mobile', '(SELECT IFNULL(dateOfBirth, "N/A")) As dateOfBirth', "Count('[[id]]') dupCount"])
            ->where(['!=','userType',User::POLITICIAN])
            ->andWhere(['in', 'id', $ids])
            ->groupBy(['firstName', 'lastName'])
            ->orderBy('joinedDate DESC')
            ->limit($limit)->offset($from);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => $limit],

        ]);
        $this->load($params);
        if (!empty($params['UserMerge'])) {
            if (!ToolKit::isEmpty($params['UserMerge']['firstName'])) {
                $query->andFilterWhere(['like', 'firstName', $params['UserMerge']['firstName']]);
            }
            if (!ToolKit::isEmpty($params['UserMerge']['lastName'])) {
                $query->andFilterWhere(['like', 'lastName', $params['UserMerge']['lastName']]);
            }
        }



        return $dataProvider;
    }

    /**
     * @param $id
     * @return ActiveDataProvider
     */
    public function searchDuplicatePeople($id): ActiveDataProvider
    {
        $ids = [];
        $model = User::findOne($id);
        $this->parentId = $id;

        $users = $this->requestUserDuplicates($model);

        foreach ($users as $user) {
            array_push($ids, (int)$user["id"]);
        }

        $query = User::find()->where(['id' => $ids]);
        return new ActiveDataProvider([
            'query' => $query,

        ]);
    }

    /**
     * @param $data
     * @return int
     */
    public function searchUniqueCount($data): int
    {
        $model = User::findOne($data->id);
        $users = $this->requestUserDuplicates($model);
        return count($users);
    }

    /**
     * @param $model
     * @return array
     */
    public function requestUserDuplicates($model): array
    {

        $query = new Query();
        $dobNullUsers = $query->select('id')
            ->from('User')
            ->where(['firstName' => $model->firstName, 'lastName' => $model->lastName, 'dateOfBirth' => null])
            ->andWhere(['=','delStatus', User::NOTDELETE])
            ->andWhere(['!=','userType', User::POLITICIAN])
            ->andWhere(['!=','userType', User::SUPER_ADMIN])
            ->all();

        $dobZeroUsers = $query->select('id')
            ->from('User')
            ->where(['firstName' => $model->firstName, 'lastName' => $model->lastName, 'dateOfBirth' => '0000-00-00'])
            ->andWhere(['=','delStatus', User::NOTDELETE])
            ->andWhere(['!=','userType', User::POLITICIAN])
            ->andWhere(['!=','userType', User::SUPER_ADMIN])
            ->all();

        $dobNotEmptyUsers = $query->select(['id', 'firstName', 'lastName', 'dateOfBirth'])
            ->from('User')
            ->where(['firstName' => $model->firstName, 'lastName' => $model->lastName])
            ->andWhere(['!=', 'dateOfBirth', '0000-00-00'])
            ->andWhere(['!=', 'dateOfBirth', 'null'])
            ->andWhere(['=','delStatus', User::NOTDELETE])
            ->andWhere(['!=','userType', User::POLITICIAN])
            ->andWhere(['!=','userType', User::SUPER_ADMIN])
            ->all();

        $sameUserRecords = [];


        foreach ($dobNotEmptyUsers as $key => $value) {
            $firstName = $dobNotEmptyUsers[$key]['firstName'];
            $lastName = $dobNotEmptyUsers[$key]['lastName'];
            $dateOfBirth = $dobNotEmptyUsers[$key]['dateOfBirth'];
            $sameDbCount = User::find()->where(['firstName' => $firstName, 'lastName' => $lastName, 'dateOfBirth' => $dateOfBirth])
                ->andWhere(['=','delStatus', User::NOTDELETE])
                ->andWhere(['!=','userType', User::POLITICIAN])
                ->andWhere(['!=','userType', User::SUPER_ADMIN])
                ->count();
            $sameDbNullCount = User::find()->where(['firstName' => $firstName, 'lastName' => $lastName, 'dateOfBirth' => null])
                ->andWhere(['=','delStatus', User::NOTDELETE])
                ->andWhere(['!=','userType', User::POLITICIAN])
                ->andWhere(['!=','userType', User::SUPER_ADMIN])
                ->count();
            $sameDbZeroCount = User::find()->where(['firstName' => $firstName, 'lastName' => $lastName, 'dateOfBirth' => '0000-00-00'])
                ->andWhere(['=','delStatus', User::NOTDELETE])
                ->andWhere(['!=','userType', User::POLITICIAN])
                ->andWhere(['!=','userType', User::SUPER_ADMIN])
                ->count();

            if ($sameDbCount > 1 || $sameDbNullCount >= 1 || $sameDbZeroCount >= 1) {
                array_push($sameUserRecords, $dobNotEmptyUsers[$key]);
            }
        }

        return array_merge($dobNullUsers, $dobZeroUsers, $sameUserRecords);

    }

    /**
     * @param $model
     * @return array
     */
    public function getSuggestionList($model): array
    {
        $query = new Query();
        // $dateOfBirth = isset($model->dateOfBirth) ? $model->dateOfBirth : null;
        $dateOfBirth = $model->dateOfBirth;

        $dobNullUsers = $query->select('*')
            ->from('User')
            ->where(['firstName' => $model->firstName, 'lastName' => $model->lastName, 'dateOfBirth' => null])
            ->andWhere(['<>', 'id', $model->id])
            ->andWhere(['=','delStatus', User::NOTDELETE])
            ->andWhere(['!=','userType', User::POLITICIAN])
            ->andWhere(['!=','userType', User::SUPER_ADMIN])
            ->all();

        $dobZeroUsers = $query->select('*')
            ->from('User')
            ->where(['firstName' => $model->firstName, 'lastName' => $model->lastName, 'dateOfBirth' => '0000-00-00'])
            ->andWhere(['<>', 'id', $model->id])
            ->andWhere(['=','delStatus', User::NOTDELETE])
            ->andWhere(['!=','userType', User::POLITICIAN])
            ->andWhere(['!=','userType', User::SUPER_ADMIN])
            ->all();

        if(null == $dateOfBirth) {
            $dobNotEmptyUsers = $query->select('*')
                ->from('User')
                ->where(['firstName' => $model->firstName, 'lastName' => $model->lastName])
                ->andWhere(['!=', 'dateOfBirth', '0000-00-00'])
                ->andWhere(['!=', 'dateOfBirth', 'null'])
                ->andWhere(['<>', 'id', $model->id])
                ->andWhere(['=','delStatus', User::NOTDELETE])
                ->andWhere(['!=','userType', User::POLITICIAN])
                ->andWhere(['!=','userType', User::SUPER_ADMIN])
                ->all();
        } else {
            $dobNotEmptyUsers = $query->select('*')
                ->from('User')
                ->where(['firstName' => $model->firstName, 'lastName' => $model->lastName, 'dateOfBirth' => $dateOfBirth])
                ->andWhere(['=','delStatus', User::NOTDELETE])
                ->andWhere(['!=','userType', User::POLITICIAN])
                ->andWhere(['!=','userType', User::SUPER_ADMIN])
                ->andWhere(['!=', 'dateOfBirth', '0000-00-00'])
                ->andWhere(['!=', 'dateOfBirth', 'null'])
                ->andWhere(['<>', 'id', $model->id])->all();
        }

        return array_merge($dobNullUsers, $dobZeroUsers, $dobNotEmptyUsers);

    }
}