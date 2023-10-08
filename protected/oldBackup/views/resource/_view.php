<?php

use app\models\Feed;
use app\models\Resource;
use app\models\User;
use yii\helpers\Html;
use yii\helpers\Url;

$data = $model;
$resourceUrl = Url::base() . '/' . Yii::$app->toolKit->resourcePathRelative;
if ($data->type == Resource::IMAGE) {
    $faIcon = "picture-o";
} else if ($data->type == Resource::VIDEO) {
    $faIcon = "video-camera";
} else {
    $faIcon = "file";
}

$modelUser = User::findOne(['id'=>$data->createdBy]);
$imgPath = '';
if ($data->type == Resource::DOCUMENT) {
    $imgPath = 'asdasd';
    $fileExt = pathinfo($data->fileName, PATHINFO_EXTENSION);
    if (in_array($fileExt, array('doc','docx','txt'))) {
        $imgPath = Yii::$app->toolKit->getImagePath() . 'officeword.png';
    } else if (in_array($fileExt, array('xls','xlsx'))) {
        $imgPath = Yii::$app->toolKit->getImagePath() . 'excelicon.png';
    } else if (in_array($fileExt, array('pdf'))) {
        $imgPath = Yii::$app->toolKit->getImagePath() . 'pdfIcon.png';
    }
}
 $actionIcons  = '';

if (Yii::$app->user->checkAccess('Resource.Update') && Yii::$app->user->id == $data->createdBy) {
    $updateUrl = Url::to(['resource/update', 'id'=>$data->id]);
    $actionIcons  = Html::a('<i class="fa fa-pencil "></i>', '#', array('title'=>Yii::t('messages', 'Update'), 'onclick'=>'resourcePopUp("'.$updateUrl.'", "Update Resource")'));
}
if (Yii::$app->user->checkAccess('Resource.View')) {
    $viewUrl = Url::to(['resource/view', 'id'=>$data->id]);
     $actionIcons .= Html::a('<i class="fa fa-eye "></i>',  '#', array('title'=>Yii::t('messages', 'View'), 'class'=>'view', 'onclick'=>'resourcePopUp("'.$viewUrl.'", "View Resource")'));
}
if (Yii::$app->user->checkAccess('Resource.Approve') && $data->status == Resource::PENDING_APPROVAL) {
    $approveUrl = Url::to(['resource/approve', 'id'=>$data->id]);
     $actionIcons .=  Html::a('<i class="fa  fa-check-circle "></i> ',  '#', array('title'=>Yii::t('messages', 'Approve'), 'class'=>'approve', 'onclick'=>'resourceApprove("'.$approveUrl.'")'));
}
if (Yii::$app->user->checkAccess('Resource.Reject') && ($data->status == Resource::PENDING_APPROVAL)) {
    $rejectUrl = Url::to(['resource/reject', 'id'=>$data->id]);
     $actionIcons .=  Html::a('<i class="fa fa-times-circle  "></i> ',  '#', array('title'=>Yii::t('messages', 'Reject'), 'class'=>'reject','onclick'=>'resourceReject("'.$rejectUrl.'")'));
}

$statusLabel = '';
if ($data->status == Resource::PENDING_APPROVAL) {
    $statusLabel = Yii::$app->toolKit->getBootLabel('default', Yii::t('messages', 'Pending'));
} elseif ($data->status == Resource::APPROVED) {
    $statusLabel = Yii::$app->toolKit->getBootLabel('success', Yii::t('messages', 'Approved'));
} elseif ($data->status == Resource::REJECTED) {
    $statusLabel = Yii::$app->toolKit->getBootLabel('danger', Yii::t('messages', 'Rejected'));
} elseif ($data->status == Resource::DELETED) {
    $statusLabel = Yii::$app->toolKit->getBootLabel('danger', Yii::t('messages', 'Deleted'));
}

$url = '';
if ($data->type == Resource::VIDEO) {
    $url = Yii::$app->toolKit->getVideoEmbedUrl($data->fileName);
    $videoImgPath = str_replace("https://www.youtube.com/embed/", "//img.youtube.com/vi/", $url);
}

$feed = new Feed();
$timeElapsed = $feed->getTimeElapsed($data->createdAt);
?>

<div class="col-sm-6 col-md-4 col-xl-3 text-center unwrap">

    <div class="resource">
        <?php if ($data->type == Resource::IMAGE): ?>
            <a class="pic" href="#"><img src="<?php echo $resourceUrl . '/' . $data->fileName;?>" alt="" class="img-thumbnail object-fit_cover"></a>
        <?php endif;?>
        <?php if ($data->type == Resource::VIDEO):?>
            <a class="pic" href="#"><img  src="<?php echo $videoImgPath."/0.jpg" ?>" alt="" class="img-thumbnail object-fit_cover"></a>
        <?php endif;?>
        <!-- Documents -->

        <?php if ($data->type == Resource::DOCUMENT): ?>
            <a class="pic" href="#">
                <img class="thumbnail res-img-thumbnail" src="<?php echo $imgPath ?>"/>
            </a>
        <?php endif;?>
        <!-- End -->

        <div class="title"><?php echo $data->title ?></div>
        <div class="desc"><?php echo $data->description ?></div>
        <div class="created-by"><?php echo Yii::t('messages', ' - By {name} {timeElapsed}', array('name'=>isset($modelUser) ? $modelUser->getName(): '', 'timeElapsed'=>$timeElapsed));?></div>
        <?php if (Yii::$app->user->checkAccessList(array('Resource.Reject', 'Resource.Approve'))): ?>
            <div class="status">
                <?php echo $statusLabel;  ?>
            </div>
        <?php endif; ?>

        <div class="actions"><?php echo $actionIcons ?></div>
    </div>
</div>