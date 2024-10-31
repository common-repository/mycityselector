<?php

namespace Mcs\Interfaces;

interface ProvinceFieldValuesInterface {
	public function getFieldId(): int;

	public function getFieldValueId(): int;

	public function getProvinceId(): int;

	public static function findForField( FieldsInterface $field, ProvincesInterface $province ): ?self;
}
