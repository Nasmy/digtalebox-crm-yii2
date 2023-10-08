<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "BroadcastLinkStat".
 *
 * @property int $id
 * @property string $shortenUrl
 * @property int $broadcastMessageId
 * @property int $networkId 1 - Twitter, 2- Facebook, 3 - LinkedIn
 * @property string $createdAt
 * @property int $clickCount Click count
 */
class BroadcastLinkStat extends \yii\db\ActiveRecord
{
    const TWITTER = 1;
    const FACEBOOK = 2;
    const FACEBOOK_PROFILE = 5;
    const LINKEDIN = 3;
    const LINKEDIN_PAGE = 4;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'BroadcastLinkStat';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['shortenUrl', 'broadcastMessageId', 'networkId', 'createdAt'], 'required'],
            [['shortenUrl'], 'string'],
            [['broadcastMessageId', 'networkId'], 'integer','integerOnly' => true],
            [['id','shortenUrl','broadcastMessageId','networkId','createdAt','clickCount'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'shortenUrl' => 'Shorten Url',
            'broadcastMessageId' => 'Broadcast Message ID',
            'networkId' => 'Network ID',
            'createdAt' => 'Created At',
            'clickCount' => 'Click Count',
        ];
    }

    /**
     * Add new link.
     * @param integer $networkId Social network identifier
     * @param array $postLinks Links extracted from
     * @param array $shortenUrls All the shorten URL within this session
     * @param array $broadcastId Id from the BroadcastMessage table
     * @return boolean true or false
     */
    public function addLinks($networkId, $postLinks, $shortenUrls, $broadcastId)
    {
        foreach ($postLinks as $postLink) {
            $link = trim($postLink);
            if (in_array($link, $shortenUrls)) {
                $model = BroadcastLinkStat::findAll(array(
                    'networkId' => $networkId,
                    'shortenUrl' => $link,
                    'broadcastMessageId' => $broadcastId
                ));

                if (empty($model)) {
                    $model = new BroadcastLinkStat();
                    $model->networkId = $networkId;
                    $model->shortenUrl = $link;
                    $model->broadcastMessageId = $broadcastId;
                    $model->createdAt = date('Y-m-d H:i:s');

                    try {
                        if ($model->save()) {
                            Yii::error('Shorten link saved');
                        } else {
                            Yii::error("Shorten link save failed. Error:" . json_encode($model->errors));
                        }
                    } catch (Exception $e) {
                        Yii::error("Shorten link save failed. Error:{$e->getMessage()}");
                    }
                }
            }
        }
    }
    /**
     * {@inheritdoc}
     * @return BroadcastlinkstatQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new BroadcastlinkstatQuery(get_called_class());
    }
}
