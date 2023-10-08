<?php
/**
 * Activity Controller.
 *
 * This class illustrate the User Activity
 *
 * @author : Umar
 * Date: 7/22/2019
 * @copyright Copyright &copy; Keeneye solutions (PVT) LTD
 */
namespace app\controllers;

use app\models\Activity;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use \app\controllers\WebUserController;

class ActivityController extends WebUserController
{
    /**
     * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
     * using two-column layout. See 'protected/views/layouts/column2.php'.
     */
    public $layout = '/column1';


    /**
     * @return array action filters
     */

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }
    /**
     * @return string  allowed actions
     */
    public function allowedActions()
    {
        $allowed = array();

        return implode(',', $allowed);
    }

    public function actionAdmin()
    {
        $model= new Activity();
        $dataProvider = $model->search();
        $model->loadDefaultValues(false);  // clear any default values
        if(isset($_GET['Activity']))

        $model->attributes=$_GET['Activity'];

        $params = array(
            'model' => $model,
            'dataProvider'=>$dataProvider,
        );

        return   $this->render('admin',$params);
    }

}
