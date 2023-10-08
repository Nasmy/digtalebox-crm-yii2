<?php

namespace app\models;

use app\models\Event;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * EventSearch represents the model behind the search form of `app\models\Event`.
 */
class EventSearch extends Event
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
//            [['id', 'fbId', 'fbPageEventId', 'createdBy', 'updatedBy'], 'integer'],
//            [['name', 'description', 'imageName', 'isFbEvent', 'fbDescription', 'location', 'locationMapCordinates', 'address', 'type', 'privacyType', 'startDate', 'startTime', 'endDate', 'endTime', 'rsvpStatus', 'status', 'keywords', 'advanceKeyword', 'priority', 'isFbPageEvent', 'comments', 'createdAt', 'updatedAt'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {

    }

    /**
     *
     * @return string
     */
    public function eventPriority($priority=null)
    {
        $priority = $priority == null ? $this->priority : $priority;
        $return = '';
        switch ($priority) {
            case self::LOW:
                $return = '#045FB4';
                break;

            case self::MEDIUM:
                $return = '#688A08';
                break;

            case self::HIGH:
                $return = '#B45F04';
                break;

            case self::URGENT:
                $return = '#8A0808';
                break;

            default:
                $return = '#999999';
                break;
        };
        return $return;
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function searchEvent($params)
    {
        $prams = $params['Event'];

        $priorty = $prams['priority'];
        $status = $prams['status'];
        $updatedBy = $prams['updatedBy'];

        $query = Event::find()
            ->where(['like', 'name', '%'.$prams['name'] . '%', false])
            ->andWhere(['>=', 'startDate', $_GET['Event']['startDate']])
            ->andWhere(['<=', 'endDate', $_GET['Event']['endDate']]);

        if (isset($prams['rsvpStatus'])) {
            $query->andWhere(['rsvpStatus' => $prams['rsvpStatus']]);
        }

        if (!empty($status)) {
            $query->andWhere(['status' => $status]);
        }

        if (isset($prams['keywords'])) {
            if (!empty($prams['keywords'])) {
                if (is_array($prams['keywords']))
                    $this->keywords = implode(",", $prams['keywords']);
                $query->andFilterWhere(['REGEXP', 'keywords', str_replace(',', '|', $prams['keywords'])]);
            } else {
                $query->andFilterWhere(['keywords', $prams['keywords']]);
            }
        }

        if (!empty($priorty)) {
            $query->andWhere(['priority'=>$priorty]);
        }

        if (!empty($prams['updatedBy'])) {
            $query->andWhere(['createdBy'=>$prams['updatedBy']]);
        }

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> ['defaultOrder' => ['startDate' => SORT_ASC]],

        ]);

        return $dataProvider;
    }
}
