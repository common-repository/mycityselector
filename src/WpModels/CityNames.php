<?php


namespace Mcs\WpModels;

use Exception;
use Mcs\Interfaces\CityNamesInterface;

class CityNames extends BaseModel implements CityNamesInterface {

	protected $properties = [
		'id',
		'city_id',
		'lang_code',
		'name'
	];
	public $city_id;
	public $lang_code;
	public $name;

	public static function getTableName(): string {
		return MCS_PREFIX . 'city_names';
	}

	public function getProperties(): array {
		return $this->properties;
	}

	/**
	 * @param int $countryId
	 * @param int $provinceId
	 * @param string $name
	 *
	 * @return static
	 * @throws Exception
	 */
	public static function findByName($countryId, $provinceId, $name) {
		global $wpdb;
		$model     = new static();
		$table     = $model->getTableName();
		$cityTable = Cities::getTableName();
		$modelData = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} cn
					   LEFT JOIN {$cityTable} c ON cn.city_id = c.id
					   WHERE cn.name = %s AND c.country_id = %d AND c.province_id = %d LIMIT 1",
				$name, $countryId, $provinceId
			), 'ARRAY_A'
		);
		if ( ! $modelData ) {
			throw new Exception( 'Not found' );
		}
		$model->fillProperties( $modelData );

		return $model;
	}
}
