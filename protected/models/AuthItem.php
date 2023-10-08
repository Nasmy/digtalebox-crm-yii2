<?php

namespace app\models;

use app\components\RActiveRecord;
use app\components\WebUser;
use Yii;
use yii\db\Query;

/**
 * This is the model class for table "AuthItem".
 *
 * @property string $name
 * @property int $type
 * @property string $description
 * @property string $category
 * @property string $bizrule
 * @property string $data
 * @property int $isGenerated Whether auto generated permission or manually added
 */
class AuthItem extends RActiveRecord
{
    /**
     * Variable to hold selected permission when creating a role
     */
    const TYPE_OPERATION = 0;
    const TYPE_TASK = 1;
    const TYPE_ROLE = 2;
    public $selectedPermissons;

    public $child;

    public $oldChild;

    // Permissions that granted only for freemium Users
    /*public $freemiumPermissions = array(
        'Site.*',
        'Signup.*',
        'BroadcastMessage.*',
        'Configuration.*',
        'Invitation.*',
        'User.*',
        'PotentialMatches.*',
        'Dashboard.*'
    );*/

    public $freemiumPermissions = array(
        'site.*',
        'signup.*',
        'broadcast-message.*',
        'configuration.*',
        'invitation.*',
        'user.*',
        'potential-matches.*',
        'dashboard.*'
    );

