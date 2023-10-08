<?php

namespace app\controllers;

use app\models\AuthAssignment;
use app\models\AuthItemChild;
use Yii;
use app\models\AuthItem;
use app\models\AuthItemSearch;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\db\Exception;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * AuthItemController implements the CRUD actions for AuthItem model.
 */
class AuthItemController extends Controller
{
    public $layout = 'column1';

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
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
     * Lists all AuthItem models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new AuthItemSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single AuthItem model.
     * @param $itemName
     * @param $type
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($itemName, $type)
    {
        $model = $this->findModel($itemName);
        switch ($type) {

            case AuthItem::TYPE_ROLE:

                $permissionDes = $model->getPermissionDescriptions($itemName);
                return $this->render('role_view', array(
                    'model' => $model,
                    'permissionDes' => $permissionDes
                ));

                break;
        }
    }

    /**
     * Creates a new AuthItem model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     * @throws Exception
     */
    public function actionCreate($type = AuthItem::TYPE_OPERATION)
    {
        $model = new AuthItem();
        switch ($type) {
            case AuthItem::TYPE_OPERATION:
                if (isset($_POST['AuthItem'])) {
                    $model->attributes = $_POST['AuthItem'];
                    $model->type = $type;
                    $model->isGenerated = 0;
                    if ($model->validate()) {
                        if ($model->save()) {
                            $modelAuthItemChild = new AuthItemChild();
                            if ("" != $model->child) {
                                $modelAuthItemChild->parent = $model->name;
                                $modelAuthItemChild->child = $model->child;
                                try {
                                    $modelAuthItemChild->save(false);
                                } catch (Exception $e) {
                                }
                            }
                            Yii::$app->session->setFlash('success', Yii::t('messages', 'Permission created'));
                            return $this->redirect(['admin', 'type' => AuthItem::TYPE_OPERATION]);
                        } else {
                            Yii::$app->session->setFlash('error', Yii::t('messages', 'Permission create failed'));
                        }
                    } else {
                        $validationErrors = json_encode($model->errors);
                        Yii::$app->session->setFlash('error', Yii::t('messages', "Permission item create failed. Validation errors:{$validationErrors}"));
                    }
                }
                return $this->render('create', array(
                    'model' => $model,
                ));

                break;
            case AuthItem::TYPE_ROLE:

                $permissionsSubmitFail = array();
                if (isset($_POST['AuthItem'])) {
                    $isSubmitFail = true;

                    $model->attributes = $_POST['AuthItem'];

                    $permissionsSubmitFail = isset($_POST['selection']) ? $_POST['selection'] : array();
                    $model->selectedPermissons = $permissionsSubmitFail;
                    $model->type = AuthItem::TYPE_ROLE;
                    $model->isGenerated = 0;
                    if ($model->validate()) {
                        if (isset($_POST['selection'])) {
                            if ($model->save()) {
                                $isSubmitFail = false;

                                // Add permissions
                                $command = Yii::$app->db->createCommand('SET foreign_key_checks = 0;');
                                $command->execute();

                                foreach ($_POST['selection'] as $permission) {
                                    $modelAuthItemChild = new AuthItemChild();
                                    $modelAuthItemChild->parent = $model->name;
                                    $modelAuthItemChild->child = $permission;
                                    $modelAuthItemChild->save();
                                }

                                Yii::$app->session->setFlash('success', Yii::t('messages', 'Role created'));
                                return $this->redirect(['admin', 'type' => AuthItem::TYPE_ROLE]);

                            } else {
                                Yii::$app->session->setFlash('error', Yii::t('messages', 'Role create failed'));
                            }
                        } else {
                            Yii::$app->session->setFlash('error', Yii::t('messages', 'Please select at least one permission'));
                        }
                    } else {
                        Yii::$app->session->setFlash('error', 'Role create failed. Validation errors');
                    }
                } else {
                    $isSubmitFail = false;
                }

                $query = new Query();
                $query->where(['=',"type",AuthItem::TYPE_OPERATION]);

                // Except superadmin for other users system shows only his permissions
                if (!Yii::$app->session->get('is_super_admin')) {
                    $assignedRole = AuthItem::getAssignedItems(Yii::$app->user->id, true, AuthItem::TYPE_ROLE);
                    $assignedOperations = AuthItemChild::getItemOperations($assignedRole[0]);
                    $query->andWhere(['name'=>explode(',', $assignedOperations)]);
                }

                $query->orderBy('category');
                $query->from('AuthItem');
                $query->all();

                $dataProvider = new ActiveDataProvider([
                        'query' => $query,
                        'pagination' => false,
                    ]
                );
                return $this->render('create_role', array(
                    'model' => $model,
                    'dataProvider' => $dataProvider,
                    'permissions' => array(),
                    'permissionsSubmitFail' => $permissionsSubmitFail,
                    'isSubmitFail' => $isSubmitFail
                ));
                break;
        }
    }

