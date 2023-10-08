<?php
$this->widget('bootstrap.widgets.TbMenu', array(
    'type' => 'tabs',
    'stacked' => false,
    'htmlOptions' => array('class' => "nav-item"),
    'items' => array(
        array(
            'label' => Yii::t('messages', 'Potential Matches'),
            'url' => array('PotentialMatches/admin/'),
            'active' => in_array(Yii::app()->controller->id, array('potentialMatches')),
            'visible' => Yii::app()->user->checkAccess('PotentialMatches.Admin'),
            'itemOptions' => array('id' => 'ppl-potential-match', 'class'=>"nav-item")
        ),
        array(
            'label' => Yii::t('messages', 'User Matching'),
            'url' => array('UserMatchMain/admin/'),
            'active' => in_array(Yii::app()->controller->id, array('userMatchMain')),
            'visible' => Yii::app()->user->checkAccess('UserMatchMain.admin'),
            'itemOptions' => array('id' => 'ppl-user-match', 'class'=>"nav-item")
        )
    ),
));
?>
