<?php

use app\models\Resource;
use app\models\User;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\CandidateInfo */

 $this->params['breadcrumbs'][] = ['label' => 'Candidate Infos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

//$fbShareButton = Yii::$app->toolKit->getFacebookShareButton($shareUrl);
$gpShareButton = Yii::$app->toolKit->getGooglePlusShareButton($shareUrl);

?>


<!-- End -->

<!-- Video -->
<?php if ($model->type == Resource::VIDEO): ?>
    <iframe border="0" width="100%" height="250" class="thumbnail res-video-div-large"
            src="<?php echo $url ?>"></iframe>
<?php endif; ?>
<!-- End -->

<!-- Images -->
<?php if ($model->type == Resource::IMAGE): ?>
    <div class="text-center">
        <div class="col-md-5">
            <img src="<?php echo $resourceUrl . '/' . $model->fileName; ?>" class="img-thumbnail" style="height: 200px"/>
        </div>
    </div>
<?php endif; ?>
<p></p>
<div class="candidate-info-view">


    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'title',
            'description',
            [
                'format' => 'html',
                'attribute'=>'createdAt',
                'value' => function ($model) {
                    $User = new User();
                    return $User->convertDBTime($model->createdAt);
                }
            ],
            [
            'label' => '',
            'format' => 'raw',
            'visible' => ($model->type == Resource::IMAGE || $model->type == Resource::DOCUMENT),
              'value' => function ($model) {
                return   Html::a('<i class="fa fa-download"></i>', Yii::$app->urlManager->createUrl(['resource/download', 'filePath' => Yii::$app->toolKit->resourcePathAbsolute . $model->fileName]), array('title' => Yii::t('messages', 'Download')));
                }
             ],
         ],
    ]) ?>

</div>
