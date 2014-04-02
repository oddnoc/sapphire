<?php

/**
 * Text input field with validation for numeric values. Supports validating
 * the numeric value as to the {@link i18n::get_locale()} value.
 * 
 * @package forms
 * @subpackage fields-formattedinput
 */
class NumericField extends TextField {

	public function setValue($value) {
		require_once "Zend/Locale/Format.php";
		if (is_numeric($value)) {
			// Converts the value of an input from a locale specific number format
			$locale = new Zend_Locale(i18n::get_locale());
			$this->value = Zend_Locale_Format::toNumber($value, array('locale' => $locale));
		} else {
			// If an invalid number, store it anyway, but validate() will fail
			$this->value = trim($value);
		}
		return $this;
	}
	
	/**
	 * Determine if the current value is a valid number in the current locale
	 * 
	 * @param mixed $value Optional value to check against. Will check the current value if not given
	 * @return bool
	 */
	public function IsNumeric($value = null) {
		if(!func_num_args()) $value = $this->value;
		require_once "Zend/Locale/Format.php";
		$locale = new Zend_Locale(i18n::get_locale());
		return Zend_Locale_Format::isNumber(
			trim($this->value),
			array('locale' => $locale)
		);
	}

	public function Type() {
		return 'numeric text';
	}

	public function validate($validator) {
		if(!$this->value && !$validator->fieldIsRequired($this->name)) {
			return true;
		}
		
		if($this->IsNumeric()) return true;

		$validator->validationError(
			$this->name,
			_t(
				'NumericField.VALIDATION', "'{value}' is not a number, only numbers can be accepted for this field",
				array('value' => $this->value)
			),
			"validation"
		);
		return false;
	}

	/**
	 * Extracts the number value from the localised string value
	 * 
	 * @return string number value
	 */
	public function dataValue() {
		require_once "Zend/Locale/Format.php";
		if(!$this->IsNumeric()) return 0;
		$locale = new Zend_Locale(i18n::get_locale());
		$number = Zend_Locale_Format::getNumber($this->value, array('locale' => $locale));
		return $number;
	}
}
