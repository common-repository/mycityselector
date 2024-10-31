<?php

namespace Mcs\WpModels;

use Exception;
use Mcs\Interfaces\FieldValuesInterface;
use Mcs\Interfaces\ModelInterface;

class FieldValues extends BaseModel implements FieldValuesInterface {

	/**
	 * @var int
	 */
	public $field_id;

	/**
	 * @var string
	 */
	public $value;

	/**
	 * @var boolean
	 */
	public $default;

	/**
	 * @var boolean
	 */
	public $is_ignore;

	public static function getTableName(): string {
		return MCS_PREFIX . 'field_values';
	}

	public function getProperties(): array {
		return [
			'id',
			'field_id',
			'value',
			'default',
			'is_ignore'
		];
	}

	public function getValue(): string {
		return $this->value;
	}

	public function isDefault() {
		return $this->default;
	}

	public function isIgnore() {
		return $this->is_ignore;
	}

	public function fillProperties( array $data ) {
		parent::fillProperties( $data );
		$this->default   = (bool) ($data['default'] ?? false);
		$this->is_ignore = (bool) ($data['is_ignore'] ?? false);
	}

	/**
	 * @param int $fieldId
	 *
	 * @return $this
	 * @throws Exception
	 */
	public static function findDefaultValue( int $fieldId ): FieldValues {
		global $wpdb;
		$model     = new static();
		$table     = $model->getTableName();
		$modelData = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE field_id = %d AND `default` LIMIT 1",
				$fieldId
			), 'ARRAY_A'
		);
		if ( ! $modelData ) {
			throw new Exception( 'Not found' );
		}
		$model->fillProperties( $modelData );

		return $model;
	}

	public function getFieldId(): int {
		return $this->field_id;
	}

	public function update( $data = [] ): ModelInterface {
		if ( $this->default == false && $data['default'] ) {
			global $wpdb;
			if ($wpdb->query(
				'update ' . self::getTableName() . ' set `default` = 0 where id != ' . (int)$this->id . ' and field_id = ' . (int)$this->field_id)
			    === false) {
				throw new Exception('Error updating default field value');
			}
		}

		return parent::update( $data );
	}
}
