<?php
namespace app\components;
use yii\base\Widget;
use yii\helpers\Html;

/**
 * Description of SCustomFields
 *
 * @author Azraar
 */
class SCustomFields extends Widget
{
    public $customFields;
    public $template;
    public $readonly;
    public $hideLabel;
    public $enableAjaxValidation;
    public $rowClass;
    public $isSearchView;
    public $isBulkPreview;
    public $previewData;
    public $isEditPreview;
    public $isMergePreview;
    public $parentData;
    public $childData;

    public function init() {
        parent::init();
    }

    public function run()
    {
        if (empty($this->template)){
            $this->template = '{label}{input}';
        }

        if (empty($this->hideLabel)){
            $this->hideLabel = false;
        }

        if (empty($this->enableAjaxValidation)){
            $this->enableAjaxValidation = false;
        }

        if (empty($this->rowClass)){
            $this->rowClass = '';
        }
        if (empty($this->rowClass)){
            $this->isSearchView = false;
        }

        if (empty($this->isBulkPreview)) {
            $this->isBulkPreview = false;
            $this->previewData = null;
        }

        if (empty($this->isMergePreview)) {
            $this->isMergePreview = false;
            $this->parentData = null;
            $this->childData = null;
        }

        if (empty($this->isEditPreview)) {
            $this->isEditPreview = false;
        }

        $colums = preg_split('/{[^}]*[^\/]}/i', $this->template, -1, PREG_SPLIT_DELIM_CAPTURE);
        return $this->render('CustomFields', array(
            'customFields' => $this->customFields,
            'colums' => $colums,
            'readonly' => $this->readonly,
            'hideLabel' => $this->hideLabel,
            'enableAjaxValidation'=> $this->enableAjaxValidation,
            'rowClass' => $this->rowClass,
            'isSearchView' => $this->isSearchView,
            'isBulkPreview' => $this->isBulkPreview,
            'previewData' => $this->previewData,
            'isEditPreview' => $this->isEditPreview,
            'isMergePreview' => $this->isMergePreview,
            'parentData' => $this->parentData,
            'childData' => $this->childData,
          ));
    }
}