<?php

namespace app\models;

use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

/**
 * This is the model class for table "BroadcastMessage".
 *
 * @property int $id
 * @property string $fbPost Facebook Message
 * @property string $twPost Twitter message
 * @property string $lnPost LinkedIn Message
 * @property string $fbProfPost Facebook profile post
 * @property string $lnPagePost LinkedIn page post
 * @property int $fbPostStatus 0-pending,1-published,2-failed
 * @property int $twPostStatus 0-pending,1-published,2-failed
 * @property int $lnPostStatus 0-pending,1-published,2-failed
 * @property int $fbProfPostStatus 0-pending, 1-published, 2- failed
 * @property int $lnPagePostStatus
 * @property string $fbImageName Facebook Image name
 * @property string $twImageName Twitter image name
 * @property string $lnImageName LinkedIn image name
 * @property string $lnPageImageName LinkedIn page image name
 * @property string $fbProfImageName Facebook profile image name
 * @property string $publishDate Publish date and time
 * @property int $createdBy User id of the creator
 * @property string $createdAt Created date and time
 * @property int $updatedBy User id fo the updator
 * @property string $updatedAt Updated date and time
 * @property int $recordStatus Record status. 0-pending,1-draft, 2-processed
 */
class BroadcastMessage extends \yii\db\ActiveRecord
{
    const FB_POST_LENGTH = 500;
    const TW_POST_LENGTH = 280;
    const LN_POST_LENGTH = 700;

    const MSG_STATUS_PENDING = 0;
    const MSG_STATUS_PUBLISHED = 1;
    const MSG_STATUS_FAILED = 2;

    const REC_STATUS_PENDING = 0;
    const REC_STATUS_DRAFT = 1;
    const REC_STATUS_PROCESSED = 2;

    public $fbImageFile;
    public $twImageFile;
    public $lnImageFile;
    public $lnPageImageFile;
    public $fbProfImageFile;
    public $fromDate;
    public $toDate;
    public $longUrl;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'BroadcastMessage';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // Common
            [['twPost'], 'string', 'max' => self::TW_POST_LENGTH],
            [['fbPost', 'fbProfPost'], 'string', 'max' => self::FB_POST_LENGTH],
            [['lnPost', 'lnPagePost'], 'string', 'max' => self::LN_POST_LENGTH],
            [['fbImageName', 'twImageName', 'lnImageName'], 'string', 'max' => 20],
            [['createdBy', 'updatedBy'], 'string', 'max' => 20],
            [['fbPostStatus', 'twPostStatus', 'lnPostStatus'], 'integer'],
            [['fbImageFile', 'twImageFile', 'lnImageFile', 'lnPageImageFile', 'fbProfImageFile'], 'file', 'skipOnEmpty' => true, 'extensions' => 'jpg,jpeg,gif,png'],
            [['longUrl'], 'safe'],

            // Create
            [['publishDate', 'createdBy', 'createdAt'], 'required', 'on' => 'create'],
            [['fbPost'], 'checkAllEmpty', 'skipOnEmpty' => false, 'skipOnError' => false, 'on' => 'create'],

            // Updatae
            [['publishDate', 'updatedBy', 'updatedAt'], 'required', 'on' => 'update'],
            [['fbPost'], 'checkAllEmpty', 'skipOnEmpty' => false, 'skipOnError' => false, 'on' => 'update'],

