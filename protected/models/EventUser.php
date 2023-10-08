<?php

namespace app\models;

use Yii;
use yii\data\ActiveDataProvider;
use yii\data\Sort;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * This is the model class for table "eventuser".
 *
 * @property int $userId
 * @property int $eventId
 * @property string $rsvpStatus 0 - invited 1 - not_replied 2 - unsure 3 - attending 4 - declined
 * @property string|null $isDigiInvite 1 - Digitale Box invitation 0 - NOT
 * @property string|null $fbRsvpStatus Facebook RSVP status 0 - invited1 - not_replied2 - unsure3 - attending4 - declined
 * @property string $isFbInvite 1 - Facebook invitation  0 - NOT
 * @property string $isParticipate 0 - Not participate 1 - participate
 */
class EventUser extends \yii\db\ActiveRecord
{

    const FB_NOT_REPLIED = 'not_replied';
    const FB_UNSURE = 'unsure';
    const FB_ATTENDING = 'attending';
    const FB_DECLINED = 'declined';
    const INVITED = '0'; //invited
    const NOT_REPLIED = '1'; //not_replied
    const UNSURE = '2'; //unsure
    const ATTENDING = '3'; //attending
    const DECLINED = '4'; //declined
    const INVITED_STR = 'invited';
    const NOT_REPLIED_STR = 'notReplied'; //'not replied';
    const UNSURE_STR = 'maybe';
    const ATTENDING_STR = 'attend';
    const DECLINED_STR = 'notAttend';
    const PRESENT = '1';
    const ABSENT = '0';

