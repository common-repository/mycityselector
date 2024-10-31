<?php


namespace Mcs\WpModels;

use Exception;
use Mcs\Interfaces\CitiesInterface;
use Mcs\Interfaces\CountriesInterface;
use Mcs\Interfaces\DataInterface;
use Mcs\Interfaces\ProvincesInterface;

/**
 * Class Cities
 * @package Mcs\WpModels
 */
class Cities extends BaseModel implements CitiesInterface {

	protected $properties = [
		'id',
		'title',
		'country_id',
		'province_id',
		'subdomain',
		'published',
		'ordering',
	];

	public $title;
	public $country_id;
	public $province_id;
	public $subdomain;
	public $published;
	public $ordering;

	public static function getTableName(): string {
		return MCS_PREFIX . 'cities';
	}

	public function getProperties(): array {
		return $this->properties;
	}

	public static function findByTitle( int $countryId, int $provinceId, string $title ): CitiesInterface {
		global $wpdb;
		$model     = new static();
		$table     = $model->getTableName();
		$modelData = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table}
					   WHERE title = %s AND country_id = %d AND province_id = %d LIMIT 1",
				$title, $countryId, $provinceId
			), 'ARRAY_A'
		);
		if ( ! $modelData ) {
			throw new Exception( 'Not found' );
		}
		$model->fillProperties( $modelData );

		return $model;
	}

	/**
	 * @throws Exception
	 */
	public function getProvince(): ProvincesInterface {
		return Provinces::findById( $this->province_id );
	}

	/**
	 * @throws Exception
	 */
	public function getCountry(): CountriesInterface {
		return Countries::findById( $this->country_id );
	}

	public function getTitle(): string {
		return $this->title;
	}

	public function getSubDomain(): ?string {
		return $this->subdomain;
	}

	public function isPublished(): bool {
		return $this->published;
	}

	public function getType(): int {
		return DataInterface::LOCATION_TYPE_CITY;
	}
}
