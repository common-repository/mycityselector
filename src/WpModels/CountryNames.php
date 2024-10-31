<?php


namespace Mcs\WpModels;

use Mcs\Interfaces\CountryNamesInterface;

class CountryNames extends BaseModel implements CountryNamesInterface {

	protected $properties = [
		'id',
		'country_id',
		'lang_code',
		'name'
	];

	public $country_id;
	public $lang_code;
	public $name;

	public static function getTableName(): string {
		return MCS_PREFIX . 'country_names';
	}

	public function getProperties(): array {
		return $this->properties;
	}
}