    /**
     * @param int $type
     * @return string
     */
    public function actionAdmin($type = AuthItem::TYPE_OPERATION)
    {
        $searchModel = new AuthItemSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        switch ($type) {
            case AuthItem::TYPE_OPERATION:
                $searchModel->type = $type;
                if (isset($_GET['AuthItem'])) {
                    $searchModel->attributes = $_GET['AuthItem'];
                }

                return $this->render('admin', array(
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ));
                break;
            case AuthItem::TYPE_ROLE:
                $searchModel->type = AuthItem::TYPE_ROLE;
                if (isset($_GET['AuthItem'])) {
                    $searchModel->attributes = $_GET['AuthItem'];
                }

                return $this->render('role_admin', array(
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ));
                break;
        }
    }

    /**
     * Generates permissions for controller actions.
     */
    public function actionGeneratePermissions()
    {
        $model = new AuthItem();
        $actions = $model->getControllerActions();
        $items = array();
        $id = 0;

        if (isset($_POST['save'])) {

            $selectedPermissions = isset($_POST['selection']) ? $_POST['selection'] : array();
            $selectedPerminssionNames = $this->extractPermissionNames($selectedPermissions);
            $permissionModels = AuthItem::find()->all();

            if (null != $permissionModels) {
                foreach ($permissionModels as $permissionModel) {
                    try {
                        if ('0' != $permissionModel->isGenerated && !in_array($permissionModel->name, $selectedPerminssionNames)) {
                            $permissionModel->delete();
                        }
                    } catch (Exception $e) {
                    }
                }
            }

            foreach ($selectedPermissions as $permissionData) {

                list($name, $category) = explode(",", $permissionData);

                $modelAuthItem = new AuthItem();
                $modelAuthItem->name = $name;
                $modelAuthItem->type = AuthItem::TYPE_OPERATION;
                $modelAuthItem->category = $category;
                $modelAuthItem->description = $name;
                $modelAuthItem->isGenerated = 1;

                try {
                    $modelAuthItem->save();
                } catch (Exception $e) {
                    Yii::$app->appLog->writeLog("Permissions generated for controller actions Save" . json_encode($e->getMessage()));
                }
            }

            Yii::$app->appLog->writeLog("Permissions generated for controller actions");

            Yii::$app->session->setFlash('success', Yii::t('messages', 'Permissions generated for actions'));

            return $this->redirect(['admin', 'type' => AuthItem::TYPE_OPERATION]);
        } else {

            if (!empty($actions['controllers'])) {
                foreach ($actions['controllers'] as $controller) {
                    foreach ($controller['actions'] as $action) {
                        $items[] = array(
                            'id' => $id,
                            'name' => ucfirst($controller['name']) . '.' . ucfirst($action['name']),
                            'category' => $controller['name'],
                        );
                        $id++;
                    }
                }
            }

            if (!empty($actions['modules'])) {
                foreach ($actions['modules'] as $module_name => $mod_controllers) {
                    foreach ($mod_controllers as $mod_controller) {
                        foreach ($mod_controller as $mod_con_name => $mod_con_data) {
                            foreach ($mod_con_data['actions'] as $action_info) {
                                $items[] = array(
                                    'id' => $id,
                                    'name' => ucfirst($module_name) . '.' . ucfirst($mod_con_name) . '.' . ucfirst($action_info['name']),
                                    'category' => "{$module_name}.{$mod_con_name}",
                                );
                                $id++;
                            }
                        }
                    }
                }
            }
        }

        $data_provider = new ArrayDataProvider([
            'allModels' => $items,
            'pagination' => false
        ]);

        return $this->render('permissions', array('dataProvider' => $data_provider, 'model' => $model));
    }


