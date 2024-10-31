<?php

namespace Mcs\Interfaces;

use Mcs\WpModels\FieldValues;

interface FieldValuesInterface {

	public static function findDefaultValue( int $fieldId ): FieldValues;

	public function getValue(): string;

	public function delete(): bool;

	public function getFieldId(): int;
}
