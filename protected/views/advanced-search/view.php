<!-- View Modal -->
<div class="modal-body edit-keyword">
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="mx-auto"><?php echo $model->getPic($model->profImage, 50, 50); ?> </div>
                    <h5 class="card-title">
                        <h5><?php echo Yii::app()->fa->getIcon('search', Yii::t('messages', 'DigitaleBox Profile')) ?></h5>
                    </h5>
                    <div class="table-wrap">
                        <?php
                        $customAttributesArray = array();
                        if (!empty($customFields) && !empty($customAttributes)) {
                            foreach ($customAttributes as $key => $val) {
                                $customAttributesArray[] = array('name' => $key, 'value' => $val);
                            }
                        }
                        $attributesArray = array(
                            array(
                                'type' => 'raw',
                                'name' => Yii::t('messages', 'Name'),
                                'value' => $model->getName(),
                            ),
                            'email',
                            'mobile',
                            array(
                                'type' => 'raw',
                                'name' => 'gender',
                                'value' => $model->getGenderLabel($model->gender, 1),
                            ),
                            'zip',
                            'address1',
                            'city',
                            array(
                                'name' => 'countryCode',
                                'value' => Country::model()->getContryByCode($model->countryCode),
                            ),
                            array(
                                'name' => 'joinedDate',
                                'value' => date('Y-m-d', strtotime($model->joinedDate)),
                            ),
                            array(
                                'name' => 'userType',
                                'value' => $model->getUserTypes($model->userType),
                            ),
                            array(
                                'name' => 'signup',
                                'value' => User::SIGNUP == $model->signup ? Yii::t('messages', 'Signup') : Yii::t('messages', 'Not Signup'),
                            ),
                            'dateOfBirth',
                            'reqruiteCount',
                            array(
                                'name' => 'keywords',
                                'value' => Keyword::model()->getKeywordsByIdList($model->keywords),
                            ),
                            'notes',

                        );

                        $attributesArray = array_merge($attributesArray, $customAttributesArray);

                        $this->widget('bootstrap.widgets.TbDetailView', array(
                            'data' => $model,
                            'attributes' => $attributesArray,
                            'htmlOptions' => array('class' => 'table table-striped table-custom'),
                        )); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php if ($twModel != null): ?>
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="mx-auto"><?php echo Yii::app()->toolKit->getPic($twModel->profileImageUrl, 50, 50); ?> </div>
                        <h5 class="card-title"><h5><i class="fa fa-twitter fa-lg profiles"></i>
                                <?php echo Yii::t('messages', 'Twitter Profile') ?></h5>
                        </h5>
                        <div class="table-wrap">
                            <?php $this->widget('bootstrap.widgets.TbDetailView', array(
                                'data' => $twModel,
                                'htmlOptions' => array('class' => 'table table-striped table-custom'),
                                'attributes' => array(
                                    'name',
                                    //'email',
                                    'screenName',
                                    'location',
                                    'friendsCount',
                                    'followerCount',
                                ),
                            )); ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <?php if ($fbModel != null): ?>
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="mx-auto"><?php echo Yii::app()->toolKit->getPic($fbModel->profileImageUrl, 50, 50); ?> </div>
                        <h5 class="card-title"><h5><i class="fa fa-facebook fa-lg profiles"></i>
                                <?php echo Yii::t('messages', 'Facebook Profile') ?>
                            </h5></h5>
                        <div class="table-wrap">
                            <?php
                            $this->widget('bootstrap.widgets.TbDetailView', array(
                                'data' => $fbModel,
                                'htmlOptions' => array('class' => 'table table-striped table-custom'),
                                'attributes' => array(
                                    'name',
                                    'username',
                                    'email',
                                    'location',
                                    'friendsCount',
                                    'followerCount',
                                ),
                            ));
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <?php if ($lnModel != null): ?>
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="mx-auto"><?php echo Yii::app()->toolKit->getPic($lnModel->pictureUrl, 50, 50); ?> </div>
                        <h5 class="card-title"><h5><i class="fa fa-linkedin fa-lg profiles"></i>
                                <?php echo Yii::t('messages', 'LinkedIn Profile') ?>
                            </h5></h5>
                        <div class="table-wrap">
                            <?php $this->widget('bootstrap.widgets.TbDetailView', array(
                                'data' => $lnModel,
                                'htmlOptions' => array('class' => 'table table-striped table-custom'),
                                'attributes' => array(
                                    'firstName',
                                    'lastName',
                                    'headline',
                                    'email',
                                    'location'
                                ),
                            )); ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="col-md-6"></div>
    <div class="col-md-6"></div>
    <div class="col-md-6"></div>
</div>
