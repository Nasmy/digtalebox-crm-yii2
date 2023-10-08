<?php

namespace app\models;

use app\components\ToolKit;
use Yii;

/**
 * This is the model class for table "DragDropMessageTemplate".
 *
 * @property int $id
 * @property string $metadata
 * @property string $template
 * @property string $code
 * @property int $createdBy
 * @property string $createdAt
 * @property string $updatedAt
 */
class DragDropMessageTemplate extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'DragDropMessageTemplate';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['metadata', 'template', 'code', 'createdBy', 'createdAt', 'updatedAt'], 'required'],
            [['metadata', 'template'], 'string'],
            [['createdBy'], 'integer'],
            [['createdAt', 'updatedAt'], 'safe'],
            [['code'], 'string', 'max' => 10],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'metadata' => 'Metadata',
            'template' => 'Template',
            'code' => 'Code',
            'createdBy' => 'Created By',
            'createdAt' => 'Created At',
            'updatedAt' => 'Updated At',
        ];
    }

    /*
	 * function to get the theme colour
	 */
    public static function getThemeColour()
    {
        $model = CandidateInfo::find()->one();
         $themeColour = Yii::$app->params['themes'][$model['themeStyle']];
        return $themeColour['color'];
    }

    /**
     * Function to check Drag drop template exist or not
     * @param String Template code
     * @return boolean
     */
    public static function isTemplateExist($random)
    {
        $model = DragDropMessageTemplate::find()->where(['code'=>$random])->all();
        if (!ToolKit::isEmpty($model)) {
            $return = true;
        } else {
            $return = false;
        }
        return $return;
    }

    /**
     * {@inheritdoc}
     * @return DragDropMessageTemplateQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new DragDropMessageTemplateQuery(get_called_class());
    }
}
