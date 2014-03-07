<?php
/**
 * Dropdown field, created from a <select> tag.
 * 
 * <b>Setting a $has_one relation</b>
 * 
 * Using here an example of an art gallery, with Exhibition pages, 
 * each of which has a Gallery they belong to.  The Gallery class is also user-defined.
 * <code>
 * 	static $has_one = array(
 * 		'Gallery' => 'Gallery',
 * 	);
 * 
 * 	public function getCMSFields() {
 * 		$fields = parent::getCMSFields();
 * 		$field = new DropdownField('GalleryID', 'Gallery', Gallery::get()->map('ID', 'Title'));
 * 		$field->setEmptyString('(Select one)');
 * 		$fields->addFieldToTab('Root.Content', $field, 'Content');
 * </code>
 * 
 * As you see, you need to put "GalleryID", rather than "Gallery" here.
 * 
 * <b>Populate with Array</b>
 * 
 * Example model defintion:
 * <code>
 * class MyObject extends DataObject {
 *   static $db = array(
 *     'Country' => "Varchar(100)"
 *   );
 * }
 * </code>
 * 
 * Example instantiation:
 * <code>
 * new DropdownField(
 *   'Country',
 *   'Country',
 *   array(
 *     'NZ' => 'New Zealand',
 *     'US' => 'United States'
 *     'GEM'=> 'Germany'
 *   )
 * );
 * </code>
 * 
 * <b>Populate with Enum-Values</b>
 * 
 * You can automatically create a map of possible values from an {@link Enum} database column.
 * 
 * Example model definition:
 * <code>
 * class MyObject extends DataObject {
 *   static $db = array(
 *     'Country' => "Enum('New Zealand,United States,Germany','New Zealand')"
 *   );
 * }
 * </code>
 * 
 * Field construction:
 * <code>
 * new DropdownField(
 *   'Country',
 *   'Country',
 *   singleton('MyObject')->dbObject('Country')->enumValues()
 * );
 * </code>
 * 
 * <b>Disabling individual items</b>
 * 
 * Individual items can be disabled by feeding their array keys to setDisabledItems.
 * 
 * <code>
 * $DrDownField->setDisabledItems( array( 'US', 'GEM' ) );
 * </code>
 * 
 * @see CheckboxSetField for multiple selections through checkboxes instead.
 * @see ListboxField for a single <select> box (with single or multiple selections).
 * @see TreeDropdownField for a rich and customizeable UI that can visualize a tree of selectable elements
 * 
 * @package forms
 * @subpackage fields-basic
 */
class DropdownField extends SelectField {
	
	public function Field($properties = array()) {
		$options = array();
		$this->isSelected = false;
		foreach($this->getSource() as $value => $title) {
			$selected = false;
			if($value === '' && ($this->value === '' || $this->value === null)) {
				$selected = true;
			} else {
				// check against value, fallback to a type check comparison when !value
				if($value) {
					$selected = ($value == $this->value);
				} else {
					$selected = ($value === $this->value) || (((string) $value) === ((string) $this->value));
				}
			}
			if($selected) $this->isSelected = true;

			$disabled = false;
			if(in_array($value, $this->disabledItems) && $title != $this->emptyString ){
				$disabled = 'disabled';
			}

			$options[] = new ArrayData(array(
				'Title' => $title,
				'Value' => $value,
				'Selected' => $selected,
				'Disabled' => $disabled,
			));
		}

		$properties = array_merge($properties, array('Options' => new ArrayList($options)));

		return parent::Field($properties);
	}
}
