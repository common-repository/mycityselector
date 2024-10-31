<?php


namespace Mcs\WpModels;

use Exception;
use Mcs\Interfaces\CountriesInterface;
use Mcs\Interfaces\DataInterface;
use Mcs\Interfaces\ProvincesInterface;

class Provinces extends BaseModel implements ProvincesInterface {

	protected $properties = [
		'id',
		'title',
		'country_id',
		'subdomain',
		'published',
		'ordering'
	];

	public $title;
	public $country_id;
	public $subdomain;
	public $published;
	public $ordering;

	public static function getTableName(): string {
		return MCS_PREFIX . 'provinces';
	}

	public function getProperties(): array {
		return $this->properties;
	}

	/**
	 * @param int $countryId
	 * @param string $name
	 *
	 * @return static
	 * @throws Exception
	 */
	public static function findByName( $countryId, $name ) {
		global $wpdb;
		$model     = new static();
		$table     = $model->getTableName();
		$modelData = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table}
					   WHERE title = %s AND country_id = %d LIMIT 1",
				$name, $countryId
			), 'ARRAY_A'
		);
		if ( ! $modelData ) {
			throw new Exception( 'Not found' );
		}
		$model->fillProperties( $modelData );

		return $model;
	}

	/**
	 * @inheritDoc
	 */
	public function getTitle(): string {
		return $this->title;
	}

	/**
	 * @return Cities[]
	 */
	public function getCities(): array {
		global $wpdb;
		$tableName  = Cities::getTableName();
		$modelsData = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$tableName} WHERE province_id = %d",
				$this->id
			), 'ARRAY_A'
		);
		$result     = [];
		foreach ( $modelsData as $modelData ) {
			$model = new Cities();
			$model->fillProperties( $modelData );
			$result[] = $model;
		}

		return $result;
	}

	public function isPublished(): bool {
		return $this->published;
	}

	public function getCountry(): CountriesInterface {
		return Countries::findById( $this->country_id );
	}

	public function getType(): int {
		return DataInterface::LOCATION_TYPE_PROVINCE;
	}
}
