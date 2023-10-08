<?php

namespace app\models;

use app\components\Validations\ValidateResourceRequireTypeOptions;
use app\components\Validations\ValidateResourecesFile;
use app\components\WebUser;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\validators\FileValidator;
use yii\validators\Validator;
use yii\web\UploadedFile;

/**
 * This is the model class for table "resource".
 *
 * @property int $id
 * @property string $title
 * @property string $description
 * @property string $tag Comma separated tag list
 * @property int $type 1 - image, 2-video,3-documents
 * @property int $size
 * @property string $fileName
 * @property int $status 0-pending approval,1-approved/active,2-deleted
 * @property int $createdBy
 * @property string $createdAt
 * @property int $updatedBy
 * @property string $updatedAt
 */
class Resource extends \yii\db\ActiveRecord
{
    // Resource types
    const IMAGE = 1;
    const VIDEO = 2;
    const DOCUMENT = 3;

    // Resource statuses
    const PENDING_APPROVAL = 0;
    const APPROVED = 1;
    const REJECTED = 2;
    const DELETED = 3;

    // Max file size
    const MAX_FILE_SIZE = 5242880; // 5MB

    // Dates
    public $fromDate = null;
    public $toDate = null;

    public $file = null;
    public $url = null;

    // Valid file types
    public $imageTypes = array('jpg', 'jpeg', 'gif', 'png');
    public $documentTypes = array('doc', 'docx', 'pdf', 'xls', 'xlsx');

    public $prvResType = null;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Resource';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.

        return [
            // Common Create/Update
            [['title', 'description', 'tag', 'type', 'size', 'status'], 'required'],
            [['type', 'size', 'status'], 'integer'],
            [['title', 'fileName'], 'string', 'max' => 64],
            [['description'], 'string', 'max' => 128],
            [['file'], ValidateResourecesFile::className(), 'imgType' => self::IMAGE, 'documentType' => self::DOCUMENT, 'on' => ['create', 'update']],
            [['url'], 'url', 'defaultScheme' => 'http', 'skipOnEmpty' => true],
            [['file'], 'file', 'skipOnEmpty' => true, 'maxSize' => self::MAX_FILE_SIZE, 'tooBig' => Yii::t('messages', 'File needs to be smaller than {size} Mb', array('size' => 5))],

            //todo: ValidateResourceRequireTypeOptions::className() need to improve

            // Create
            [['createdBy', 'createdAt', 'fileName'], 'required', 'on' => 'create'],
            [['file'], 'requireTypeOptions', 'on' => 'create'],
            [['url'], 'requireTypeOptions', 'on' => 'create'],
            // Update
            [['updatedBy', 'updatedAt'], 'required', 'on' => 'update'],
            [['updatedBy'], 'string', 'max' => 20, 'on' => 'update'],
            [['file'], 'requireTypeOptionsOnUpdate', 'on' => 'update'],
            [['url'], 'requireTypeOptionsOnUpdate', 'on' => 'update'],

            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            [['id', 'title', 'description', 'tag', 'type', 'size', 'fileName', 'status', 'createdBy', 'createdAt', 'updatedBy', 'updatedAt', 'fromDate', 'toDate'], 'safe', 'on' => 'search']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => Yii::t('messages', 'Title'),
            'description' => Yii::t('messages', 'Description'),
            'tag' => Yii::t('messages', 'Tag'),
            'type' => Yii::t('messages', 'Type'),
            'size' => Yii::t('messages', 'Size'),
            'fileName' => Yii::t('messages', 'File Name'),
            'status' => Yii::t('messages', 'Status'),
            'createdBy' => Yii::t('messages', 'Created By'),
            'createdAt' => Yii::t('messages', 'Created At'),
            'updatedBy' => 'Updated By',
            'updatedAt' => 'Updated At',
            'fromDate' => Yii::t('messages', 'Start Date'),
            'toDate' => Yii::t('messages', 'End Date')
        ];
    }

    /**
     * Validate upload file type according to selected type
     */
    public function validateFile()
    {
        if (null != $this->file) {
            if (self::IMAGE == $this->type) {
                $validator = new FileValidator(['extensions' => 'png,jpg,gif,jpeg', 'wrongExtension' => "The file {$this->file->name} cannot be uploaded. Only files with these extensions are allowed: jpg, jpeg, gif, png."]);
                $valid = $validator->validate($this->file);

                if (!$valid) {
                    $this->addError('file', $validator->wrongExtension);
                }

            } else if (self::DOCUMENT == $this->type) {
                $validator = new FileValidator(['extensions' => 'doc,docx,pdf,xls,xlsx', 'wrongExtension' => "The file {$this->file->name} cannot be uploaded. Only files with these extensions are allowed: doc,docx,pdf,xls,xlsx"]);
                $valid = $validator->validate($this->file);

                if (!$valid) {
                    $this->addError('file', $validator->wrongExtension);
                }
            }
        }
    }

    /**
     * Retrieve resource type dropdown options.
     * @param boolean $emptyOption Whether to include empty option to the list
     * @param boolean $labelOnly Whether to return only label associate with the option
     * @param string $optionValue Option value that required for label
     * @return mixed Array of options or label
     */
    public function getResourceTypeOptions($emptyOption = false, $labelOnly = false, $optionValue = null)
    {
        $options = array();
        if ($emptyOption) {
            $options[''] = Yii::t('messages', '- Resource Type -');
        }

        $options[self::IMAGE] = Yii::t('messages', 'Image');
        $options[self::VIDEO] = Yii::t('messages', 'Video');
        $options[self::DOCUMENT] = Yii::t('messages', 'Document');

        if ($labelOnly) {
            return $options[$optionValue];
        } else {
            return $options;
        }
    }


    /**
     * Require upload file or URL depend on slected type
     */
    public function requireTypeOptions($attribute, $params)
    {
        if (self::IMAGE == $this->type || self::DOCUMENT == $this->type) {
            $validator = Validator::createValidator('required', $this, 'file', array());
            $validator->validate($this);
        } else if (self::VIDEO == $this->type) {
            $validator = Validator::createValidator('required', $this, 'url', array());
            $validator->validate($this);
        }
    }

    /**
     * Required resource type according to selected type on update
     */
    public function requireTypeOptionsOnUpdate($attribute, $params)
    {
        if ($this->prvResType != $this->type) {
            $this->requireTypeOptions($attribute, $params);
        }
    }

    /**
     * Retrieve status type dropdown options.
     * @param boolean $emptyOption Whether to include empty option to the list
     * @param boolean $labelOnly Whether to return only label associate with the option
     * @param string $optionValue Option value that required for label
     * @return mixed Array of options or label
     */
    public function getStatusOptions($emptyOption = false, $labelOnly = false, $optionValue = null)
    {
        $options = array();
        if ($emptyOption) {
            $options[''] = Yii::t('messages', '- Status -');
        }
        $options[self::PENDING_APPROVAL] = Yii::t('messages', 'Pending Approval');
        $options[self::APPROVED] = Yii::t('messages', 'Approved');
        $options[self::REJECTED] = Yii::t('messages', 'Rejected');
        $options[self::DELETED] = Yii::t('messages', 'Deleted');

        if ($labelOnly) {
            return $options[$optionValue];
        } else {
            return $options;
        }
    }

    /**
     * {@inheritdoc}
     * @return ResourceQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ResourceQuery(get_called_class());
    }
}
