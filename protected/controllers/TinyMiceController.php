<?php


namespace app\controllers;


use yii\web\Controller;
use Yii;
use yii\web\BadRequestHttpException;

/**
 * Class TinyMiceController
 * @package app\controllers
 */
class TinyMiceController extends Controller
{
    /**
     * {@inheritdoc}
     * @throws BadRequestHttpException
     */
    public function beforeAction($action) {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionUpload() {
        /*******************************************************
         * Only these origins will be allowed to upload images *
         ******************************************************/
        $accepted_origins = [Yii::$app->request->baseUrl];
        $results = null;
        /*********************************************
         * Change this line to set the upload folder *
         *********************************************/
        $imageFolder = Yii::$app->params['tinyMiceUpload'];

        reset ($_FILES);
        $temp = current($_FILES);
        if (is_uploaded_file($temp['tmp_name'])){

            header('Access-Control-Allow-Origin: ' . Yii::$app->request->baseUrl);

            // Sanitize input
            if (preg_match("/([^\w\s\d\-_~,;:\[\]\(\).])|([\.]{2,})/", $temp['name'])) {
                header("HTTP/1.1 400 Invalid file name.");
                return;
            }

            // Verify extension
            if (!in_array(strtolower(pathinfo($temp['name'], PATHINFO_EXTENSION)), array("gif", "jpg", "png"))) {
                header("HTTP/1.1 400 Invalid extension.");
                return;
            }

            // Accept upload if there was no origin, or if it is an accepted origin
            $filetowrite = $imageFolder . $temp['name'];
            move_uploaded_file($temp['tmp_name'], $filetowrite);

            // Respond to the successful upload with JSON.
            // Use a location key to specify the path to the saved image resource.
            $encodedArray = ['location' => $temp['name']];
            $results = json_encode($encodedArray);
        } else {
            // Notify editor that the upload failed
            header("HTTP/1.1 500 Server Error");
        }
        echo $results;
    }

}
