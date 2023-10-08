<?php

/**
 * IntlPhoneInput class file.
 *
 * @author Odirlei Santos
 * @version 0.1
 */
class IntlPhoneInput extends CInputWidget
{
	/**
	 * Use this property to set jQuery settings
	 * See {@link https://github.com/Bluefieldscom/intl-tel-input/#options}
	 * @var Array
	 */
	public $options;
	/**
	 * Use this property to update the data to only show localised country names.
	 * @var Boolean
	 */
	public $localisedCountryNames=true;
	/**
	 * Use this property to get the current number formatted to the [E.164 standard]
	 * See {@link http://en.wikipedia.org/wiki/E.164}
	 * @var Boolean
	 */
	public $E164=true;
	/**
	 * Enable formatting/validation etc. by specifying the path to the included "utils.js" script
	 * @var String
	 */
	private $utilsScript;

	/**
	 * Executes the widget.
	 * This method registers all needed client scripts and renders
	 * the text field.
	 */
	public function run()
	{
		list($name, $id)=$this->resolveNameID();
		if(!isset($this->htmlOptions['id']))
			$this->htmlOptions['id']=$id;
		if(!isset($this->htmlOptions['name']))
			$this->htmlOptions['name']=$name;

		$this->registerClientScript();

		if($this->hasModel()) {
			$value=$this->model->{$this->attribute};
			echo CHtml::activeHiddenField($this->model, $this->attribute);
		}
		else {
			$value=$this->value;
			echo CHtml::hiddenField($this->htmlOptions['name'], $this->value);
		}
		$htmlOptions=$this->htmlOptions;
		unset($htmlOptions['id'], $htmlOptions['name']);
		echo CHtml::textField('intl-phone-input', $value, $htmlOptions);
	}

	/**
	 * Registers the needed CSS and JavaScript.
	 */
	private function registerClientScript()
	{
		$assets=Yii::app()->getAssetManager()->publish(dirname(__FILE__) . '/build');
		$lib=Yii::app()->getAssetManager()->publish(dirname(__FILE__) . '/lib');
		$this->utilsScript=$lib . '/libphonenumber/build/utils.js';

		// Configures JavaScript
		$config=$this->config();
		$options=CJavaScript::encode($config);
		$js="jQuery('#intl-phone-input').intlTelInput({$options});";

		$clone='val()';
		if($this->E164 === true)
			$clone='intlTelInput(\'getNumber\')';
		$js.="jQuery('#intl-phone-input').change(function() {
                        jQuery('#{$this->htmlOptions['id']}').val(jQuery(this).{$clone});
                });";

		if($this->localisedCountryNames === true) {
			$js.="var countryData = $.fn.intlTelInput.getCountryData();
                        $.each(countryData, function(i, country) {
                                country.name = country.name.replace(/.+\((.+)\)/,'$1');
                        });";
		}

		// Add other JavaScript methods to $js.
		// See https://github.com/Bluefieldscom/intl-tel-input#public-methods
		// See https://github.com/Bluefieldscom/intl-tel-input#static-methods

		$cs=Yii::app()->getClientScript();
		$cs->registerCssFile($assets . '/css/intlTelInput.css');
		$cs->registerScriptFile($assets . '/js/intlTelInput.min.js');
		$cs->registerScript(__CLASS__ . '#' . $this->htmlOptions['id'], $js);
	}

	/**
	 * jQuery settings
	 * See {@link https://github.com/Bluefieldscom/intl-tel-input/#options}
	 * @return Array the options for the Widget
	 */
	private function config()
	{
		// Predefined settings.
		$options=array(
			'defaultCountry' => 'auto',
			'numberType' => 'MOBILE',
			'preferredCountries' => array('fr','us','ca'),
			'responsiveDropdown' => true,
			'localisedCountryNames'=>false,
			'nationalMode' => false,
//			'autoFormat' => false,
//			'formatOnDisplay'=>false,
		);
		// Client options
		if(is_array($this->options)) {
			foreach($this->options as $key => $value)
				$options[$key]=$value;
		}
		// Specifies/overwrites the path to the included "utils.js" script
		$options['utilsScript']=$this->utilsScript;
		return $options;
	}

}