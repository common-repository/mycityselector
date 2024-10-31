<?php


use Mcs\Interfaces\CitiesInterface;
use Mcs\Interfaces\CityFieldValuesInterface;
use Mcs\Interfaces\CountriesInterface;
use Mcs\Interfaces\DataInterface;
use Mcs\Interfaces\FieldsInterface;
use Mcs\Interfaces\FieldValuesInterface;
use Mcs\Interfaces\ModelInterface;
use Mcs\Interfaces\ProvincesInterface;
use Mcs\WpModels\Cities;
use Mcs\WpModels\CityFieldValues;
use Mcs\WpModels\Countries;
use Mcs\WpModels\Fields;
use Mcs\WpModels\FieldValues;
use Mcs\WpModels\Provinces;
use Step\Acceptance\User;

class BaseCest {
	/**
	 * @var array|CitiesInterface[]|ModelInterface[]
	 */
	protected $cities = [];

	/**
	 * @var CitiesInterface
	 */
	protected $defaultCity;

	/**
	 * @var CitiesInterface
	 */
	protected $notDefaultCity;

	/**
	 * @var array|ProvincesInterface[]|ModelInterface[]
	 */
	protected $provinces = [];

	/**
	 * @var array|CountriesInterface[]|ModelInterface[]
	 */
	protected $countries = [];


	/**
	 * @var WP_Post
	 */
	protected $originalPost;

	/**
	 * @var FieldsInterface
	 */
	protected $field;

	/**
	 * @var FieldValuesInterface|ModelInterface
	 */
	protected $fieldValueDefault;

	/**
	 * @var FieldValuesInterface|ModelInterface
	 */
	protected $fieldValue;

	/**
	 * @var CityFieldValuesInterface|ModelInterface
	 */
	protected $cityFieldValue;


	/**
	 * @param User $I
	 *
	 * @throws Exception
	 */
	public function _before( User $I ) {
		$I->configurePlugin();
		$options = $I->getOptions();
		// USA
		$country           = Countries::create( [
			'title'     => 'USA',
			'subdomain' => 'usa',
			'published' => true,
			'code'      => 'us',
		] );
		$this->countries[] = $country;
		$province          = Provinces::create( [
			'title'      => 'NY State',
			'country_id' => $country->id,
			'subdomain'  => 'new-york-state',
			'published'  => true
		] );
		$this->provinces[] = $province;
		$this->defaultCity = Cities::create( [
			'title'       => 'New York',
			'country_id'  => $country->id,
			'province_id' => $province->id,
			'subdomain'   => 'new-york',
			'published'   => true
		] );
		$this->cities[]    = $this->defaultCity;

		$options->updateDefaultLocationType( DataInterface::LOCATION_TYPE_CITY );
		$options->updateDefaultLocationId( $this->defaultCity->id );

		$country->update( [
			'default_city_id' => $this->defaultCity->id
		] );

		$province             = Provinces::create( [
			'title'      => 'California',
			'country_id' => $country->id,
			'subdomain'  => 'california',
			'published'  => true
		] );
		$this->provinces[]    = $province;
		$this->notDefaultCity = Cities::create( [
			'title'       => 'Los Angeles',
			'country_id'  => $country->id,
			'province_id' => $province->id,
			'subdomain'   => 'los-angeles',
			'published'   => true
		] );

		$this->cities[] = $this->notDefaultCity;

		// Germany
		$country           = Countries::create( [
			'title'     => 'Germany',
			'subdomain' => 'germany',
			'published' => true,
			'code'      => 'de',
		] );
		$this->countries[] = $country;
		$province          = Provinces::create( [
			'title'      => 'Brandenburg',
			'country_id' => $country->id,
			'subdomain'  => 'brandenburg',
			'published'  => true
		] );
		$this->provinces[] = $province;

		$city           = Cities::create( [
			'title'       => 'Berlin',
			'country_id'  => $country->id,
			'province_id' => $province->id,
			'subdomain'   => 'berlin',
			'published'   => true
		] );
		$this->cities[] = $city;
		$country->update( [
			'default_city_id' => $city->id
		] );

		$province          = Provinces::create( [
			'title'      => 'Bayern',
			'country_id' => $country->id,
			'subdomain'  => 'bayern',
			'published'  => true
		] );
		$this->provinces[] = $province;

		$this->cities[] = Cities::create( [
			'title'       => 'Munchen',
			'country_id'  => $country->id,
			'province_id' => $province->id,
			'subdomain'   => 'munchen',
			'published'   => true
		] );

		$this->originalPost      = $I->getPost();
		$this->field             = Fields::create( [
			'name'      => 'test',
			'published' => true
		] );
		$this->fieldValueDefault = FieldValues::create( [
			'field_id' => $this->field->id,
			'value'    => uniqid(),
			'default'  => true
		] );
		$this->fieldValue        = FieldValues::create( [
			'field_id' => $this->field->id,
			'value'    => uniqid(),
			'default'  => false
		] );

		$this->cityFieldValue = CityFieldValues::create( [
			'field_id'       => $this->field->id,
			'field_value_id' => $this->fieldValue->id,
			'city_id'        => $this->notDefaultCity->getId()
		] );

	}

	/**
	 * @param User $I
	 *
	 * @throws Exception
	 */
	public function _after( User $I ) {
		$this->cityFieldValue->delete();
		$this->fieldValue->delete();
		$this->fieldValueDefault->delete();
		$this->field->delete();
		foreach ( $this->countries as $country ) {
			/** @var Countries $country */
			$country->update( [
				'id'              => $country->getId(),
				'default_city_id' => null
			] );
		}
		foreach ( $this->cities as $city ) {
			$city->delete();
		}
		$this->cities = [];
		foreach ( $this->provinces as $province ) {
			$province->delete();
		}
		$this->provinces = [];
		foreach ( $this->countries as $country ) {
			$country->delete();
		}
		$this->countries = [];
		$I->updatePost( $this->originalPost );
	}
}
