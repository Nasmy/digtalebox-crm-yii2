<?php

use app\models\User;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
use yii\helpers\Html;

$form = ActiveForm::begin([
    'id' => 'form-edit',
    'method' => 'POST',
]);
?>
    <div class="alert alert-success alert-dismissable" id="update-success">
        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button> People updated
    </div>
    <div class="modal-body edit-keyword">
        <div class="form-group">
            <?php
            if ($keywordEdit) { ?>
                <?php
                $model->userKeywords = $model->keywords;
                echo $form->field($model, 'keywords')->widget(Select2::className(), [
                    'name' => 'keywords',
                    'data' => $keywords,
                    'size' => Select2::MEDIUM,
                    'options' => [
                        'placeholder' => 'Select a Keyword ...',
                        'class' => 'form-control form-control-selectize',
                        'multiple' => true,
                        'id' => 'user-keywords'

                    ],

                ]);

                echo Html::hiddenInput('check', 2);
                ?>
            <?php } else {
                echo $form->field($model, 'userType')->dropdownList(User::getUserTypes(),
                    ['class' => 'form-control', 'prompt' => Yii::t('messages', '--- Select Category ---')]
                )->label(false);
            }
            echo $form->field($model, 'id')->hiddenInput()->label(false);
            echo Html::hiddenInput('check', 4);
            ?>
        </div>
    </div>
    <div class="modal-footer">
        <?php
        if ($keywordEdit) {
            echo Html::submitButton(Yii::t('messages', 'Save changes'), ['class' => 'updateKeyword btn btn-primary']);
        } else {
            echo Html::submitButton(Yii::t('messages', 'Save changes'), ['class' => 'updateCategory btn btn-primary']);
        }
        ?>
    </div>
<?php ActiveForm::end(); ?>
<?php
$updateKeyword_url = Yii::$app->urlManager->createUrl(['people/update-people-ajax', "id" => $model['id'], "check" => 2]);
$updateCategory_url = Yii::$app->urlManager->createUrl(['people/update-people-ajax', "id" => $model['id'], "check" => 4]);
$updateGrid_url = Yii::$app->urlManager->createUrl(['advanced-search/grid-update']);
$this->registerJs("
  $(document).ready(function () 
  {
        $('#update-success').hide();
        $('.updateCategory').on('click', function () 
        {
            var urlCategory = '" . $updateCategory_url . "';
            var filters = [];
            $('.filter:checked', window.parent.document).each(function () 
            {
                filters.push($(this).val());
            });
            var criteriaId = $('#criteriaId option:selected', window.parent.document).val();
            var url = '" . $updateGrid_url . "';
            var dataArray = $('.search-form form', window.parent.document).serialize();
            $.post(urlCategory, {
               data: $('#form-edit').serialize()},
                function (data) 
                {
                    if (data) 
                    {
                        $.post(url, {filters: filters, data: dataArray, criteriaId: criteriaId},
                        function (returnedData) 
                        {
                             $('#update-success').show();
                            return false;
        
                        });
                        return false;
                        
                    }
                   
                    return false;
                });
            return false;
        });
        
         $('.updateKeyword').on('click', function () 
         {
             var urlKeyword = '" . $updateKeyword_url . "';
             var keywords=$('#user-keywords').val();
             var filters = [];
            $('.filter:checked', window.parent.document).each(function () 
            {
                filters.push($(this).val());
            });
            var criteriaId = $('#criteriaId option:selected', window.parent.document).val();
            var url = '" . $updateGrid_url . "';
            var dataArray = $('.search-form form', window.parent.document).serialize();
            $.post(urlKeyword, {data: $('#form-edit').serialize()},
                function (data) {
                    if (data) {
                         $.post(url, {filters: filters, data: dataArray, criteriaId: criteriaId},
                            function (returnedData) 
                            {
                                 $('#update-success').show();
                                return false;
            
                            });
                            
                        return false;
                    }
                    
                    return false;
                });
            return false;
        });

    });
    
    ");
?>