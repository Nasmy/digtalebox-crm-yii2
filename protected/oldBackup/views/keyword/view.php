<br/>
<div class="app-body">
    <?php

    use app\models\User;
    use yii\widgets\DetailView;

    echo DetailView::widget([
        'model' => $model,
        'attributes' => [
            'name',
            [
                'label' => 'lastUpdated',
                'value' => User::convertSystemTime($model->lastUpdated),
            ],
            [                      // the owner name of the model
                'format' => 'raw',
                'label' => 'behaviour',
                'value' => $model->getBehaviourOptions($model->behaviour),
            ],
            [                      // the owner name of the model
                'label' => 'lastUpdated',
                'value' => User::convertSystemTime($model->lastUpdated),
            ],
            [                      // the owner name of the model
                'format' => 'raw',
                'label' => 'status',
                'value'=>$model->getKeywordLable($model->status),
            ],
            [                      // the owner name of the model
                'format' => 'raw',
                'label' => 'conditions',
                'value'=>$ruleStr,
            ],
         ],
    ]);

    ?>
</div>
