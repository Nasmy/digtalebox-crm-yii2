<?php

namespace app\models;

use app\components\RActiveRecord;
use app\components\ToolKit;
use app\components\Validations\ValidateImageDimesions;
use app\components\Validations\ValidateVolunteerBgImageDimesions;
use Yii;
use yii\helpers\Html;

/**
 * This is the model class for table "CandidateInfo".
 *
 * @property int $id
 * @property string $profImageName
 * @property string $volunteerBgImageName
 * @property string $slogan
 * @property string $introduction
 * @property string $promoText
 * @property string $signupFields Mandatory signup fields
 * @property string $frontImages front page sliding images
 * @property string $aboutText about text on front page
 * @property string $headerText Header text
 * @property int $themeStyle Theme style id. Ids are configured at config file
 * @property string $bgImage Background image file
 */
class CandidateInfo extends RActiveRecord
{
    /**
     * Maximum width and height and size for image in pixels. Front images
     */
    const MAX_IMG_WIDTH = 1500;
    const MAX_IMG_HEIGHT = 982;
    const MIN_IMG_WIDTH = 800;
    const MIN_IMG_HEIGHT = 524;
    const MAX_BG_FILE_SIZE = 1000000; // 1MB
    const MAX_SIZE = 512000;
    const MIN_VOL_IMG_WIDTH = 1400;
    const MIN_VOL_IMG_HEIGHT = 800;
    const MIN_BG_VOL_IMG_WIDTH = 1200;
    const MIN_BG_VOL_IMG_HEIGHT = 675;
    const MAX_BG_VOL_IMG_WIDTH = 1920;
    const MAX_BG_VOL_IMG_HEIGHT = 1080;

    /**
     * @param integer thumbnail image width
     */
    public $imgWidth = 256;

    /**
     * @param integer thumbnail image height
     */
    public $imgHeight = 256;

    /**
     * @param string Thumbnail image name
     */
    public $newProfThumb = '';

    /**
     * @param string User registration process public URl
     */
    public $regUrl = '';

    /**
     * @param string frontImage Front page image. Single instance
     */
//    public $frontImages = '';

    /**
     * @param string Background image file
     */
    public $bgImageFile = '';

    /**
     * @param string Background image file
     */
    public $volunteerBgImageFile = '';