    /**
     * Updates an existing AuthItem model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param $itemName
     * @param int $type
     * @return mixed
     * @throws Exception
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($itemName, $type = AuthItem::TYPE_OPERATION)
    {
        $model = $this->findModel($itemName);
        switch ($type) {
            case AuthItem::TYPE_OPERATION:
                if (isset($_POST['AuthItem'])) {
                    $model->attributes = $_POST['AuthItem'];
                    if ($model->validate()) {

                        if ($model->save()) {
                            $modelAuthItemChild = new AuthItemChild();
                            if ($model->child != $model->oldChild) {
                                $modelAuthItemChild->deleteAll(['child' => $model->oldChild, 'parent' => $model->name]);
                            }
                            if ("" != $model->child) {
                                $modelAuthItemChild->parent = $model->name;
                                $modelAuthItemChild->child = $model->child;
                                try {
                                    $modelAuthItemChild->save(false);
                                } catch (Exception $e) {
                                    Yii::$app->session->setFlash('error', Yii::t('messages', 'Permission Created Failed'));
                                    return $this->redirect(['admin', 'type' => AuthItem::TYPE_OPERATION]);
                                }
                            }
                            Yii::$app->session->setFlash('success', Yii::t('messages', 'Permission updated'));
                            return $this->redirect(['admin', 'type' => AuthItem::TYPE_OPERATION]);
                        }
                    }
                } else {
                    $child = AuthItemChild::getChild($itemName);
                    $model->child = $child;
                    $model->oldChild = $child;
                }

                return $this->render('update', [
                    'model' => $model
                ]);

                break;
            case AuthItem::TYPE_ROLE:

                $permissionsSubmitFail = array();

                if (isset($_POST['AuthItem'])) {
                    $isSubmitFail = true;

                    $model->attributes = $_POST['AuthItem'];

                    $permissionsSubmitFail = isset($_POST['selection']) ? $_POST['selection'] : array();

                    $model->selectedPermissons = $permissionsSubmitFail;

                    $model->type = AuthItem::TYPE_ROLE;

                    if ($model->validate()) {
                        if (isset($_POST['selection'])) {

                            if ($model->save()) {
                                $isSubmitFail = false;

                                // Add permissions
                                $command = Yii::$app->db->createCommand('SET foreign_key_checks = 0;');
                                $command->execute();
                                AuthItemChild::deleteAll(array('parent' => $model->name));
                                foreach ($_POST['selection'] as $permission) {
                                    $modelAuthItemChild = new AuthItemChild();
                                    $modelAuthItemChild->parent = $model->name;
                                    $modelAuthItemChild->child = $permission;
                                    $modelAuthItemChild->save();
                                }

                                Yii::$app->session->setFlash('success', Yii::t('messages', 'Role updated'));
                                Yii::$app->appLog->writeLog("Role updated. Role name:{$model->name}, Permissions:" . json_encode($_POST['selection']));

                                return $this->redirect(array('admin', 'type' => AuthItem::TYPE_ROLE));
                            } else {
                                Yii::$app->appLog->writeLog("Role update failed. Role name:{$model->name}, Permissions:" . json_encode($_POST['selection']));
                                Yii::$app->session->setFlash('error', Yii::t('messages', 'Role update failed'));
                            }
                        } else {
                            Yii::$app->appLog->writeLog("Role update failed. No permission selected");
                            Yii::$app->session->setFlash('error', Yii::t('messages', 'Please select at least one permission'));
                        }
                    } else {
                        Yii::$app->appLog->writeLog("Role update failed. Validation errors:" . json_encode($model->errors));
                    }
                } else {
                    $isSubmitFail = false;
                }

                $isSubmitFail = false;
                $query = new Query();
                $query->where('type =' . AuthItem::TYPE_OPERATION);
                $query->orderBy('category');
                $query->from('AuthItem');
                $query->all();
                $dataProvider = new ActiveDataProvider([
                    'query' => $query,
                    'pagination' => false
                ]);

                return $this->render('update_role', array(
                    'model' => $model,
                    'dataProvider' => $dataProvider,
                    'itemName' => $itemName,
                    'permissions' => array(),
                    'permissionsSubmitFail' => $permissionsSubmitFail,
                    'isSubmitFail' => $isSubmitFail
                ));

                break;
        }
    }

    /**
     * Deletes an existing AuthItem model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param $itemName
     * @param int $type
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($itemName, $type = AuthItem::TYPE_OPERATION)
    {
        $model = $this->findModel($itemName);

        switch ($type) {

            case AuthItem::TYPE_OPERATION:
            case AuthItem::TYPE_ROLE:

                $modelAuthAssignment = AuthAssignment::find()->where(['itemname' => $itemName])->all();
                // print_r($modelAuthAssignment); die();

                $typeName = $type == AuthItem::TYPE_OPERATION ? 'Permission' : 'Role';
                $allChildrens = $this->deleteGetChildrens($itemName);

                if (null == $modelAuthAssignment) {
                    if ($model->delete()) {
                        foreach ($allChildrens as $v) {
                            AuthItemChild::deleteAll(['parent' => $v]);
                        }
                        Yii::$app->session->setFlash('success', Yii::t('messages', "{$typeName} deleted"));
                    } else {
                        Yii::$app->session->setFlash('error', Yii::t('messages', "{$typeName} delete failed"));
                    }
                } else {
                    Yii::$app->session->setFlash('error', Yii::t('messages', "{$typeName} delete failed, already being assigned to an user"));
                }

                break;
        }

        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * Extract names from selected permissions.
     * @params Array $selectedPermissions Selected permissions
     * @param $selectedPermissions
     * @return array $arrNames Array of extracted names
     */
    private function extractPermissionNames($selectedPermissions)
    {
        $arrNames = array();
        foreach ($selectedPermissions as $permission_data) {
            list($name, $group) = explode(",", $permission_data);
            $arrNames[] = $name;
        }

        return $arrNames;
    }

    /**
     * Retrieve parent item of the chile
     * @param string $authItem Permission item
     * @return string Parent permission item
     */
    public function actionGetParent($authItem)
    {
        $parentItem = AuthItemChild::getChild($authItem);
        echo $parentItem;
    }

    /**
     * Retrieve childrens attached to a parent
     * @param string $authItem Permission item
     * @return string Comma separated string of childerens
     */
    public function actionGetChildren($authItem)
    {
        $childrens = AuthItemChild::getChildren($authItem);
        echo $childrens;
    }

    /**
     * @param $authItem
     * @return array
     */
    public function deleteGetChildrens($authItem)
    {
        $childrens = AuthItemChild::getItemChildren($authItem);
        return $childrens;
    }

    /**
     * Finds the AuthItem model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return AuthItem the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */


    protected function findModel($id)
    {
        if (($model = AuthItem::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
