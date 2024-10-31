<?php


namespace Mcs\WpModels;

use Exception;
use Mcs\Interfaces\ProvinceNamesInterface;

class ProvinceNames extends BaseModel implements ProvinceNamesInterface {

	protected $properties = [
		'id',
		'province_id',
		'lang_code',
		'name'
	];

	public $province_id;
	public $lang_code;
	public $name;

	public static function getTableName(): string {
		return MCS_PREFIX . 'province_names';
	}

	public function getProperties(): array {
		return $this->properties;
	}

	/**
	 * @param int $countryId
	 * @param string $name
	 *
	 * @return ProvinceNames
	 * @throws Exception
	 */
	public static function findByName($countryId, $name) {
		global $wpdb;
		$model     = new static();
		$table     = ProvinceNames::getTableName();
		$provinceTable = Provinces::getTableName();
		$modelData = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} pn
					   LEFT JOIN {$provinceTable} p ON p.id = pn.province_id
					   WHERE pn.name = %s AND p.country_id = %d LIMIT 1",
				$name, $countryId
			), 'ARRAY_A'
		);
		if ( ! $modelData ) {
			throw new Exception( 'Not found' );
		}
		$model->fillProperties( $modelData );

		return $model;
	}
}