    /**
     * Profile image file
     */
    public $profImgFile = null;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'CandidateInfo';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return [
            // Common
            [['id'], 'number', 'integerOnly' => true],
            [['profImageName'], 'string', 'max' => 45],
            [['slogan', 'promoText'], 'string', 'max' => 100],
            [['newProfThumb', 'regUrl', 'signupFields', 'themeStyle'], 'safe'],
            // [['profImgFile'],'file','skipOnEmpty'=>true, 'extensions' => 'jpg,jpeg,png', 'maxSize' => self::MAX_SIZE, 'tooBig' => Yii::t('messages', 'File needs to be smaller than 500 Kb'), 'on' => ['uploadProImage']],
            [['profImgFile'], 'file', 'skipOnEmpty' => true, 'extensions' => 'jpg,jpeg,png', 'maxSize' => self::MAX_SIZE, 'on' => 'uploadProImage'],
            [['profImgFile'], ValidateImageDimesions::className(), 'MinImgMinWidth' => self::MAX_IMG_WIDTH, 'MinImgMinHeight' => self::MAX_IMG_HEIGHT],

            // Create
            [['profImageName', 'slogan', 'introduction', 'promoText'], 'required', 'on' => 'create'],

            // Update
            [['slogan', 'introduction', 'promoText'], 'required', 'on' => 'update'],
            [['profImageName'], 'required', 'on' => 'update'],

            // Upload image
            [['frontImages'], 'required', 'on' => 'uploadImage'],

            // Upload background image
            [['bgImageFile'], 'required', 'on' => 'uploadBgImage'],

            // Upload volunteer background image
            [['volunteerBgImageFile'], 'file', 'skipOnEmpty' => true, 'extensions' => 'jpg,jpeg,gif,png', 'on' => 'update'],
            [['volunteerBgImageFile'], ValidateVolunteerBgImageDimesions::className(), 'VolImgMinWidth' => self::MIN_VOL_IMG_WIDTH, 'VolImgMinHeight' => self::MIN_VOL_IMG_WIDTH, 'on' => 'update'],

            // Update texts
            [['aboutText', 'headerText'], 'required', 'on' => 'updateText'],
            [['headerText'], 'string', 'max' => 100, 'on' => 'updateText'],

            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            [['id', 'profImageName', 'volunteerBgImageName', 'slogan', 'introduction', 'promoText'], 'safe', 'on' => 'search']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'profImageName' => Yii::t('messages', 'Profile Image'),
            'volunteerBgImageName' => Yii::t('messages', 'Volunteer Background Image Name'),
            'slogan' => Yii::t('messages', 'Slogan'),
            'introduction' => Yii::t('messages', 'Introduction'),
            'promoText' => Yii::t('messages', 'Promo Text'),
            'bgImage' => Yii::t('messages', 'Background Image'),
            'bgImageFile' => Yii::t('messages', 'Background Image'),
            'aboutText' => Yii::t('messages', 'About Text'),
            'headerText' => Yii::t('messages', 'Header Text'),
            'frontImages' => Yii::t('messages', 'Image'),
            'volunteerBgImageFile' => Yii::t('messages', 'Volunteer Background Image'),
            'signupFields' => Yii::t('messages', 'Sign Up Fields'),
            'regUrl' => Yii::t('messages', 'Reg Url'),
        ];
    }

    /**
     * Validate image width and height
     */
    public function validateVolunteerBgImageDimesions()
    {
        if ("" != $this->volunteerBgImageFile) {
            $imagehw = getimagesize($this->volunteerBgImageFile->tempName);
            $imagewidth = $imagehw[0];
            $imageheight = $imagehw[1];

            if ($imagewidth < self::MIN_VOL_IMG_WIDTH) {
                $this->addError('volunteerBgImageFile', Yii::t('messages', 'Image width should be {width}', array('width' => self::MIN_VOL_IMG_WIDTH . 'px')));
            } else if ($imageheight < self::MIN_VOL_IMG_HEIGHT) {
                $this->addError('volunteerBgImageFile', Yii::t('messages', 'Image height should be {height}', array('height' => self::MIN_VOL_IMG_HEIGHT . 'px')));
            }
        }
    }

    /**
     * Validate image width and height
     */
    public function validateBgImageDimesions($data)
    {
        $error = 1;
        $errorMsg = '';
        if (!ToolKit::isEmpty($data)) {
            $imagehw = getimagesizefromstring($data);
            $imagewidth = $imagehw[0];
            $imageheight = $imagehw[1];
            $errorMsg = Yii::t('messages', 'Image should be with in Minimum size of {minWidth}px X {minHeight}px to Maximum size of {maxWidth}px X {maxHeight}px', ['minWidth' => self::MIN_BG_VOL_IMG_WIDTH,
                'minHeight' => self::MIN_BG_VOL_IMG_HEIGHT, 'maxWidth' => self::MAX_BG_VOL_IMG_WIDTH, 'maxHeight' => self::MAX_BG_VOL_IMG_HEIGHT]);


            if ('image/png' == $imagehw['mime']) {
                if ($imagewidth > self::MAX_BG_VOL_IMG_WIDTH || $imagewidth < self::MIN_BG_VOL_IMG_WIDTH) {
                    $error = 0;
                } else if ($imageheight > self::MAX_BG_VOL_IMG_HEIGHT || $imageheight < self::MIN_BG_VOL_IMG_HEIGHT) {
                    $error = 0;
                }
            }
        } else {
            $error = 0;
            $errorMsg = Yii::t('messages', 'Please choose an image file. (.jpg, .jpeg, .png');
        }
        return array($error, $errorMsg);
    }


    /**
     * Validate image width and height
     */
    public function validateImageDimesions()
    {
        if ("" != $this->profImgFile) {
            $imagehw = getimagesize($this->profImgFile->tempName);
            $imagewidth = $imagehw[0];
            $imageheight = $imagehw[1];

            if ($imagewidth > self::MAX_IMG_WIDTH) {
                $this->addError('profImgFile', Yii::t('messages', 'Image width is too large.'));
            } else if ($imageheight > self::MAX_IMG_HEIGHT) {
                $this->addError('profImgFile', Yii::t('messages', 'Image height is too large.'));
            }
        }
    }

    /**
     * Retrieve image link
     */
    public function getImageLink($data)
    {
        if ($data['isDefault']) {
            return Html::img(Yii::$app->toolKit->getImagePath() . $data['name'], array('alt' => 'img', 'width' => 200, 'class' => 'img-thumbnail object-fit_cover'));
        } else {
            return Html::img('/' . Yii::$app->toolKit->resourcePathRelative . $data['name'], array('alt' => 'img', 'width' => 200, 'class' => 'img-thumbnail object-fit_cover'));
        }
    }

    /**
     * {@inheritdoc}
     * @return CandidateInfoQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CandidateInfoQuery(get_called_class());
    }

    /**
     * Retrieve image link
     */
    public function getImageUrl($data)
    {
        if ($data['isDefault']) {
            return Yii::$app->toolKit->getImagePath() . $data['name'];
        } else {
            return '/' . Yii::$app->toolKit->resourcePathRelative . $data['name'];
        }
    }
}
