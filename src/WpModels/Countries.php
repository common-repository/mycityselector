<?php


namespace Mcs\WpModels;

use Mcs\Interfaces\CitiesInterface;
use Mcs\Interfaces\CountriesInterface;
use Mcs\Interfaces\DataInterface;

class Countries extends BaseModel implements CountriesInterface {

	protected $properties = [
		'id',
		'title',
		'subdomain',
		'published',
		'ordering',
		'code',
		'domain',
		'default_city_id'
	];

	public $title;
	public $subdomain;
	public $published;
	public $ordering;
	public $code;
	public $domain;
	public $default_city_id;

	public static function getTableName(): string {
		return MCS_PREFIX . 'countries';
	}

	public function getProperties(): array {
		return $this->properties;
	}

	public function delete(): bool {
		foreach ( $this->getCities() as $city ) {
			$city->delete();
		}
		foreach ( $this->getProvinces() as $province ) {
			$province->delete();
		}

		return parent::delete();
	}

	public function countProvinces() {
		global $wpdb;
		$tableName = Provinces::getTableName();

		return (int) $wpdb->get_var( $wpdb->prepare( "SELECT count(*) FROM {$tableName} WHERE country_id = %d",
			$this->id ) );
	}

	public function countCities() {
		global $wpdb;
		$tableName = Cities::getTableName();

		return (int) $wpdb->get_var( $wpdb->prepare( "SELECT count(*) FROM {$tableName} WHERE country_id = %d",
			$this->id ) );

	}

	/**
	 * @return Provinces[]
	 */
	public function getProvinces() {
		global $wpdb;
		$tableName  = Provinces::getTableName();
		$modelsData = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$tableName} WHERE country_id = %d",
				$this->id
			), 'ARRAY_A'
		);
		$result     = [];
		foreach ( $modelsData as $modelData ) {
			$model = new Provinces();
			$model->fillProperties( $modelData );
			$result[] = $model;
		}

		return $result;
	}

	/**
	 * @return Cities[]
	 */
	public function getCities() {
		global $wpdb;
		$tableName  = Cities::getTableName();
		$modelsData = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$tableName} WHERE country_id = %d",
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

	public function getDefaultCity(): CitiesInterface {
		global $wpdb;
		$tableName = Cities::getTableName();
		$modelData = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$tableName} WHERE id = %d limit 1",
				$this->default_city_id
			), 'ARRAY_A'
		);
		$model     = new Cities();
		$model->fillProperties( $modelData );

		return $model;
	}

	/**
	 * @inheritDoc
	 */
	public function getTitle(): string {
		return $this->title;
	}

	public function isPublished(): bool {
		return $this->published;
	}

	public function getType(): int {
		return DataInterface::LOCATION_TYPE_COUNTRY;
	}
}
