<?php

use yii\helpers\Html;
use yii\jui\DatePicker;

$len = count($colums);
$count = ($len - 1) / 2;
$index = 0;

foreach ($customFields as $n => $cf) {
    //Label
    $cf->fieldLabel = Yii::t('messages', $cf->fieldLabel);

    //Template
    $index = ($n + 1) % $count;
    if (($count === 1 && $index === 0) || $index === 1)
        echo $colums[0];

    $gap = $index === 0 ? $colums[$len - 2] : $colums[($index * 2) - 1];

    if ($readonly) {
        echo Html::label($cf->fieldLabel, false);
        echo $gap;
        echo Html::encode($cf->fieldValue);

    } else if ($isSearchView) {

        switch ($cf->fieldType) {
            case 'boolean':
                echo  Html::activeDropDownList($cf,'[' . $cf->customFieldId . ']fieldValue', array(
                    '' => '- ' . Yii::t('messages', $cf->fieldLabel) . ' -',
                    1 => Yii::t('messages', 'Yes'),
                    0 => Yii::t('messages', 'No'),
                ), ['class' => 'form-control']);
                break;
            case 'dropdown':
            case 'radiobutton':
            case 'checkbox':
                break;
            case 'date':
                echo DatePicker::widget([
                    'model' => $cf,
                    'attribute' => '[' . $cf->customFieldId . ']fieldValue',
                    'language' => '',
                    'clientOptions' => [
                        'dateFormat' => 'yy-mm-dd',
                        'maxDate' => 'js:new Date(' . date('Y-10,m,d,H,i') . ')',
                        'changeYear' => true,
                        'yearRange' => "1900:" . date("Y"),
                    ],
                    'options' => array('readonly' => true, 'placeholder' => $cf->fieldLabel,
                        'class' => 'form-control datetimepicker-input'),

                ]);
                break;
            default:
                echo  Html::activeTextInput($cf, '[' . $cf->customFieldId . ']fieldValue', array('placeholder' => $cf->fieldLabel, 'class' => 'form-control'));
                break;
        }
    } else if ($isBulkPreview) {
        $opt['class'] = 'form-control';
        echo "<tr>";
        echo "<td>".Html::activeDropDownList($cf, '[' . $cf->customFieldId . ']fieldValue', $previewData, $opt).Html::error($cf, '[' . $cf->customFieldId . ']fieldValue', array('class' => 'help-block error'))."</td>";
        echo "<td class=\"text-center\"><i class=\"fa fa-long-arrow-right mapping-arrow fa-lg mt-3\"></i></td>";
        echo "<td><div class=\"mt-2 font14\">".Html::activeLabel($cf, '[' . $cf->customFieldId . ']fieldValue', array('label' => $cf->fieldLabel, 'class' => 'control-label', 'required'=>false))."</div>"."</td>";
        // echo CHtml::error($cf, '[' . $cf->customFieldId . ']fieldValue', array('class' => 'help-inline error'));
        echo "</tr>";
    }
    else if ($isMergePreview) {
        if (!empty($parentData[$cf->fieldName]) && !empty($childData[$cf->fieldName])) {
            if ($parentData[$cf->fieldName] != $childData[$cf->fieldName]) {
                $data = array('#'.$parentData[$cf->fieldName] => $parentData[$cf->fieldName], '#'.$childData[$cf->fieldName] => $childData[$cf->fieldName]);
                $opt = array('class'=>'form-check-input custom-icheck','style'=>'display:inline');
                echo '<div class="control-group">';
                $req = '<span class="">' . '&nbsp&nbsp&nbsp' . '</span>';
                echo '<label class="control-label">' . $cf->fieldLabel . $req.'</label>';
                echo '<div class="controls ml-2">';
                echo Html::activeRadioList($cf, '[' . $cf->customFieldId . ']fieldValue', $data, $opt);
                echo '<br>';
                echo Html::error($cf, '[' . $cf->customFieldId . ']fieldValue', array('class' => 'help-inline error'));
                echo '</div></div>';
            }
        }
    }

    else if ($isEditPreview) {

        switch ($cf->fieldType) {
            case 'date':
                $opt = array('class' => 'form-control', 'readonly' => true);
                if (!empty($rowClass))
                    $opt = array('class' => $rowClass);
                if (!$hideLabel) {
                    echo Html::activeLabel($cf, '[' . $cf->customFieldId . ']fieldValue', array('label' => $cf->fieldLabel, 'class' => 'control-label'));
                    echo $gap;
                } else {
                    $opt['placeholder'] = $cf->fieldLabel;
                    echo '<span class="">' . '&nbsp&nbsp&nbsp' . '</span>';
                }

                echo  DatePicker::widget([
                    'model' => $cf,
                    'attribute' => '[' . $cf->customFieldId . ']fieldValue',
                    'language' => '',
                    'clientOptions' => [
                        'dateFormat' => 'yy-mm-dd',
                        'changeYear' => true,
                        'yearRange' => "1900:" . date("Y"),
                    ],
                    'options' =>  $opt

                ]);
                echo Html::error($cf, '[' . $cf->customFieldId . ']fieldValue', array('class' => 'help-inline error'));
                break;
            case 'dropdown':
                //Item list
                $listValues=strip_tags($cf->listValues);
                $clear = trim(preg_replace('/ +/', ' ', preg_replace('/[^A-Za-z0-9 ]/', ' ', urldecode(html_entity_decode(strip_tags($listValues))))));
                $list=str_replace(' ',',',$clear);
                $list = explode(",", $list);
                $data = array();
                foreach ($list as $item)
                    $data[$item] = $item;
                //Option array
                $opt = array();
                if (!empty($cf->fieldIsRequired) &&  $cf->fieldIsRequired == 1){
                    $opt['options'] = ['required'=>'true'];
                }
                $opt['empty'] = '';
                if (!empty($cf->fieldDefaultValue)) {
                    $opt['options'] = array($cf->fieldDefaultValue => array('selected' => true));
                }
                if (!empty($rowClass))
                    $opt = array('class' => $rowClass);

                if (!$hideLabel) {
                    echo Html::activeLabel($cf, '[' . $cf->customFieldId . ']fieldValue', array('label' => $cf->fieldLabel, 'class' => 'control-label'));
                    echo $gap;
                } else {
                    echo '&nbsp';
                }
                echo Html::activeDropDownList($cf, '[' . $cf->customFieldId . ']fieldValue', $data, $opt);
                echo Html::error($cf, '[' . $cf->customFieldId . ']fieldValue', array('class' => 'help-inline error'));
                break;
            case 'boolean':
                if (!$hideLabel) {
                    echo Html::activeLabel($cf, '[' . $cf->customFieldId . ']fieldValue', ['label' => $cf->fieldLabel. '&nbsp', 'class' => 'control-label']);
                    echo $gap;
                } else {
                    echo '<span class="">' . $cf->fieldLabel . '&nbsp' . '</span>';
                }
                echo Html::activeCheckbox($cf, '[' . $cf->customFieldId . ']fieldValue',['label'=>null]);
                echo Html::error($cf, '[' . $cf->customFieldId . ']fieldValue', array('class' => 'help-inline error'));
                break;
            case 'radiobutton':
                //Item list
                $listValues=strip_tags($cf->listValues);
                $clear = trim(preg_replace('/ +/', ' ', preg_replace('/[^A-Za-z0-9 ]/', ' ', urldecode(html_entity_decode(strip_tags($listValues))))));
                $list=str_replace(' ',',',$clear);
                $list = explode(",", $list);
                $data = array();
                foreach ($list as $item)
                    $data[$item] = $item;

                $opt = array('labelOptions' => array('style' => 'display:inline'));
                if (!empty($cf->fieldDefaultValue)) {
                    $_POST['CustomValue'][$cf->customFieldId]['fieldValue'] = $cf->fieldDefaultValue;
                }
                if (!$hideLabel) {
                    echo Html::activeLabel($cf, '[' . $cf->customFieldId . ']fieldValue', array('label' => $cf->fieldLabel, 'class' => 'control-label'));
                    echo $gap;
                } else {
                    echo '<span class="">' . $cf->fieldLabel . '&nbsp' . '</span>';
                }
                echo '<table cellpadding="0" cellspacing="0" class="customcheckboxes"><tr><td>';
                echo Html::activeRadioList($cf, '[' . $cf->customFieldId . ']fieldValue', $data, $opt);
                echo '</td></tr></table>';
                echo Html::error($cf, '[' . $cf->customFieldId . ']fieldValue', array('class' => 'help-inline error'));
                break;
            case 'checkbox':
                //Item list
                $listValues=strip_tags($cf->listValues);
                $clear = trim(preg_replace('/ +/', ' ', preg_replace('/[^A-Za-z0-9 ]/', ' ', urldecode(html_entity_decode(strip_tags($listValues))))));
                $list=str_replace(' ',',',$clear);
                $list = explode(",", $list);
                $data = array();
                foreach ($list as $item)
                    $data[$item] = $item;

                $cf->fieldValue = explode(',', $cf->fieldValue);

                $opt = array('labelOptions' => array('style' => 'display:inline', 'template' => '{input}&nbsp;{label}'));
                if (!empty($cf->fieldDefaultValue)) {
                    $_POST['CustomValue'][$cf->customFieldId]['fieldValue'] = $cf->fieldDefaultValue;
                }
                if (!$hideLabel) {
                    echo Html::activeLabel($cf, '[' . $cf->customFieldId . ']fieldValue',
                        array('label' => $cf->fieldLabel, 'class' => 'control-label'));
                    echo $gap;
                } else {
                    echo '<span class="">' . $cf->fieldLabel . '</span>';
                }
                echo '<table cellpadding="0" cellspacing="0" class="customcheckboxes"><tr><td>';
                echo Html::activeCheckboxList($cf, '[' . $cf->customFieldId . ']fieldValue', $data, $opt);
                echo '</td></tr></table>';
                echo Html::error($cf, '[' . $cf->customFieldId . ']fieldValue', array('class' => 'help-inline error'));
                break;
            default:
                echo Html::activeLabel($cf, '[' . $cf->customFieldId . ']fieldValue', array('label' => $cf->fieldLabel,
                    'class' => 'control-label'));
                echo $gap;
                echo Html::activeTextInput($cf, '[' . $cf->customFieldId . ']fieldValue', array('class' => 'form-control','id'=>''));
                echo Html::error($cf, '[' . $cf->customFieldId . ']fieldValue', array('class' => 'help-inline error'));
                break;
        }
    }
    else {

        switch ($cf->fieldType) {
            case 'int':
                $opt = array('class' => 'form-control');
                if (!empty($rowClass))
                    $opt = array('class' => $rowClass);
                if (!$hideLabel) {
                    echo Html::activeLabel($cf, '[' . $cf->customFieldId . ']fieldValue', array('label' => $cf->fieldLabel,
                        'class' => 'control-label'));
                    echo $gap;
                } else {
                    $opt['placeholder'] = $cf->fieldLabel;
                    //echo '<span class="">' . '&nbsp&nbsp&nbsp' . '</span>';
                }
                echo Html::activeTextInput($cf, '[' . $cf->customFieldId . ']fieldValue', $opt);
                if ($enableAjaxValidation) {
                    echo '<span class="help-block error" id="CustomValue_' . $cf->customFieldId . '_fieldValue" style="display: none;"></span>';
                } else {
                    echo Html::error($cf,'[' . $cf->customFieldId . ']fieldValue', array('class' => 'help-inline error'));
                }
                break;
            case 'float':
                $opt = array('class' => 'form-control');
                if (!empty($rowClass))
                    $opt = array('class' => $rowClass);
                if (!$hideLabel) {
                    echo Html::activeLabel($cf, '[' . $cf->customFieldId . ']fieldValue', array('label' => $cf->fieldLabel,
                        'class' => 'control-label'));
                    echo $gap;
                } else {
                    $opt['placeholder'] = $cf->fieldLabel;
                    // echo '<span class="">' . '&nbsp&nbsp&nbsp' . '</span>';
                }
                echo Html::activeTextInput($cf, '[' . $cf->customFieldId . ']fieldValue', $opt);
                if ($enableAjaxValidation) {
                    echo '<span class="help-block error" id="CustomValue_' . $cf->customFieldId . '_fieldValue_em_" style="display: none;"></span>';
                } else {
                    echo Html::error($cf,'[' . $cf->customFieldId . ']fieldValue', array('class' => 'help-inline error'));
                }
                break;
            case 'text':
                $opt = array('class' => 'form-control');
                if (!empty($rowClass))
                    $opt = array('class' => $rowClass);
                if (!$hideLabel) {
                    echo Html::activeLabel($cf, '[' . $cf->customFieldId . ']fieldValue', array('label' => $cf->fieldLabel,
                        'class' => 'control-label'));
                    echo $gap;
                } else {
                    $opt['placeholder'] = $cf->fieldLabel;
//                    echo '<span class="">' . '&nbsp&nbsp&nbsp' . '</span>';
                }
                echo Html::activeTextInput($cf, '[' . $cf->customFieldId . ']fieldValue', $opt);
                if ($enableAjaxValidation) {
                    echo '<span class="help-block error" id="CustomValue_' . $cf->customFieldId . '_fieldValue_em_" style="display: none;"></span>';
                } else {
                    echo Html::error($cf,'[' . $cf->customFieldId . ']fieldValue', array('class' => 'help-inline error'));
                }
                break;
            case 'textarea':
                $opt = array('class' => 'form-control', 'style' => $cf->fieldStyle);
                if (!empty($rowClass))
                    $opt = array('class' => $rowClass);
                if (!$hideLabel) {
                    echo Html::activeLabel($cf, '[' . $cf->customFieldId . ']fieldValue', array('label' => $cf->fieldLabel, 'class' => 'control-label'));
                    echo $gap;
                } else {
                    $opt['placeholder'] = $cf->fieldLabel;
                    // echo '<span class="">' . '&nbsp&nbsp&nbsp' . '</span>';
                }
                echo Html::activeTextArea($cf, '[' . $cf->customFieldId . ']fieldValue', $opt);
                if ($enableAjaxValidation) {
                    echo '<span class="help-block error" id="CustomValue_' . $cf->customFieldId . '_fieldValue_em_" style="display: none;"></span>';
                } else {
                    echo Html::error($cf,'[' . $cf->customFieldId . ']fieldValue', array('class' => 'help-inline error'));
                }
                break;
            case 'date':
                $opt = array('class' => 'form-control', 'readonly' => true);
                if (!empty($rowClass))
                    $opt = array('class' => $rowClass);
                if (!$hideLabel) {
                    echo Html::activeLabel($cf, '[' . $cf->customFieldId . ']fieldValue', array('label' => $cf->fieldLabel, 'class' => 'control-label'));
                    echo $gap;
                } else {
                    $opt['placeholder'] = $cf->fieldLabel;
                    //echo '<span class="">' . '&nbsp&nbsp&nbsp' . '</span>';
                }

                echo  DatePicker::widget([
                    'model' => $cf,
                    'attribute' => '[' . $cf->customFieldId . ']fieldValue',
                    'language' => '',
                    'options' => [
                        'dateFormat' => 'yy-mm-dd',
                        'changeYear' => true,
                        'yearRange' => "1900:" . date("Y"),
                        'class' => 'form-control',
                        'readonly' => true,
                    ],

                ]);
                if ($enableAjaxValidation) {
                    echo '<span class="help-block error" id="CustomValue_' . $cf->customFieldId . '_fieldValue_em_" style="display: none;"></span>';
                } else {
                    echo Html::error($cf,'[' . $cf->customFieldId . ']fieldValue', array('class' => 'help-inline error'));
                }
                break;
            case 'boolean':
                if (!$hideLabel) {
                    echo Html::activeLabel($cf, '[' . $cf->customFieldId . ']fieldValue', array('label' => $cf->fieldLabel, 'class' => 'control-label'));
                    echo $gap;
                    echo '<br>';
                } else {
                    echo '<span class="">' . $cf->fieldLabel . '&nbsp' . '</span>';
                }
                echo Html::activeCheckbox($cf, '[' . $cf->customFieldId . ']fieldValue',['label'=>null]);
                if ($enableAjaxValidation) {
                    echo '<span class="help-block error" id="CustomValue_' . $cf->customFieldId . '_fieldValue_em_" style="display: none;"></span>';
                } else {
                    echo Html::error($cf,'[' . $cf->customFieldId . ']fieldValue', array('class' => 'help-inline error'));
                }
                break;
            case 'url':
                $opt = array('class' => 'form-control');
                if (!empty($rowClass))
                    $opt = array('class' => $rowClass);
                if (!$hideLabel) {
                    echo Html::activeLabel($cf, '[' . $cf->customFieldId . ']fieldValue', array('label' => $cf->fieldLabel, 'class' => 'control-label'));
                    echo $gap;
                } else {
                    $opt['placeholder'] = $cf->fieldLabel;
                    echo '&nbsp';
                }
                echo Html::activeTextInput($cf, '[' . $cf->customFieldId . ']fieldValue', $opt);
                if ($enableAjaxValidation) {
                    echo '<span class="help-block error" id="CustomValue_' . $cf->customFieldId . '_fieldValue_em_" style="display: none;"></span>';
                } else {
                    echo Html::error($cf,'[' . $cf->customFieldId . ']fieldValue', array('class' => 'help-inline error'));
                }
                break;
            case 'email':
                $opt = array('class' => 'form-control');
                if (!empty($rowClass))
                    $opt = array('class' => $rowClass);
                if (!$hideLabel) {
                    echo Html::activeLabel($cf, '[' . $cf->customFieldId . ']fieldValue', array('label' => $cf->fieldLabel, 'class' => 'control-label'));
                    echo $gap;
                } else {
                    $opt['placeholder'] = $cf->fieldLabel;
                    echo '&nbsp';
                }
                echo Html::activeTextInput($cf, '[' . $cf->customFieldId . ']fieldValue', $opt);
                if ($enableAjaxValidation) {
                    echo '<span class="help-block error" id="CustomValue_' . $cf->customFieldId . '_fieldValue_em_" style="display: none;"></span>';
                } else {
                    echo Html::error($cf,'[' . $cf->customFieldId . ']fieldValue', array('class' => 'help-inline error'));
                }
                break;
            case 'dropdown':
                //Item list

                $list = explode(",", $cf->listValues);
                $data = array();
                foreach ($list as $item)
                    $data[$item] = $item;

                //Option array
                $opt = array();
                if (empty($cf->fieldIsRequired))
                    $opt['empty'] = '';
                if (!empty($cf->fieldDefaultValue)) {
                    $opt['options'] = array($cf->fieldDefaultValue => array('selected' => true));
                }
                if (!empty($rowClass))
                    $opt = array('class' => $rowClass);

                if (!$hideLabel) {
                    echo Html::activeLabel($cf, '[' . $cf->customFieldId . ']fieldValue', array('label' => $cf->fieldLabel, 'class' => 'control-label'));
                    echo $gap;
                } else {
                    echo '&nbsp';
                }
                echo Html::activeDropDownList($cf, '[' . $cf->customFieldId . ']fieldValue', $data, $opt);
                if ($enableAjaxValidation) {
                    echo '<span class="help-block error" id="CustomValue_' . $cf->customFieldId . '_fieldValue_em_" style="display: none;"></span>';
                } else {
                    echo Html::error($cf,'[' . $cf->customFieldId . ']fieldValue', array('class' => 'help-inline error'));
                }                break;
            case 'radiobutton':
                //Item list
                $list = explode(",", $cf->listValues);
                $data = array();
                foreach ($list as $item)
                    $data[$item] = $item;

                $opt = array('separator'=>'&nbsp;&nbsp;&nbsp;', 'style' => 'display:inline;margin-left:unset;', 'class' => 'form-check-input custom-icheck');
                if (!empty($cf->fieldDefaultValue)) {
                    $_POST['CustomValue'][$cf->customFieldId]['fieldValue'] = $cf->fieldDefaultValue;
                }
                if (!$hideLabel) {
                    echo Html::activeLabel($cf, '[' . $cf->customFieldId . ']fieldValue', array('label' => $cf->fieldLabel, 'class' => 'control-label'));
                    echo $gap;
                } else {
                    echo '<span class="">' . $cf->fieldLabel . '&nbsp' . '</span>';
                }
                echo '<table cellpadding="0" cellspacing="0" class="customcheckboxes"><tr><td>';
                echo Html::activeRadioList($cf, '[' . $cf->customFieldId . ']fieldValue', $data, $opt);
                echo '</td></tr></table>';
                if ($enableAjaxValidation) {
                    echo '<span class="help-block error" id="CustomValue_' . $cf->customFieldId . '_fieldValue_em_" style="display: none;"></span>';
                } else {
                    echo Html::error($cf,'[' . $cf->customFieldId . ']fieldValue', array('class' => 'help-inline error'));
                }                break;
            case 'checkbox':
                //Item list
                $list = explode(",", $cf->listValues);
                $data = array();
                foreach ($list as $item)
                    $data[$item] = $item;


                /** Note
                 *  when Create $cf->fieldValue will come as Array and we have to convert in to string with coma so we use implode
                 *  when Update $cf->fieldValue will come as String and we have to convert in to array so we use explode
                 * */

                if(is_array($cf->fieldValue)){
                    $cf->fieldValue = implode(',', $cf->fieldValue);
                }{
                $cf->fieldValue = explode(',', $cf->fieldValue);
            }


                $opt = array('separator'=>'&nbsp;&nbsp;&nbsp;', 'style' => 'display:inline;margin-left:unset;', 'template' => '{input}&nbsp;{label}', 'class' => 'form-check-input custom-icheck');
                if (!empty($cf->fieldDefaultValue)) {
                    $_POST['CustomValue'][$cf->customFieldId]['fieldValue'] = $cf->fieldDefaultValue;
                }
                if (!$hideLabel) {
                    echo Html::activeLabel($cf, '[' . $cf->customFieldId . ']fieldValue', array('label' => $cf->fieldLabel, 'class' => 'control-label'));
                    echo $gap;
                } else {
                    echo '<span class="">' . $cf->fieldLabel . '</span>';
                }

                echo '<table cellpadding="0" cellspacing="0" class="customcheckboxes"><tr><td>';
                echo Html::activeCheckBoxList($cf, '[' . $cf->customFieldId . ']fieldValue', $data, $opt);
                echo '</td></tr></table>';
                if ($enableAjaxValidation) {
                    echo '<span class="help-block error" id="CustomValue_' . $cf->customFieldId . '_fieldValue_em_" style="display: none;"></span>';
                } else {
                    echo Html::error($cf,'[' . $cf->customFieldId . ']fieldValue', array('class' => 'help-inline error'));
                }
                break;
        }

    }
    echo $index === 0 ? $colums[$len - 1] : $colums[$index * 2];
}

if ($index !== 0) {
    for ($i = ($index + 1); $i <= $count; $i++)
        echo $colums[($i * 2) - 1] . $colums[$i * 2];
}

?>
