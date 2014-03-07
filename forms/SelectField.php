<?php

/**
 * Represents a field that allows users to select one or more items from a list
 */
abstract class SelectField extends FormField {
	
	/**
	 * Source data for all dropdown items. This could be an associative array,
	 * SS_List, or some other list object.
	 * 
	 * @var mixed
	 */
	protected $source;
	
	/**
	 * Determines if the field was selected
	 * at the time it was rendered, so if {@link $value} matches on of the array
	 * values specified in {@link $source}
	 * 
	 * @var bool
	 */
	protected $isSelected;
	
	/**
	 * Show the first <option> element as empty (not having a value),
	 * with an optional label defined through {@link $emptyString}.
	 * By default, the <select> element will be rendered with the
	 * first option from {@link $source} selected.
	 * 
	 * @var bool
	 */
	protected $hasEmptyDefault = false;
	
	/**
	 * The title shown for an empty default selection,
	 * e.g. "Select...".
	 * 
	 * @var string
	 */
	protected $emptyString = '';
	
	/**
	 * @var array $disabledItems The keys for items that should be disabled (greyed out) in the dropdown
	 */
	protected $disabledItems = array();
	
	/**
	 * @param string $name The field name
	 * @param string $title The field title
	 * @param array $source An map of the dropdown items
	 * @param string $value The current value
	 * @param Form $form The parent form
	 * @param string|bool $emptyString Add an empty selection on to of the {@link $source}-Array (can also be
	 *					boolean, which  results in an empty string).  Argument is deprecated
	 * 					in 3.1, please use{@link setEmptyString()} and/or
	 * 					{@link setHasEmptyDefault(true)} instead.
	 */
	public function __construct($name, $title=null, $source=array(), $value='', $form=null, $emptyString=null) {
		$this->setSource($source);

		if($emptyString === true) {
			Deprecation::notice('3.1',
				'Please use setHasEmptyDefault(true) instead of passing a boolean true $emptyString argument',
				Deprecation::SCOPE_GLOBAL);
		}
		if(is_string($emptyString)) {
			Deprecation::notice('3.1', 'Please use setEmptyString() instead of passing a string emptyString argument.',
				Deprecation::SCOPE_GLOBAL);
		}

		if($emptyString) $this->setHasEmptyDefault(true);
		if(is_string($emptyString)) $this->setEmptyString($emptyString);

		parent::__construct($name, ($title===null) ? $name : $title, $value, $form);
	}
	
	/**
	 * Mark certain elements as disabled,
	 * regardless of the {@link setDisabled()} settings.
	 * 
	 * @param array $items Collection of array keys, as defined in the $source array
	 */
	public function setDisabledItems($items){
		$this->disabledItems = $items;
		return $this;
	}
	
	/**
	 * @return Array
	 */
	public function getDisabledItems(){
		return $this->disabledItems;
	}

	public function getAttributes() {
		return array_merge(
			parent::getAttributes(),
			array('type' => null, 'value' => null)
		);
	}

	/**
	 * Determines if the field was selected at the time it was rendered,
	 * so if {@link $value} matches on of the array values specified in {@link $source}
	 * 
	 * @return bool
	 */
	public function isSelected() {
		return $this->isSelected;
	}

	/**
	 * Gets the source array including any empty default values.
	 * 
	 * @return array
	 */
	public function getSource() {
		
		// Inject default option
		if($this->getHasEmptyDefault()) {
			return array('' => $this->getEmptyString()) + $this->source;
		} else {
			return $this->source;
		}
	}

	/**
	 * Set the source for this list
	 * 
	 * @param mixed $source
	 */
	public function setSource($source) {
				
		// Extract source as an array
		if($source instanceof SS_List) {
			$source = $source->map();
		}
		if($source instanceof SS_Map) {
			$source = $source->toArray();
		}
		if(!is_array($source)) {
			user_error('$source passed in as invalid type', E_USER_ERROR);
		}
		
		$this->source = $source;
		return $this;
	}
	
	/**
	 * @param boolean $bool
	 * @return self Self reference
	 */
	public function setHasEmptyDefault($bool) {
		$this->hasEmptyDefault = $bool;
		return $this;
	}
	
	/**
	 * @return bool
	 */
	public function getHasEmptyDefault() {
		return $this->hasEmptyDefault;
	}

	/**
	 * Set the default selection label, e.g. "select...".
	 * Defaults to an empty string. Automatically sets
	 * {@link $hasEmptyDefault} to true.
	 *
	 * @param string $string
	 */
	public function setEmptyString($string) {
		$this->setHasEmptyDefault(true);
		$this->emptyString = $string;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getEmptyString() {
		return $this->emptyString;
	}

	public function performReadonlyTransformation() {
		$field = $this->castedCopy('LookupField');
		$field->setSource($this->getSource());
		$field->setReadonly(true);
		
		return $field;
	}
	
	/** 
	 * Determine if this field supports multiple selection
	 * 
	 * @return bool
	 */
	public function getMultiple() {
		return false;
	}
}
