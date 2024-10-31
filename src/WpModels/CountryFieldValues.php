<?php
namespace Mcs\WpModels;

use Mcs\Interfaces\CountriesInterface;
use Mcs\Interfaces\CountryFieldValuesInterface;
use Mcs\Interfaces\FieldsInterface;

class CountryFieldValues extends BaseModel implements CountryFieldValuesInterface {

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
	public $country_id;

	public static function getTableName(): string {
		return MCS_PREFIX . 'country_field_values';
	}

	public function getProperties(): array {
		return [
			'id',
			'field_id',
			'field_value_id',
			'country_id',
		];
	}

	public function getFieldId(): int {
		return $this->field_id;
	}

	public function getFieldValueId(): int {
		return $this->field_value_id;
	}

	public function getCountryId(): int {
		return $this->country_id;
	}

	public static function findForField( FieldsInterface $field, CountriesInterface $country ): ?CountryFieldValuesInterface {
		global $wpdb;
		$table      = static::getTableName();
		$modelData = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE country_id = %d AND field_id = %d",
				$country->getId(),
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