            [['fbPost', 'lnPost', 'fbImageFile', 'twImageFile', 'lnImageFile', 'lnPageImageFile', 'lnPagePost', 'fbProfImageFile'], 'safe'],

            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            [['publishDate', 'fromDate', 'toDate'], 'safe', 'on' => 'search'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fbPost' => Yii::t('messages', 'Facebook Post'),
            'twPost' => Yii::t('messages', 'Twitter Post'),
            'lnPost' => Yii::t('messages', 'LinkedIn Post'),
            'lnPagePost' => Yii::t('messages', 'LinkedIn Page Post'),
            'fbProfPost' => Yii::t('messages', 'Facebook Profile Post'),
            'fbPostStatus' => Yii::t('messages', 'Facebook Post Status'),
            'fbProfPostStatus' => Yii::t('messages', 'Facebook Profile Post Status'),
            'twPostStatus' => Yii::t('messages', 'Twitter Post Status'),
            'lnPostStatus' => Yii::t('messages', 'LinkedIn Post Status'),
            'lnPagePostStatus' => Yii::t('messages', 'LinkedIn Page Post Status'),
            'fbImageName' => 'Fb Image Name',
            'twImageName' => 'Tw Image Name',
            'lnImageName' => 'Ln Image Name',
            'publishDate' => Yii::t('messages', 'Publish Time'),
            'createdBy' => Yii::t('messages', 'Created By'),
            'createdAt' => Yii::t('messages', 'Created At'),
            'updatedBy' => Yii::t('messages', 'Updated By'),
            'updatedAt' => Yii::t('messages', 'Updated At'),
            'fbImageFile' => Yii::t('messages', ''),
            'twImageFile' => Yii::t('messages', ''),
            'lnImageFile' => Yii::t('messages', ''),
            'fbProfImageFile' => Yii::t('messages', ''),
            'lnPageImageFile' => Yii::t('messages', ''),
            'fromDate' => Yii::t('messages', 'Start Date'),
            'toDate' => Yii::t('messages', 'End Date'),
            'longUrl' => Yii::t('messages', 'Long URL'),
        ];
    }

    /**
     * Retrieve image saving file name
     * @param string $type Post type
     * @return string File name
     */
    public static function getImageFileName($id, $suffix, $extension)
    {

        return "{$id}_broadcast_{$suffix}.{$extension}";
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
     * @return ActiveDataProvider the data provider that can return the models
     * based on the search/filter conditions.
     */
    public function search($params = null)
    {
         // @todo Please modify the following code to remove attributes that should not be searched.
        $expression = new Expression('NOW()');
        $now = (new \yii\db\Query)->select($expression)->scalar();  // SELECT NOW();

        $query = (new \yii\db\Query())
            ->select('*')
            ->from('BroadcastMessage')
            ->orderBy('publishDate', SORT_DESC)
            ->limit(10);

        if (!empty($params['BroadcastMessage']['fromDate']) && empty($params['BroadcastMessage']['toDate'])) {
            $query->andFilterWhere(['>', 'publishDate', $params['BroadcastMessage']['fromDate']]);
        } else if (empty($params['BroadcastMessage']['fromDate']) && !empty($params['BroadcastMessage']['fromDate'])) {
            $query->andFilterWhere(['<', 'publishDate', $params['BroadcastMessage']['toDate'] . ' 23:59:59']);
        } else if (!empty($params['BroadcastMessage']['fromDate']) && !empty($params['BroadcastMessage']['toDate'])) {
            $query->andFilterWhere(['>', 'publishDate', $params['BroadcastMessage']['fromDate']]);
            $query->andFilterWhere(['<', 'publishDate', $params['BroadcastMessage']['toDate'] . ' 23:59:59']);
        }


        // add conditions that should always apply here
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'publishDate',
            ],
            'defaultOrder' => ['publishDate' => SORT_DESC]
        ]);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        return $dataProvider;

    }

    /**
     * Check whether atleast one post is there
     */
    public function checkAllEmpty()
    {
        if (empty($this->fbPost) && empty($this->twPost) && empty($this->lnPost) && empty($this->lnPagePost) && empty($this->fbProfPost)) {
            $this->addError('fbPost', Yii::t('messages', 'Please complete atleast one post'));
            $this->addError('twPost', Yii::t('messages', 'Please complete atleast one post'));
            $this->addError('lnPost', Yii::t('messages', 'Please complete atleast one post'));
            $this->addError('lnPagePost', Yii::t('messages', 'Please complete atleast one post'));
            $this->addError('fbProfPost', Yii::t('messages', 'Please complete atleast one post'));
        }
    }


    /**
     * Upload images to the server
     */
    public static function uploadImages($model = null)
    {


        Yii::$app->toolKit->setResourceInfo();
        /*
                if (!empty($this->fbImageFile)) {
                    $fbImageName = $this->getImageFileName($this->id, 'fb', pathinfo($this->fbImageFile->name, PATHINFO_EXTENSION));
                    $this->fbImageName = $fbImageName;
                    $fbImageSavePath = Yii::$app->toolKit->resourcePathAbsolute . $fbImageName;
                     $this->fbImageFile->saveAs($fbImageSavePath);
                }

                if (!empty($this->fbProfImageFile)) {
                    $fbProfImageName = $this->getImageFileName($this->id, 'fbProf', pathinfo($this->fbProfImageFile->name, PATHINFO_EXTENSION));
                    $this->fbProfImageName = $fbProfImageName;
                    $fbProfImageSavePath = Yii::$app->toolKit->resourcePathAbsolute . $fbProfImageName;
                     $this->fbProfImageFile->saveAs($fbProfImageSavePath);
                }

                if (!empty($this->twImageFile)) {
                    $twImageName = $this->getImageFileName($this->id, 'tw', pathinfo($this->twImageFile->name, PATHINFO_EXTENSION));
                    $this->twImageName = $twImageName;
                    $twImageSavePath = Yii::$app->toolKit->resourcePathAbsolute . $twImageName;
                     $this->twImageFile->saveAs($twImageSavePath);
                }
            */

        if (!empty($model->twImageFile)) {
            $twImage = $model->twImageFile;
            $twImageName = self::getImageFileName($model->id, 'tw', pathinfo($twImage->name, PATHINFO_EXTENSION));
            $model->twImageName = $twImageName;
            $twImageSavePath = Yii::$app->toolKit->resourcePathAbsolute . $twImageName;
            $model->twImageFile->saveAs($twImageSavePath);
        }

        if (!empty($model->lnImageFile)) {
            $lnImage = $model->lnImageFile;
            $lnImageName = self::getImageFileName($model->id, 'ln', pathinfo($lnImage->name, PATHINFO_EXTENSION));
            $model->lnImageName = $lnImageName;
            $lnImageSavePath = Yii::$app->toolKit->resourcePathAbsolute . $lnImageName;
            $model->lnImageFile->saveAs($lnImageSavePath);
        }


        if (!empty($model->lnPageImageFile)) {

            $lnPageImageFile = $model->lnPageImageFile;
            $lnPageImageName = self::getImageFileName($model->id, 'lnPage', pathinfo($lnPageImageFile->name, PATHINFO_EXTENSION));
            $model->lnPageImageName = $lnPageImageName;
            $lnPageImageSavePath = Yii::$app->toolKit->resourcePathAbsolute . $lnPageImageName;
            $model->lnPageImageFile->saveAs($lnPageImageSavePath);
        }

        try {
            if ($model->save(false)) {
                Yii::error("Uploaded image details saved.");
            } else {
                Yii::error("Uploaded image details save failed. Validation errors:" . json_encode($model->errors));
            }
        } catch (Exception $e) {
            Yii::error("Uploaded image details save failed. Errors:{$e->getMessage()}");
        }
    }

    public static function getStat($nt, $param = null)
    {
        $model = BroadcastLinkStat::find()->where(['broadcastMessageId' => $param['id']])->where(['networkId' => $nt])->one();
        if ($model && $model->clickCount !== null) {
            return $model->clickCount;
        } else {
            return 0;
        }
    }

    /**
     * Retrieve label
     * @param integer $status Post status
     * @return string $label Bootstrap label
     */
    private function getLabel($status)
    {
        $label = '';
        switch ($status) {
            case self::MSG_STATUS_PENDING;
                $label = Yii::$app->toolKit->getBootLabel('default', Yii::t('messages', 'Pending'));
                break;

            case self::MSG_STATUS_PUBLISHED;
                $label = Yii::$app->toolKit->getBootLabel('success', Yii::t('messages', 'Published'));
                break;

            case self::MSG_STATUS_FAILED;
                $label = Yii::$app->toolKit->getBootLabel('danger', Yii::t('messages', 'Failed'));
                break;
        }

        return $label;
    }

    /**
     * Retrieve Facebook profile post status label
     */
    public function getFbProfStatusLabel()
    {
        $label = '';
        if (!empty($this->fbProfPost)) {
            $label = $this->getLabel($this->fbProfPostStatus);
        } else {
            $label = Yii::t('messages', 'N/A');
        }

        return $label;
    }


    /**
     * Retrieve LinkedIn page status label
     */
    public function getLnPageStatusLabel()
    {
        $label = '';
        if (!empty($this->lnPagePost)) {
            $label = $this->getLabel($this->lnPagePostStatus);
        } else {
            $label = Yii::t('messages', 'N/A');
        }

        return $label;
    }


    /**
     * Retrieve Twitter post status label
     */
    public function getTwStatusLabel()
    {
        $label = '';
        if (!empty($this->twPost)) {
            $label = $this->getLabel($this->twPostStatus);
        } else {
            $label = Yii::t('messages', 'N/A');
        }

        return $label;
    }

    /**
     * Retrieve LinkedIn post status label
     */
    public function getLnStatusLabel()
    {
        $label = '';
        if (!empty($this->lnPost)) {
            $label = $this->getLabel($this->lnPostStatus);
        } else {
            $label = Yii::t('messages', 'N/A');
        }

        return $label;
    }

    /**
     * Retrieve Facebook post status label
     */
    public function getFbStatusLabel()
    {
        $label = '';
        if (!empty($this->fbPost)) {
            $label = $this->getlabel($this->fbPostStatus);
        } else {
            $label = yii::t('messages', 'N/A');
        }

        return $label;
    }


    public static function getPostText($para)
    {
        if (!empty($para['fbPost'])) {
            return $para['fbPost'];
        } else if (!empty($para['fbProfPost'])) {
            return $para['fbProfPost'];
        } else if (!empty($para['twPost'])) {
            return $para['twPost'];
        } else if (!empty($para['lnPost'])) {
            return $para['lnPost'];
        } else if (!empty($para['lnPagePost'])) {
            return $para['fbProfPost'];
        }
    }

    /**
     * {@inheritdoc}
     * @return BroadcastMessageQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new BroadcastMessageQuery(get_called_class());
    }
}
