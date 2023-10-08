<?php

namespace app\models;

use app\components\RActiveRecord;
use Yii;

/**
 * This is the model class for table "KeywordUrl".
 *
 * @property int $id
 * @property string $title
 * @property string $keywords
 * @property string|null $url
 * @property string $externalUrl
 * @property string $createdAt
 * @property string|null $updatedAt
 */
class KeywordUrl extends RActiveRecord
{
    const SUBSCRIBE_URL = 'newsletter/keyword-subscribe';
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'KeywordUrl';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'keywords', 'externalUrl'], 'required', 'on' => ['create', 'update']],
            [['title'], 'string', 'max' => 64],
            [['title'], 'unique'],
            [['url','externalUrl'], 'url'],
            [['url', 'externalUrl'], 'string', 'max' => 2000],
            [['createdAt', 'updatedAt'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' =>  Yii::t('messages','Id'),
            'title' => Yii::t('messages','Title'),
            'externalUrl' => Yii::t('messages','External Url'),
            'keywords' => Yii::t('messages','Keywords'),
            'url' => Yii::t('messages','Url'),
            'createdAt' => Yii::t('messages','Created At'),
            'updatedAt' => Yii::t('messages','Updated At'),
        ];
    }

    /**
     * generate URL based on keywords
     * @param $keywords
     * @return string
     */
    public static function generateUrl($keywords)
    {
        $url = 'http://' . Yii::$app->request->getServerName() . YII::$app->urlManager->createUrl('/'.self::SUBSCRIBE_URL);
        return $url . '/'. base64_encode($keywords);
    }

    /**
     * generate URL based on keywords
     * @param $id
     */
    public static function appendIdUrl($id)
    {
        $data = self::findOne($id);
        $oldUrl = $data->url;
        $data->url = $oldUrl . '||' .  $data->id;
        $data->save(false);
    }

    /**
     * {@inheritdoc}
     * @return KeywordUrlQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new KeywordUrlQuery(get_called_class());
    }
}
