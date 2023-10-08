<?php


namespace app\components;


use yii\base\Component;

class FileKit extends Component
{
    public static function checkFileExist($filePath, $fileName, $extension = null) {
        $filePath = (is_null($extension)) ? $filePath.$fileName : $filePath.$fileName.$extension;
           if(file_exists($filePath)) {
               return file_get_contents($filePath);
           } else {
               return '';
           }
    }

}