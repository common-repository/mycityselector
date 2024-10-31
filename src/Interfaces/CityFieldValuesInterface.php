<?php

namespace Mcs\Interfaces;

interface CityFieldValuesInterface {
	public function getFieldId(): int;

	public function getFieldValueId(): int;

	public function getCityId(): int;

	public static function findForField( FieldsInterface $field, CitiesInterface $city ): ?self;

	public function delete(): bool;
}
