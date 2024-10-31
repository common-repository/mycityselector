<?php

namespace Mcs\Interfaces;

interface CountryFieldValuesInterface {
	public function getFieldId(): int;

	public function getFieldValueId(): int;

	public function getCountryId(): int;

	public static function findForField( FieldsInterface $field, CountriesInterface $country ): ?self;
}
