<?php
namespace Mcs\WpModels;

use Mcs\Interfaces\FieldsInterface;
use Mcs\Interfaces\ProvinceFieldValuesInterface;
use Mcs\Interfaces\ProvincesInterface;

class ProvinceFieldValues extends BaseModel implements ProvinceFieldValuesInterface {

	/**
	 * @var
	 */
	public $field_id;

	/**
	 * @var int
	 */
	public $field_value_id;

	/**
	 * @var int
	 */
	public $province_id;

	public static function getTableName(): string {
		return MCS_PREFIX . 'province_field_values';
	}

	public function getProperties(): array {
		return [
			'id',
			'field_id',
			'field_value_id',
			'province_id',

		];
	}

	public function getFieldId(): int {
		return $this->field_id;
	}

	public function getFieldValueId(): int {
		return $this->field_value_id;
	}

	public function getProvinceId(): int {
		return $this->province_id;
	}

	public static function findForField( FieldsInterface $field, ProvincesInterface $province ): ?ProvinceFieldValuesInterface {
		global $wpdb;
		$table      = static::getTableName();
		$modelData = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE province_id = %d AND field_id = %d",
				$province->getId(),
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
