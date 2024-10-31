<?php

namespace Mcs\WpModels;

use Mcs\Interfaces\CitiesInterface;
use Mcs\Interfaces\CityFieldValuesInterface;
use Mcs\Interfaces\FieldsInterface;

class CityFieldValues extends BaseModel implements CityFieldValuesInterface {

	/**
	 * @var int
	 */
	public $field_id;

	/**
	 * @var int
	 */
	public $field_value_id;

	/**
	 * @var int
	 */
	public $city_id;

	public static function getTableName(): string {
		return MCS_PREFIX . 'city_field_values';
	}

	public function getProperties(): array {
		return [
			'id',
			'field_id',
			'field_value_id',
			'city_id',
		];
	}

	public function getFieldValueId(): int {
		return $this->field_value_id;
	}

	public function getCityId(): int {
		return $this->city_id;
	}

	public function getFieldId(): int {
		return $this->field_id;
	}

	public static function findForField( FieldsInterface $field, CitiesInterface $city ): ?CityFieldValuesInterface {
		global $wpdb;
		$table      = static::getTableName();
		$modelData = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE city_id = %d AND field_id = %d",
				$city->getId(),
				$field->getId()
			), 'ARRAY_A'
		);
		if ( ! empty( $modelData ) ) {
				$model = new static();
				$model->fillProperties( $modelData );
				return $model;
		}
		return null;
	}
}
