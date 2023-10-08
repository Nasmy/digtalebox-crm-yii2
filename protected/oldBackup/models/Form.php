<?php

namespace app\models;

use app\components\ToolKit;
use app\components\Validations\ValidateEnablePayment;
use app\components\Validations\ValidateRequiredFields;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * This is the model class for table "Form".
 *
 * @property int $id
 * @property string $title
 * @property string|null $content
 * @property string|null $keywords
 * @property string|null $redirectAddress
 * @property string|null $enabled
 * @property string|null $notified
 * @property string|null $enablePayment
 * @property string|null $isMembership
 * @property string|null $isDonation
 * @property int $templateId
 * @property string $createdAt
 * @property string|null $updatedAt
 *
 * @property FormCustomField[] $formCustomFields
 * @property FormField[] $formFields
 */
class Form extends \yii\db\ActiveRecord
{
    public $fieldList;
    public $preview;
    public $captcha;

    const YOUNG = 1;
    const SINGLE = 2;
    const COUPLE = 3;
    const MEMBER = 1;
    const DONNER = 2;
    const MEMBER_ONLY = 3;
    const DONNER_ONLY = 4;
    public $isUpdate = false;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Form';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'fieldList', 'keywords', 'redirectAddress', 'enablePayment'], 'required'],
            [['content'], 'required', 'on' => 'update'],
            [['fieldList'], ValidateRequiredFields::className()],
            [['enablePayment'], ValidateEnablePayment::className()],
            [['captcha'], 'safe'],
            [['title'], 'unique'],
            [['redirectAddress'], 'url'],
            [['id'], 'integer'],
            [['enabled'], 'number', 'max' => 256],
            [['templateId'], 'default', 'value' => 0],
            [['updatedAt', 'content', 'preview', 'keywords', 'redirectAddress', 'notified', 'templateId', 'enablePayment', 'isMembership', 'isDonation'], 'safe'],
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            [['id', 'title', 'content', 'enabled', 'createdAt', 'updatedAt', 'notified', 'templateId'], 'safe', 'on' => 'search']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return
            [
                'id' => Yii::t('messages', 'ID'),
                'title' => Yii::t('messages', 'Title'),
                'content' => Yii::t('messages', 'Content'),
                'enabled' => Yii::t('messages', 'Enabled'),
                'notified' => Yii::t('messages', 'Notify Admin'),
                'createdAt' => Yii::t('messages', 'Created On'),
                'updatedAt' => Yii::t('messages', 'Updated At'),
                'fieldList' => Yii::t('messages', 'Form Fields'),
                'preview' => Yii::t('messages', 'Preview'),
                'keywords' => Yii::t('messages', 'Keywords'),
                'redirectAddress' => Yii::t('messages', 'Redirect URL'),
                'templateId' => Yii::t('messages', 'Template'),
                'isMembership' => Yii::t('messages', 'Membership Payment'),
                'isDonation' => Yii::t('messages', 'Donation Payment'),
                'enablePayment' => Yii::t('messages', 'Enable Payment'),
            ];
    }

    /**
     * Gets query for [[FormCustomFields]].
     *
     * @return \yii\db\ActiveQuery|FormCustomFieldQuery
     */
    public function getFormCustomFields()
    {
        return $this->hasMany(FormCustomField::className(), ['formId' => 'id']);
    }

    /**
     * Gets query for [[FormFields]].
     *
     * @return \yii\db\ActiveQuery|FormFieldQuery
     */
    public function getFormFields()
    {
        return $this->hasMany(FormField::className(), ['formId' => 'id']);
    }

    /**
     * @return string CSV of the sub area list
     */
    public static function getList($id)
    {
        $result = array();
        $data = FormField::find()->where('formId = :formId', [':formId' => $id])->all();
        foreach ($data as $val) {
            $field = FormFieldList::findOne($val->fieldId);
            if (isset($field->displayName)) {
                $result[] = Yii::t('messages', $field->displayName);
            }
        }
        $dataCustom = FormCustomField::findAll('formId=:formId', [':formId' => $id]);
        foreach ($dataCustom as $val) {
            $field = CustomField::find()->where(['fieldName' => $val->customFieldId]);
            if (isset($field->label)) {
                $result[] = $field->label;
            }
        }
        return implode(",", $result);
    }

    /**
     * Validate if required fields are selected
     */

    public function validateRequiredFields($attribute)
    {

        $firstName = FormFieldList::find()->where(['name' => 'firstName'])->all();
        $lastName = FormFieldList::find()->where(['name' => 'lastName'])->all();
        $email = FormFieldList::find()->where(['name' => 'email'])->all();

        if (!empty($this->fieldList)) {

            if (!in_array($firstName[0]->id, $this->fieldList)) {
                $this->addError($attribute, Yii::t('messages', 'First name must be selected.'));
            }
            if (!in_array($lastName[0]->id, $this->fieldList)) {
                $this->addError($attribute, Yii::t('messages', 'Last name must be selected.'));
            }
            if (!in_array($email[0]->id, $this->fieldList)) {
                $this->addError($attribute, Yii::t('messages', 'Email must be selected.'));
            }
            return false;
        }
        return true;
    }

    /**
     * Returns form fields.
     * @return array Available fields
     * @throws \yii\db\Exception
     */
    public function getFieldList($selectedFields = array())
    {
        $customArr = array();
        $list = array();
        $customFields = CustomValue::getCustomData(CustomType::CF_PEOPLE, 0, CustomField::ACTION_CREATE, ToolKit::post('CustomValue'), CustomType::CF_SUB_PEOPLE_FORM_BUILDER);
        foreach ($customFields as $customField) {
            $customArr[$customField->fieldName] = $customField->fieldLabel;
        }
        $customKeys = array_keys($customArr);

        if (empty($selectedFields)) {
            $list = array();
            $data = ArrayHelper::map(FormFieldList::find()->all(), 'id', 'displayName');

            foreach ($data as $key => $val) {
                $list[$key] = Yii::t('messages', $val);
            }
            $list = array_replace($list, $customArr);
        } else {
            $data1 = ArrayHelper::map(FormFieldList::find()->all(), 'id', 'displayName');
            $data1 = array_replace($data1, $customArr);
            foreach ($selectedFields as $val) {
                if (in_array($val, $customKeys)) {
                    $field = CustomField::find()->where(['fieldName' => $val]);
                    if (isset($field->label)) {
                        $list[$field->fieldName] = $field->label;
                    }
                } else {
                    $data2 = FormFieldList::findOne($val);
                    if (isset($data2->displayName)) {
                        $list[$data2->id] = Yii::t('messages', $data2->displayName);
                    }
                }
            }
            $list = $list + $data1;
        }

        return $list;
    }

    /**
     * Validate if enable payment is checked
     */
    public function validateEnablePayment()
    {
        $stripeClientId = Configuration::findOne(Configuration::STRIPE_CLIENT_ID);
        $stripeSecretId = Configuration::findOne(Configuration::STRIPE_SECRET_ID);
        if (!empty($this->enablePayment) && (ToolKit::isEmpty($stripeClientId->value) || ToolKit::isEmpty($stripeSecretId->value))) {
            $this->addError('enablePayment', Yii::t('messages', 'Stripe Client Id and Secret Id cannot be empty to enable payment. (System -> Configuration)'));
        }
        if (!empty($this->enablePayment) && !$this->hasErrors('enablePayment')) { //if stripe client id & enable is set
            if (empty($this->isMembership) && empty($this->isDonation)) {
                $this->addError('enablePayment', Yii::t('messages', '{isMembership} or {isDonation} cannot be empty', array('isMembership' => $this->getAttributeLabel('isMembership'), 'isDonation' => $this->getAttributeLabel('isDonation'))));
            }
        }
    }



    /**
     * Generates the form contents.
     * @throws \Exception
     */
    public function generateContent()
    {

        $formFields = FormField::find()->where(['formId' => $this->id])->all();
        $formCustomFields = FormCustomField::find()->where(['formId' => $this->id])->all();
        $url = 'https://' . Yii::$app->request->getServerName();
        $url .= Url::to(['/newsletter/subscribe']);

        $base_url = Url::base(true);

        $jquery_url = $base_url . "/captcha/node_modules/jquery/jquery.min.js";
        $captcha_url = $base_url . "/captcha/node_modules/jquery-captcha/dist/jquery-captcha.min.js";
        $basic_url = $base_url . "/captcha/js/basic.js";

        $isSecure = Yii::$app->request->isSecureConnection;
        if ($isSecure) {
            $url = str_replace('http://', 'https://', urldecode($url));
            $jquery_url = str_replace('http://', 'https://', urldecode($jquery_url));
            $captcha_url = str_replace('http://', 'https://', urldecode($captcha_url));
            $basic_url = str_replace('http://', 'https://', urldecode($basic_url));
            $base_url = str_replace('http://', 'https://', urldecode($base_url));
        }

        $formId = base64_encode($this->id);
        $redirectAdd = is_null($this->redirectAddress) ? '' : $this->redirectAddress;
        $htmlFooter = "<script src='{$jquery_url}' type='text/javascript'></script>
                            <script src='{$captcha_url}' type='text/javascript'></script>
                            <script src='{$basic_url}' type='text/javascript'></script>";
        $formHead = "<form method=\"post\" id='basicForm' action='{$url}'><div id=\"form-messages\"></div>";
        $formBody = '';
        $formFooter = "<br><input type='hidden' id='formActionUrl' value='{$url}'><input type='hidden' id='siteBaseUrl' value='{$base_url}'><input type='hidden' name='formId' value='{$formId}'><input type='hidden' name='callBackUrl' value='{$redirectAdd}'><!-- Redirect URL ex: http://www.google.com, if not set redirects to client page -->
            <input id='submitForm' style=\"font-family: 'Helvetica Neue', Arial, Lucida Grande, sans-serif; width: 100%; max-width: 125px; height: 40px; font-size: 14px; -webkit-border-radius: 5px; -moz-border-radius: 5px; border-radius: 5px; border: solid 1px #7abcff; background-color: #4096ee; color: #ffffff; background: -moz-linear-gradient(top,  #7abcff 0%, #60abf8 44%, #4096ee 100%); background: -webkit-linear-gradient(top,  #7abcff 0%,#60abf8 44%,#4096ee 100%); background: linear-gradient(to bottom,  #7abcff 0%,#60abf8 44%,#4096ee 100%); filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#7abcff', endColorstr='#4096ee',GradientType=0 ); cursor: pointer;\" type='submit' name='Submit' value='".Yii::t('messages','Submit')."'>
        </form>";
        $formBody .= "<input type='hidden' name='isConcern' value='1'>";
        foreach ($formFields as $key => $val) {

            $field = FormFieldList::findOne($val->fieldId);

            if ($field) { //1927-10-04
                switch ($field->name) {
                    case 'email':
                        $formBody .= Html::label($field->displayName, '', array('style' => 'font-size: 12px; font-weight: normal; color: #696969;', 'required' => true)) .
                            '<br> ' . Html::textInput($field->name, '',
                                ['placeholder' => 'ex: john@abc.com', 'type' => 'email', 'required' => true, 'style' => "width: 100%; height: 30px; color: #696969; margin-top: 2px; padding: 0px 10px; max-width: 250px; margin-bottom: 10px; border: solid 1px #ccc; -webkit-border-radius: 5px; -moz-border-radius: 5px; border-radius: 5px; -webkit-box-shadow: 0px 0px 2px 0px #ccc; -moz-box-shadow: 0px 0px 2px 0px #ccc; box-shadow: 0px 0px 2px 0px #ccc; font-family: 'Helvetica Neue', Arial, Lucida Grande, sans-serif;"]) . '<br>';
                        break;
                    case 'firstName':
                        $formBody .= Html::label($field->displayName, '', array('style' => 'font-size: 12px; font-weight: normal; color: #696969;', 'required' => true)) .
                            '<br> ' . Html::textInput($field->name, '', ['required' => true, 'style' => "width: 100%; height: 30px; color: #696969; margin-top: 2px; padding: 0px 10px; max-width: 250px; margin-bottom: 10px; border: solid 1px #ccc; -webkit-border-radius: 5px; -moz-border-radius: 5px; border-radius: 5px; -webkit-box-shadow: 0px 0px 2px 0px #ccc; -moz-box-shadow: 0px 0px 2px 0px #ccc; box-shadow: 0px 0px 2px 0px #ccc; font-family: 'Helvetica Neue', Arial, Lucida Grande, sans-serif;"]) . '<br>';
                        break;
                    case 'lastName':
                        $formBody .= Html::label($field->displayName, '', array('style' => 'font-size: 12px; font-weight: normal; color: #696969;', 'required' => true)) .
                            '<br> ' . Html::textInput($field->name, '', ['required' => true, 'style' => "width: 100%; height: 30px; color: #696969; margin-top: 2px; padding: 0px 10px; max-width: 250px; margin-bottom: 10px; border: solid 1px #ccc; -webkit-border-radius: 5px; -moz-border-radius: 5px; border-radius: 5px; -webkit-box-shadow: 0px 0px 2px 0px #ccc; -moz-box-shadow: 0px 0px 2px 0px #ccc; box-shadow: 0px 0px 2px 0px #ccc; font-family: 'Helvetica Neue', Arial, Lucida Grande, sans-serif;"]) . '<br>';
                        break;
                    case 'dateOfBirth':
                        $formBody .= Html::label($field->displayName, '', array('style' => 'font-size: 12px; font-weight: normal; color: #696969;')) .
                            '<br> ' . Html::textInput($field->name, '', ['placeholder' => 'ex: 1990-01-31', 'style' => "width: 100%; height: 30px; color: #696969; margin-top: 2px; padding: 0px 10px; max-width: 250px; margin-bottom: 10px; border: solid 1px #ccc; -webkit-border-radius: 5px; -moz-border-radius: 5px; border-radius: 5px; -webkit-box-shadow: 0px 0px 2px 0px #ccc; -moz-box-shadow: 0px 0px 2px 0px #ccc; box-shadow: 0px 0px 2px 0px #ccc; font-family: 'Helvetica Neue', Arial, Lucida Grande, sans-serif;"]) . '<br>';
                        break;
                    case 'countryCode':
                         $formBody .= Html::label($field->displayName, '', array('style' => 'font-size: 12px; font-weight: normal; color: #696969;')) .
                        '<br> ' . Html::dropDownList($field->name, '', ArrayHelper::map(Country::find()->asArray()->all(), 'countryCode', 'countryName'),
                                ['style' => 'width: 100%; height: 30px; color: #696969; margin-top: 2px;  max-width: 250px; margin-bottom: 10px; border: solid 1px #ccc; -webkit-border-radius: 5px; -moz-border-radius: 5px; border-radius: 5px; -webkit-box-shadow: 0px 0px 2px 0px #ccc; -moz-box-shadow: 0px 0px 2px 0px #ccc; box-shadow: 0px 0px 2px 0px #ccc; font-family: \'Helvetica Neue\', Arial, Lucida Grande, sans-serif;']
                        ) . '<br>';
                        break;
                    case 'gender':
                        $formBody .= Html::label($field->displayName, '', array('style' => 'font-size: 12px; font-weight: normal; color: #696969;')) .
                            '<br> ' . Html::dropDownList($field->name, '', ['2' => 'Male', '1' => 'Female'], ['style' => 'width: 100%; height: 30px; color: #696969; margin-top: 2px;  max-width: 250px; margin-bottom: 10px; border: solid 1px #ccc; -webkit-border-radius: 5px; -moz-border-radius: 5px; border-radius: 5px; -webkit-box-shadow: 0px 0px 2px 0px #ccc; -moz-box-shadow: 0px 0px 2px 0px #ccc; box-shadow: 0px 0px 2px 0px #ccc; font-family: \'Helvetica Neue\', Arial, Lucida Grande, sans-serif;']) . '<br>';
                        break;
                    case 'mobile':
                        $formBody .= Html::label($field->displayName, '', array('style' => 'font-size: 12px; font-weight: normal; color: #696969;', 'required' => true)) .
                            '<br> ' . Html::textInput($field->name, '', ['placeholder' => 'ex: +330610882049', 'required' => true, 'style' => "width: 100%; height: 30px; color: #696969; margin-top: 2px; padding: 0px 10px; max-width: 250px; margin-bottom: 10px; border: solid 1px #ccc; -webkit-border-radius: 5px; -moz-border-radius: 5px; border-radius: 5px; -webkit-box-shadow: 0px 0px 2px 0px #ccc; -moz-box-shadow: 0px 0px 2px 0px #ccc; box-shadow: 0px 0px 2px 0px #ccc; font-family: 'Helvetica Neue', Arial, Lucida Grande, sans-serif;"]) . '<br>';
                        break;
                    default:
                        $formBody .= Html::label($field->displayName, '', array('style' => 'font-size: 12px; font-weight: normal; color: #696969;')) .
                            '<br> ' . Html::textInput($field->name, '', ['style' => "width: 100%; height: 30px; color: #696969; margin-top: 2px; padding: 0px 10px; max-width: 250px; margin-bottom: 10px; border: solid 1px #ccc; -webkit-border-radius: 5px; -moz-border-radius: 5px; border-radius: 5px; -webkit-box-shadow: 0px 0px 2px 0px #ccc; -moz-box-shadow: 0px 0px 2px 0px #ccc; box-shadow: 0px 0px 2px 0px #ccc; font-family: 'Helvetica Neue', Arial, Lucida Grande, sans-serif;"]) . '<br>';
                        break;
                }
            }
        }
        $formPayment = '';
        if ($this->enablePayment) {
            if ($this->isMembership) {
                $formPayment .= Html::label(Yii::t('messages', 'Membership'), '', ['style' => 'font-size: 12px; font-weight: normal; color: #696969;']) .
                    '<br> ' . Html::dropDownList('isMembership', '', $this->getPaymentTypes(), ['style' => 'width: 100%; height: 30px; color: #696969; margin-top: 2px;  max-width: 250px; margin-bottom: 10px; border: solid 1px #ccc; -webkit-border-radius: 5px; -moz-border-radius: 5px; border-radius: 5px; -webkit-box-shadow: 0px 0px 2px 0px #ccc; -moz-box-shadow: 0px 0px 2px 0px #ccc; box-shadow: 0px 0px 2px 0px #ccc; font-family: \'Helvetica Neue\', Arial, Lucida Grande, sans-serif;']) . '<br>';
            }
            if ($this->isDonation) {
                $formPayment .= Html::label(Yii::t('messages', 'Donation'), '', array('style' => 'font-size: 12px; font-weight: normal; color: #696969;')) .
                    '<br> ' . Html::textInput('isDonation', '', ['placeholder' => Yii::t('messages', 'Minimum donation amount is 1 eur'), 'style' =>
                        "width: 100%; height: 30px; color: #696969; margin-top: 2px; padding: 0px 10px; max-width: 250px; margin-bottom: 10px; border: solid 1px #ccc; -webkit-border-radius: 5px; -moz-border-radius: 5px; border-radius: 5px; -webkit-box-shadow: 0px 0px 2px 0px #ccc; -moz-box-shadow: 0px 0px 2px 0px #ccc; box-shadow: 0px 0px 2px 0px #ccc; font-family: 'Helvetica Neue', Arial, Lucida Grande, sans-serif;"]) . '<br>';
            }
        }

        foreach ($formCustomFields as $val) {
            $field = CustomField::find()->where(['fieldName' => $val->customFieldId])->one();
            $customType = CustomType::findOne($field->customTypeId);
            if ($field) { //1927-10-04
                switch ($customType->typeName) {
                    case 'date':
                        $formBody .= Html::label($field->label, '', ['style' => 'font-size: 12px; font-weight: normal; color: #696969;']) .
                            '<br> ' . Html::textInput('CustomValue[' . $field->id . '][fieldValue]', '', ['placeholder' => 'ex: 1990-01-31', 'style' => "width: 100%; height: 30px; color: #696969; margin-top: 2px; padding: 0px 10px; max-width: 250px; margin-bottom: 10px; border: solid 1px #ccc; -webkit-border-radius: 5px; -moz-border-radius: 5px; border-radius: 5px; -webkit-box-shadow: 0px 0px 2px 0px #ccc; -moz-box-shadow: 0px 0px 2px 0px #ccc; box-shadow: 0px 0px 2px 0px #ccc; font-family: 'Helvetica Neue', Arial, Lucida Grande, sans-serif;"]) . '<br>';
                        break;
                    case 'dropdown':
                        //Item list
                        $list = explode("\r", $field->listValues);
                        $data = array();
                        foreach ($list as $item) {
                            $data[$item] = $item;
                        }

                        $formBody .= Html::label($field->label, '', ['style' => 'font-size: 12px; font-weight: normal; color: #696969;']) .
                            '<br> ' . Html::dropDownList('CustomValue[' . $field->id . '][fieldValue]', '', $data, ['style' => "width: 100%; height: 30px; color: #696969; margin-top: 2px; padding: 0px 10px; max-width: 250px; margin-bottom: 10px; border: solid 1px #ccc; -webkit-border-radius: 5px; -moz-border-radius: 5px; border-radius: 5px; -webkit-box-shadow: 0px 0px 2px 0px #ccc; -moz-box-shadow: 0px 0px 2px 0px #ccc; box-shadow: 0px 0px 2px 0px #ccc; font-family: 'Helvetica Neue', Arial, Lucida Grande, sans-serif;"]) . '<br>';
                        break;
                    case 'boolean':
                        $formBody .= Html::label($field->label, '', ['style' => 'font-size: 12px; font-weight: normal; color: #696969;']) .
                            '<br> ' . Html::checkbox('CustomValue[' . $field->id . '][fieldValue]', '', ['style' => "color: #696969; margin-top: 2px; padding: 0px 10px;margin-bottom: 10px; -webkit-border-radius: 5px; -moz-border-radius: 5px; border-radius: 5px; font-family: 'Helvetica Neue', Arial, Lucida Grande, sans-serif;"]) . '<br>';
                        break;
                    case 'radiobutton':
                        //Item list
                        $list = explode("\r", $field->listValues);
                        $data = array();
                        foreach ($list as $item) {
                            $data[$item] = $item;
                        }
                        $formBody .= Html::label($field->label, '', ['style' => 'font-size: 12px; font-weight: normal; color: #696969;']) .
                            '<br> ' . Html::radioList('CustomValue[' . $field->id . '][fieldValue]', '', $data, ['labelOptions' => ['style' => 'color: #696969;'], 'style' => "color: #696969; margin-top: 2px; padding: 0px 10px; margin-bottom: 10px; -webkit-border-radius: 5px; -moz-border-radius: 5px; border-radius: 5px; font-family: 'Helvetica Neue', Arial, Lucida Grande, sans-serif;"]) . '<br>';
                        break;
                    case 'checkbox':
                        //Item list
                        $list = explode("\r", $field->listValues);
                        $data = array();
                        foreach ($list as $item) {
                            $data[$item] = $item;
                        }
                        $formBody .= Html::label($field->label, '', ['style' => 'font-size: 12px; font-weight: normal; color: #696969;']) .
                            '<br> ' . Html::checkboxList('CustomValue[' . $field->id . '][fieldValue]', '', $data, ['labelOptions' => ['style' => "color: #696969"]], ['style' => "color: #696969; margin-top: 2px; padding: 0px 10px; margin-bottom: 10px; -webkit-border-radius: 5px; -moz-border-radius: 5px; border-radius: 5px; font-family: 'Helvetica Neue', Arial, Lucida Grande, sans-serif;"]) . '<br>';
                        break;
                    default:
                        $formBody .= Html::label($field->label, '', ['style' => 'font-size: 12px; font-weight: normal; color: #696969;']) .
                            '<br> ' . Html::textInput('CustomValue[' . $field->id . '][fieldValue]', '', ['style' => "width: 100%; height: 30px; color: #696969; margin-top: 2px; padding: 0px 10px; max-width: 250px; margin-bottom: 10px; border: solid 1px #ccc; -webkit-border-radius: 5px; -moz-border-radius: 5px; border-radius: 5px; -webkit-box-shadow: 0px 0px 2px 0px #ccc; -moz-box-shadow: 0px 0px 2px 0px #ccc; box-shadow: 0px 0px 2px 0px #ccc; font-family: 'Helvetica Neue', Arial, Lucida Grande, sans-serif;"]) . '<br>';
                        break;
                }
            }
        }
        /* compulsory checkbox field asking for consent */
        $concern = '<br>' . Html::checkbox('concern', '', ['required' => true, 'style' => "color: #696969; margin-top: 2px; padding: 0px 10px;margin-bottom: 10px; -webkit-border-radius: 5px; -moz-border-radius: 5px; border-radius: 5px; font-family: 'Helvetica Neue', Arial, Lucida Grande, sans-serif;"]) .
            Html::label(Yii::t('messages', 'In submitting this form you are confirming that you are agreeing to provide above filled information to our system, to the best of your knowledge.'), '', ['style' => 'font-size: 14px; font-weight: bold; color: #696969;']) . '<br>';

        // add captcha
        $captcha_text = Yii::t('messages', 'Retype the characters from the picture');
        $add_captcha = "<br> <div id=\"botdetect-captcha\" data-captchastylename=\"jqueryBasicCaptcha\"></div>
                        <label>
                            <span style=\"font-size: 12px; font-weight: normal; color: #696969;\">'{$captcha_text}'</span>
                        </label>
                        <!-- captcha code: user-input textbox -->
                        <br><input 
                                type=\"text\" 
                                id=\"userCaptchaInput\" 
                                required
                                style=\"width: 100%; height: 30px; color: #696969; margin-top: 2px; padding: 0px 10px; max-width: 250px; margin-bottom: 10px; border: solid 1px #ccc; -webkit-border-radius: 5px; -moz-border-radius: 5px; border-radius: 5px; -webkit-box-shadow: 0px 0px 2px 0px #ccc; -moz-box-shadow: 0px 0px 2px 0px #ccc; box-shadow: 0px 0px 2px 0px #ccc; font-family: 'Helvetica Neue', Arial, Lucida Grande, sans-serif;\"
                            ><br>";

         return $formHead . $formBody . $formPayment . $concern . $add_captcha . $formFooter . $htmlFooter;
    }

    /**
     * @param $string
     * @return mixed
     */
    public function getTranslated($string)
    {
        $fieldList = array('Male', 'Female', 'value=\'Submit\'');
        $data = FormFieldList::find()->all();
        foreach ($data as $val) {
            $fieldList[] = $val->displayName;
        }
        foreach ($fieldList as $field) {
            if (strpos($string, $field) !== false) { //$field
                $string = str_replace($field, Yii::t('messages', $field), $string);
            }
        }


        return $string;
    }

    /**
     * Returns Payment type fee.
     * @param $id
     * @return string Available payment type fee
     */
    public static function getPaymentTypeFee($id) {
        $membershipTypes = FormMembershipType::findOne($id);
        return $membershipTypes->fee;
    }

    /**
     * Returns payment & donation amount.
     * @param $id
     * @return float|null Available payment types
     */
    public static function getPaymentAmount($id) {
        $price = null;
        $formMem = FormMembershipDonation::findOne($id);
        if (!is_null($formMem->memberFee) && !is_null($formMem->donationFee)) {
            $price = $formMem->memberFee + $formMem->donationFee;
        } else if (!is_null($formMem->memberFee)) {
            $price = $formMem->memberFee;
        } else if (!is_null($formMem->donationFee)) {
            $price = $formMem->donationFee;
        }

        return $price;
    }


    /**
     * Returns payment & donation amount.
     * @param $id
     * @return string|null Available payment types
     */
    public static function getPaymentPlan($id) {
        $plan = null;
        $formMem = FormMembershipDonation::findOne($id);
        if (!is_null($formMem->memberFee) && !is_null($formMem->donationFee)) {
            $plan = Yii::t('messages', 'Membership & Donation');
        } else if (!is_null($formMem->memberFee)) {
            $plan = Yii::t('messages', 'Membership');
        } else if (!is_null($formMem->donationFee)) {
            $plan = Yii::t('messages', 'Donation');
        }

        return $plan;
    }

    /**
     * This method is called at the beginning of inserting or updating a record.
     *
     * The default implementation will trigger an [[EVENT_BEFORE_INSERT]] event when `$insert` is `true`,
     * or an [[EVENT_BEFORE_UPDATE]] event if `$insert` is `false`.
     * When overriding this method, make sure you call the parent implementation like the following:
     *
     * ```php
     * public function beforeSave($insert)
     * {
     *     if (!parent::beforeSave($insert)) {
     *         return false;
     *     }
     *
     *     // ...custom code here...
     *     return true;
     * }
     * ```
     *
     * @param bool $insert whether this method called while inserting a record.
     * If `false`, it means the method is called while updating a record.
     * @return bool whether the insertion or updating should continue.
     * If `false`, the insertion or updating will be cancelled.
     * @throws \Exception
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {

            if (!$this->isUpdate) {
                $this->createdAt = User::convertSystemTime();
            } else {
                $this->updatedAt = User::convertSystemTime();

            }
        }
        return parent::beforeSave($insert);
    }

    /**
     * This method is called at the end of inserting or updating a record.
     * The default implementation will trigger an [[EVENT_AFTER_INSERT]] event when `$insert` is `true`,
     * or an [[EVENT_AFTER_UPDATE]] event if `$insert` is `false`. The event class used is [[AfterSaveEvent]].
     * When overriding this method, make sure you call the parent implementation so that
     * the event is triggered.
     * @param bool $insert whether this method called while inserting a record.
     * If `false`, it means the method is called while updating a record.
     * @param array $changedAttributes The old values of attributes that had changed and were saved.
     * You can use this parameter to take action based on the changes made for example send an email
     * when the password had changed or implement audit trail that tracks all the changes.
     * `$changedAttributes` gives you the old attribute values while the active record (`$this`) has
     * already the new, updated values.
     *
     * Note that no automatic type conversion performed by default. You may use
     * [[\yii\behaviors\AttributeTypecastBehavior]] to facilitate attribute typecasting.
     * See http://www.yiiframework.com/doc-2.0/guide-db-active-record.html#attributes-typecasting.
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($this->isUpdate) {
            FormField::deleteAll('formId=:formId', [':formId' => $this->id]);
            FormCustomField::deleteAll('formId=:formId', [':formId' => $this->id]);
        }
        if (!empty($this->fieldList)) {
            $data = ArrayHelper::map(FormFieldList::find()->all(), 'id', 'displayName');
            $fieldKeys = array_keys($data);
            foreach ($this->fieldList as $val) {
                if (in_array($val, $fieldKeys)) {
                    $model = new FormField();
                    $model->formId = $this->id;
                    $model->fieldId = $val;
                    $model->save(false);
                } else {
                    $form = FormCustomField::find()->where(['formId' => $this->id, 'customFieldId' => $val])->all();
                    if (empty($form)) {
                        $model = new FormCustomField();
                        $model->formId = $this->id;
                        $model->customFieldId = $val;
                        $model->save(false);
                    }
                }
            }
        }

        return parent::afterSave($insert, $changedAttributes);
    }

    /**
     * Returns payment types.
     * @return array Available payment types
     */
    public static function getPaymentTypes()
    {
        $data = array();
        $membershipTypes = FormMembershipType::find()->all();
        foreach ($membershipTypes as $membership) {
            $data[$membership->id] = Yii::t('messages', '{title} ({fee} eur)', [
                'title' => $membership->title,
                'fee' => number_format($membership->fee)
            ]);
        }

        return $data;
    }


    /**
     * {@inheritdoc}
     * @return FormQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new FormQuery(get_called_class());
    }

    /**
     * Returns member / donner types.
     * @param integer $key type
     * @return array Available types
     */
    public static function getMemberDonationTypes($key = null)
    {
        $types = array(
            self::MEMBER => Yii::t('messages', 'Membership'),
            self::DONNER => Yii::t('messages', 'Donation'),
            self::MEMBER_ONLY => Yii::t('messages', 'Membership Only'),
            self::DONNER_ONLY => Yii::t('messages', 'Donation Only'),
        );

        if (null != $key) {
            return isset($types[$key]) ? $types[$key] : '';
        }

        return $types;
    }
}