    // Permissions that granted only for communitiy management
    /*public $cmPermissions = array(
        'Site.*',
        'Signup.*',
        'Feed.*',
        'FeedAction.*',
        'FollowUp.*',
        'BroadcastMessage.*',
        'Configuration.*',
        'Dashboard.*',
        'AdvancedSearch.*',
        'People.*',
        'Campaign.*',
        'CampaignUsers.*',
        'MessageTemplate.*',
        'MsgBox.*',
        'Erbac.Authitem.*',
        'User.*',
        'PotentialMatches.*',
        'UserMatchMain.*',
        'UserMatchSub.*',
        'SearchCriteria.*',
        'FeedSearchKeyword.*',
        'CustomField.*',
        'Form.*',
        'Newsletter.*',
        'AdvancedBulkInsert.*',
        // TODO: if feature the client need this access we need to un comment
        'CandidateInfo.ManageImages',
        'CandidateInfo.UpdateTexts',
        'CandidateInfo.Theme',
        'CandidateInfo.ChangeBgImage',
        'ABTesting.*',
        'KeywordUrl.*',
        'FormMembershipDonation.*',
        'MembershipType.*',
        'Event.*',
        'EventReminder.*',
    );*/
    public $cmPermissions = array(
        'site.*',
        'signup.*',
        'feed.*',
        'feed-action.*',
        'follow-up.*',
        'broadcast-message.*',
        'configuration.*',
        'dashboard.*',
        'advanced-search.*',
        'people.*',
        'campaign.*',
        'campaign-users.*',
        'message-template.*',
        'msg-box.*',
        'erbac.auth-item.*',
        'user.*',
        'potential-matches.*',
        'user-match-main.*',
        'user-match-sub.*',
        'search-criteria.*',
        'feed-search-keyword.*',
        'custom-field.*',
        'form.*',
        'form-builder.*',
        'newsletter.*',
        'advanced-bulk-insert.*',
        // TODO: if feature the client need this access we need to un comment
        'candidate-info.ManageImages',
        'candidate-info.UpdateTexts',
        'candidate-info.Theme',
        'candidate-info.ChangeBgImage',
        'ABTesting.*',
        'keyword-url.*',
        'form-membership-donation.*',
        'membership-type.*',
        'event.*',
        'event-reminder.*',
    );

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'AuthItem';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'type'], 'required'],
            [['type'], 'number', 'integerOnly' => true],
            [['name', 'category'], 'string', 'max' => 64],
            [['name'], 'unique'],
            [['name'], 'match', 'pattern' => '/^[a-zA-Z][A-Za-z0-9_.]*$/', 'message' => 'Name is invalid'],
            [['description', 'isGenerated', 'bizrule', 'data', 'child', 'oldChild'], 'safe'],
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            [['name', 'type', 'description', 'category', 'bizrule', 'data'], 'safe', 'on' => 'search'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'name' => Yii::t('messages', 'Name'),
            'type' => Yii::t('messages', 'Type'),
            'description' => Yii::t('messages', 'Description'),
            'category' => Yii::t('messages', 'Category'),
            'bizrule' => Yii::t('messages', 'Bizrule'),
            'data' => Yii::t('messages', 'Data'),
            'isGenerated' => Yii::t('messages', 'Whether auto generated permission or manually added'),
            'child' => 'Child Item'
        ];
    }

    public function getAuthAssignments()
    {
        return $this->hasMany(AuthAssignment::class, ['itemname' => 'name']);
    }

    public static function getAssignedItems($userId, $key = false, $type = null)
    {
        $items = AuthAssignment::find()->where(['userId' => $userId])->with('authItems')->one();
        $assignedItems = array();
        if (null != $items) {
            foreach ($items->authItems as $item) {
                $assignedItems[] = $key ? $item->name : $item->description;
            }
        }
        return $assignedItems;
    }

    /**
     * Check whether role is default
     * @param string $roleName Role name
     * @return boolean true if default role otherwise false
     */
    public static function isDefaultRole($roleName)
    {
        return in_array($roleName, array(
                WebUser::POLITICIAN_ROLE_NAME,
                WebUser::SUPERADMIN_ROLE_NAME,
                WebUser::POLITICIAN_ADMIN_ROLE_NAME,
                WebUser::TEAM_LEAD_ROLE_NAME,
                WebUser::SUPPORTER_ROLE_NAME,
                WebUser::SUPPORTER_WITHOUT_TEAM,
                WebUser::DELETED_TEAM_MEMBER)
        );
    }

    /**
     * Check whether user has permission
     * @param AuthItem $modelAuthItem Auth item model instance
     * @return boolean true if has permission otherwise false
     */
    public function checkAccess($modelAuthItem)
    {
        if (Yii::$app->session->get("is_super_admin")) {
            return true;
        } else if (in_array($modelAuthItem->name, array(
            WebUser::POLITICIAN_ROLE_NAME,
            WebUser::SUPERADMIN_ROLE_NAME,
            WebUser::POLITICIAN_ADMIN_ROLE_NAME,
            WebUser::TEAM_LEAD_ROLE_NAME,
            WebUser::SUPPORTER_ROLE_NAME,
            WebUser::SUPPORTER_WITHOUT_TEAM,
            WebUser::DELETED_TEAM_MEMBER))) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Prepare role type label whether system default role or custom role
     * @param string $roleName Role name
     * @return string Bootstrap label
     */
    public function getRoleTypeLabel($roleName)
    {
        if ($this->isDefaultRole($roleName)) {
            return '<span class="label label-important">' . Yii::t('messages', 'System Default') . '</span>';
        } else {
            return '<span class="label label-success">' . Yii::t('messages', 'User Defined') . '</span>';
        }
    }

    /**
     * Returns all controllers under the specified path.
     * @param string $path the path.
     * @return array the controllers.
     */
    public function getControllersInPath($path) {
        $controllers = array();

        if (file_exists($path) === true) {
            $controllerDirectory = scandir($path);
            foreach ($controllerDirectory as $entry) {
                if ($entry{0} !== '.') {
                    $entryPath = $path.DIRECTORY_SEPARATOR.$entry;
                    if (strpos(strtolower($entry), 'controller') !== false) {
                        $name = substr($entry, 0, -14);
                        $controllers[strtolower($name)] = array(
                            'name'=>$name,
                            'file'=>$entry,
                            'path'=>$entryPath,
                        );
                    }

                    if (is_dir($entryPath) === true)
                        foreach ($this->getControllersInPath($entryPath) as $controllerName=> $controller)
                            $controllers[$controllerName] = $controller;
                }
            }
        }

        return $controllers;
    }

    /**
     * Check whether permission already generated.
     * @param string $name Permission name.
     * @return boolean true if permission exists otherwise false.
     */
    public static function isPermissionExists($name)
    {
        $model = AuthItem::findOne(array('name'=>$name));
         if (null != $model)
        {
             return true;
        }

        return false;
    }

    /**
     * Returns all the controllers under the specified path.
     * @param string $path the path.
     * @return array the controllers.
     */
    protected function getControllersInModules($path) {
        $items = array();

        $modulePath = $path.DIRECTORY_SEPARATOR.'modules';
        if (file_exists($modulePath) === true) {
            $moduleDirectory = scandir($modulePath);
            foreach ($moduleDirectory as $entry) {
                if (substr($entry, 0, 1) !== '.' /* && $entry !== 'rights' */) {
                    $subModulePath = $modulePath.DIRECTORY_SEPARATOR.$entry;
                    if (file_exists($subModulePath) === true) {
                        $items[$entry]['controllers'] = $this->getControllersInPath($subModulePath.DIRECTORY_SEPARATOR.'controllers');
                        $items[$entry]['modules'] = $this->getControllersInModules($subModulePath);
                    }
                }
            }
        }
        return $items;
    }
    /**
     * Returns a list of all application controllers.
     * @return array the controllers.
     */
    public function getAllControllers() {
        $basePath = Yii::$app->basePath;
        $items['controllers'] = $this->getControllersInPath($basePath.DIRECTORY_SEPARATOR.'controllers');
        $items['modules'] = $this->getControllersInModules($basePath);
        return $items;
    }


    /**
     * Findout controller actions in controller path
     */
    public function getControllerActions($items = null) {
        if ($items === null)
            $items = $this->getAllControllers();

        foreach ($items['controllers'] as $controllerName=> $controller) {
            $actions = array();
            $file = fopen($controller['path'], 'r');
            $lineNumber = 0;
            while (feof($file) === false) {
                ++$lineNumber;
                $line = fgets($file);
                preg_match('/public[ \t]+function[ \t]+action([A-Z]{1}[a-zA-Z0-9]+)[ \t]*\(/', $line, $matches);
                if ($matches !== array()) {
                    $name = $matches[1];
                    $actions[strtolower($name)] = array(
                        'name'=>$name,
                        'line'=>$lineNumber
                    );
                }
            }

            $items['controllers'][$controllerName]['actions'] = $actions;
        }

        foreach ($items['modules'] as $moduleName=> $module)
            $items['modules'][$moduleName] = $this->getControllerActions($module);

        return $items;
    }


    /**
     * Retrieve descriptions of a role permissions.Used to display on role view
     * @param string $roleName Role name
     * @return string permissionDes String of permission descriptions.
     */
    public function getPermissionDescriptions($roleName)
    {
        $query = new Query();
        $authItemChildModels = $query->select('t.*, AI.description')
            ->from('AuthItemChild t')
            ->join('INNER JOIN', 'AuthItem AI', 'AI.name = t.child')
            ->where(['t.parent' => $roleName])
            ->all();
        $permissionDes = '';

        if (null != $authItemChildModels) {
            foreach ($authItemChildModels as $authItemChild) {
                if (isset($authItemChild['description']) && $authItemChild['description'] != '') {
                    $permissionDes .= Yii::t("messages", $authItemChild['description']) . "<br/>";
                }
            }
        }
        return $permissionDes;
    }

    /**
     * Retrieve items by item type.This will not return system default super admin role
     * @return array items.
     */
    public static function getItemOptions($type = AuthItem::TYPE_ROLE)
    {

        $items = AuthItem::find()->where(['=', 'type', $type])->andWhere(['!=', 'name', WebUser::SUPERADMIN_ROLE_NAME])->all();

        $itemOptions = array();

        if (null != $items) {
            foreach ($items as $item) {
                $itemOptions[$item->name] = $item->description;
            }
        }

        return $itemOptions;
    }

    /**
     * {@inheritdoc}
     * @return AuthItemQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new AuthItemQuery(get_called_class());
    }
}
