<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "adbannerdata".
 *
 * @property int $id
 * @property string $imageName
 * @property string $slogan
 * @property string $promoText
 * @property string $textLine1
 * @property string $textLine2
 */
class AdBannerData extends \yii\db\ActiveRecord
{

    // Maximum width and height and size for image in pixels
    const MAX_IMG_WIDTH = 630;
    const MAX_IMG_HEIGHT = 320;
    const MAX_SIZE = 512000;

    public $imgFile;

    public $embedUrl;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'AdBannerData';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['slogan', 'promoText'],'string', 'max'=>100],
            [['textLine1', 'textLine2'],'string', 'max'=>225],
            [['imgFile'], 'file', 'skipOnEmpty'=>true, 'extension'=>'jpg,jpeg,gif,png', 'maxSize'=>self::MAX_SIZE, 'tooLarge'=>Yii::t('messages', 'File has to be smaller than 500 Kb')],
            [['imgFile'], 'validateImageDimesions'],
            [['embedUrl'], 'safe'],

			// Create action
			[['slogan','promoText','textLine1','textLine2','imgFile'],'required', 'on'=>'create'],

            // Search action
            [['id','imageName','slogan','promoText','textLine1','textLine2','imgFile'],'safe', 'on'=>'search'],

        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'imageName' => 'Image Name',
            'slogan' => 'Slogan',
            'promoText' => 'Promo Text',
            'textLine1' => 'Text Line1',
            'textLine2' => 'Text Line2',
        ];
    }



    /**
     * Validate image width and height
     */
    public function validateImageDimesions()
    {
        if ("" != $this->imgFile) {
            $imageSize = getimagesize($this->imgFile->tempName);
            $imageWidth = $imageSize[0];
            $imageHeight = $imageSize[1];

            if ($imageWidth > self::MAX_IMG_WIDTH) {
                $this->addError('imgFile', Yii::t('messages', 'Image width is too large'));
            } else if($imageHeight > self::MAX_IMG_HEIGHT) {
                $this->addError('imgFile', Yii::t('messages', 'Image height is too large'));
            }
        }
    }


    /**
     * {@inheritdoc}
     * @return AdbannerdataQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new AdbannerdataQuery(get_called_class());
    }
}
