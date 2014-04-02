<?php

/**
 * @package framework
 * @subpackage tests
 */
class NumericFieldTest extends SapphireTest {
	
	protected $usesDatabase = false;

	public function testValidator() {
		i18n::set_locale('en_US');

		$field = new NumericField('Number');
		$field->setValue('12.00');

		$validator = new RequiredFields('Number');
		$this->assertTrue($field->validate($validator));
		$this->assertEquals(12.0, $field->dataValue());

		$field->setValue('12,00');
		$this->assertFalse($field->validate($validator));

		// Treats '0' as given for the sake of required fields
		$field->setValue('0');
		$this->assertTrue($field->validate($validator));
		$this->assertEquals(0, $field->dataValue());

		// Should fail the 'required but not given' test
		$field->setValue('');
		$this->assertFalse($field->validate($validator));

		$field->setValue(false);
		$this->assertFalse($field->validate($validator));
		
		// Test german locale
		i18n::set_locale('de_DE');
		$field->setValue('12,00');
		$validator = new RequiredFields();
		$this->assertTrue($field->validate($validator));
		$this->assertEquals('12,00', $field->Value());
		$this->assertEquals(12.0, $field->dataValue());

		// Test forgiveness tolerance
		$field->setValue('12.00');
		$this->assertTrue($field->validate($validator));
		$this->assertEquals('12,00', $field->Value()); // converts decimal to comma
		$this->assertEquals(12.0, $field->dataValue());

		// Test finish locale
		i18n::set_locale('fi_FI');
		$field->setValue('12,00');
		$validator = new RequiredFields();
		$this->assertTrue($field->validate($validator));
		$this->assertEquals('12,00', $field->Value());
		$this->assertEquals(12.0, $field->dataValue());
		
		// Thousands separator
		$field->setValue('21 212,00');
		$validator = new RequiredFields();
		$this->assertTrue($field->validate($validator));
		$this->assertEquals('21 212,00', $field->Value());
		$this->assertEquals(21212.0, $field->dataValue());

		// Test forgiveness tolerance
		$field->setValue('12.00');
		$validator = new RequiredFields();
		$this->assertTrue($field->validate($validator));
		$this->assertEquals('12,00', $field->Value()); // converts decimal to comma
		$this->assertEquals(12.0, $field->dataValue());

		$field->setValue('21212,00');
		$validator = new RequiredFields();
		$this->assertTrue($field->validate($validator));
		$this->assertEquals(21212.0, $field->dataValue());
	}
}
