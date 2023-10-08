<br/>
<div class="app-body">
    <?php

    use app\models\User;
    use yii\helpers\Html;
    use yii\widgets\DetailView;

    $title = Yii::t('messages', 'View Broadcast Messages');
    $titleDescription = Yii::t('messages', 'Facebook/Twitter/LinkedIn broadcast messages');

    Yii::$app->toolKit->registerFancyboxScripts();

    $fbImgUrl = Yii::$app->urlManager->baseUrl . '/' . Yii::$app->toolKit->resourcePathRelative . $model->fbImageName . '?rand' . mt_rand(10, 100);
    $fbProfImgUrl = Yii::$app->urlManager->baseUrl . '/' . Yii::$app->toolKit->resourcePathRelative . $model->fbProfImageName . '?rand' . mt_rand(10, 100);
    $twImgUrl = Yii::$app->urlManager->baseUrl . '/' . Yii::$app->toolKit->resourcePathRelative . $model->twImageName . '?rand' . mt_rand(10, 100);
    $lnImgUrl = Yii::$app->urlManager->baseUrl . '/' . Yii::$app->toolKit->resourcePathRelative . $model->lnImageName . '?rand' . mt_rand(10, 100);
    $lnPageImgUrl = Yii::$app->urlManager->baseUrl . '/' . Yii::$app->toolKit->resourcePathRelative . $model->lnPageImageName . '?rand' . mt_rand(10, 100);
    $fbImghtml = Html::img($fbImgUrl,array('width' => 20, 'height' => 20, 'class' => 'thumbnail', 'style' => ' display: inline-block;'));
    $fbProfImghtml = Html::img($fbProfImgUrl, array('width' => 20, 'height' => 20, 'class' => 'thumbnail', 'style' => ' display: inline-block;'));
    $twImghtml = Html::img($twImgUrl, array('width' => 20, 'height' => 20, 'class' => 'thumbnail', 'style' => ' display: inline-block;'));
    $lnImghtml = Html::img($lnImgUrl,  array('width' => 20, 'height' => 20, 'class' => 'thumbnail', 'style' => ' display: inline-block;'));
    $lnPageImghtml = Html::img($lnPageImgUrl,  array('width' => 20, 'height' => 20, 'class' => 'thumbnail', 'style' => ' display: inline-block;'));

    $fbImage = '';
    if ('' != $model->fbImageName) {
        $fbImage = Html::a($fbImghtml, $fbImgUrl, array('id' => 'fbImage'));
    }

    $fbProfImage = '';
    if ('' != $model->fbProfImageName) {
        $fbProfImage = Html::a($fbProfImghtml, $fbProfImgUrl, array('id' => 'fbProfImage'));
    }

    $twImage = '';
    if ('' != $model->twImageName) {
        $twImage = Html::a($twImghtml, $twImgUrl, array('id' => 'twImage'));
    }

    $lnImage = '';
    if ('' != $model->lnImageName) {
        $lnImage = Html::a($lnImghtml, $lnImgUrl, array('id' => 'lnImage'));
    }

    $lnPageImage = '';
    if ('' != $model->lnPageImageName) {
        $lnPageImage = Html::a($lnPageImghtml, $lnPageImgUrl, array('id' => 'lnPageImage'));
    }

//    var_dump($model); die();
     echo DetailView::widget([
        'model' => $model,
        'attributes' => [
            [
                'attribute' => 'fbPost',
                'format'=>'raw',
                 'value' => Yii::$app->toolKit->convertTextUrlsToLinks($model->fbPost, 'linkStat', $title) . ' ' . $fbImage,
            ],
            [                      // the owner name of the model
                'format' => 'raw',
                'attribute' => 'fbProfPost',
                'value' => Yii::$app->toolKit->convertTextUrlsToLinks($model->fbProfPost, 'linkStat', $title) . ' ' . $fbProfImage,
            ], [                      // the owner name of the model
                'format' => 'raw',
                'attribute' => 'twPost',
                'value' => Yii::$app->toolKit->convertTextUrlsToLinks($model->twPost, 'linkStat', $title) . ' ' . $twImage,
            ],[                      // the owner name of the model
                'format' => 'raw',
                'attribute' => 'lnPost',
                'value' => Yii::$app->toolKit->convertTextUrlsToLinks($model->lnPost, 'linkStat', $title) . ' ' . $lnImage,
            ],[                      // the owner name of the model
                'format' => 'raw',
                'attribute' => 'lnPagePost',
                'value' => Yii::$app->toolKit->convertTextUrlsToLinks($model->lnPagePost, 'linkStat', $title) . ' ' . $lnPageImage,
            ],[                      // the owner name of the model
                'format' => 'raw',
                'attribute' => 'fbPostStatus',
                'value' => $model->getFbStatusLabel(),
            ],[                      // the owner name of the model
                'format' => 'raw',
                'attribute' => 'fbProfPostStatus',
                'value' => $model->getFbProfStatusLabel(),
            ],[                      // the owner name of the model
                'format' => 'raw',
                'attribute' => 'twPostStatus',
                'value' => $model->getTwStatusLabel(),
            ],[                      // the owner name of the model
                'format' => 'raw',
                'attribute' => 'lnPostStatus',
                'value' => $model->getLnStatusLabel(),
            ],[                      // the owner name of the model
                'format' => 'raw',
                'attribute' => 'lnPagePostStatus',
                'value' => $model->getLnPageStatusLabel(),
            ],[                      // the owner name of the model
                'format' => 'raw',
                'attribute' => 'publishDate',
                'value' => User::convertSystemTime($model->publishDate),
            ],[                      // the owner name of the model
                'format' => 'raw',
                'attribute' => 'createdBy',
                'value' => User::getNameById($model->createdBy),
            ],[                      // the owner name of the model
                'format' => 'raw',
                'attribute' => 'createdAt',
                'value' => User::convertDBTime($model->createdAt),
            ],

        ],
    ]);

    ?>
</div>
