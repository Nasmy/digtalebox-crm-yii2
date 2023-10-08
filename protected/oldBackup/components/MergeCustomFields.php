<?php


namespace app\components;


use app\models\CustomField;
use yii\base\Component;
use yii\helpers\Html;

class MergeCustomFields extends Component
{
    public function previewCustomField($parentCustomField, $childCustomField) {
        foreach ($parentCustomField as $key => $parentItem) {
            foreach ($childCustomField as $childKey => $childItem) {
                if($parentCustomField[$key]['customFieldId'] === $childCustomField[$childKey]['customFieldId']) {
                    if($parentCustomField[$key]['fieldValue'] != $childCustomField[$childKey]['fieldValue']) {
                        if($parentCustomField[$key]['fieldValue'] != null) {
                            $parentRadioLabel = $parentCustomField[$key]['fieldValue'];
                        } else {
                            $parentRadioLabel = 'N/A';
                        }

                        if($childCustomField[$childKey]['fieldValue'] != null) {
                            $childRadioLabel = $childCustomField[$childKey]['fieldValue'];
                        } else {
                            $childRadioLabel = 'N/A';
                        }

                        echo '<p><strong>'.$childCustomField[$childKey]['customFieldInfo']['label'].'</strong></p>';
                        echo '<div class="form-row form-row-separated mb-3 mr-5"><div class="form-group"><div class="form-check form-check-inline">';
                        if(isset($childCustomField[$key]['customFieldId'])) {
                            $customFieldLabel = $childCustomField[$childKey]['customFieldInfo']['label'];
                            echo Html::radio("CustomField[".$customFieldLabel."]", false, ['value' => $parentCustomField[$key]['fieldValue'], 'label' => $parentRadioLabel]);
                            echo Html::radio("CustomField[".$customFieldLabel."]", false, ['value' => $childCustomField[$key]['fieldValue'], 'label' => $childRadioLabel]);
                            echo '</div></div></div>';
                       }
                    }
                }
            }

        }
    }
}