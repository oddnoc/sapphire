<?php
/**
 * Displays a set of checkboxes as a logical group.
 *
 * ASSUMPTION -> IF you pass your source as an array, you pass values as an array too. Likewise objects are handled
 * the same.
 * 
 * Example:
 * <code php>
 * new CheckboxSetField(
 *    $name = "topics",
 *    $title = "I am interested in the following topics",
 *    $source = array(
 *       "1" => "Technology",
 *       "2" => "Gardening",
 *       "3" => "Cooking",
 *       "4" => "Sports"
 *    ),
 *    $value = "1"
 * )
 * </code>
 * 
 * <b>Saving</b>
 * The checkbox set field will save its data in one of ways:
 * - If the field name matches a many-many join on the object being edited, that many-many join will be updated to
 *   link to the objects selected on the checkboxes.  In this case, the keys of your value map should be the IDs of
 *   the database records.
 * - If the field name matches a database field, a comma-separated list of values will be saved to that field.  The
 *   keys can be text or numbers.
 * 
 * @todo Document the different source data that can be used
 * with this form field - e.g ComponentSet, ArrayList,
 * array. Is it also appropriate to accept so many different
 * types of data when just using an array would be appropriate?
 * 
 * @package forms
 * @subpackage fields-basic
 */
class CheckboxSetField extends MultiSelectField {
	
	/**
	 * List of items to mark as checked, and may not be unchecked
	 * 
	 * @var array
	 */
	protected $defaultItems = array();
	
	/**
	 * @todo Explain different source data that can be used with this field,
	 * e.g. SQLMap, ArrayList or an array.
	 */
	public function Field($properties = array()) {
		Requirements::css(FRAMEWORK_DIR . '/css/CheckboxSetField.css');

		$selectedValues = $this->getValueArray();
		$defaultItems = $this->getDefaultItems();

		// Get values from the join, if available
		if(empty($selectedValues) && $this->form instanceof Form) {
			$record = $this->form->getRecord();
			if($record && $record->hasMethod($this->name)) {
				$relation = $record->{$this->name}();
				if($relation) $selectedValues = $relation->column('ID');
			}
		}
		
		// Generate list of options to display
		$odd = 0;
		foreach($this->sourceItems() as $value => $title) {
			$itemID = $this->ID() . '_' . preg_replace('/[^a-zA-Z0-9]/', '', $value);
			$odd = ($odd + 1) % 2;
			$extraClass = $odd ? 'odd' : 'even';
			$extraClass .= ' val' . preg_replace('/[^a-zA-Z0-9\-\_]/', '_', $value);

			$options[] = new ArrayData(array(
				'ID' => $itemID,
				'Class' => $extraClass,
				'Name' => "{$this->name}[{$value}]",
				'Value' => $value,
				'Title' => $title,
				'isChecked' => in_array($value, $selectedValues) || in_array($value, $defaultItems),
				'isDisabled' => $this->disabled || in_array($value, $defaultItems)
			));
		}

		$properties = array_merge($properties, array('Options' => new ArrayList($options)));

		return $this->customise($properties)->renderWith($this->getTemplates());
	}
	
	public function getMultiple() {
		// All checkbox set fields allow multiple selection
		return true;
	}
	
	/**
	 * Default selections, regardless of the {@link setValue()} settings.
	 * Note: Items marked as disabled through {@link setDisabledItems()} can still be
	 * selected by default through this method.
	 * 
	 * @param array $items Collection of array keys, as defined in the $source array
	 * @return self Self reference
	 */
	public function setDefaultItems($items) {
		$this->defaultItems = $items;
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function getDefaultItems() {
		return $this->defaultItems;
	}
	
	/**
	 * Load a value into this CheckboxSetField
	 * 
	 * @param mixed $value
	 * @param mixed $obj
	 * @return self Self reference
	 */
	public function setValue($value, $obj = null) {
		
		// If we're not passed a value directly, we can look for it in a relation method
		// on the object passed as a second arg
		if(!$value && $obj && $obj instanceof DataObject && $obj->hasMethod($this->name)) {
			$funcName = $this->name;
			$value = $obj->$funcName()->getIDList();
		}

		parent::setValue($value, $obj);
		return $this;
	}
	
	public function getValueArray() {
		
		// Null case
		if(empty($this->value)) return array();
		
		// Extract string in JSON format
		if(is_string($this->value)) {
			
			// If json deserialisation fails, then fallover to legacy format
			$result = json_decode($this->value);
			if($result !== false) {
				return $result;
			} else {
				// Parse data in legacy {comma} format
				$items = explode(',', $this->value);
				return str_replace('{comma}', ',', $items);
			}
		}
		
		return parent::getValueArray();
	}


	/**
	 * Save the current value of this CheckboxSetField into a DataObject.
	 * If the field it is saving to is a has_many or many_many relationship,
	 * it is saved by setByIDList(), otherwise it creates a comma separated
	 * list for a standard DB text/varchar field.
	 *
	 * @param DataObject $record The record to save into
	 */
	public function saveInto(DataObjectInterface $record) {
		$fieldname = $this->name;
		if(empty($fieldname) || empty($record)) return;
		
		$relation = $record->hasMethod($fieldname)
			? $record->$fieldname()
			: null;
		
		// Detect DB relation or field
		if($relation instanceof Relation) {
			// Save ids into relation
			$relation->setByIDList($this->getValueArray());
		} elseif($record->hasField($fieldname)) {
			// Save dataValue into field
			$record->$fieldname = $this->dataValue();
		}
	}
	
	public function getHasEmptyDefault() {
		// CheckboxSetField will ignore any attempt to assign a default 'blank' value
		return false;
	}
	
	/**
	 * Return the CheckboxSetField value as a string 
	 * selected item keys.
	 * 
	 * @return string
	 */
	public function dataValue() {
		// JSON Encode this string value
		$values = $this->getValueArray();
		return json_encode(array_values($values));
	}
	
	public function performDisabledTransformation() {
		$clone = clone $this;
		$clone->setDisabled(true);
		
		return $clone;
	}
	
	/**
	 * Transforms the source data for this CheckboxSetField
	 * into a comma separated list of values.
	 * 
	 * @return ReadonlyField
	 */
	public function performReadonlyTransformation() {
		$values = '';
		$data = array();
		
		$items = $this->value;
		if($this->source) {
			foreach($this->source as $source) {
				if(is_object($source)) {
					$sourceTitles[$source->ID] = $source->Title;
				}
			}
		}
		
		if($items) {
			// Items is a DO Set
			if($items instanceof SS_List) {
				foreach($items as $item) {
					$data[] = $item->Title;
				}
				if($data) $values = implode(', ', $data);
				
			// Items is an array or single piece of string (including comma seperated string)
			} else {
				if(!is_array($items)) {
					$items = preg_split('/ *, */', trim($items));
				}
				
				foreach($items as $item) {
					if(is_array($item)) {
						$data[] = $item['Title'];
					} elseif(is_array($this->source) && !empty($this->source[$item])) {
						$data[] = $this->source[$item];
					} elseif(is_a($this->source, 'SS_List')) {
						$data[] = $sourceTitles[$item];
					} else {
						$data[] = $item;
					}
				}
				
				$values = implode(', ', $data);
			}
		}
		
		$field = $this->castedCopy('ReadonlyField');
		$field->setValue($values);
		
		return $field;
	}

	public function Type() {
		return 'optionset checkboxset';
	}
	
}