    public $fbId = '';
    public $fbUserId = '';
    public $name = '';
    public $firstName = '';
    public $lastName = '';
    public $profImage = '';
    public $email = '';
    public $updatedDateTime = '';
    public $confirmed = 0;
    public $isFbInvite = 0;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'EventUser';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['userId', 'eventId'], 'required'],
            [['eventId'], 'integer'],
            [['userId'], 'integer'],
            [['rsvpStatus', 'isDigiInvite', 'fbRsvpStatus', 'isFbInvite', 'isParticipate'], 'string', 'max' => 1],
            [['userId', 'eventId', 'rsvpStatus', 'isDigiInvite', 'fbRsvpStatus', 'isFbInvite', 'isParticipate'], 'safe', 'on' => 'search'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'userId' => Yii::t('messages', 'User'),
            'eventId' => Yii::t('messages', 'Event'),
            'rsvpStatus' => Yii::t('messages', 'RSVP Status'),
            'isDigiInvite' => 'Digitale Invite',
            'fbRsvpStatus' => Yii::t('messages', 'Facebook RSVP Status'),
            'isFbInvite' => Yii::t('messages', 'Facebook Invite'),
            'isParticipate' => Yii::t('messages', 'Participate'),
            'name' => Yii::t('messages', 'Name'),
            'profImgFile' => Yii::t('messages', 'Profile Image'),
        ];
    }

    /**
     * Returns event RSVP user count.
     * @return array Available RSVP status count
     */
    public static function getEventRsvpStatusCount($eventId, $isFb = false)
    {

        // commented due to fb was not implemented

        /* if ($isFb) {
             $eventUsers = EventUser::find()->where(['=', 'eventId', $eventId])->andWhere(['=', 'isFbInvite', 1])->all();
             $eventUsersList = ArrayHelper::map($eventUsers, 'userId', 'eventId', 'fbRsvpStatus');
         } else {
              $eventUsers = EventUser::find()->where(['=', 'eventId', $eventId])->andWhere(['=', 'isDigiInvite', 1])->all();
             $eventUsersList = ArrayHelper::map($eventUsers, 'userId', 'eventId', 'rsvpStatus');
         }*/

        $eventUsers = EventUser::find()->where(['=', 'eventId', $eventId])->andWhere(['=', 'isDigiInvite', 1])->all();
        $eventUsersList = ArrayHelper::map($eventUsers, 'userId', 'eventId', 'rsvpStatus');


        $rsvpStatus[self::ATTENDING] = 0;
        $rsvpStatus[self::DECLINED] = 0;
        $rsvpStatus[self::UNSURE] = 0;
        $rsvpStatus[self::INVITED] = 0;

        foreach ($eventUsersList as $rsvp => $users) {
            $rsvpStatus[$rsvp] = count($users);
        }
        return $rsvpStatus;
    }

    /**
     * @param $rsvpKey
     * @return string
     */
    public static function getRsvpString($rsvpKey)
    {
        switch ($rsvpKey) {
            case self::NOT_REPLIED:
                $rsvpStr = self::NOT_REPLIED_STR;
                break;

            case self::UNSURE:
                $rsvpStr = self::UNSURE_STR;
                break;

            case self::ATTENDING:
                $rsvpStr = self::ATTENDING_STR;
                break;

            case self::DECLINED:
                $rsvpStr = self::DECLINED_STR;
                break;

            case self::INVITED:
                $rsvpStr = self::INVITED_STR;
                break;
        }

        return $rsvpStr;
    }

    /*
    * Get the user list to the Grid
    * @param $id integer, $type rsvp type integer, $inviteType integer
    * @return $dataProvider array
    */

    public static function getMembers($id, $type, $inviteType)
    {
        $query = new Query();
        $query->select(['t.*', 'User.firstName as firstName', 'User.lastName as lastName', 'User.profImage', 'User.email AS email'])
            ->from('EventUser t')
            ->innerJoin('User', 'User.id=t.userId');

        if ($inviteType == Event::EMAIL) {
            $query->andWhere(['t.rsvpStatus' => $type])
                ->andWhere(['t.isDigiInvite' => 1]);
        }

        $query->andWhere(['t.eventId' => $id]);

        $sort = new Sort([
            'attributes' => [
                Yii::t('messages', 'firstName') => [
                    'asc' => [
                        'User.firstName' => SORT_ASC,
                    ],
                    'desc' => [
                        'User.firstName' => SORT_DESC,
                    ],
                ],

                Yii::t('messages', 'lastName') => [
                    'asc' => [
                        'User.lastName' => SORT_ASC,
                    ],
                    'desc' => [
                        'User.lastName' => SORT_DESC,
                    ],
                ],
                Yii::t('messages', 'email') => [
                    'asc' => [
                        'User.email' => SORT_ASC,
                    ],
                    'desc' => [
                        'User.email' => SORT_DESC,
                    ],
                ],
            ],
        ]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => $sort
//            'pagination' => [
//                'pageSize' => 20,
//            ],
        ]);


        return $dataProvider;

    }

    public function invite($event, $user, $rsvpStatus = '0', $fbRsvpStatus = '0', $isDigiInvite = '1', $isFbInvite = '0')
    {
        $return = array();
        $model = new EventUser;

        $model->eventId = $event;
        $model->userId = $user;
        $model->rsvpStatus = $rsvpStatus;
        $model->fbRsvpStatus = $fbRsvpStatus;
        // $model->isFbInvite = $isFbInvite;  due to fb was not implemented
        $model->isDigiInvite = $isDigiInvite;

        try {
            if ($model->save(false)) {
                $return = array('status' => true);
            } else {
                $return = array('status' => false, 'error' => 'Event invitation send failed.', 'code' => 0);
            }
        } catch (\Exception $ex) {
            if (23000 == $ex->getCode()) {
                $model = EventUser::find()->where(array('userId' => $user, 'eventId' => $event))->one();
                //  $model->isFbInvite = $isFbInvite == 1 ? $isFbInvite : $model->isFbInvite;  due to fb was not implemented
                $model->isDigiInvite = $isDigiInvite == '1' ? $isDigiInvite : $model->isDigiInvite;
                if ($model->save()) {
                    $return = array('status' => true);
                } else {
                    $return = array('status' => false, 'error' => 'Event invitation send failed.', 'code' => $ex->getCode());
                }
            } else {
                $return = array('status' => false, 'error' => 'Event invitation send failed.', 'code' => $ex->getCode());
            }
        }

        return $return;
    }


    public function searchEvent($id, $type, $inviteType)
    {
        $params = Yii::$app->request->queryParams['User'];

        $query = new Query();
        $query->select(['eu.*', 'User.firstName as firstName', 'User.lastName as lastName', 'User.profImage', 'User.email AS email'])
            ->from('EventUser eu')
            ->where(['eu.eventId' => $id])
            ->innerJoin('User', 'User.id=eu.userId');

        if ($inviteType == Event::EMAIL) {
            $query->andWhere(['eu.rsvpStatus' => $type])
                ->andWhere(['eu.isDigiInvite' => 1]);
        }

        if (!is_null($params) || !empty($params)) {

            $query->where(['eu.eventId' => $id])
                ->andFilterWhere(['like', 'firstName', $params['firstName']])
                ->andFilterWhere(['like', 'lastName', $params['lastName']])
                ->andFilterWhere(['like', 'email', $params['email']]);

        }

        $sort = new Sort([
            'attributes' => [
                Yii::t('messages', 'firstName') => [
                    'asc' => [
                        'User.firstName' => SORT_ASC,
                    ],
                    'desc' => [
                        'User.firstName' => SORT_DESC,
                    ],
                ],

                Yii::t('messages', 'lastName') => [
                    'asc' => [
                        'User.lastName' => SORT_ASC,
                    ],
                    'desc' => [
                        'User.lastName' => SORT_DESC,
                    ],
                ],
                Yii::t('messages', 'email') => [
                    'asc' => [
                        'User.email' => SORT_ASC,
                    ],
                    'desc' => [
                        'User.email' => SORT_DESC,
                    ],
                ],
            ],
        ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => $sort
        ]);

        return $dataProvider;
    }


    /**
     * {@inheritdoc}
     * @return EventUserQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new EventUserQuery(get_called_class());
    }
}
