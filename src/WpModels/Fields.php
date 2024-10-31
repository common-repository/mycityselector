<?php

namespace Mcs\WpModels;

use Exception;
use Mcs\Interfaces\CitiesInterface;
use Mcs\Interfaces\CountriesInterface;
use Mcs\Interfaces\FieldsInterface;
use Mcs\Interfaces\FieldValuesInterface;
use Mcs\Interfaces\ModelInterface;
use Mcs\Interfaces\ProvincesInterface;

class Fields extends BaseModel implements FieldsInterface {

	protected $properties = [
		'id',
		'name',
		'published'
	];

	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var boolean;
	 */
	public $published;

	public static function getTableName(): string {
		return MCS_PREFIX . 'fields';
	}

	public function getProperties(): array {
		return [
			'id',
			'name',
			'published'
		];
	}

	public function getName() {
		return $this->name;
	}

	public function isPublished() {
		return $this->published;
	}

	/**
	 * @return FieldValues[]
	 */
	public function getFieldValues(): array {
		return FieldValues::findByPropertyValue( 'field_id', $this->id );
	}

	/**
	 * @throws Exception
	 */
	public function getDefaultValue(): FieldValuesInterface {
		return FieldValues::findDefaultValue( $this->id );
	}

	public function getFieldValueForLocation( ModelInterface $location ): string {
		if ( $location instanceof CitiesInterface ) {
			$cityFieldValue = CityFieldValues::findForField( $this, $location );
			if ( ! empty( $cityFieldValue ) ) {
				return FieldValues::findById( $cityFieldValue->getFieldValueId() )->getValue();
			}
		} elseif ( $location instanceof ProvincesInterface ) {
			$provinceFieldValue = ProvinceFieldValues::findForField($this, $location);
			if (!empty($provinceFieldValue)) {
				return FieldValues::findById( $provinceFieldValue->getFieldValueId() )->getValue();
			}
		} elseif ( $location instanceof CountriesInterface ) {
			$countryFieldValue = CountryFieldValues::findForField($this, $location);
			if (!empty($countryFieldValue)) {
				return FieldValues::findById( $countryFieldValue->getFieldValueId() )->getValue();
			}
		}
		try {
			return $this->getDefaultValue()->getValue();
		} catch ( Exception $exception ) {

		}

		return '';
	}
}
