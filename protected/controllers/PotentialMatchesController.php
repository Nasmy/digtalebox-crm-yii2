<?php

namespace app\controllers;

use app\models\UserMerge;
use borales\extensions\phoneInput\PhoneInputBehavior;
use Throwable;
use Yii;
use app\models\User;
use app\models\CustomValue;
use app\models\CustomType;
use app\models\CustomField;
use yii\db\Exception;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\components\ToolKit;
use app\models\CustomMerge;
use yii\web\Response;
use \app\controllers\WebUserController;

/**
 * PotentialMatchesController implements the CRUD actions for duplicate user.
 */
class PotentialMatchesController extends WebUserController
{
    public $layout = '/column1';

    /**
     * {@inheritdoc}
     */

    public function behaviors()
    {
        return [
            [
                'class' => PhoneInputBehavior::className(),
                'countryCodeAttribute' => 'countryCode',
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],

                ],
            ],
        ];
    }

    /**
     * Lists all User models.
     * @return mixed
     * @throws Exception
     */
    public function actionAdmin()
    {
        $model = new UserMerge();
        $model->loadDefaultValues();
        if (isset($_GET['User'])) {
            $model->attributes = $_GET['User'];
        }
        $dataProvider = $model->searchUniquePeople(Yii::$app->request->queryParams);
        return $this->render('index', [
            'model' => $model,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single User model.
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new User();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * @param $id
     * @return Response
     * @throws NotFoundHttpException
     * @throws Throwable
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param $id
     * @return User|null
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * @param $userId
     * @return string
     */
    public function actionDuplicates($userId): string
    {

        $this->layout = 'dialog';
        $model = new UserMerge();
        $model->loadDefaultValues();  // clear any default values
        $model->parentId = $userId;

        if (isset($_GET['User'])) {
            $model->attributes = $_GET['User'];
        }

        return $this->render('duplicate', array(
            'model' => $model,
            'parentId' => $userId
        ));
    }

    /**
     * @param $userId
     * @return string|Response
     * @throws Exception
     */
    public function actionGetUsersToMerge($userId)
    {

        $model = new CustomMerge();
        $userMerge = new UserMerge();
        $suggestionListWithCustomField = [];
        $customFieldPostValue = [];
        $suggestionList = [];
        $childRecord = [];
        $suggestionListWithCustomField = [];


        // Get child user info
        $childModel = User::find()->where(['id'=>$userId])->one();

        $childModel->firstName = isset($childModel->firstName) ? $childModel->firstName: null;
        $childModel->lastName = isset($childModel->lastName) ? $childModel->lastName: null;
        $childModel->dateOfBirth = isset($childModel->dateOfBirth) ? $childModel->dateOfBirth: null;

        // Get suggestion lists
        $suggestionList = $userMerge->getSuggestionList($childModel);
        $childRecord = $childModel->toArray();



        $customValue = new CustomValue();

        foreach ($suggestionList as $parentModel) {
            $parentCustomField = ArrayHelper::toArray($customValue->getCustomData(CustomType::CF_PEOPLE, $parentModel['id'], CustomField::ACTION_EDIT, ToolKit::post('CustomValue')));
            $parentModel['customFields'] = $parentCustomField;
            foreach ($parentModel['customFields'] as $key => $customField) {
                $parentModel['customFields'][$key]['customFieldInfo'] = ArrayHelper::toArray(CustomField::findOne($parentModel['customFields'][$key]['customFieldId']));
            }
            array_push($suggestionListWithCustomField, $parentModel);
        }

        $childCustomField = ArrayHelper::toArray($customValue->getCustomData(CustomType::CF_PEOPLE, $userId, CustomField::ACTION_EDIT, ToolKit::post('CustomValue')));

        $childRecord['customFields'] = $childCustomField;
        foreach ($childRecord['customFields'] as $key => $childCustomField) {
            $childRecord['customFields'][$key]['customFieldInfo'] = ArrayHelper::toArray(CustomField::findOne($childRecord['customFields'][$key]['customFieldId']));
        }

        $isAccountMerge = false;

        if(isset($_POST['CustomMerge']) || isset($_POST['CustomField'])) {
            $isAccountMerge = true;
        } else {
            $isAccountMerge = false;
        }
        // print_r($_POST['CustomMerge']);die();
        if($isAccountMerge && (isset($_POST['parentId']) && $_POST['parentId'] != null)) {
            $parentId = $_POST['parentId'];
            $childId = $userId;
            $parentModel = User::findOne($parentId); // get data of parent user
            $customMergeRequest = (isset($_POST['CustomMerge'])) ? $_POST['CustomMerge'] : [];
            $customMergePostValue = array();

            // Ignore N/A values
            foreach ($customMergeRequest as $key => $item) {
                if($customMergeRequest[$key] != null) {
                    $customMergePostValue[$key] = $customMergeRequest[$key]; // Ignore null values and extract results
                }
            }

            // check customField value is there in each post. if customField value is N/A ignore the field.
            if(isset($_POST['CustomField'])) {
                $customFieldRequest = $_POST['CustomField'];
                foreach ($customFieldRequest  as $key => $item) {
                    if($customFieldRequest[$key] != null) {
                        $customFieldPostValue[$key] = $customFieldRequest[$key];
                    }
                }
            }


            $parentModel->attributes = $customMergePostValue;
            $postCustomFieldInfo = [];

            $i=0;
            foreach ($customFieldPostValue as $key => $value) {
                $postCustomField = CustomField::find()->where(['label'=>$key])->one();
                $postCustomFieldId = $postCustomField->id;
                $postCustomFieldRelatedId = $parentModel->id;
                $postCustomFieldInfo[$i]['customFieldId'] = $postCustomFieldId;
                $postCustomFieldInfo[$i]['relatedId'] =  $postCustomFieldRelatedId;
                $postCustomFieldInfo[$i]['fieldValue'] =  $customFieldPostValue[$key];
                $postCustomFieldInfo[$i]['childId'] = $childId;
                $i++;
            }

            if($parentModel->validate()) {
                try {
                    if($parentModel->userType == 0 || $parentModel->userType == null) {
                        $parentModel->userType == 5;
                    }
                    if($parentModel->updateAttributes($customMergePostValue)) {
                        $childModel = User::findOne($childId);
                        $status = $this->deleteChildData($childModel, $customFieldPostValue, $parentId, $childId);
                        $this->customFieldProcessAfterPost($postCustomFieldInfo, $parentId);
                        if($status) {
                            return $this->redirect(['potential-matches/admin']);
                        }
                    } else {
                        $childModel = User::findOne($childId);
                        if(!empty($childModel)) {
                            $status = $this->deleteChildData($childModel, $customFieldPostValue, $parentId, $childId);
                            $this->customFieldProcessAfterPost($postCustomFieldInfo, $parentId);
                            if($status) {
                                return $this->redirect(['potential-matches/admin']);
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Yii::$app->session->setFlash('error',Yii::t('messages', 'You are failed to merge contact. Please check appropriate fields are selected in merge'));
                }
            }
        } else {
            if(isset($_POST['preview'])) {
                Yii::$app->session->setFlash('error', Yii::t('messages', 'You are failed to merge contact. Please check appropriate fields are selected in merge'));
            }
        }


        return $this->render('preview', array(
                'model' => $model,
                'parentDatas' => $suggestionListWithCustomField,
                'childData' => $childRecord
            )
        );
    }

    /**
     * @param $childModel
     * @param $customFieldPostValue
     * @param $parentId
     * @param $childId
     * @return bool
     * @throws Exception
     */
    public function deleteChildData($childModel, $customFieldPostValue, $parentId, $childId) {
        $status = false;
        if($childModel->delete()) {
            (new Query())
                ->createCommand()
                ->delete('CustomValue', ['relatedId' => $childId])
                ->execute();
            $status = true;
        }
        if(!empty($customFieldPostValue)) {
            try {
                foreach ($customFieldPostValue as $key => $value) {
                    (new Query)
                        ->createCommand()
                        ->delete('CustomValue', ['customFieldId' => $key, 'relatedId' => $parentId])
                        ->execute();
                    $newFieldPost = array(
                        'customFieldId' => $key,
                        'relatedId' => $parentId,
                        'fieldValue' => $customFieldPostValue[$key]
                    );
                    $cvNewModel = new CustomValue();
                    $cvNewModel->attributes = $newFieldPost;
                    $cvNewModel->save();
                }
                $status = true;
            } catch (Exception $e) {
                $status = false;
            }
        }

        return $status;
    }

    /**
     * @param $customFieldInfo
     * @param null $parentId
     */
    public function customFieldProcessAfterPost($customFieldInfo, $parentId = null) {
        if(empty($customFieldInfo)) { // set custom value null if user not suggest to merge custom field.
            $customFieldParentModel = ArrayHelper::toArray(CustomValue::find()->where(['relatedId' => $parentId])->all());
            foreach ($customFieldParentModel as $model) {

                $customFieldModel = CustomValue::findOne(['id'=>$model['id']]);
                $customFieldModel['fieldValue'] = null;
                $customFieldModel->save();
            }
        } else {
            foreach ($customFieldInfo as $model) { // Update the custom value if the user suggest
                $customFieldParentModel = CustomValue::find()->where(['customFieldId' => $model['customFieldId']])->andWhere(['relatedId' => $model['relatedId']])->one();
                $customFieldParentModel->fieldValue = $model['fieldValue'];
                $customFieldParentModel->save();
            }
        }
    }

}
