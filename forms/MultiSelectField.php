<?php

/**
 * Represents a SelectField that may potentially have multiple selections, and may have
 * a {@link ManyManyList} as a data source.
 */
abstract class MultiSelectField extends SelectField {

	/**
	 * Extracts the value of this field, normalised as an array.
	 * Scalar values will return a single length array, even if empty
	 * 
	 * @return array List of values as an array
	 */
	public function getValueArray() {
		
		// Direct array
		if(is_array($this->value)) return $this->value;
		
		// Extract lists
		if($this->value instanceof SS_List) {
			return $this->value->column('ID');
		}
		
		return array(trim($this->value));
	}
	
}
