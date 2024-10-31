<?php

namespace Mcs\Interfaces;

interface FieldsInterface {

	public function getId(  ): int;

	/**
	 * @return FieldValuesInterface[]
	 */
	public function getFieldValues():array;

	public function getFieldValueForLocation(ModelInterface $location): ?string;

	public function getDefaultValue(): FieldValuesInterface;

	public function delete(  ): bool;
}
