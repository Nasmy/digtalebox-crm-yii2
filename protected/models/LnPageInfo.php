<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "LnPageInfo".
 *
 * @property string $pageId LinkedIn page id
 * @property string $pageName LinkedIn page name
 * @property string $postCollectedTime LinkedIn page post collected time
 */
class LnPageInfo extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'LnPageInfo';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['pageId', 'pageName'], 'required'],
            [['postCollectedTime'], 'safe'],
            [['pageId'], 'string', 'max' => 10],
            [['pageName'], 'string', 'max' => 64],
            [['pageId'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'pageId' => 'Page ID',
            'pageName' => 'Page Name',
            'postCollectedTime' => 'Post Collected Time',
        ];
    }

    /**
     * Add LinkedIn page(company) details.
     * @param string $pageId Page id
     * @param string $pageName Page name
     * @return boolean true if success otherwise false
     */
    public function addLnPageInfo($pageId, $pageName)
    {
        $model = LnPageInfo::find()->where(array('pageName' => $pageName))->one();

        if (null == $model) {
            $model= new LnPageInfo();
        }

        $model->pageId=$pageId;
        $model->pageName=$pageName;

        try {
            if ($model->save()) {
                 Yii::$app->appLog->writeLog("LinkedIn pageinfo added.");
                return true;
            } else {
                 Yii::$app->appLog->writeLog("LinkedIn pageinfo add failed. Validation errors:" . json_encode($model->errors));
            }
        } catch (\Exception $e) {
             Yii::$app->appLog->writeLog("LinkedIn pageinfo add failed. Errors:{$e->getMessage()}");
        }

        return false;
    }

    /**
     * {@inheritdoc}
     * @return LnPageInfoQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new LnPageInfoQuery(get_called_class());
    }
}
